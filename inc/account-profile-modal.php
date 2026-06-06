<?php
/**
 * Glandore – Account Profile Modal glue
 *
 * Responsibilities:
 * - Enqueue account-profile-modal.js on the front-end.
 * - Render the Account Profile modal template into wp_footer.
 * - Handle profile updates via admin-post.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue JS for the Account Profile modal.
 */
if ( ! function_exists( 'glandore_enqueue_account_profile_modal' ) ) {
	function glandore_enqueue_account_profile_modal() {
		if ( is_admin() ) {
			return;
		}

		$theme_uri = get_template_directory_uri();

		wp_enqueue_script(
			'glandore-account-profile-modal',
			$theme_uri . '/assets/js/account-profile-modal.js',
			array(),
			'1.0',
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_account_profile_modal' );

/**
 * Render the Account Profile modal in the footer.
 */
if ( ! function_exists( 'glandore_render_account_profile_modal' ) ) {
	function glandore_render_account_profile_modal() {
		if ( is_admin() ) {
			return;
		}

		get_template_part( 'template_parts/modals/modal', 'account-profile' );
	}
}
add_action( 'wp_footer', 'glandore_render_account_profile_modal', 30 );

/**
 * Handle Account Profile updates from the front-end modal.
 */
if ( ! function_exists( 'glandore_handle_account_profile_update' ) ) {
	function glandore_handle_account_profile_update() {
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}

		// Nonce check.
		if (
			! isset( $_POST['glandore_profile_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['glandore_profile_nonce'] ) ),
				'glandore_update_account_profile'
			)
		) {
			$redirect = add_query_arg(
				array(
					'account_profile_status' => 'error',
					'account_profile_error'  => 'nonce',
				),
				wp_get_referer() ? wp_get_referer() : home_url( '/' )
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		$user_id = get_current_user_id();

		$display_name = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
		$first_name   = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last_name    = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$user_email   = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';

		// Basic validation.
		if ( '' === $display_name ) {
			$redirect = add_query_arg(
				array(
					'account_profile_status' => 'error',
					'account_profile_error'  => 'update_failed',
				),
				wp_get_referer() ? wp_get_referer() : home_url( '/' )
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		if ( '' === $user_email || ! is_email( $user_email ) ) {
			$redirect = add_query_arg(
				array(
					'account_profile_status' => 'error',
					'account_profile_error'  => 'invalid_email',
				),
				wp_get_referer() ? wp_get_referer() : home_url( '/' )
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		$userdata = array(
			'ID'           => $user_id,
			'display_name' => $display_name,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'user_email'   => $user_email,
		);

		$result = wp_update_user( $userdata );

		if ( is_wp_error( $result ) ) {
			$redirect = add_query_arg(
				array(
					'account_profile_status' => 'error',
					'account_profile_error'  => 'update_failed',
				),
				wp_get_referer() ? wp_get_referer() : home_url( '/' )
			);
		} else {
			$redirect = add_query_arg(
				array(
					'account_profile_status' => 'success',
				),
				wp_get_referer() ? wp_get_referer() : home_url( '/' )
			);
		}

		wp_safe_redirect( $redirect );
		exit;
	}
}
add_action( 'admin_post_glandore_update_account_profile', 'glandore_handle_account_profile_update' );
