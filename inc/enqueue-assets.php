<?php
/**
 * File: /wp-content/themes/glandore/inc/enqueue-assets.php
 *
 * Purpose:
 * - Single, deterministic front-end asset pipeline
 * - Register canonical base style handle expected by older modules
 * - Ensure cop.css loads LAST
 * - Enqueue front-page-only home carousel JS when present
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'glandore_enqueue_assets', 1000 );

function glandore_enqueue_assets() : void {

	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();

	// -----------------------------
	// 1) Main theme stylesheet
	// -----------------------------
	$style_path = $theme_dir . '/style.css';

	if ( file_exists( $style_path ) ) {
		wp_register_style(
			'glandore-style',
			$theme_uri . '/style.css',
			array(),
			filemtime( $style_path )
		);

		wp_enqueue_style( 'glandore-style' );
	}

	// Backward-compatible alias for code that still depends on glandore-style-css.
	if ( wp_style_is( 'glandore-style', 'registered' ) && ! wp_style_is( 'glandore-style-css', 'registered' ) ) {
		wp_register_style(
			'glandore-style-css',
			false,
			array( 'glandore-style' ),
			null
		);
		wp_enqueue_style( 'glandore-style-css' );
	}

	// -----------------------------
	// 2) Mobile layer
	// -----------------------------
	$mobile_rel  = '/assets/css/mobile.css';
	$mobile_path = $theme_dir . $mobile_rel;

	if ( file_exists( $mobile_path ) ) {
		wp_enqueue_style(
			'glandore-mobile-css',
			$theme_uri . $mobile_rel,
			array( 'glandore-style' ),
			filemtime( $mobile_path )
		);
	}

	// -----------------------------
	// 3) Front page featured carousel JS
	// -----------------------------
	if ( is_front_page() ) {
		$carousel_rel  = '/assets/js/home-carousel.js';
		$carousel_path = $theme_dir . $carousel_rel;

		if ( file_exists( $carousel_path ) ) {
			wp_enqueue_script(
				'glandore-home-carousel',
				$theme_uri . $carousel_rel,
				array(),
				filemtime( $carousel_path ),
				true
			);
		}
	}

	// -----------------------------
	// 4) CoP layer (MUST BE LAST)
	// -----------------------------
	$cop_rel  = '/assets/css/cop.css';
	$cop_path = $theme_dir . $cop_rel;

	wp_dequeue_style( 'glandore-cop-css' );
	wp_deregister_style( 'glandore-cop-css' );

	if ( file_exists( $cop_path ) ) {
		wp_enqueue_style(
			'glandore-cop-css',
			$theme_uri . $cop_rel,
			array_filter(
				array(
					'glandore-style',
					wp_style_is( 'glandore-mobile-css', 'registered' ) ? 'glandore-mobile-css' : null,
				)
			),
			filemtime( $cop_path )
		);
	}
}