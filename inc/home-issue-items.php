<?php
/**
 * Home page – Issue Studio carousel items.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$artefacts_table = $wpdb->prefix . 'sa_artefacts';
$snapshots_table = $wpdb->prefix . 'sa_artefact_snapshots';

$artefacts_exists = $wpdb->get_var(
	$wpdb->prepare(
		'SHOW TABLES LIKE %s',
		$artefacts_table
	)
);

$snapshots_exists = $wpdb->get_var(
	$wpdb->prepare(
		'SHOW TABLES LIKE %s',
		$snapshots_table
	)
);

if ( ! $artefacts_exists || ! $snapshots_exists ) {
	return array();
}

function glandore_home_issue_extract_overview( array $snapshot ) : string {
	if ( empty( $snapshot['snapshot_json'] ) ) {
		return '';
	}

	$decoded = json_decode( (string) $snapshot['snapshot_json'], true );

	if ( is_array( $decoded ) && ! empty( $decoded['overview'] ) && is_string( $decoded['overview'] ) ) {
		return trim( wp_strip_all_tags( $decoded['overview'] ) );
	}

	if ( empty( $snapshot['snapshot_markdown'] ) ) {
		return '';
	}

	$text = (string) $snapshot['snapshot_markdown'];

	if ( preg_match( '/Overview\s+(.*?)(Perspective analysis|Ideas for consideration|Snapshot details|$)/is', $text, $matches ) ) {
		return trim( wp_strip_all_tags( $matches[1] ) );
	}

	return trim( wp_strip_all_tags( wp_trim_words( $text, 90, '…' ) ) );
}

$sql = "
	SELECT
		a.id,
		a.issue_description,
		a.artefact_title,
		a.status,
		s.snapshot_json,
		s.snapshot_markdown,
		s.created_at AS snapshot_created_at
	FROM {$artefacts_table} a
	INNER JOIN (
		SELECT artefact_id, MAX(id) AS latest_snapshot_id
		FROM {$snapshots_table}
		GROUP BY artefact_id
	) latest ON latest.artefact_id = a.id
	INNER JOIN {$snapshots_table} s ON s.id = latest.latest_snapshot_id
	WHERE a.status IN ('active', 'complete')
	ORDER BY s.created_at DESC, s.id DESC
	LIMIT 8
";

$rows = $wpdb->get_results( $sql, ARRAY_A ) ?: array();

$items = array();

foreach ( $rows as $row ) {
	$title = isset( $row['issue_description'] ) ? trim( wp_strip_all_tags( (string) $row['issue_description'] ) ) : '';

	if ( '' === $title && ! empty( $row['artefact_title'] ) ) {
		$title = trim( wp_strip_all_tags( (string) $row['artefact_title'] ) );
	}

	if ( '' === $title ) {
		continue;
	}

	$overview = glandore_home_issue_extract_overview( $row );

	if ( '' === $overview ) {
		$overview = 'Open this Issue Studio assessment to review the submitted issue, perspective analysis and ideas for consideration.';
	}

	$items[] = array(
		'title'   => $title,
		'url'     => add_query_arg(
			'artefact_id',
			absint( $row['id'] ),
			home_url( '/issue-studio-result/' )
		),
		'excerpt' => $overview,
		'image'   => '',
	);
}

return $items;