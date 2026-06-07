<?php
/**
 * Glandore – Force logged-in, non-inducted users to induction.
 *
 * This catches URM/modal logins even when the normal WordPress login_redirect
 * filter is bypassed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_user_needs_induction(): bool {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	$user_id  = get_current_user_id();
	$complete = get_user_meta( $user_id, 'glandore_cop_about_complete', true );

	return (string) $complete !== '1';
}

function glandore_force_induction_redirect() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	if ( ! glandore_user_needs_induction() ) {
		return;
	}

	if ( is_page( 'induction' ) ) {
		return;
	}

	if ( is_page( 'logout' ) ) {
		return;
	}

	wp_safe_redirect( home_url( '/induction/' ) );
	exit;
}
add_action( 'template_redirect', 'glandore_force_induction_redirect', 1 );