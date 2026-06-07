<?php
/**
 * Template Name: Community Membership
 *
 * Contribution gate for CoP posting.
 *
 * Page slug: community-membership
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$return_to = function_exists( 'glandore_cop_membership_get_return_to' )
	? glandore_cop_membership_get_return_to()
	: '';

if ( ! $return_to ) {
	$return_to = home_url( '/community-discussions/' );
}

/**
 * Anonymous users:
 * send them to the home page registration modal.
 */
if ( ! is_user_logged_in() ) {
	wp_safe_redirect(
		add_query_arg(
			array(
				'registration_modal' => '1',
				'cop_return_to'      => rawurlencode( $return_to ),
			),
			home_url( '/' )
		)
	);
	exit;
}

/**
 * Logged-in but not inducted:
 * send them to the real induction page.
 */
if (
	function_exists( 'glandore_cop_user_is_member_ready' ) &&
	! glandore_cop_user_is_member_ready()
) {
	wp_safe_redirect(
		add_query_arg(
			array(
				'return_to' => rawurlencode( $return_to ),
			),
			home_url( '/induction/' )
		)
	);
	exit;
}

/**
 * Fully ready:
 * continue to intended action.
 */
wp_safe_redirect( $return_to );
exit;