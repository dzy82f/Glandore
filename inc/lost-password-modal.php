<?php
/**
 * Glandore – Lost Password Modal glue
 *
 * Responsibilities:
 * - Enqueue lost-password-modal.js on the front-end.
 * - Render the Lost Password modal template into wp_footer.
 * - Handle lost password requests via admin-post.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue JS for the Lost Password modal.
 */
if ( ! function_exists( 'glandore_enqueue_lost_password_modal' ) ) {
	function glandore_enqueue_lost_password_modal() {
		if ( is_admin() ) {
			return;
		}

		$theme_uri = get_template_directory_uri();

		wp_enqueue_script(
			'glandore-lost-password-modal',
			$theme_uri . '/assets/js/lost-password-modal.js',
			array(),
			'1.0',
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_lost_password_modal' );

/**
 * Render the Lost Password modal in the footer.
 */
if ( ! function_exists( 'glandore_render_lost_password_modal' ) ) {
	function glandore_render_lost_password_modal() {
		if ( is_admin() ) {
			return;
		}

		get_template_part( 'template_parts/modals/modal', 'lost-password' );
	}
}
add_action( 'wp_footer', 'glandore_render_lost_password_modal', 30 );

/**
 * Handle Lost Password requests from the front-end modal.
 */
if ( ! function_exists( 'glandore_handle_lost_password_request' ) ) {
	function glandore_handle_lost_password_request() {
		// Nonce check.
		if (
			! isset( $_POST['glandore_lost_password_nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['glandore_lost_password_nonce'] ) ),
				'glandore_lost_password_request'
			)
		) {
			$redirect = add_query_arg(
				array(
					'lost_password_status' => 'error',
					'lost_password_error'  => 'nonce',
				),
				wp_get_referer() ? wp_get_referer() : home_url( '/' )
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		$user_login = isset( $_POST['user_login'] ) ? sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) : '';

		if ( '' === $user_login ) {
			$redirect = add_query_arg(
				array(
					'lost_password_status' => 'error',
					'lost_password_error'  => 'empty',
				),
				wp_get_referer() ? wp_get_referer() : home_url( '/' )
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		// Use WordPress core lost password flow.
		$result = retrieve_password( $user_login );

		if ( is_wp_error( $result ) ) {
			$redirect = add_query_arg(
				array(
					'lost_password_status' => 'error',
					'lost_password_error'  => 'failed',
				),
				wp_get_referer() ? wp_get_referer() : home_url( '/' )
			);
		} else {
			$redirect = add_query_arg(
				array(
					'lost_password_status' => 'success',
				),
				wp_get_referer() ? wp_get_referer() : home_url( '/' )
			);
		}

		wp_safe_redirect( $redirect );
		exit;
	}
}
add_action( 'admin_post_nopriv_glandore_lost_password_request', 'glandore_handle_lost_password_request' );
add_action( 'admin_post_glandore_lost_password_request', 'glandore_handle_lost_password_request' );
