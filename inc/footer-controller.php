<?php
/**
 * File: /wp-content/themes/glandore/inc/footer-controller.php
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_enqueue_footer_controller() : void {

	if ( ! is_front_page() ) {
		return;
	}

	$handle   = 'glandore-footer-controller';
	$relative = '/assets/js/footer-controller.js';
	$file     = get_stylesheet_directory() . $relative;

	if ( ! file_exists( $file ) ) {
		return;
	}

	wp_enqueue_script(
		$handle,
		get_stylesheet_directory_uri() . $relative,
		array(),
		filemtime( $file ),
		true
	);

	wp_localize_script(
		$handle,
		'glandoreAjax',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_footer_controller' );