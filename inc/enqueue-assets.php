<?php
/**
 * Glandore – Asset Enqueue
 *
 * Rule: Do not break existing theme pipeline.
 * Only add Issue Studio JS safely.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'glandore_enqueue_assets', 20 );

function glandore_enqueue_assets() {

	$theme_uri  = get_stylesheet_directory_uri();
	$theme_path = get_stylesheet_directory();

	/*
	|--------------------------------------------------------------------------
	| Preserve existing theme styles
	|--------------------------------------------------------------------------
	*/

	wp_enqueue_style(
		'glandore-style',
		$theme_uri . '/style.css',
		array(),
		filemtime( $theme_path . '/style.css' )
	);

	/*
	|--------------------------------------------------------------------------
	| Load CoP CSS LAST (existing rule)
	|--------------------------------------------------------------------------
	*/

	$cop_css = $theme_path . '/assets/css/cop.css';

	if ( file_exists( $cop_css ) ) {
		wp_enqueue_style(
			'glandore-cop',
			$theme_uri . '/assets/css/cop.css',
			array( 'glandore-style' ),
			filemtime( $cop_css )
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Issue Studio Result JS (SAFE ADDITION ONLY)
	|--------------------------------------------------------------------------
	*/

	if ( is_page_template( 'page-issue-studio-result.php' ) ) {

		$js_file = $theme_path . '/assets/js/issue-studio-result.js';

		if ( file_exists( $js_file ) ) {
			wp_enqueue_script(
				'glandore-issue-studio-result',
				$theme_uri . '/assets/js/issue-studio-result.js',
				array(),
				filemtime( $js_file ),
				true
			);
		}
	}
}