<?php
/**
 * File: /wp-content/themes/glandore/inc/turnstile.php
 *
 * Cloudflare Turnstile integration for Glandore.
 *
 * Protects:
 * - wp-login.php login form
 * - wp-login.php registration form
 * - wp-login.php lost password form
 * - custom theme forms when explicitly verified by handler
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_turnstile_enabled() : bool {
	return defined( 'GLANDORE_TURNSTILE_SITE_KEY' )
		&& defined( 'GLANDORE_TURNSTILE_SECRET_KEY' )
		&& GLANDORE_TURNSTILE_SITE_KEY
		&& GLANDORE_TURNSTILE_SECRET_KEY;
}

function glandore_turnstile_site_key() : string {
	return defined( 'GLANDORE_TURNSTILE_SITE_KEY' ) ? (string) GLANDORE_TURNSTILE_SITE_KEY : '';
}

function glandore_turnstile_secret_key() : string {
	return defined( 'GLANDORE_TURNSTILE_SECRET_KEY' ) ? (string) GLANDORE_TURNSTILE_SECRET_KEY : '';
}

function glandore_turnstile_enqueue_script() : void {
	if ( ! glandore_turnstile_enabled() ) {
		return;
	}

	wp_enqueue_script(
		'cloudflare-turnstile',
		'https://challenges.cloudflare.com/turnstile/v0/api.js',
		array(),
		null,
		true
	);
}
add_action( 'login_enqueue_scripts', 'glandore_turnstile_enqueue_script' );
add_action( 'wp_enqueue_scripts', 'glandore_turnstile_enqueue_script' );

function glandore_turnstile_render() : void {
	if ( ! glandore_turnstile_enabled() ) {
		return;
	}

	printf(
		'<div class="cf-turnstile" data-sitekey="%s" data-theme="auto" style="margin: 16px 0;"></div>',
		esc_attr( glandore_turnstile_site_key() )
	);
}

function glandore_turnstile_verify() : bool {
	if ( ! glandore_turnstile_enabled() ) {
		return true;
	}

	$token = isset( $_POST['cf-turnstile-response'] )
		? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) )
		: '';

	if ( '' === $token ) {
		return false;
	}

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'timeout' => 10,
			'body'    => array(
				'secret'   => glandore_turnstile_secret_key(),
				'response' => $token,
				'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	return is_array( $body ) && ! empty( $body['success'] );
}

function glandore_turnstile_error_message() : string {
	return __( 'Please complete the security check and try again.', 'glandore' );
}

/**
 * Native WordPress login form.
 */
add_action( 'login_form', 'glandore_turnstile_render' );

add_filter(
	'authenticate',
	function ( $user, $username, $password ) {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return $user;
		}

		if ( ! glandore_turnstile_verify() ) {
			return new WP_Error(
				'glandore_turnstile_failed',
				glandore_turnstile_error_message()
			);
		}

		return $user;
	},
	30,
	3
);

/**
 * Native WordPress registration form.
 */
add_action( 'register_form', 'glandore_turnstile_render' );

add_filter(
	'registration_errors',
	function ( $errors ) {
		if ( ! glandore_turnstile_verify() ) {
			$errors->add(
				'glandore_turnstile_failed',
				glandore_turnstile_error_message()
			);
		}

		return $errors;
	},
	30
);

/**
 * Native WordPress lost password form.
 */
add_action( 'lostpassword_form', 'glandore_turnstile_render' );

add_filter(
	'lostpassword_errors',
	function ( $errors ) {
		if ( ! glandore_turnstile_verify() ) {
			$errors->add(
				'glandore_turnstile_failed',
				glandore_turnstile_error_message()
			);
		}

		return $errors;
	},
	30
);

/**
 * Helper for custom form handlers.
 *
 * Usage inside any POST handler:
 *
 * if ( ! glandore_turnstile_require_valid_or_die() ) {
 *     return;
 * }
 */
function glandore_turnstile_require_valid_or_die() : bool {
	if ( glandore_turnstile_verify() ) {
		return true;
	}

	wp_die(
		esc_html( glandore_turnstile_error_message() ),
		esc_html__( 'Security check failed', 'glandore' ),
		array( 'response' => 403 )
	);
}

/**
 * Inject Turnstile into User Registration plugin forms
 */
add_action(
	'user_registration_after_form_fields',
	function () {
		if ( function_exists( 'glandore_turnstile_render' ) ) {
			glandore_turnstile_render();
		}
	}
);

/**
 * Validate User Registration form with Turnstile
 */
add_filter(
	'user_registration_validate_fields',
	function ( $errors, $data ) {

		if ( ! glandore_turnstile_verify() ) {
			$errors->add(
				'glandore_turnstile_failed',
				glandore_turnstile_error_message()
			);
		}

		return $errors;
	},
	10,
	2
);