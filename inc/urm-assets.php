<?php
/**
 * File: /wp-content/themes/glandore/inc/urm-assets.php
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_urm_password_toggle_assets() : void {

	if ( ! function_exists( 'user_registration' ) ) {
		return;
	}

	wp_enqueue_script(
		'glandore-urm-show-password',
		get_stylesheet_directory_uri() . '/js/urm-show-password.js',
		array(),
		'20260206-1',
		true
	);

	wp_enqueue_style(
		'glandore-urm-show-password',
		get_stylesheet_directory_uri() . '/css/urm-show-password.css',
		array(),
		'20260206-1'
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_urm_password_toggle_assets' );

function glandore_enqueue_urm_stage1_styles() : void {

	if ( ! is_page( 'registration-stage-1' ) ) {
		return;
	}

	wp_enqueue_style(
		'glandore-urm-stage1',
		get_stylesheet_directory_uri() . '/assets/css/urm-stage1.css',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_urm_stage1_styles' );

function glandore_enqueue_urm_account_css() : void {

	if ( ! is_page( 'account' ) ) {
		return;
	}

	$handle   = 'glandore-urm-account';
	$relative = '/assets/css/urm-account.css';
	$file     = get_stylesheet_directory() . $relative;

	if ( ! file_exists( $file ) ) {
		return;
	}

	wp_enqueue_style(
		$handle,
		get_stylesheet_directory_uri() . $relative,
		array(),
		filemtime( $file )
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_enqueue_urm_account_css', 99 );