<?php
// /wp-content/themes/glandore/inc/cop-privacy.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'glandore_cop_is_moderator' ) ) {
	/**
	 * MVP "moderator" check.
	 * Later this can become a proper capability/role.
	 */
	function glandore_cop_is_moderator() : bool {
		return current_user_can( 'manage_options' );
	}
}

if ( ! function_exists( 'glandore_cop_can_view_contribution' ) ) {
	/**
	 * Decide whether current user can view a contribution row.
	 *
	 * Expects properties: visibility, user_id, recipient_user_id.
	 */
	function glandore_cop_can_view_contribution( $row ) : bool {
		$vis = isset( $row->visibility ) ? (string) $row->visibility : 'public';

		if ( 'public' === $vis ) {
			return true;
		}

		if ( 'members' === $vis ) {
			return is_user_logged_in();
		}

		if ( 'moderators' === $vis ) {
			return glandore_cop_is_moderator();
		}

		if ( 'direct' === $vis ) {
			if ( glandore_cop_is_moderator() ) {
				return true;
			}

			$uid = get_current_user_id();
			$from = isset( $row->user_id ) ? (int) $row->user_id : 0;
			$to   = isset( $row->recipient_user_id ) ? (int) $row->recipient_user_id : 0;

			return ( $uid > 0 && ( $uid === $from || $uid === $to ) );
		}

		// Unknown visibility => safest default: hide unless moderator.
		return glandore_cop_is_moderator();
	}
}