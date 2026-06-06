<?php
/**
 * Template Name: CoP – Induction
 *
 * Community of Practice onboarding via a short induction form.
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Must be logged in for induction.
if ( ! is_user_logged_in() ) {
    wp_safe_redirect( wp_login_url( get_permalink() ) );
    exit;
}

get_header();

$user_id = get_current_user_id();

// Has this user already completed induction?
$is_complete = function_exists( 'glandore_cop_is_profile_complete' )
    ? glandore_cop_is_profile_complete( $user_id )
    : false;

// AJAX endpoint for JS.
$ajax_url = admin_url( 'admin-ajax.php' );

// Current bio for the review editor (fallback to original, if present).
$bio_current = get_user_meta( $user_id, 'cop_profile_bio', true );
if ( '' === $bio_current ) {
    $bio_current = get_user_meta( $user_id, 'cop_profile_bio_original', true );
}
?>

<main id="primary" class="site-main cop-dialogue-main">
    <div class="cop-dialogue-container">
        <div class="entry-content">

            <h3 class="cop-induction-heading">Induction</h3>

            <?php if ( $is_complete ) : ?>

                <p>
                    Induction is already complete, although it can be revisited if anything needs to be updated.
                    You can still refine how your short public bio is worded below.
                </p>

            <?php else : ?>

                <p>Hello, I’m Ariadne, Julian’s digital assistant.</p>

                <p>
                    As you're new to the community and want to post, he's asked me to help you prepare a short bio
                    to introduce yourself to the rest of us. The only reason to do this step is if you want to share
                    your thoughts with the community in your own posts — if you're mainly here to read, that’s fine
                    and you don't need to do anything more.
                </p>

                <p>
                    The questions below are straightforward. They help us understand who is here, what kind of work
                    they’re doing, and how we can support their interests.
                </p>

            <?php endif; ?>

            <form
                id="cop-induction-form"
                class="cop-induction-form"
                method="post"
                action=""
                data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
            >
                <?php wp_nonce_field( 'glandore_cop_induction', 'cop_induction_nonce' ); ?>

                <!-- Who you are -->
                <div class="field">
                    <label for="cop_who">How would you briefly describe who you are?</label>
                    <?php
                    wp_editor(
                        '',
                        'cop_who',
                        array(
                            'textarea_name' => 'who',
                            'media_buttons' => false,
                            'teeny'         => true,
                            'quicktags'     => false,
                            'textarea_rows' => 5,
                            'editor_height' => 200,
                        )
                    );
                    ?>
                </div>

                <p>and what's your role, industry and location?</p>

                <!-- Role / Industry / Location in one row -->
                <div class="field-row--ril">

                    <div class="field">
                        <label for="cop_role">Role</label>
                        <select id="cop_role" name="role">
                            <option value="">- Please select -</option>
                            <option value="Executive / C-suite">Executive / C-suite</option>
                            <option value="Senior leader / Director">Senior leader / Director</option>
                            <option value="Manager / Team lead">Manager / Team lead</option>
                            <option value="Consultant / Advisor">Consultant / Advisor</option>
                            <option value="Practitioner / Professional">Practitioner / Professional</option>
                            <option value="Researcher / Academic">Researcher / Academic</option>
                            <option value="Student / Early career">Student / Early career</option>
                            <option value="Retired / Emeritus">Retired / Emeritus</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="cop_industry">Industry</label>
                        <select id="cop_industry" name="industry">
                            <option value="">- Please select -</option>
                            <option value="Financials">Financials</option>
                            <option value="Basic Materials">Basic Materials</option>
                            <option value="Energy">Energy</option>
                            <option value="Consumer Defensive">Consumer Defensive</option>
                            <option value="Consumer Cyclical">Consumer Cyclical</option>
                            <option value="Telecoms">Telecoms</option>
                            <option value="Utilities">Utilities</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Industrials">Industrials</option>
                            <option value="Technology">Technology</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="cop_location">Location</label>
                        <select id="cop_location" name="location">
                            <option value="">- Please select -</option>
                            <option value="UK">UK</option>
                            <option value="Europe (non-UK)">Europe (non-UK)</option>
                            <option value="North America">North America</option>
                            <option value="Latin America">Latin America</option>
                            <option value="Africa">Africa</option>
                            <option value="Middle East">Middle East</option>
                            <option value="Asia">Asia</option>
                            <option value="Australasia">Australasia</option>
                            <option value="Other / mixed">Other / mixed</option>
                        </select>
                    </div>

                </div><!-- .field-row--ril -->

                <!-- Work -->
                <div class="field">
                    <label for="cop_work">How would you briefly describe the work you do?</label>
                    <?php
                    wp_editor(
                        '',
                        'cop_work',
                        array(
                            'textarea_name' => 'work',
                            'media_buttons' => false,
                            'teeny'         => true,
                            'quicktags'     => false,
                            'textarea_rows' => 5,
                            'editor_height' => 200,
                        )
                    );
                    ?>
                </div>

                <!-- Support / interests -->
                <div class="field">
                    <label for="cop_support">How are you hoping the community will serve your interests?</label>
                    <?php
                    wp_editor(
                        '',
                        'cop_support',
                        array(
                            'textarea_name' => 'support',
                            'media_buttons' => false,
                            'teeny'         => true,
                            'quicktags'     => false,
                            'textarea_rows' => 4,
                            'editor_height' => 100,
                        )
                    );
                    ?>
                </div>

                <!-- Anything else -->
                <div class="field">
                    <label for="cop_extra">Anything else you think is relevant</label>
                    <?php
                    wp_editor(
                        '',
                        'cop_extra',
                        array(
                            'textarea_name' => 'extra',
                            'media_buttons' => false,
                            'teeny'         => true,
                            'quicktags'     => false,
                            'textarea_rows' => 3,
                            'editor_height' => 100,
                        )
                    );
                    ?>
                </div>

                <!-- Agreements -->
                <div class="field">
                    <label>
                        <input type="checkbox" name="open_source" value="1" />
                        I understand all material on the site is shared under the Creative Commons Attribution-ShareAlike 4.0 licence.
                    </label>
                </div>

                <div class="field">
                    <label>
                        <input type="checkbox" name="profile_visible" value="1" />
                        I’m comfortable for this profile (excluding my email address) to be visible to other members.
                    </label>
                </div>

                <div class="field">
                    <label>
                        <input type="checkbox" name="ethos_accepted" value="1" />
                        I will treat this space as a place for thoughtful, good-faith conversation, not quick takes or advertising.
                    </label>
                </div>

                <div class="field cop-induction-actions">
                    <button type="submit">Send</button>
                </div>
            </form>

            <!-- Result / messages from the gatekeeper -->
            <div id="cop-induction-result" class="cop-induction-result"></div>

            <!-- Bio review block (TinyMCE) -->
            <div
                id="cop-bio-review-wrapper"
                class="cop-bio-review"
                <?php echo $is_complete ? '' : 'style="display:none"'; ?>
            >
                <p>
                    <strong>Review your bio.</strong>
                    This is how you will appear on the Contributors list. Julian is happy for you to adjust
                    the wording or pronouns, as long as it remains accurate and in good faith.
                </p>

                <?php
                wp_editor(
                    $bio_current,
                    'cop_bio_review',
                    array(
                        'textarea_name' => 'cop_bio_review',
                        'media_buttons' => false,
                        'teeny'         => true,
                        'quicktags'     => false,
                        'textarea_rows' => 7,
                        'editor_height' => 180,
                    )
                );
                ?>

                <div class="cop-bio-review-actions">
                    <button type="button" id="cop-bio-save" class="button button-secondary">
                        Save bio
                    </button>
                    <span id="cop-bio-save-status" class="cop-bio-save-status" aria-live="polite"></span>
                </div>
            </div>

        </div><!-- .entry-content -->
    </div><!-- .cop-dialogue-container -->
</main>

<?php
get_footer( 'cop-minimal' );   // uses footer-cop-minimal.php
