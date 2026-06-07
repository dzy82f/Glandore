<?php
/**
 * Glandore – CoP Membership Gate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_cop_user_is_member_ready( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return false;
	}

	return (string) get_user_meta( $user_id, 'glandore_cop_about_complete', true ) === '1';
}

function glandore_cop_membership_url( $return_to = '' ) {
	if ( ! $return_to ) {
		$return_to = home_url( '/community-discussions/' );
	}

	if ( ! is_user_logged_in() ) {
		return add_query_arg(
			array(
				'cop_member_login' => '1',
				'return_to'        => rawurlencode( $return_to ),
			),
			home_url( '/community-discussions/' )
		);
	}

	if ( ! glandore_cop_user_is_member_ready() ) {
		return add_query_arg(
			array(
				'return_to' => rawurlencode( $return_to ),
			),
			home_url( '/induction/' )
		);
	}

	return $return_to;
}

function glandore_cop_require_membership( $return_to = '' ) {
	if ( glandore_cop_user_is_member_ready() ) {
		return;
	}

	wp_safe_redirect( glandore_cop_membership_url( $return_to ) );
	exit;
}

function glandore_cop_membership_get_return_to() {
	if ( empty( $_GET['return_to'] ) ) {
		return '';
	}

	$raw = rawurldecode( sanitize_text_field( wp_unslash( $_GET['return_to'] ) ) );

	return wp_validate_redirect( $raw, home_url( '/community-discussions/' ) );
}

function glandore_cop_membership_state() {
	if ( ! is_user_logged_in() ) {
		return 'anonymous';
	}

	if ( glandore_cop_user_is_member_ready() ) {
		return 'complete';
	}

	return 'induction';
}

function glandore_cop_membership_meta( $key, $default = '' ) {
	$value = get_user_meta( get_current_user_id(), $key, true );
	return '' !== $value ? $value : $default;
}