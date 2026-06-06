<?php
/**
 * One-off script to set the real opening post content
 * for "The Learning Organisation: What Has Changed?"
 *
 * Usage (from WP root):
 *   wp eval-file wp-content/themes/glandore/tools/cop-set-learning-opening.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/cop-db.php';

global $wpdb;

$topic_table = glandore_cop_table( 'topic' );
$thread_table = glandore_cop_table( 'thread' );
$post_table = glandore_cop_table( 'post' );
$contrib_table = glandore_cop_table( 'contributor' );

// Find Julian's contributor row (user_id = 1).
$julian_contrib = $wpdb->get_row(
	"SELECT * FROM $contrib_table WHERE user_id = 1 LIMIT 1"
);

if ( ! $julian_contrib ) {
	echo "No contributor row for user_id 1 – aborting.\n";
	return;
}

// Find the topic.
$topic_slug = 'the-learning-organisation-what-has-changed';

$topic = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT * FROM $topic_table WHERE slug = %s LIMIT 1",
		$topic_slug
	)
);

if ( ! $topic ) {
	echo "Topic not found – aborting.\n";
	return;
}

// Find the first thread under that topic.
$thread = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT * FROM $thread_table WHERE topic_id = %d ORDER BY created_at ASC LIMIT 1",
		$topic->id
	)
);

if ( ! $thread ) {
	echo "Thread not found – aborting.\n";
	return;
}

// Canonical opening-post content (as HTML).
$content = <<<HTML
<p>Twenty years ago, the “learning organisation” was usually described in terms of shared vision, team learning, and systems thinking. The focus was often on capturing lessons and spreading best practice.</p>

<p>Today, the environment feels very different: faster, more uncertain, more interconnected. As a result, the idea of the learning organisation seems to have shifted.</p>

<p>Learning now appears less about formal programmes and more about continuous feedback loops. Culture—especially trust and psychological safety—often matters more than structure. And much of the most important learning happens across organisational boundaries, in networks and communities rather than within a single institution.</p>

<p><strong>A few questions for the group:</strong></p>

<ul>
<li>What does a learning organisation mean in your context today?</li>
<li>What has changed most in the last 10–20 years?</li>
<li>Where does real learning actually happen in practice?</li>
</ul>
HTML;

// Look for an existing opening post in this thread.
$opening = $wpdb->get_row(
	$wpdb->prepare(
		"SELECT * FROM $post_table WHERE thread_id = %d AND is_opening = 1 LIMIT 1",
		$thread->id
	)
);

if ( $opening ) {
	$wpdb->update(
		$post_table,
		array(
			'title'               => 'Opening post: The Learning Organisation',
			'content'             => $content,
			'from_contributor_id' => (int) $julian_contrib->id,
			'status'              => 'published',
			'is_opening'          => 1,
			'updated_at'          => current_time( 'mysql', 1 ),
		),
		array(
			'id' => (int) $opening->id,
		),
		array( '%s', '%s', '%d', '%s', '%d', '%s' ),
		array( '%d' )
	);

	echo "Updated existing opening post (ID {$opening->id}).\n";
} else {
	$wpdb->insert(
		$post_table,
		array(
			'thread_id'           => (int) $thread->id,
			'parent_post_id'      => null,
			'from_contributor_id' => (int) $julian_contrib->id,
			'to_contributor_id'   => null,
			'title'               => 'Opening post: The Learning Organisation',
			'content'             => $content,
			'status'              => 'published',
			'is_opening'          => 1,
			'created_at'          => current_time( 'mysql', 1 ),
			'updated_at'          => current_time( 'mysql', 1 ),
		),
		array( '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
	);

	echo "Inserted new opening post (ID {$wpdb->insert_id}).\n";
}

echo "Done.\n";