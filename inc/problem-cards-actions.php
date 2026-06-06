<?php
/**
 * File: /wp-content/themes/glandore/inc/problem-cards-actions.php
 *
 * AJAX actions for DB-backed Problem Cards.
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_glandore_problem_card_save_step', 'glandore_problem_card_save_step_ajax' );
add_action( 'wp_ajax_nopriv_glandore_problem_card_save_step', 'glandore_problem_card_save_step_ajax' );

function glandore_problem_card_save_step_ajax() : void {
	check_ajax_referer( 'glandore_problem_card', 'nonce' );

	$card_id = isset( $_POST['card_id'] ) ? absint( $_POST['card_id'] ) : 0;
	$step_id = isset( $_POST['step_id'] ) ? absint( $_POST['step_id'] ) : 0;

	if ( ! $card_id || ! $step_id || ! glandore_problem_card_step_belongs_to_card( $step_id, $card_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid problem card step.' ), 400 );
	}

	$session = glandore_problem_card_get_or_create_session( $card_id );
	if ( ! $session ) {
		wp_send_json_error( array( 'message' => 'Unable to find or create session.' ), 500 );
	}

	$items = glandore_problem_card_get_items_for_step( $step_id );
	foreach ( $items as $item ) {
		$key = 'item_' . (int) $item->id;
		if ( isset( $_POST[ $key ] ) ) {
			glandore_problem_card_save_response( (int) $session->id, $step_id, (int) $item->id, $_POST[ $key ] );
		} elseif ( 'checkbox' === $item->item_type ) {
			glandore_problem_card_save_response( (int) $session->id, $step_id, (int) $item->id, array() );
		}
	}

	global $wpdb;
	$wpdb->update(
		glandore_problem_card_table( 'sessions' ),
		array( 'current_step_id' => $step_id ),
		array( 'id' => (int) $session->id ),
		array( '%d' ),
		array( '%d' )
	);

	wp_send_json_success(
		array(
			'message' => 'Step saved.',
		)
	);
}
