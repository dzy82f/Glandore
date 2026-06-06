<?php
/**
 * Glandore – Delete Account (page-based, hard delete only)
 *
 * Works with any page that:
 *  - Shows a form with:
 *      method="post"
 *      action="" (or current page URL)
 *      wp_nonce_field( 'glandore_delete_account_page', 'glandore_delete_account_nonce' )
 *  - Optional fields:
 *      name="glandore_delete_reason"
 *      name="glandore_delete_reason_comment"
 *
 * This handler:
 *  - Runs on template_redirect (front-end only)
 *  - Checks for POST + valid nonce
 *  - Hard-deletes the current user (with a self-delete capability shim)
 *  - Logs the “reason for leaving”
 *  - On success: logs out + redirects home
 *  - On failure: wp_die with a clear message
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TEMP capability shim: allow a user to delete themselves.
 * Only used during the delete call.
 */
function glandore_map_meta_cap_allow_self_delete( $caps, $cap, $user_id, $args ) {
	if ( 'delete_user' === $cap && ! empty( $args[0] ) ) {
		$target_id = (int) $args[0];

		// Allow only self-delete.
		if ( $user_id === $target_id ) {
			// 'exist' is a pseudo-cap that any real user has.
			return array( 'exist' );
		}
	}

	return $caps;
}

/**
 * Log why the user left.
 */
function glandore_delete_account_log_reason( $user_id, $reason_code, $reason_comment, $hard_deleted ) {
	$payload = array(
		'user_id'        => (int) $user_id,
		'reason_code'    => $reason_code ? (string) $reason_code : null,
		'reason_comment' => $reason_comment ? (string) $reason_comment : null,
		'hard_deleted'   => $hard_deleted ? true : false,
		'timestamp'      => gmdate( 'c' ),
	);

	error_log( '[GDEL_REASON] ' . wp_json_encode( $payload ) );
}

/**
 * Handle POST from *any* delete-account page form.
 *
 * We do NOT rely on a specific slug/template.
 * We only trigger when:
 *  - method is POST, AND
 *  - $_POST['glandore_delete_account_nonce'] is present and valid.
 */
function glandore_handle_delete_account_page() {
	// Only front-end.
	if ( is_admin() ) {
		return;
	}

	// Only care about POST requests.
	if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return;
	}

	// Only act if our nonce is present.
	if ( empty( $_POST['glandore_delete_account_nonce'] ) ) {
		return;
	}

	error_log( '[GDEL] handler entered' );

	// Must be logged in.
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	// Nonce check.
	if (
		! wp_verify_nonce(
			wp_unslash( $_POST['glandore_delete_account_nonce'] ),
			'glandore_delete_account_page'
		)
	) {
		wp_die(
			esc_html__( 'Security check failed while deleting your account.', 'glandore' ),
			esc_html__( 'Delete account', 'glandore' ),
			array( 'response' => 403 )
		);
	}

	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		wp_die(
			esc_html__( 'No user is logged in.', 'glandore' ),
			esc_html__( 'Delete account', 'glandore' ),
			array( 'response' => 400 )
		);
	}

	// Optional reason + comments.
	$reason_code    = isset( $_POST['glandore_delete_reason'] )
		? sanitize_text_field( wp_unslash( $_POST['glandore_delete_reason'] ) )
		: '';
	$reason_comment = isset( $_POST['glandore_delete_reason_comment'] )
		? wp_kses_post( wp_unslash( $_POST['glandore_delete_reason_comment'] ) )
		: '';

	// Make sure delete functions are available.
	if ( ! function_exists( 'wp_delete_user' ) ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
	}

	// Temporarily allow this user to delete themselves.
	add_filter( 'map_meta_cap', 'glandore_map_meta_cap_allow_self_delete', 10, 4 );

	if ( is_multisite() ) {
		$deleted = wpmu_delete_user( $user_id );
	} else {
		$deleted = wp_delete_user( $user_id );
	}

	remove_filter( 'map_meta_cap', 'glandore_map_meta_cap_allow_self_delete', 10 );

	$hard_deleted = (bool) $deleted;

	// Log the reason + result.
	glandore_delete_account_log_reason( $user_id, $reason_code, $reason_comment, $hard_deleted );

	if ( ! $hard_deleted ) {
		wp_die(
			esc_html__( 'Your account could not be deleted. Please contact the site owner.', 'glandore' ),
			esc_html__( 'Delete account', 'glandore' ),
			array( 'response' => 500 )
		);
	}

	// Successful hard delete: logout + redirect home.
	wp_clear_auth_cookie();
	wp_safe_redirect( home_url( '/' ) );
	exit;
}
add_action( 'template_redirect', 'glandore_handle_delete_account_page', 1 );
