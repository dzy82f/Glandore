<?php
/**
 * Registration Modal – Script Enqueue
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_enqueue_registration_modal() {

	wp_enqueue_script(
		'glandore-registration-modal',
		get_stylesheet_directory_uri() . '/assets/js/registration-modal.js',
		array(),
		'20260501-2',
		true
	);
	
	wp_enqueue_script(
	'glandore-registration-next-steps',
	get_stylesheet_directory_uri() . '/assets/js/registration-next-steps.js',
	array(),
	'20260501-3',
	true
);

	wp_enqueue_script(
		'glandore-login-modal',
		get_stylesheet_directory_uri() . '/assets/js/login-modal.js',
		array(),
		'20260501-2',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_registration_modal' );