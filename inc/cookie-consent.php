<?php
/**
 * Cookie consent: banner + modal glue
 * Theme: Glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue cookie-consent.js
 */
function glandore_cookie_consent_enqueue() {

	$relative = '/assets/js/cookie-consent.js';
	$file     = get_stylesheet_directory() . $relative;

	if ( ! file_exists( $file ) ) {
		return;
	}

	wp_enqueue_script(
		'glandore-cookie-consent',
		get_stylesheet_directory_uri() . $relative,
		[],
		filemtime( $file ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_cookie_consent_enqueue' );

/**
 * Output banner + modal markup in the footer
 */
function glandore_cookie_consent_markup() {
	?>

	<!-- Cookie banner -->
	<div id="cookie-banner" class="cookie-banner" hidden>
		<p>Glandore uses essential cookies and privacy-conscious analytics.</p>

		<div class="cookie-banner-actions">
			<button type="button" data-cookie-action="accept-analytics">
				Accept analytics
			</button>

			<button type="button" data-cookie-action="manage-preferences">
				Manage cookies
			</button>
		</div>
	</div>

	<?php
	// Cookie preferences modal:
	// template_parts/modals/cookies-modal.php
	get_template_part( 'template_parts/modals/cookies-modal' );
}
add_action( 'wp_footer', 'glandore_cookie_consent_markup' );
