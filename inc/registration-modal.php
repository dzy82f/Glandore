<?php
/**
 * Registration Modal – Script + Styles Enqueue
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function glandore_enqueue_registration_modal() {

    // Only needed on the front page (landing) – adjust if you want it elsewhere.
    if ( ! is_front_page() ) {
        return;
    }

    // JS controller for the registration modal
    wp_enqueue_script(
        'glandore-registration-modal',
        get_stylesheet_directory_uri() . '/assets/js/registration-modal.js',
        array(),
        '20260109-2', // <-- bump this whenever you change the JS
        true
    );
	
	 wp_enqueue_script(
			'glandore-login-modal',
			get_stylesheet_directory_uri() . '/assets/js/login-modal.js',
			array(),
			'20260318-1',
			true
		);	

    // If you have a dedicated CSS file for the modal, enqueue it here.
    // wp_enqueue_style(
    //     'glandore-registration-modal',
    //     get_stylesheet_directory_uri() . '/assets/css/registration-modal.css',
    //     array(),
    //     '20260109-1'
    // );
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_registration_modal' );
