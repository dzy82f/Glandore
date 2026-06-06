<?php
/**
 * Glandore Access Gate
 *
 * Enforce "logged-in only" access for the site, while allowing
 * specific public entry points (registration, email verification, login).
 *
 * Rules:
 * - The whole site is members-only, except for a small set of public URLs.
 * - URM email-confirmation links (?ur_token=...) must NOT be intercepted.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detect URM email confirmation requests.
 *
 * Any request with ?ur_token=... is an email-confirmation click
 * from User Registration (URM). Access-gate must not intercept it.
 *
 * @return bool
 */
function glandore_is_urm_email_confirmation_request() {
	return isset( $_GET['ur_token'] );
}

/**
 * Normalise the current request path.
 *
 * - Strips query string.
 * - Ensures leading slash.
 * - Adds trailing slash for normal pages, but leaves *.php alone.
 *
 * @return string
 */
function glandore_get_request_path() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '/';
	$path = strtok( $uri, '?' ); // strip query string.

	if ( ! $path ) {
		$path = '/';
	}

	// Ensure leading slash.
	if ( $path[0] !== '/' ) {
		$path = '/' . ltrim( $path, '/' );
	}

	// Do not add trailing slash to .php endpoints.
	if ( substr( $path, -4 ) !== '.php' ) {
		$path = trailingslashit( $path );
	}

	return $path;
}

/**
 * Enforce members-only access, while allowing URM and login flows.
 */
function glandore_access_gate() {

	// Never interfere with admin, AJAX, REST, cron, or CLI.
	if (
		is_admin() ||
		wp_doing_ajax() ||
		wp_doing_cron() ||
		( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
		( defined( 'WP_CLI' ) && WP_CLI )
	) {
		return;
	}

	// Normalised request path.
	$request_path = glandore_get_request_path();

	/**
	 * URM email confirmation (?ur_token=...).
	 *
	 * URM will:
	 * - Validate the token
	 * - Mark the user as Approved / Active
	 * - Handle its own redirects (often to /account/ or a login prompt)
	 *
	 * Access-gate must not redirect or log the user out here.
	 */
	if ( glandore_is_urm_email_confirmation_request() ) {
		return;
	}

	/**
	 * Anonymous GET to /user-email-validation/ with no token:
	 * treat this as a request to open the registration modal on home.
	 */
	if (
		$request_path === '/user-email-validation/' &&
		! is_user_logged_in() &&
		isset( $_SERVER['REQUEST_METHOD'] ) &&
		'GET' === $_SERVER['REQUEST_METHOD']
	) {
		wp_safe_redirect( home_url( '/?registration_modal=1' ) );
		exit;
	}

	// If the user is logged in, access-gate has nothing more to do.
	// Further gating (e.g. profile completion) is handled elsewhere.
	if ( is_user_logged_in() ) {
		return;
	}

	// From here on, we are dealing with anonymous visitors.

	// Normalise home path (usually "/").
	$home_path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	if ( ! $home_path ) {
		$home_path = '/';
	}
	$home_path = trailingslashit( $home_path );

	/**
	 * Public entry points for anonymous users.
	 *
	 * - Home: marketing / landing
	 * - Login / logon: our own login page(s)
	 * - Register: URM registration form endpoints
	 * - User-email-validation: URM's own processing page
	 * - Lost/reset password
	 * - Core wp-login.php endpoint (used by wp_login_form etc.)
	 */
	$public_paths = array(
		$home_path,
		'/login/',                 // custom login page (if used).
		'/logon/',                 // alias.
		'/register/',              // custom register page.
		'/registration/',          // alias.
		'/user-registration/',     // URM endpoints.
		'/user-email-validation/', // URM's validation handler.
		'/lost-password/',
		'/reset-password/',
		'/wp-login.php',
	);

	if ( in_array( $request_path, $public_paths, true ) ) {
		return;
	}

	// Everything else: block anonymous access.
	wp_safe_redirect( home_url( '/' ) );
	exit;
}

add_action( 'template_redirect', 'glandore_access_gate', 9 );
