<?php
/**
 * Account modal assets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_account_modal_assets() {
	$needs_modal =
		is_user_logged_in() ||
		isset( $_GET['account_modal'] ) ||
		isset( $_GET['cop_member_login'] );

	if ( ! $needs_modal ) {
		return;
	}

	wp_enqueue_script(
		'glandore-account-modal',
		get_stylesheet_directory_uri() . '/assets/js/account-modal.js',
		array(),
		'1.1',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'glandore_account_modal_assets' );