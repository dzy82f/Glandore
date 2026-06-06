<?php
/**
 * Registration update modal assets.
 *
 * @package Glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue JS for the registration update modal.
 */
function glandore_enqueue_registration_update_modal_script() {
	if ( is_user_logged_in() ) {
		wp_enqueue_script(
			'glandore-registration-update-modal',
			get_template_directory_uri() . '/assets/js/registration-update-modal.js',
			array(),
			'1.0',
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_registration_update_modal_script' );
