<?php
/**
 * CoP contribution login redirect.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_cop_capture_contribution_return_to() {
	if ( is_admin() || empty( $_GET['cop_member_login'] ) || empty( $_GET['return_to'] ) ) {
		return;
	}

	$return_to = rawurldecode( sanitize_text_field( wp_unslash( $_GET['return_to'] ) ) );
	$return_to = wp_validate_redirect( $return_to, home_url( '/community-discussions/' ) );

	setcookie(
		'glandore_cop_return_to',
		rawurlencode( $return_to ),
		time() + HOUR_IN_SECONDS,
		COOKIEPATH ?: '/',
		COOKIE_DOMAIN,
		is_ssl(),
		true
	);
}
add_action( 'template_redirect', 'glandore_cop_capture_contribution_return_to', 1 );

function glandore_cop_login_redirect_after_member_login( $redirect_to, $requested_redirect_to, $user ) {
	if ( ! $user instanceof WP_User ) {
		return $redirect_to;
	}

	if ( empty( $_COOKIE['glandore_cop_return_to'] ) ) {
		return $redirect_to;
	}

	$return_to = rawurldecode( sanitize_text_field( wp_unslash( $_COOKIE['glandore_cop_return_to'] ) ) );
	$return_to = wp_validate_redirect( $return_to, home_url( '/community-discussions/' ) );

	setcookie(
		'glandore_cop_return_to',
		'',
		time() - HOUR_IN_SECONDS,
		COOKIEPATH ?: '/',
		COOKIE_DOMAIN,
		is_ssl(),
		true
	);

	if ( function_exists( 'glandore_cop_user_is_member_ready' ) && ! glandore_cop_user_is_member_ready( (int) $user->ID ) ) {
		return add_query_arg(
			array(
				'return_to' => rawurlencode( $return_to ),
			),
			home_url( '/induction/' )
		);
	}

	return $return_to;
}
add_filter( 'login_redirect', 'glandore_cop_login_redirect_after_member_login', 40, 3 );