<?php
/**
 * Glandore – Issue Studio Artefacts
 *
 * Canonical architecture:
 * - User input → wp_sa_artefacts
 * - Selected perspectives → wp_sa_artefact_perspectives
 * - User comments → wp_sa_artefact_comments
 * - Generated analysis → wp_sa_artefact_snapshots
 * - Never overwrite thinking output; regeneration creates a new snapshot.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_init', 'glandore_sa_install_artefact_tables' );
add_action( 'admin_post_glandore_sa_submit', 'glandore_sa_handle_submit' );
add_action( 'admin_post_nopriv_glandore_sa_submit', 'glandore_sa_handle_submit' );
add_action( 'admin_post_glandore_sa_rerun_with_comment', 'glandore_sa_handle_rerun_with_comment' );
add_action( 'admin_post_nopriv_glandore_sa_rerun_with_comment', 'glandore_sa_handle_rerun_with_comment' );

function glandore_sa_install_artefact_tables() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$charset_collate    = $wpdb->get_charset_collate();
	$artefacts_table    = $wpdb->prefix . 'sa_artefacts';
	$perspectives_table = $wpdb->prefix . 'sa_artefact_perspectives';
	$snapshots_table    = $wpdb->prefix . 'sa_artefact_snapshots';
	$comments_table     = $wpdb->prefix . 'sa_artefact_comments';

	$sql = "
	CREATE TABLE {$artefacts_table} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id BIGINT UNSIGNED NULL,
		class_id BIGINT UNSIGNED NULL,
		issue_id BIGINT UNSIGNED NULL,
		artefact_title VARCHAR(255) NOT NULL,
		issue_description LONGTEXT NOT NULL,
		tried LONGTEXT NULL,
		success_failure LONGTEXT NULL,
		success_looks_like LONGTEXT NULL,
		other_relevant LONGTEXT NULL,
		status VARCHAR(50) NOT NULL DEFAULT 'active',
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		updated_at DATETIME NULL,
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY class_id (class_id),
		KEY issue_id (issue_id),
		KEY status (status)
	) {$charset_collate};

	CREATE TABLE {$perspectives_table} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		artefact_id BIGINT UNSIGNED NOT NULL,
		question_id BIGINT UNSIGNED NOT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY artefact_id (artefact_id),
		KEY question_id (question_id)
	) {$charset_collate};

	CREATE TABLE {$snapshots_table} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		artefact_id BIGINT UNSIGNED NOT NULL,
		snapshot_version VARCHAR(20) NOT NULL DEFAULT '1.0',
		snapshot_json LONGTEXT NOT NULL,
		snapshot_markdown LONGTEXT NOT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY artefact_id (artefact_id)
	) {$charset_collate};

	CREATE TABLE {$comments_table} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		artefact_id BIGINT UNSIGNED NOT NULL,
		snapshot_id BIGINT UNSIGNED NULL,
		user_id BIGINT UNSIGNED NULL,
		comment_html LONGTEXT NOT NULL,
		comment_text LONGTEXT NOT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY artefact_id (artefact_id),
		KEY snapshot_id (snapshot_id),
		KEY user_id (user_id)
	) {$charset_collate};
	";

	dbDelta( $sql );
}

function glandore_sa_handle_submit() {
	global $wpdb;

	if (
		! isset( $_POST['glandore_sa_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glandore_sa_nonce'] ) ), 'glandore_sa_submit' )
	) {
		wp_die( 'Security check failed.' );
	}

	$class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0;
	$issue_id = isset( $_POST['issue_id'] ) ? absint( $_POST['issue_id'] ) : 0;
	$boilerplate_artefact_id = isset( $_POST['boilerplate_artefact_id'] ) ? absint( $_POST['boilerplate_artefact_id'] ) : 0;

	$issue_description  = isset( $_POST['od_issue'] ) ? sanitize_textarea_field( wp_unslash( $_POST['od_issue'] ) ) : '';
	$tried              = isset( $_POST['od_tried'] ) ? sanitize_textarea_field( wp_unslash( $_POST['od_tried'] ) ) : '';
	$success_failure    = isset( $_POST['od_results'] ) ? sanitize_textarea_field( wp_unslash( $_POST['od_results'] ) ) : '';
	$success_looks_like = isset( $_POST['od_success'] ) ? sanitize_textarea_field( wp_unslash( $_POST['od_success'] ) ) : '';
	$other_relevant     = isset( $_POST['od_other'] ) ? sanitize_textarea_field( wp_unslash( $_POST['od_other'] ) ) : '';

	$perspectives = isset( $_POST['perspectives'] ) && is_array( $_POST['perspectives'] )
		? array_values( array_filter( array_map( 'absint', wp_unslash( $_POST['perspectives'] ) ) ) )
		: array();

	if ( empty( $issue_description ) ) {
		wp_die( 'Please describe the issue before submitting.' );
	}

	$artefacts_table    = $wpdb->prefix . 'sa_artefacts';
	$perspectives_table = $wpdb->prefix . 'sa_artefact_perspectives';

	$artefact_data = array(
		'user_id'            => get_current_user_id() ?: null,
		'class_id'           => $class_id ?: null,
		'issue_id'           => $issue_id ?: null,
		'artefact_title'     => wp_trim_words( $issue_description, 12, '…' ),
		'issue_description'  => $issue_description,
		'tried'              => $tried,
		'success_failure'    => $success_failure,
		'success_looks_like' => $success_looks_like,
		'other_relevant'     => $other_relevant,
		'status'             => 'active',
		'updated_at'         => current_time( 'mysql' ),
	);

	$artefact_formats = array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' );

	if ( $boilerplate_artefact_id > 0 ) {
		$existing_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$artefacts_table} WHERE id = %d LIMIT 1",
				$boilerplate_artefact_id
			)
		);

		if ( ! $existing_id ) {
			wp_die( 'The boilerplate artefact could not be found.' );
		}

		$updated = $wpdb->update(
			$artefacts_table,
			$artefact_data,
			array( 'id' => $boilerplate_artefact_id ),
			$artefact_formats,
			array( '%d' )
		);

		if ( false === $updated ) {
			wp_die( 'The boilerplate artefact could not be completed: ' . esc_html( $wpdb->last_error ) );
		}

		$artefact_id = $boilerplate_artefact_id;
	} else {
		$artefact_data['created_at'] = current_time( 'mysql' );

		$inserted = $wpdb->insert(
			$artefacts_table,
			$artefact_data,
			array_merge( $artefact_formats, array( '%s' ) )
		);

		if ( false === $inserted ) {
			wp_die( 'The artefact could not be saved: ' . esc_html( $wpdb->last_error ) );
		}

		$artefact_id = absint( $wpdb->insert_id );
	}

	$wpdb->delete(
		$perspectives_table,
		array( 'artefact_id' => $artefact_id ),
		array( '%d' )
	);

	foreach ( $perspectives as $question_id ) {
		if ( $question_id > 0 ) {
			$wpdb->insert(
				$perspectives_table,
				array(
					'artefact_id' => $artefact_id,
					'question_id' => $question_id,
				),
				array( '%d', '%d' )
			);
		}
	}

	$snapshot_id = glandore_sa_create_snapshot_row( $artefact_id );

	if ( ! $snapshot_id ) {
		wp_die( 'The artefact was saved, but the snapshot could not be created.' );
	}

	wp_safe_redirect( add_query_arg( 'artefact_id', $artefact_id, home_url( '/issue-studio-result/' ) ) );
	exit;
}

function glandore_sa_handle_rerun_with_comment() {
	global $wpdb;

	if (
		! isset( $_POST['glandore_sa_rerun_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['glandore_sa_rerun_nonce'] ) ), 'glandore_sa_rerun_with_comment' )
	) {
		wp_die( 'Security check failed.' );
	}

	$artefact_id = isset( $_POST['artefact_id'] ) ? absint( $_POST['artefact_id'] ) : 0;
	$snapshot_id = isset( $_POST['snapshot_id'] ) ? absint( $_POST['snapshot_id'] ) : 0;

	$comment_html = isset( $_POST['sa_comment'] ) ? wp_kses_post( wp_unslash( $_POST['sa_comment'] ) ) : '';
	$comment_text = trim( wp_strip_all_tags( $comment_html ) );

	if ( ! $artefact_id || '' === $comment_text ) {
		wp_die( 'Please add a comment before rerunning the analysis.' );
	}

	$comments_table = $wpdb->prefix . 'sa_artefact_comments';

	$wpdb->insert(
		$comments_table,
		array(
			'artefact_id'  => $artefact_id,
			'snapshot_id'  => $snapshot_id ?: null,
			'user_id'      => get_current_user_id() ?: null,
			'comment_html' => $comment_html,
			'comment_text' => $comment_text,
		),
		array( '%d', '%d', '%d', '%s', '%s' )
	);

	$comment_id = absint( $wpdb->insert_id );

	glandore_sa_create_snapshot_row(
		$artefact_id,
		$comment_text,
		$snapshot_id,
		$comment_id
	);

	wp_safe_redirect( add_query_arg( 'artefact_id', $artefact_id, home_url( '/issue-studio-result/' ) ) );
	exit;
}

function glandore_sa_create_snapshot_row( int $artefact_id, string $user_comment = '', int $previous_snapshot_id = 0, int $comment_id = 0 ) : int {
	global $wpdb;

	$snapshots_table = $wpdb->prefix . 'sa_artefact_snapshots';

	$snapshot = glandore_sa_generate_snapshot( $artefact_id, $user_comment, $previous_snapshot_id );

	if ( ! glandore_sa_snapshot_is_valid( $snapshot ) ) {
		$snapshot = glandore_sa_generate_fallback_snapshot(
			$artefact_id,
			$user_comment,
			$previous_snapshot_id,
			'The AI response could not be converted into a complete Issue Studio snapshot.'
		);
	}

	if ( ! is_array( $snapshot ) ) {
		return 0;
	}

	if ( empty( $snapshot['metadata'] ) || ! is_array( $snapshot['metadata'] ) ) {
		$snapshot['metadata'] = array();
	}

	$snapshot['metadata']['version']              = '1.0';
	$snapshot['metadata']['created_at']           = current_time( 'mysql' );
	$snapshot['metadata']['comment_id']           = $comment_id ?: null;
	$snapshot['metadata']['based_on_snapshot_id'] = $previous_snapshot_id ?: null;
	$snapshot['metadata']['input_mode']            = '' !== trim( $user_comment ) ? 'comment_as_submitted_information' : 'original_submission';

	$inserted = $wpdb->insert(
		$snapshots_table,
		array(
			'artefact_id'       => $artefact_id,
			'snapshot_version'  => '1.0',
			'snapshot_json'     => wp_json_encode( $snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
			'snapshot_markdown' => glandore_sa_snapshot_to_markdown( $snapshot ),
		),
		array( '%d', '%s', '%s', '%s' )
	);

	if ( false === $inserted ) {
		return 0;
	}

	return absint( $wpdb->insert_id );
}

function glandore_sa_snapshot_is_valid( $snapshot ) : bool {
	if ( ! is_array( $snapshot ) ) {
		return false;
	}

	if ( '' === glandore_sa_clean_text( $snapshot['overview'] ?? '' ) ) {
		return false;
	}

	if ( empty( $snapshot['perspectives'] ) || ! is_array( $snapshot['perspectives'] ) ) {
		return false;
	}

	foreach ( $snapshot['perspectives'] as $perspective ) {
		if ( ! is_array( $perspective ) ) {
			return false;
		}

		if ( '' === glandore_sa_clean_text( $perspective['question'] ?? '' ) ) {
			return false;
		}

		if ( '' === glandore_sa_clean_text( $perspective['analysis'] ?? '' ) ) {
			return false;
		}
	}

	return true;
}

function glandore_sa_generate_snapshot( $artefact_id, string $user_comment = '', int $previous_snapshot_id = 0 ) {
	global $wpdb;

	$artefacts_table    = $wpdb->prefix . 'sa_artefacts';
	$perspectives_table = $wpdb->prefix . 'sa_artefact_perspectives';
	$questions_table    = $wpdb->prefix . 'sa_perspective_questions';
	$snapshots_table    = $wpdb->prefix . 'sa_artefact_snapshots';

	$artefact = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$artefacts_table} WHERE id = %d", $artefact_id ),
		ARRAY_A
	);

	if ( ! $artefact ) {
		return false;
	}

	$questions = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT q.id, q.question_text
			 FROM {$perspectives_table} p
			 INNER JOIN {$questions_table} q ON q.id = p.question_id
			 WHERE p.artefact_id = %d
			 ORDER BY q.sort_order ASC, q.id ASC",
			$artefact_id
		),
		ARRAY_A
	);

	$previous_snapshot = '';

	if ( $previous_snapshot_id ) {
		$previous_snapshot = (string) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT snapshot_markdown
				 FROM {$snapshots_table}
				 WHERE id = %d AND artefact_id = %d",
				$previous_snapshot_id,
				$artefact_id
			)
		);
	}

	$prompt    = glandore_sa_build_prompt( $artefact, $questions, $user_comment, $previous_snapshot );
	$ai_result = glandore_sa_call_openai( $prompt );

	if ( ! is_array( $ai_result ) ) {
		return false;
	}

	$ai_result = glandore_sa_normalise_snapshot( $ai_result, $questions );

	$ai_result['input'] = glandore_sa_snapshot_input_block( $artefact, $user_comment );

	if ( empty( $ai_result['metadata'] ) || ! is_array( $ai_result['metadata'] ) ) {
		$ai_result['metadata'] = array();
	}

	return $ai_result;
}

function glandore_sa_generate_fallback_snapshot( int $artefact_id, string $user_comment = '', int $previous_snapshot_id = 0, string $reason = '' ) {
	global $wpdb;

	$artefacts_table    = $wpdb->prefix . 'sa_artefacts';
	$perspectives_table = $wpdb->prefix . 'sa_artefact_perspectives';
	$questions_table    = $wpdb->prefix . 'sa_perspective_questions';

	$artefact = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$artefacts_table} WHERE id = %d", $artefact_id ),
		ARRAY_A
	);

	if ( ! $artefact ) {
		return false;
	}

	$questions = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT q.id, q.question_text
			 FROM {$perspectives_table} p
			 INNER JOIN {$questions_table} q ON q.id = p.question_id
			 WHERE p.artefact_id = %d
			 ORDER BY q.sort_order ASC, q.id ASC",
			$artefact_id
		),
		ARRAY_A
	);

	$perspectives = array();

	foreach ( $questions as $question ) {
		$perspectives[] = array(
			'question' => $question['question_text'],
			'analysis' => 'The rerun was requested, but a complete replacement analysis could not be generated in the required structure. The comment has been saved and the previous analysis remains preserved. Please rerun again, or adjust the comment to request a broader revised assessment.',
		);
	}

	if ( empty( $perspectives ) ) {
		$perspectives[] = array(
			'question' => 'Perspective analysis',
			'analysis' => 'No selected perspective questions were found for this artefact.',
		);
	}

	return array(
		'input' => glandore_sa_snapshot_input_block( $artefact, $user_comment ),
		'overview' => 'A rerun was requested, but Issue Studio could not produce a complete revised analysis in the required structure.',
		'perspectives' => $perspectives,
		'ideas_for_consideration' => array(
			'Review the saved comment and rerun the analysis.',
			'If the comment refers to one perspective only, ask for a complete refreshed assessment as well as deeper treatment of that perspective.',
			'The original snapshot has not been overwritten.',
		),
		'metadata' => array(
			'fallback' => true,
			'fallback_reason' => $reason,
			'user_comment' => $user_comment,
			'based_on_snapshot_id' => $previous_snapshot_id ?: null,
		),
	);
}

function glandore_sa_normalise_snapshot( array $snapshot, array $questions = array() ) : array {
	$normalised = array(
		'overview' => '',
		'perspectives' => array(),
		'ideas_for_consideration' => array(),
		'metadata' => array(),
	);

	$normalised['overview'] = glandore_sa_clean_text(
		$snapshot['overview']
		?? $snapshot['summary']
		?? $snapshot['assessment']
		?? ''
	);

	$raw_perspectives = $snapshot['perspectives']
		?? $snapshot['perspective_analysis']
		?? $snapshot['analysis']
		?? array();

	if ( is_string( $raw_perspectives ) ) {
		$raw_perspectives = array(
			array(
				'question' => 'Perspective analysis',
				'analysis' => $raw_perspectives,
			),
		);
	}

	if ( is_array( $raw_perspectives ) ) {
		foreach ( $raw_perspectives as $index => $perspective ) {
			if ( is_string( $perspective ) ) {
				$question = $questions[ $index ]['question_text'] ?? 'Perspective';
				$analysis = $perspective;
			} elseif ( is_array( $perspective ) ) {
				$question = $perspective['question']
					?? $perspective['title']
					?? $perspective['lens']
					?? $perspective['perspective']
					?? ( $questions[ $index ]['question_text'] ?? 'Perspective' );

				$analysis = $perspective['analysis']
					?? $perspective['answer']
					?? $perspective['content']
					?? $perspective['response']
					?? '';
			} else {
				continue;
			}

			$question = glandore_sa_clean_text( $question );
			$analysis = glandore_sa_clean_text( $analysis );

			if ( '' !== $question && '' !== $analysis ) {
				$normalised['perspectives'][] = array(
					'question' => $question,
					'analysis' => $analysis,
				);
			}
		}
	}

	$raw_ideas = $snapshot['ideas_for_consideration']
		?? $snapshot['ideas']
		?? $snapshot['next_steps']
		?? $snapshot['recommendations']
		?? array();

	if ( is_string( $raw_ideas ) ) {
		$raw_ideas = array( $raw_ideas );
	}

	if ( is_array( $raw_ideas ) ) {
		foreach ( $raw_ideas as $idea ) {
			$idea_text = glandore_sa_clean_text( $idea );

			if ( '' !== $idea_text ) {
				$normalised['ideas_for_consideration'][] = $idea_text;
			}
		}
	}

	if ( empty( $normalised['overview'] ) && ! empty( $normalised['perspectives'][0]['analysis'] ) ) {
		$normalised['overview'] = wp_trim_words( $normalised['perspectives'][0]['analysis'], 70, '…' );
	}

	if ( empty( $normalised['ideas_for_consideration'] ) ) {
		$normalised['ideas_for_consideration'][] = 'Use the revised assessment to identify the most useful next conversation or action.';
	}

	if ( isset( $snapshot['metadata'] ) && is_array( $snapshot['metadata'] ) ) {
		$normalised['metadata'] = $snapshot['metadata'];
	}

	return $normalised;
}

function glandore_sa_clean_text( $value ) : string {
	if ( is_array( $value ) || is_object( $value ) ) {
		$value = wp_json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}

	return trim( wp_strip_all_tags( (string) $value ) );
}

function glandore_sa_snapshot_input_block( $artefact, string $user_comment = '' ) {
	if ( '' !== trim( $user_comment ) ) {
		return array(
			'issue_description'  => $user_comment,
			'tried'              => '',
			'success_failure'    => '',
			'success_looks_like' => '',
			'other_relevant'     => 'This snapshot was refined from a user comment. The previous snapshot remains preserved as context.',
		);
	}

	return array(
		'issue_description'  => $artefact['issue_description'] ?? '',
		'tried'              => $artefact['tried'] ?? '',
		'success_failure'    => $artefact['success_failure'] ?? '',
		'success_looks_like' => $artefact['success_looks_like'] ?? '',
		'other_relevant'     => $artefact['other_relevant'] ?? '',
	);
}

function glandore_sa_build_prompt( $artefact, $questions, string $user_comment = '', string $previous_snapshot = '' ) {
	$comment_as_input = '' !== trim( $user_comment );

	$submitted_issue = $comment_as_input
		? $user_comment
		: ( $artefact['issue_description'] ?? '' );

	$submitted_tried = $comment_as_input
		? ''
		: ( $artefact['tried'] ?? '' );

	$submitted_results = $comment_as_input
		? ''
		: ( $artefact['success_failure'] ?? '' );

	$submitted_success = $comment_as_input
		? ''
		: ( $artefact['success_looks_like'] ?? '' );

	$submitted_other = $comment_as_input
		? 'This issue version was refined from a user comment. Use the previous analysis only as background context.'
		: ( $artefact['other_relevant'] ?? '' );

	$question_lines = array();

	foreach ( $questions as $question ) {
		$question_lines[] = '- ' . $question['question_text'];
	}

	$rerun_block = '';

	if ( '' !== trim( $previous_snapshot ) || '' !== trim( $user_comment ) ) {
		$rerun_block = "

This is a refined issue version created from a user comment.

Previous analysis, for context only:
{$previous_snapshot}

User comment now treated as the latest submitted issue:
{$user_comment}

Use the comment as the primary submitted issue input. Use the previous analysis only to preserve continuity and avoid losing useful context. Do not merely append the comment. Produce a new complete analysis.";
	}

	return "
You are generating an Issue Studio artefact for Glandore Associates.

Use the submitted material and selected perspectives to produce:

1. A concise overview of the analysis.
2. Structured analysis under each selected perspective.
3. Practical 'Ideas for consideration' for next steps.

Rules:
- Do not mention Occam.
- Do not mention internal prompt mechanics.
- Do not overstate certainty.
- Use clear, serious, practical language.
- Treat the perspectives as the organising structure.
- Return valid JSON only.
- Always include every selected perspective in the returned JSON.
- Each perspective must include both \"question\" and \"analysis\".
{$rerun_block}

Submitted issue:
{$submitted_issue}

What has been tried:
{$submitted_tried}

Successes / failures:
{$submitted_results}

What success would look like:
{$submitted_success}

Anything else relevant:
{$submitted_other}

Selected perspectives:
" . implode( "\n", $question_lines ) . "

Return JSON in this exact structure:

{
  \"overview\": \"...\",
  \"perspectives\": [
    {
      \"question\": \"...\",
      \"analysis\": \"...\"
    }
  ],
  \"ideas_for_consideration\": [
    \"...\"
  ]
}
";
}

function glandore_sa_call_openai( $prompt ) {
	if ( ! defined( 'GLANDORE_OPENAI_API_KEY' ) || empty( GLANDORE_OPENAI_API_KEY ) ) {
		return false;
	}

	$model = defined( 'GLANDORE_OPENAI_MODEL' ) && GLANDORE_OPENAI_MODEL
		? GLANDORE_OPENAI_MODEL
		: 'gpt-5.4-mini';

	$response = wp_remote_post(
		'https://api.openai.com/v1/responses',
		array(
			'timeout' => 90,
			'headers' => array(
				'Authorization' => 'Bearer ' . GLANDORE_OPENAI_API_KEY,
				'Content-Type'  => 'application/json',
			),
			'body' => wp_json_encode(
				array(
					'model' => $model,
					'input' => $prompt,
				)
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$body_raw = wp_remote_retrieve_body( $response );
	$body     = json_decode( $body_raw, true );

	if ( ! is_array( $body ) ) {
		return false;
	}

	$text = glandore_sa_extract_openai_text( $body );

	if ( empty( $text ) ) {
		return false;
	}

	$data = glandore_sa_decode_json_from_text( $text );

	return is_array( $data ) ? $data : false;
}

function glandore_sa_extract_openai_text( array $body ) : string {
	if ( isset( $body['output_text'] ) && is_string( $body['output_text'] ) ) {
		return trim( $body['output_text'] );
	}

	$text_parts = array();

	if ( ! empty( $body['output'] ) && is_array( $body['output'] ) ) {
		foreach ( $body['output'] as $output_item ) {
			if ( empty( $output_item['content'] ) || ! is_array( $output_item['content'] ) ) {
				continue;
			}

			foreach ( $output_item['content'] as $content_item ) {
				if ( isset( $content_item['text'] ) && is_string( $content_item['text'] ) ) {
					$text_parts[] = $content_item['text'];
				}

				if ( isset( $content_item['output_text'] ) && is_string( $content_item['output_text'] ) ) {
					$text_parts[] = $content_item['output_text'];
				}
			}
		}
	}

	return trim( implode( "\n", $text_parts ) );
}

function glandore_sa_decode_json_from_text( string $text ) {
	$text = trim( $text );

	$data = json_decode( $text, true );

	if ( is_array( $data ) ) {
		return $data;
	}

	if ( preg_match( '/```(?:json)?\s*(.*?)\s*```/is', $text, $matches ) ) {
		$data = json_decode( trim( $matches[1] ), true );

		if ( is_array( $data ) ) {
			return $data;
		}
	}

	$first_brace = strpos( $text, '{' );
	$last_brace  = strrpos( $text, '}' );

	if ( false !== $first_brace && false !== $last_brace && $last_brace > $first_brace ) {
		$json = substr( $text, $first_brace, $last_brace - $first_brace + 1 );
		$data = json_decode( $json, true );

		if ( is_array( $data ) ) {
			return $data;
		}
	}

	return false;
}

function glandore_sa_snapshot_to_markdown( $snapshot ) {
	$markdown = "# Issue Studio Analysis\n\n";

	if ( ! empty( $snapshot['input'] ) ) {
		$markdown .= "## Submitted information\n\n";

		if ( ! empty( $snapshot['input']['issue_description'] ) ) {
			$markdown .= "### Issue\n\n" . $snapshot['input']['issue_description'] . "\n\n";
		}

		if ( ! empty( $snapshot['input']['tried'] ) ) {
			$markdown .= "### What has been tried\n\n" . $snapshot['input']['tried'] . "\n\n";
		}

		if ( ! empty( $snapshot['input']['success_failure'] ) ) {
			$markdown .= "### Successes and failures\n\n" . $snapshot['input']['success_failure'] . "\n\n";
		}

		if ( ! empty( $snapshot['input']['success_looks_like'] ) ) {
			$markdown .= "### What success would look like\n\n" . $snapshot['input']['success_looks_like'] . "\n\n";
		}

		if ( ! empty( $snapshot['input']['other_relevant'] ) ) {
			$markdown .= "### Anything else relevant\n\n" . $snapshot['input']['other_relevant'] . "\n\n";
		}
	}

	if ( ! empty( $snapshot['overview'] ) ) {
		$markdown .= "## Overview\n\n" . $snapshot['overview'] . "\n\n";
	}

	if ( ! empty( $snapshot['perspectives'] ) && is_array( $snapshot['perspectives'] ) ) {
		foreach ( $snapshot['perspectives'] as $perspective ) {
			$markdown .= "## " . ( $perspective['question'] ?? 'Perspective' ) . "\n\n";
			$markdown .= ( $perspective['analysis'] ?? '' ) . "\n\n";
		}
	}

	if ( ! empty( $snapshot['ideas_for_consideration'] ) && is_array( $snapshot['ideas_for_consideration'] ) ) {
		$markdown .= "## Ideas for consideration\n\n";

		foreach ( $snapshot['ideas_for_consideration'] as $idea ) {
			$markdown .= "- " . glandore_sa_clean_text( $idea ) . "\n";
		}
	}

	if ( ! empty( $snapshot['metadata']['fallback'] ) ) {
		$markdown .= "\n## Snapshot note\n\n";
		$markdown .= "This snapshot was created as a fallback because the rerun response could not be normalised into the expected Issue Studio structure.\n";
	}

	return $markdown;
}
/**
 * Issue Studio boilerplate handler.
 *
 * Creates a prefilled artefact shell, then redirects to Issue Input with
 * boilerplate_artefact_id so the user can review and edit before submission.
 */
add_action( 'admin_post_glandore_sa_boilerplate', 'glandore_sa_handle_boilerplate' );
add_action( 'admin_post_nopriv_glandore_sa_boilerplate', 'glandore_sa_handle_boilerplate' );

function glandore_sa_handle_boilerplate() : void {
	if (
		! isset( $_GET['glandore_sa_boilerplate_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_GET['glandore_sa_boilerplate_nonce'] ) ),
			'glandore_sa_boilerplate'
		)
	) {
		wp_die( esc_html__( 'Security check failed.', 'glandore' ) );
	}

	global $wpdb;

	$class_id = isset( $_GET['class_id'] ) ? absint( $_GET['class_id'] ) : 0;
	$issue_id = isset( $_GET['issue_id'] ) ? absint( $_GET['issue_id'] ) : 0;

	if ( ! $class_id || ! $issue_id ) {
		wp_safe_redirect( home_url( '/issue-studio/' ) );
		exit;
	}

	$classes_table   = $wpdb->prefix . 'sa_classes';
	$issues_table    = $wpdb->prefix . 'sa_issues';
	$artefacts_table = $wpdb->prefix . 'sa_artefacts';

	$class_title = (string) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT class_title FROM {$classes_table} WHERE id = %d AND status = 'active' LIMIT 1",
			$class_id
		)
	);

	$issue_title = (string) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT issue_title FROM {$issues_table} WHERE id = %d AND class_id = %d AND status = 'active' LIMIT 1",
			$issue_id,
			$class_id
		)
	);

	if ( '' === trim( $class_title ) || '' === trim( $issue_title ) ) {
		wp_safe_redirect( home_url( '/issue-studio/' ) );
		exit;
	}

	$user_id = get_current_user_id();

	$wpdb->insert(
		$artefacts_table,
		array(
			'user_id'              => $user_id ? $user_id : null,
			'class_id'             => $class_id,
			'issue_id'             => $issue_id,
			'artefact_title'       => $issue_title,
			'issue_description'    => 'The issue concerns ' . $issue_title . ' within the ' . $class_title . ' domain. Please describe what is happening, who is affected, where the pressure is showing and why this matters now.',
			'tried'                => 'Please describe what has already been tried, including formal actions, informal workarounds, previous initiatives, escalation routes, or attempts to improve the situation.',
			'success_failure'      => 'Please describe what has worked, what has not worked, and any unintended consequences that have emerged.',
			'success_looks_like'   => 'Please describe what a better outcome would look like in practical terms for patients, staff, leaders, partners, or the wider system.',
			'other_relevant'       => 'Please add any relevant context, constraints, risks, relationships, history, data, or local circumstances that may help Issue Studio understand the situation.',
			'status'               => 'draft',
			'created_at'           => current_time( 'mysql' ),
			'updated_at'           => current_time( 'mysql' ),
		),
		array(
			'%d',
			'%d',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		)
	);

	$artefact_id = (int) $wpdb->insert_id;

	$redirect_args = array(
		'class_id'                 => $class_id,
		'issue_id'                 => $issue_id,
		'boilerplate_artefact_id'  => $artefact_id,
	);

	if ( isset( $_GET['perspectives'] ) ) {
		$raw_perspectives = wp_unslash( $_GET['perspectives'] );

		if ( is_array( $raw_perspectives ) ) {
			$redirect_args['perspectives'] = array_map( 'absint', $raw_perspectives );
		}
	}

	wp_safe_redirect(
		add_query_arg(
			$redirect_args,
			home_url( '/issue-input/' )
		)
	);
	exit;
}
