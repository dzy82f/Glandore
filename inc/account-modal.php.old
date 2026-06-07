<?php
/**
 * Account dashboard modal – assets
 * - Enqueues JS for the Account modal
 * - Modal HTML is loaded via footer.php:
 *   get_template_part( 'template_parts/modals/modal-account' );
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue JS for the Account modal.
 */
if ( ! function_exists( 'glandore_account_modal_assets' ) ) {
	function glandore_account_modal_assets() {

		// Only needed for logged-in users.
		if ( ! is_user_logged_in() ) {
			return;
		}

		wp_enqueue_script(
			'glandore-account-modal',
			get_stylesheet_directory_uri() . '/assets/js/account-modal.js',
			array(),      // no deps – vanilla JS
			'1.0',
			true          // load in footer
		);
	}

	add_action( 'wp_enqueue_scripts', 'glandore_account_modal_assets' );
}
