<?php
/**
 * CoP DB helpers – typed access to custom tables.
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the full table name for a CoP entity.
 *
 * @param string $type contributor|topic|thread|post
 * @return string
 */
function glandore_cop_table( string $type ) : string {
	global $wpdb;

	switch ( $type ) {
		case 'contributor':
			return $wpdb->prefix . 'cop_contributor';
		case 'topic':
			return $wpdb->prefix . 'cop_topic';
		case 'thread':
			return $wpdb->prefix . 'cop_thread';
		case 'post':
			return $wpdb->prefix . 'cop_post';
		default:
			throw new InvalidArgumentException( 'Unknown CoP table type: ' . $type );
	}
}

/**
 * Build a sensible display name from a WP_User.
 *
 * @param WP_User $user User object.
 * @return string
 */
function glandore_cop_user_display_name( WP_User $user ) : string {
	$first = trim( (string) get_user_meta( $user->ID, 'first_name', true ) );
	$last  = trim( (string) get_user_meta( $user->ID, 'last_name', true ) );
	$name  = trim( $first . ' ' . $last );

	if ( '' !== $name ) {
		return $name;
	}

	if ( ! empty( $user->display_name ) ) {
		return (string) $user->display_name;
	}

	return (string) $user->user_login;
}

/**
 * Ensure we have a cop_contributor row for a given user.
 *
 * @param int $user_id WP user ID.
 * @return object|null DB row (->id, ->user_id, ->display_name, ...)
 */
 
 /**
 * Build a canonical CoP contributor display name from user meta.
 *
 * Prefer "first_name last_name" from usermeta.
 * Fall back to WordPress display_name, then user_login.
 *
 * @param WP_User $user User object.
 * @return string
 */
 
function glandore_cop_build_contributor_display_name( WP_User $user ) : string {
	$first = trim( (string) get_user_meta( $user->ID, 'first_name', true ) );
	$last  = trim( (string) get_user_meta( $user->ID, 'last_name', true ) );

	$name = trim( $first . ' ' . $last );

	if ( '' !== $name ) {
		return $name;
	}

	// Fallbacks if meta is missing.
	if ( ! empty( $user->display_name ) ) {
		return (string) $user->display_name;
	}

	return (string) $user->user_login;
}
 
/**
 * Get or create a CoP contributor row for a given user.
 *
 * Ensures display_name is always "first_name last_name" from usermeta,
 * with sane fallbacks.
 *
 * @param int $user_id WordPress user ID.
 * @return object|null Row from wp_cop_contributor or null on failure.
 */
function glandore_cop_get_or_create_contributor_for_user( int $user_id ) {
	global $wpdb;

	$table = glandore_cop_table( 'contributor' );

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return null;
	}

	$canonical_name = glandore_cop_build_contributor_display_name( $user );

	// Try to find an existing contributor row for this user.
	$contributor = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE user_id = %d LIMIT 1",
			$user_id
		)
	);

	$now = current_time( 'mysql', true );

	if ( $contributor ) {
		// Optionally normalise the stored display_name if it has drifted.
		if ( (string) $contributor->display_name !== $canonical_name ) {
			$wpdb->update(
				$table,
				array(
					'display_name' => $canonical_name,
					'updated_at'   => $now,
				),
				array( 'id' => (int) $contributor->id ),
				array( '%s', '%s' ),
				array( '%d' )
			);
			// Refresh the row.
			$contributor = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
					$contributor->id
				)
			);
		}

		return $contributor;
	}

	// No contributor row yet – create one.
	$inserted = $wpdb->insert(
		$table,
		array(
			'user_id'      => $user_id,
			'display_name' => $canonical_name,
			'created_at'   => $now,
			'updated_at'   => $now,
		),
		array( '%d', '%s', '%s', '%s' )
	);

	if ( ! $inserted ) {
		return null;
	}

	$new_id = (int) $wpdb->insert_id;

	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
			$new_id
		)
	);
}

/**
 * Create or fetch a **virtual** contributor (eg Ariadne).
 *
 * @param string $display_name Human label, eg "Ariadne (digital Assistant)".
 * @return object|null
 */
function glandore_cop_get_or_create_virtual_contributor( string $display_name ) {
	global $wpdb;

	$table = glandore_cop_table( 'contributor' );

	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM $table WHERE display_name = %s AND is_virtual = 1 LIMIT 1",
			$display_name
		)
	);

	if ( $row ) {
		return $row;
	}

	$inserted = $wpdb->insert(
		$table,
		array(
			'user_id'      => null,
			'display_name' => $display_name,
			'is_virtual'   => 1,
			'created_at'   => current_time( 'mysql', 1 ),
			'updated_at'   => current_time( 'mysql', 1 ),
		),
		array( '%d', '%s', '%d', '%s', '%s' )
	);

	if ( ! $inserted ) {
		return null;
	}

	$new_id = (int) $wpdb->insert_id;

	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM $table WHERE id = %d LIMIT 1",
			$new_id
		)
	);
}

/**
 * Fetch all active topics.
 *
 * @return array of stdClass rows.
 */
function glandore_cop_get_topics() : array {
	global $wpdb;

	$table = glandore_cop_table( 'topic' );

	return $wpdb->get_results(
		"SELECT * FROM $table WHERE status = 'active' ORDER BY title ASC"
	);
}

/**
 * Insert a new topic.
 *
 * @param string $title      Topic title.
 * @param int    $creator_id Contributor ID.
 * @return int|false New topic ID on success, false on failure.
 */
function glandore_cop_insert_topic( string $title, int $creator_id ) {
	global $wpdb;

	$table = glandore_cop_table( 'topic' );

	$slug = sanitize_title( $title );

	$inserted = $wpdb->insert(
		$table,
		array(
			'title'      => $title,
			'slug'       => $slug,
			'status'     => 'active',
			'created_by' => $creator_id,
			'created_at' => current_time( 'mysql', 1 ),
			'updated_at' => current_time( 'mysql', 1 ),
		),
		array( '%s', '%s', '%s', '%d', '%s', '%s' )
	);

	if ( ! $inserted ) {
		return false;
	}

	return (int) $wpdb->insert_id;
}

/**
 * Insert a new thread within a topic.
 *
 * @param int    $topic_id   Topic ID.
 * @param string $title      Thread title.
 * @param int    $creator_id Contributor ID.
 * @return int|false New thread ID or false on failure.
 */
function glandore_cop_insert_thread( int $topic_id, string $title, int $creator_id ) {
	global $wpdb;

	$table = glandore_cop_table( 'thread' );

	$inserted = $wpdb->insert(
		$table,
		array(
			'topic_id'   => $topic_id,
			'title'      => $title,
			'status'     => 'open',
			'created_by' => $creator_id,
			'created_at' => current_time( 'mysql', 1 ),
			'updated_at' => current_time( 'mysql', 1 ),
		),
		array( '%d', '%s', '%s', '%d', '%s', '%s' )
	);

	if ( ! $inserted ) {
		return false;
	}

	return (int) $wpdb->insert_id;
}

/**
 * Get threads for a topic (simple, no paging yet).
 *
 * @param int $topic_id Topic ID.
 * @return array
 */
function glandore_cop_get_threads_by_topic( int $topic_id ) : array {
	global $wpdb;

	$table = glandore_cop_table( 'thread' );

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $table WHERE topic_id = %d AND status != 'archived' ORDER BY created_at ASC",
			$topic_id
		)
	);
}

/**
 * Insert a post into a thread.
 *
 * @param array $args {
 *   @type int    $thread_id
 *   @type int    $from_contributor_id
 *   @type int    $to_contributor_id   Optional.
 *   @type string $title               Optional.
 *   @type string $content             Required.
 *   @type int    $parent_post_id      Optional (for replies / branching).
 *   @type bool   $is_opening          Optional (default false).
 *   @type string $status              Optional (default 'pending').
 * }
 * @return int|false New post ID or false on failure.
 */
function glandore_cop_insert_post( array $args ) {
	global $wpdb;

	$defaults = array(
		'thread_id'           => 0,
		'from_contributor_id' => 0,
		'to_contributor_id'   => null,
		'title'               => '',
		'content'             => '',
		'parent_post_id'      => null,
		'is_opening'          => false,
		'status'              => 'pending',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( ! $args['thread_id'] || ! $args['from_contributor_id'] || '' === trim( $args['content'] ) ) {
		return false;
	}

	$table = glandore_cop_table( 'post' );

	$inserted = $wpdb->insert(
		$table,
		array(
			'thread_id'           => (int) $args['thread_id'],
			'parent_post_id'      => $args['parent_post_id'] ? (int) $args['parent_post_id'] : null,
			'from_contributor_id' => (int) $args['from_contributor_id'],
			'to_contributor_id'   => $args['to_contributor_id'] ? (int) $args['to_contributor_id'] : null,
			'title'               => (string) $args['title'],
			'content'             => (string) $args['content'],
			'status'              => (string) $args['status'],
			'is_opening'          => $args['is_opening'] ? 1 : 0,
			'created_at'          => current_time( 'mysql', 1 ),
			'updated_at'          => current_time( 'mysql', 1 ),
		),
		array( '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
	);

	if ( ! $inserted ) {
		return false;
	}

	return (int) $wpdb->insert_id;
}

/**
 * Get posts in a thread, oldest first.
 *
 * @param int $thread_id Thread ID.
 * @return array
 */
function glandore_cop_get_posts_by_thread( int $thread_id ) : array {
	global $wpdb;

	$table = glandore_cop_table( 'post' );

	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $table WHERE thread_id = %d AND status = 'published' ORDER BY created_at ASC, id ASC",
			$thread_id
		)
	);
}

/**
 * Get inducted CoP contributors for the selector + bio pane.
 *
 * Definition of "contributor" (for now):
 * - glandore_cop_about_complete = 1
 * - cop_profile_visible = 1
 * - cop_profile_bio is non-empty
 *
 * Returns an array of:
 *   [
 *     'key'          => 'u235' or 'ariadne',
 *     'user_id'      => int|null,
 *     'display_name' => string,
 *     'bio_html'     => string,
 *     'is_virtual'   => bool,
 *   ]
 *
 * This pulls directly from usermeta (induction data) and adds Ariadne
 * as a virtual contributor.
 *
 * @return array
 */
function glandore_cop_get_inducted_contributors_for_selector() : array {
	$results = array();

	// 1. Real users who have completed induction and are visible.
	$user_args = array(
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => 'glandore_cop_about_complete',
				'value'   => '1',
				'compare' => '=',
			),
			array(
				'key'     => 'cop_profile_visible',
				'value'   => '1',
				'compare' => '=',
			),
		),
		'fields' => array( 'ID' ),
	);

	$users = get_users( $user_args );

	if ( ! empty( $users ) ) {
		foreach ( $users as $user ) {
			$user_id  = (int) $user->ID;
			$user_obj = get_user_by( 'id', $user_id );

			if ( ! $user_obj ) {
				continue;
			}

			// Final public CoP bio from induction.
			$bio_html = (string) get_user_meta( $user_id, 'cop_profile_bio', true );

			// Skip if bio is effectively empty.
			if ( '' === trim( wp_strip_all_tags( $bio_html ) ) ) {
				continue;
			}

			$display_name = glandore_cop_user_display_name( $user_obj );

			$results[] = array(
				'key'          => 'u' . $user_id,
				'user_id'      => $user_id,
				'display_name' => $display_name,
				'bio_html'     => $bio_html,
				'is_virtual'   => false,
			);
		}

		// Sort by display name for a stable, human-friendly dropdown.
		usort(
			$results,
			function ( $a, $b ) {
				return strcasecmp( $a['display_name'], $b['display_name'] );
			}
		);
	}

	// 2. Add Ariadne as a virtual contributor.
	$results[] = array(
		'key'          => 'ariadne',
		'user_id'      => null,
		'display_name' => 'Ariadne (Digital Assistant)',
		'bio_html'     => '<p>Ariadne is an AI Assistant who helps host and curate this Community of Practice. She supports induction, moderates contributions, and works with Julian to develop the methods and artefacts used here.</p>',
		'is_virtual'   => true,
	);

	return $results;
}