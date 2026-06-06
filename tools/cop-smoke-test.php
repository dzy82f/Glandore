<?php
/**
 * One-off smoke test / seeder for CoP tables.
 *
 * Usage (from WP root):
 *   wp eval-file wp-content/themes/glandore/tools/cop-smoke-test.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Make sure our helpers are available.
require_once get_template_directory() . '/inc/cop-db.php';

/**
 * Helper: basic CLI logger that also works if WP-CLI is not present.
 *
 * @param string $message Message to output.
 */
function glandore_cop_cli_log( string $message ) : void {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		\WP_CLI::log( $message );
	} else {
		echo $message . PHP_EOL;
	}
}

glandore_cop_cli_log( '==== Glandore CoP smoke test / seeder ====' );

// 1. Ensure there is at least one administrator user to act as "Julian".
$admin_users = get_users(
	array(
		'role__in' => array( 'administrator' ),
		'number'   => 1,
		'orderby'  => 'ID',
		'order'    => 'ASC',
	)
);

if ( empty( $admin_users ) ) {
	glandore_cop_cli_log( 'No administrator users found – aborting.' );
	return;
}

$admin_user = $admin_users[0];

glandore_cop_cli_log( 'Using admin user: ' . $admin_user->user_login . ' (ID ' . $admin_user->ID . ')' );

// 2. Ensure a CoP contributor row for the admin user.
$admin_contributor = glandore_cop_get_or_create_contributor_for_user( (int) $admin_user->ID );

if ( ! $admin_contributor || empty( $admin_contributor->id ) ) {
	glandore_cop_cli_log( 'Failed to create or load contributor for admin user – aborting.' );
	return;
}

glandore_cop_cli_log( 'Admin contributor ID: ' . $admin_contributor->id );

// 3. Ensure a virtual contributor for Ariadne.
$ariadne = glandore_cop_get_or_create_virtual_contributor( 'Ariadne (digital Assistant)' );

if ( ! $ariadne || empty( $ariadne->id ) ) {
	glandore_cop_cli_log( 'Failed to create or load virtual contributor "Ariadne" – aborting.' );
	return;
}

glandore_cop_cli_log( 'Ariadne contributor ID: ' . $ariadne->id );

// 4. Create (or fetch) the "Learning Organisation" topic.
$topic_title = 'The Learning Organisation: What Has Changed?';
$topic_slug  = sanitize_title( $topic_title );

global $wpdb;

$topic_table = glandore_cop_table( 'topic' );

$existing_topic = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT * FROM $topic_table WHERE slug = %s LIMIT 1",
		$topic_slug
	)
);

if ( $existing_topic ) {
	$topic_id = (int) $existing_topic->id;
	glandore_cop_cli_log( 'Existing topic found: ID ' . $topic_id );
} else {
	$topic_id = glandore_cop_insert_topic( $topic_title, (int) $admin_contributor->id );

	if ( ! $topic_id ) {
		glandore_cop_cli_log( 'Failed to insert topic – aborting.' );
		return;
	}

	glandore_cop_cli_log( 'Created topic ID: ' . $topic_id );
}

// 5. Create (or fetch) a thread under that topic.
$thread_title = 'What does a learning organisation mean today?';

$thread_table = glandore_cop_table( 'thread' );

$existing_thread = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT * FROM $thread_table WHERE topic_id = %d AND title = %s LIMIT 1",
		$topic_id,
		$thread_title
	)
);

if ( $existing_thread ) {
	$thread_id = (int) $existing_thread->id;
	glandore_cop_cli_log( 'Existing thread found: ID ' . $thread_id );
} else {
	$thread_id = glandore_cop_insert_thread(
		$topic_id,
		$thread_title,
		(int) $admin_contributor->id
	);

	if ( ! $thread_id ) {
		glandore_cop_cli_log( 'Failed to insert thread – aborting.' );
		return;
	}

	glandore_cop_cli_log( 'Created thread ID: ' . $thread_id );
}

// 6. Create a simple opening post if there is not one already.
$post_table = glandore_cop_table( 'post' );

$existing_opening = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT * FROM $post_table WHERE thread_id = %d AND is_opening = 1 LIMIT 1",
		$thread_id
	)
);

if ( $existing_opening ) {
	glandore_cop_cli_log( 'Opening post already exists: ID ' . $existing_opening->id );
} else {
	$post_content = <<<HTML
This is a seeded opening post for the topic <strong>"The Learning Organisation: What Has Changed?"</strong>.

It exists purely as a smoke test for the new CoP schema. Once the templates are wired up,
this post can be edited or replaced via the normal CoP UI.
HTML;

	$post_id = glandore_cop_insert_post(
		array(
			'thread_id'           => $thread_id,
			'from_contributor_id' => (int) $admin_contributor->id,
			'to_contributor_id'   => null,
			'title'               => 'Seed: The learning organisation today',
			'content'             => $post_content,
			'parent_post_id'      => null,
			'is_opening'          => true,
			'status'              => 'published',
		)
	);

	if ( ! $post_id ) {
		glandore_cop_cli_log( 'Failed to insert opening post.' );
	} else {
		glandore_cop_cli_log( 'Created opening post ID: ' . $post_id );
	}
}

glandore_cop_cli_log( '==== Done. You can now inspect cop_topic / cop_thread / cop_post ====' );