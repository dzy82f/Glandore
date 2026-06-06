<?php
/**
 * Cookie banner + preferences modal
 * - Banner sits above footer
 * - Modal holds full preferences
 * - Matomo is loaded only if analytics consent is true
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue JS + provide config.
 */
function glandore_cookie_assets() {

	// Main behaviour script
	wp_enqueue_script(
		'glandore-cookies',
		get_stylesheet_directory_uri() . '/assets/js/cookies.js',
		[],
		'1.0',
		true
	);

	// Optional: separate CSS file, or keep in style.css
	// wp_enqueue_style(
	// 	'glandore-cookies',
	// 	get_stylesheet_directory_uri() . '/assets/css/cookies.css',
	// 	[],
	// 	'1.0'
	// );

	wp_localize_script(
		'glandore-cookies',
		'glandoreCookies',
		[
			// Toggle if Matomo is in play; JS will no-op if false.
			'matomoEnabled' => true,

			// Fill these with your actual Matomo details.
			'matomoUrl'      => 'https://matomo.example.com/', // trailing slash
			'matomoSiteId'   => '1',

			// For accessibility & storage keys.
			'storageKey'     => 'glandore_cookie_prefs',
			'bannerText'     => 'Glandore uses essential cookies and privacy-conscious analytics to understand how the site is used.',
		]
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_cookie_assets' );

/**
 * Output banner + modal shell at the end of the page.
 * This keeps the footer itself purely navigational.
 */
function glandore_cookie_shell() {
	get_template_part( 'template_parts/cookies-modal' );
}
add_action( 'wp_footer', 'glandore_cookie_shell' );
