<?php
/**
 * CoP – OpenAI gatekeeper for About-You profiles.
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get the OpenAI API key from constant or environment.
 *
 * @return string
 */
function glandore_cop_get_openai_api_key() {
    if ( defined( 'GL_OPENAI_API_KEY' ) && GL_OPENAI_API_KEY ) {
        return GL_OPENAI_API_KEY;
    }

    $env_key = getenv( 'OPENAI_API_KEY' );
    return $env_key ? $env_key : '';
}

/**
 * Call OpenAI to evaluate an About-You profile and generate a bio.
 *
 * Expected $profile structure:
 * [
 *   'opening'   => string,
 *   'region'    => string,
 *   'industry'  => string,
 *   'seniority' => string,
 *   'interests' => string,
 *   'extra'     => string,
 * ]
 *
 * Returns:
 * [
 *   'decision'    => 'pass'|'fail',
 *   'status'      => 'welcome'|'need_more_detail'|'not_suitable',
 *   'bio'         => 'Short third-person bio…',
 *   'explanation' => 'Model’s internal reasoning (not shown to users)',
 *   'questions'   => [ 'question 1', 'question 2', ... ],
 * ]
 * or a WP_Error on failure.
 *
 * @param array $profile
 * @return array|\WP_Error
 */
function glandore_cop_evaluate_about_you_profile( array $profile ) {

    $api_key = glandore_cop_get_openai_api_key();
    if ( ! $api_key ) {
        return new WP_Error( 'cop_no_api_key', 'OpenAI API key is not configured.' );
    }

    $system_instructions = <<<EOT
You are the friendly but serious gatekeeper for a Community of Practice on
organisational learning, complex systems, and AI-enabled practice.

You receive a JSON object describing a new member:
- opening: their free-text self-introduction to the community
- region: broad geographic region (not street-level detail)
- industry: high-level sector or domain
- seniority: broad level (e.g. executive, manager, consultant, student, etc.)
- interests: what they hope to learn or explore here
- extra: any additional context they chose to add

Your job is to:
1. Decide whether they have provided enough substance to let them start posting
   immediately ("pass") or whether they should be asked to elaborate a bit more
   before posting ("fail").
2. Provide a "status" code that refines this decision:
   - "welcome": they can start contributing; they are aligned and there is enough detail.
   - "need_more_detail": they appear aligned and genuine, but what they have written is too
     thin, vague, or generic for others to understand who they are and what they care about.
   - "not_suitable": they do not appear interested in genuine participation, or they describe
     a role or intent that is fundamentally misaligned with a reflective, good-faith
     Community of Practice.
3. Write a concise, warm third-person bio suitable for a public Contributors page.
4. Summarise briefly why you made that decision.
5. Pull out one to three genuine questions they seem to be carrying into the Community
   (what they are trying to figure out).

IMPORTANT: How to use STATUS and DECISION.

- If status is "welcome":
  - decision MUST be "pass".
- If status is "need_more_detail":
  - decision MUST be "fail".
- If status is "not_suitable":
  - decision MUST be "fail".

Use "not_suitable" when ANY of the following is true:

- They explicitly say they have no real interest in this Community or "so-called community
  of practice" and are only filling this in because they have to, as a joke, or to waste
  time.
- They describe themselves primarily in terms of harming, exploiting, or threatening others
  (for example: "I am a highwayman", "I rob people", "I threaten people with GBH", "I mug
  people for fun", "I enjoy hurting people", "I run scams", "I organise hate campaigns").
  Treat these as not suitable even if wrapped in humour or bravado.
- They describe a central identity or intent that is about trolling, creating chaos or
  disruption in the community rather than contribution.

Use "need_more_detail" when:

- They appear broadly aligned and genuine, but their introduction is so brief, generic, or
  vague that other members would not have a meaningful sense of who is speaking or what
  they hope to explore.

Use "welcome" when:

- They show at least a minimal but coherent sense of who they are and the work they do,
  and a plausible interest in learning, practice, or reflection that fits a Community of
  Practice, even if written in informal, humorous, or self-deprecating language.

Be welcoming and generous, but this is a serious Community, not a spam trap.

IMPORTANT OUTPUT FORMAT:
Respond ONLY with a single JSON object with the following keys:
- "decision": "pass" or "fail"
- "status": "welcome", "need_more_detail", or "not_suitable"
- "bio": a short third-person bio (max ~120 words)
- "explanation": one or two sentences explaining your decision (this is for admins, not for UI)
- "questions": an array of 1–3 short strings, each a question they are carrying

Do not include any other keys or any text outside the JSON object.
EOT;

    $user_payload = wp_json_encode( $profile );

    $request_body = array(
        'model'           => 'gpt-4.1-mini',
        'response_format' => array( 'type' => 'json_object' ),
        'messages'        => array(
            array(
                'role'    => 'system',
                'content' => $system_instructions,
            ),
            array(
                'role'    => 'user',
                'content' => $user_payload,
            ),
        ),
    );

    $response = wp_remote_post(
        'https://api.openai.com/v1/chat/completions',
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode( $request_body ),
            'timeout' => 20,
        )
    );

    if ( is_wp_error( $response ) ) {
        error_log( '[COP_OPENAI] HTTP error: ' . $response->get_error_message() );
        return $response;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );

    if ( ! is_array( $data ) || empty( $data['choices'][0]['message']['content'] ) ) {
        error_log( '[COP_OPENAI] Unexpected API response: ' . substr( $body, 0, 500 ) );
        return new WP_Error( 'cop_bad_api_response', 'Unexpected OpenAI API response.' );
    }

    $json_text = $data['choices'][0]['message']['content'];
    $result    = json_decode( $json_text, true );

    if ( ! is_array( $result ) ) {
        error_log( '[COP_OPENAI] Could not parse JSON result: ' . substr( $json_text, 0, 500 ) );
        return new WP_Error( 'cop_bad_json', 'Could not parse OpenAI JSON result.' );
    }

    $decision    = isset( $result['decision'] ) ? strtolower( trim( (string) $result['decision'] ) ) : 'fail';
    $status      = isset( $result['status'] ) ? strtolower( trim( (string) $result['status'] ) ) : 'need_more_detail';
    $bio         = isset( $result['bio'] ) ? trim( (string) $result['bio'] ) : '';
    $explanation = isset( $result['explanation'] ) ? trim( (string) $result['explanation'] ) : '';
    $questions   = isset( $result['questions'] ) && is_array( $result['questions'] )
        ? array_values( array_map( 'strval', $result['questions'] ) )
        : array();

    // Basic sanity normalisation.
    if ( ! in_array( $status, array( 'welcome', 'need_more_detail', 'not_suitable' ), true ) ) {
        $status = 'need_more_detail';
    }

    if ( 'welcome' === $status ) {
        $decision = 'pass';
    } elseif ( in_array( $status, array( 'need_more_detail', 'not_suitable' ), true ) ) {
        $decision = 'fail';
    }

    return array(
        'decision'    => $decision,
        'status'      => $status,
        'bio'         => $bio,
        'explanation' => $explanation,
        'questions'   => $questions,
    );
}

/**
 * Local fallback if the API is unavailable.
 *
 * Very simple: if total text length is above a threshold, "welcome", otherwise "need_more_detail".
 *
 * @param array $profile
 * @return array
 */
function glandore_cop_local_fallback_about_you( array $profile ) {
    $opening   = isset( $profile['opening'] ) ? trim( (string) $profile['opening'] ) : '';
    $interests = isset( $profile['interests'] ) ? trim( (string) $profile['interests'] ) : '';

    $min_chars = 400;
    $length_ok = ( strlen( $opening ) + strlen( $interests ) ) >= $min_chars;

    $status   = $length_ok ? 'welcome' : 'need_more_detail';
    $decision = $length_ok ? 'pass' : 'fail';

    $bio = sprintf(
        '%%NAME%% is a member of the Community of Practice based in %s and working in %s. '
        . 'They are particularly interested in %s.',
        $profile['region']    ?? 'an unspecified region',
        $profile['industry']  ?? 'an unspecified sector',
        $profile['interests'] ?? 'how complex systems and learning interact'
    );

    $explanation = $length_ok
        ? 'Approved using the local fallback rule: there is enough detail for others to understand who is speaking and what they care about.'
        : 'The local fallback rule would like a little more detail about background and what is hoped for here before posting.';

    $questions = array();
    if ( ! empty( $interests ) ) {
        $questions[] = 'How can the Community help explore: ' . $interests;
    }

    return array(
        'decision'    => $decision,
        'status'      => $status,
        'bio'         => $bio,
        'explanation' => $explanation,
        'questions'   => $questions,
    );
}
