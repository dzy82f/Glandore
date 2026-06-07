<?php
/**
 * Glandore – Registration Continuation Gate
 *
 * Responsibilities:
 *  - Handle submission of the Stage 2 profile completion form.
 *  - Enforce a "home-only until complete" rule for logged-in,
 *    non-admin users who are in continuation mode.
 *
 * Continuation mode is defined as:
 *  - glandore_show_completion_modal == "1"
 *  - glandore_profile_complete     != "1"
 *
 * NOTE:
 * - Login redirect to /?registration_continue=1 is handled by the MU-plugin:
 *   "Glandore – Completion Modal Login Redirect (v0_2 trace)".
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optional logger – only logs when WP_DEBUG_LOG is enabled.
 *
 * @param string $stage Short stage label.
 * @param array  $extra Additional context fields.
 */
function glandore_profile_gate_log( $stage, array $extra = array() ) {
	if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
		return;
	}

	$payload = array_merge(
		array(
			'stage' => $stage,
			'ts'    => gmdate( 'c' ),
		),
		$extra
	);

	error_log( '[GL_PROFILE_GATE] ' . wp_json_encode( $payload ) );
}

/**
 * Handle the Stage 2 profile completion form submission.
 *
 * Form is defined in modal-registration-completion.php and includes:
 *   wp_nonce_field( 'glandore_profile_completion', 'glandore_profile_nonce' );
 *
 * On success:
 *  - Saves glandore_profile_data
 *  - Sets glandore_profile_complete = 1
 *  - Sets glandore_show_completion_modal = 0
 *  - Re-asserts the auth cookie so the user stays logged in
 *  - Redirects to the clean home URL (/)
 */
function glandore_handle_profile_completion_form() {

	// Only care about POST requests.
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	// Only for logged-in users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	// Check the nonce exists.
	if ( empty( $_POST['glandore_profile_nonce'] ) ) {
		return;
	}

	$nonce = wp_unslash( $_POST['glandore_profile_nonce'] );

	if ( ! wp_verify_nonce( $nonce, 'glandore_profile_completion' ) ) {
		glandore_profile_gate_log(
			'bad_nonce',
			array(
				'user_id' => get_current_user_id(),
			)
		);

		// Fail safely: send them back home, still gated.
		wp_safe_redirect( home_url( '/?registration_continue=1' ) );
		exit;
	}

	$user_id = get_current_user_id();

	/**
	 * Collect and sanitise profile fields into a single array.
	 * Note: ai_framework is now an ARRAY of selected frameworks.
	 */
	// AI frameworks – multi-select.
	$ai_frameworks_raw = array();
	if ( isset( $_POST['multi_select'] ) && is_array( $_POST['multi_select'] ) ) {
		$ai_frameworks_raw = wp_unslash( $_POST['multi_select'] ); // handles arrays
	}

	$ai_frameworks = array_values(
		array_filter(
			array_map( 'sanitize_text_field', $ai_frameworks_raw ),
			'strlen'
		)
	);

	$profile_data = array(
		'geography'        => isset( $_POST['dropdown'] )
			? sanitize_text_field( wp_unslash( $_POST['dropdown'] ) )
			: '',
		'geography_other'  => isset( $_POST['input_text'] )
			? sanitize_text_field( wp_unslash( $_POST['input_text'] ) )
			: '',
		'job_level'        => isset( $_POST['dropdown_1'] )
			? sanitize_text_field( wp_unslash( $_POST['dropdown_1'] ) )
			: '',
		'job_level_other'  => isset( $_POST['input_text_1'] )
			? sanitize_text_field( wp_unslash( $_POST['input_text_1'] ) )
			: '',
		'industry'         => isset( $_POST['dropdown_2'] )
			? sanitize_text_field( wp_unslash( $_POST['dropdown_2'] ) )
			: '',
		'industry_other'   => isset( $_POST['input_text_2'] )
			? sanitize_text_field( wp_unslash( $_POST['input_text_2'] ) )
			: '',
		'role_description' => isset( $_POST['description'] )
			? sanitize_textarea_field( wp_unslash( $_POST['description'] ) )
			: '',
		'site_value'       => isset( $_POST['description_1'] )
			? sanitize_textarea_field( wp_unslash( $_POST['description_1'] ) )
			: '',
		'ai_framework'     => $ai_frameworks, // now an array of selected values
	);

	update_user_meta( $user_id, 'glandore_profile_data', $profile_data );

	// Mark profile as complete and clear the login flag.
	update_user_meta( $user_id, 'glandore_profile_complete', 1 );
	update_user_meta( $user_id, 'glandore_show_completion_modal', 0 );

	// Make absolutely sure the user stays logged in and
	// session tokens are created/updated.
	if ( ! function_exists( 'wp_set_auth_cookie' ) ) {
		require_once ABSPATH . WPINC . '/pluggable.php';
	}

	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, false ); // false = session cookie

	glandore_profile_gate_log(
		'profile_completed',
		array(
			'user_id' => $user_id,
		)
	);

	// Redirect to the clean home URL (no registration_continue param).
	wp_safe_redirect( home_url( '/' ) );
	exit;
}
add_action( 'template_redirect', 'glandore_handle_profile_completion_form', 5 );

/**
 * Enforce "home-only until profile complete" for logged-in users
 * who are in continuation mode.
 *
 * Continuation mode:
 *   - glandore_show_completion_modal == "1"
 *   - glandore_profile_complete     != "1"
 */
function glandore_enforce_profile_completion_gate() {

	// Only gate logged-in users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	// Never gate admin-side or AJAX.
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	$user_id = get_current_user_id();

	// Never gate admins.
	if ( user_can( $user_id, 'manage_options' ) ) {
		return;
	}

	// Check completion + continuation flags.
	$complete_flag = get_user_meta( $user_id, 'glandore_profile_complete', true );
	$show_flag     = get_user_meta( $user_id, 'glandore_show_completion_modal', true );

	// If profile is complete OR continuation flag is not "1",
	// we do NOT apply the continuation gate.
	if ( '1' === (string) $complete_flag || '1' !== (string) $show_flag ) {
		return;
	}

	// At this point:
	// - logged in
	// - non-admin
	// - profile NOT complete
	// - continuation flag == "1"
	// => user is in continuation mode and should be gated.

	// Allow the front page to load (modal will be opened by JS when
	// redirected here with ?registration_continue=1 or by JS flag).
	if ( is_front_page() || is_home() ) {
		glandore_profile_gate_log(
			'allow_front_page_incomplete',
			array(
				'user_id' => $user_id,
			)
		);
		return;
	}

	// Any other URL → redirect to home with the trigger param.
	$target = home_url( '/?registration_continue=1' );

	glandore_profile_gate_log(
		'redirect_non_front_incomplete',
		array(
			'user_id' => $user_id,
			'target'  => $target,
		)
	);

	wp_safe_redirect( $target );
	exit;
}
add_action( 'template_redirect', 'glandore_enforce_profile_completion_gate', 20 );

/**
 * For incomplete users on the front page, tell JS to open
 * the registration completion modal automatically.
 *
 * This uses the existing window.GlandoreShowCompletionModal flag
 * that registration-completion-modal.js already understands.
 */
function glandore_flag_completion_modal_on_front_page() {

	// Only front-end.
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	// Only logged-in users.
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id = get_current_user_id();

	// Never bother admins.
	if ( user_can( $user_id, 'manage_options' ) ) {
		return;
	}

	// Only care if profile is NOT complete.
	$complete_flag = get_user_meta( $user_id, 'glandore_profile_complete', true );
	if ( '1' === (string) $complete_flag ) {
		return;
	}

	// Only front page / home (where the modal markup is rendered).
	if ( ! ( is_front_page() || is_home() ) ) {
		return;
	}

	// At this point:
	// - logged in
	// - non-admin
	// - profile incomplete
	// - on the home/front page
	//
	// Ensure the completion modal script is present and set the flag
	// BEFORE it runs, so it opens immediately.
	$js = 'window.GlandoreShowCompletionModal = true;';

	wp_add_inline_script(
		'glandore-registration-completion-modal',
		$js,
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_flag_completion_modal_on_front_page', 30 );
