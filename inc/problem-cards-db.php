<?php
/**
 * File: /wp-content/themes/glandore/inc/problem-cards-db.php
 *
 * Generic DB helpers for Glandore / Tychevia Problem Cards.
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_problem_card_table( string $type ) : string {
	global $wpdb;

	$map = array(
		'cards'         => 'problem_cards',
		'steps'         => 'problem_card_steps',
		'items'         => 'problem_card_step_items',
		'sessions'      => 'problem_card_sessions',
		'responses'     => 'problem_card_responses',
		'snapshots'     => 'problem_card_snapshots',
		'relationships' => 'problem_card_relationships',
	);

	return $wpdb->prefix . ( $map[ $type ] ?? $type );
}

function glandore_problem_card_get_by_slug( string $slug ) : ?object {
	global $wpdb;

	$table = glandore_problem_card_table( 'cards' );
	$card  = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE slug = %s AND status = 'published' LIMIT 1",
			$slug
		)
	);

	return $card ?: null;
}

function glandore_problem_card_get_steps( int $card_id ) : array {
	global $wpdb;

	$table = glandore_problem_card_table( 'steps' );

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE card_id = %d ORDER BY display_order ASC, id ASC",
			$card_id
		)
	) ?: array();
}

function glandore_problem_card_get_items_for_step( int $step_id ) : array {
	global $wpdb;

	$table = glandore_problem_card_table( 'items' );

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE step_id = %d ORDER BY display_order ASC, id ASC",
			$step_id
		)
	) ?: array();
}

function glandore_problem_card_session_cookie_name( int $card_id ) : string {
	return 'glandore_problem_card_' . $card_id;
}

function glandore_problem_card_get_or_create_session( int $card_id ) : ?object {
	global $wpdb;

	$table       = glandore_problem_card_table( 'sessions' );
	$user_id     = get_current_user_id() ?: null;
	$cookie_name = glandore_problem_card_session_cookie_name( $card_id );
	$session_key = '';

	if ( $user_id ) {
		$session_key = hash( 'sha256', 'user:' . $user_id . ':card:' . $card_id );
	} elseif ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
		$session_key = preg_replace( '/[^a-f0-9]/', '', strtolower( (string) wp_unslash( $_COOKIE[ $cookie_name ] ) ) );
	}

	if ( 64 !== strlen( $session_key ) ) {
		$session_key = hash( 'sha256', wp_generate_uuid4() . '|' . microtime( true ) );
	}

	$session = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE card_id = %d AND session_key = %s LIMIT 1",
			$card_id,
			$session_key
		)
	);

	if ( ! $session ) {
		$wpdb->insert(
			$table,
			array(
				'card_id'     => $card_id,
				'user_id'     => $user_id,
				'session_key' => $session_key,
				'status'      => 'in_progress',
			),
			array( '%d', '%d', '%s', '%s' )
		);

		$session_id = (int) $wpdb->insert_id;
		$session    = $session_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $session_id ) ) : null;
	}

	if ( ! headers_sent() && ! $user_id ) {
		setcookie(
			$cookie_name,
			$session_key,
			array(
				'expires'  => time() + MONTH_IN_SECONDS,
				'path'     => COOKIEPATH ?: '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	return $session ?: null;
}

function glandore_problem_card_get_responses( int $session_id ) : array {
	global $wpdb;

	$table = glandore_problem_card_table( 'responses' );
	$rows  = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE session_id = %d",
			$session_id
		)
	) ?: array();

	$out = array();
	foreach ( $rows as $row ) {
		$out[ (int) $row->item_id ] = $row;
	}

	return $out;
}

function glandore_problem_card_save_response( int $session_id, int $step_id, int $item_id, $value ) : bool {
	global $wpdb;

	$table = glandore_problem_card_table( 'responses' );
	$text  = '';
	$json  = null;

	if ( is_array( $value ) ) {
		$clean = array_map( 'sanitize_text_field', wp_unslash( $value ) );
		$json  = wp_json_encode( array_values( $clean ) );
		$text  = implode( "\n", $clean );
	} else {
		$text = sanitize_textarea_field( (string) wp_unslash( $value ) );
	}

	$existing_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT id FROM {$table} WHERE session_id = %d AND item_id = %d LIMIT 1",
			$session_id,
			$item_id
		)
	);

	$data = array(
		'session_id'    => $session_id,
		'step_id'       => $step_id,
		'item_id'       => $item_id,
		'response_text' => $text,
		'response_json' => $json,
	);

	$formats = array( '%d', '%d', '%d', '%s', '%s' );

	if ( $existing_id ) {
		return false !== $wpdb->update( $table, $data, array( 'id' => (int) $existing_id ), $formats, array( '%d' ) );
	}

	return false !== $wpdb->insert( $table, $data, $formats );
}

function glandore_problem_card_step_belongs_to_card( int $step_id, int $card_id ) : bool {
	global $wpdb;

	$table = glandore_problem_card_table( 'steps' );

	return (bool) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT id FROM {$table} WHERE id = %d AND card_id = %d LIMIT 1",
			$step_id,
			$card_id
		)
	);
}
