<?php
/**
 * Issue Studio Chain Support
 *
 * Creates a new Issue Studio artefact from an existing snapshot, using the
 * newly selected Domain, Issue and Perspectives.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function glandore_sa_chain_clean_text( $value ) : string {
	if ( is_array( $value ) || is_object( $value ) ) {
		$value = wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}

	return trim( wp_strip_all_tags( (string) $value ) );
}

function glandore_sa_chain_table_columns( string $table ) : array {
	global $wpdb;

	$columns = $wpdb->get_col( "SHOW COLUMNS FROM {$table}", 0 );

	return is_array( $columns ) ? $columns : array();
}

function glandore_sa_chain_pick_columns( string $table, array $data ) : array {
	$columns = glandore_sa_chain_table_columns( $table );

	if ( empty( $columns ) ) {
		return $data;
	}

	return array_intersect_key( $data, array_flip( $columns ) );
}

function glandore_sa_chain_get_snapshot_context( int $parent_artefact_id, int $parent_snapshot_id ) : array {
	global $wpdb;

	$snapshots_table = $wpdb->prefix . 'sa_artefact_snapshots';
	$artefacts_table = $wpdb->prefix . 'sa_artefacts';
	$classes_table   = $wpdb->prefix . 'sa_classes';
	$issues_table    = $wpdb->prefix . 'sa_issues';

	$snapshot = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT *
			 FROM {$snapshots_table}
			 WHERE id = %d
			   AND artefact_id = %d
			 LIMIT 1",
			$parent_snapshot_id,
			$parent_artefact_id
		)
	);

	if ( ! $snapshot ) {
		return array();
	}

	$parent = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT
				a.id,
				a.class_id,
				a.issue_id,
				c.class_title,
				i.issue_title
			 FROM {$artefacts_table} a
			 LEFT JOIN {$classes_table} c ON c.id = a.class_id
			 LEFT JOIN {$issues_table} i ON i.id = a.issue_id
			 WHERE a.id = %d
			 LIMIT 1",
			$parent_artefact_id
		)
	);

	$data = json_decode( (string) $snapshot->snapshot_json, true );

	if ( ! is_array( $data ) ) {
		$data = array();
	}

	$overview = glandore_sa_chain_clean_text( $data['overview'] ?? '' );

	$perspectives = array();

	if ( ! empty( $data['perspectives'] ) && is_array( $data['perspectives'] ) ) {
		foreach ( $data['perspectives'] as $perspective ) {
			$question = glandore_sa_chain_clean_text( $perspective['question'] ?? $perspective['title'] ?? 'Perspective' );
			$analysis = glandore_sa_chain_clean_text( $perspective['analysis'] ?? '' );

			if ( '' !== $question || '' !== $analysis ) {
				$perspectives[] = trim( $question . "\n" . $analysis );
			}
		}
	}

	$ideas = array();

	if ( ! empty( $data['ideas_for_consideration'] ) && is_array( $data['ideas_for_consideration'] ) ) {
		foreach ( $data['ideas_for_consideration'] as $idea ) {
			$idea = glandore_sa_chain_clean_text( $idea );

			if ( '' !== $idea ) {
				$ideas[] = $idea;
			}
		}
	}

	return array(
		'parent'            => $parent,
		'snapshot'          => $snapshot,
		'snapshot_markdown' => (string) ( $snapshot->snapshot_markdown ?? '' ),
		'overview'          => $overview,
		'perspectives'      => $perspectives,
		'ideas'             => $ideas,
	);
}

function glandore_sa_chain_create_assessment( int $class_id, int $issue_id, array $perspectives, int $parent_artefact_id, int $parent_snapshot_id ) : int {
	global $wpdb;

	if ( $class_id <= 0 || $issue_id <= 0 || $parent_artefact_id <= 0 || $parent_snapshot_id <= 0 ) {
		return 0;
	}

	$context = glandore_sa_chain_get_snapshot_context( $parent_artefact_id, $parent_snapshot_id );

	if ( empty( $context ) ) {
		return 0;
	}

	$artefacts_table    = $wpdb->prefix . 'sa_artefacts';
	$perspectives_table = $wpdb->prefix . 'sa_artefact_perspectives';
	$chains_table       = $wpdb->prefix . 'sa_artefact_chains';
	$classes_table      = $wpdb->prefix . 'sa_classes';
	$issues_table       = $wpdb->prefix . 'sa_issues';
	$snapshots_table    = $wpdb->prefix . 'sa_artefact_snapshots';

	$class_title = (string) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT class_title FROM {$classes_table} WHERE id = %d LIMIT 1",
			$class_id
		)
	);

	$issue_title = (string) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT issue_title FROM {$issues_table} WHERE id = %d LIMIT 1",
			$issue_id
		)
	);

	$selected_perspective_questions = array();
	$perspectives = array_values(
		array_unique(
			array_filter(
				array_map( 'absint', $perspectives )
			)
		)
	);

	if ( ! empty( $perspectives ) ) {
		$questions_table = $wpdb->prefix . 'sa_perspective_questions';
		$placeholders    = implode( ',', array_fill( 0, count( $perspectives ), '%d' ) );

		$selected_perspective_questions = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT question_text
				 FROM {$questions_table}
				 WHERE id IN ({$placeholders})
				 ORDER BY sort_order ASC, id ASC",
				...$perspectives
			)
		) ?: array();
	}

	$selected_perspective_text = '';

	if ( ! empty( $selected_perspective_questions ) ) {
		$selected_perspective_text = "\nSelected Perspectives for this chained assessment:\n";

		foreach ( $selected_perspective_questions as $selected_question ) {
			$selected_question = glandore_sa_chain_clean_text( $selected_question );

			if ( '' !== $selected_question ) {
				$selected_perspective_text .= 'Perspective: ' . $selected_question . "\n";
			}
		}
	}

	$source_title = '';

	if ( ! empty( $context['parent'] ) ) {
		$source_domain = (string) ( $context['parent']->class_title ?? 'previous domain' );
		$source_issue  = (string) ( $context['parent']->issue_title ?? 'previous issue' );

		$source_title = trim( $source_domain . ' / ' . $source_issue );
	}

	$issue_description = trim(
		"This is a chained assessment. It reinterprets a previous Issue Studio snapshot through a newly selected Domain, Issue and set of Perspectives.\n\n" .
		"New assessment framing:\n" .
		"Domain: " . $class_title . "\n" .
		"Issue: " . $issue_title . "\n\n" .
		"Previous assessment framing:\n" .
		"Previous assessment: " . $source_title . "\n" .
		"Source artefact: #" . $parent_artefact_id . "\n" .
		"Source snapshot: #" . $parent_snapshot_id
	);

	$tried = trim(
		"A previous Issue Studio assessment has already been generated. This chained assessment uses the preserved snapshot as source material rather than asking the user to re-enter the situation from scratch.\n\n" .
		"The earlier assessment remains intact and is being used as context for a new interpretation."
	);

	$success_failure = trim(
		"The earlier assessment surfaced an initial interpretation of the issue. This chain tests whether a different Domain, Issue and set of Perspectives reveals a more useful or complementary interpretation.\n\n" .
		"The important point is not whether the previous assessment was right or wrong, but what becomes visible when the same situation is reframed."
	);

	$success_looks_like = trim(
		"Success would mean producing a new assessment that builds on the existing snapshot while revealing different dynamics, causes, constraints, risks or opportunities through the newly selected framing."
	);

	$other_relevant = trim(
		"Selected Perspectives for this chained assessment:\n" .
		trim( $selected_perspective_text ) . "\n\n" .
		"The previous snapshot is passed to the analysis engine as preserved context. It should inform this assessment, but it should not be reproduced as raw markdown inside the submitted information fields."
	);

	$input_json = wp_json_encode(
		array(
			'chain' => array(
				'parent_artefact_id' => $parent_artefact_id,
				'parent_snapshot_id' => $parent_snapshot_id,
				'source_title'       => $source_title,
			),
			'issue_description'  => $issue_description,
			'tried'              => $tried,
			'success_failure'    => $success_failure,
			'success_looks_like' => $success_looks_like,
			'other_relevant'     => $other_relevant,
		),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	);

	/*
	 * IMPORTANT:
	 * A chain is a new snapshot in the same artefact, not a new artefact.
	 * We update the artefact's current framing, replace its active perspective
	 * selection, and then create a new snapshot under the parent artefact.
	 * Existing snapshots remain preserved and immutable.
	 */
	$artefact_data = glandore_sa_chain_pick_columns(
		$artefacts_table,
		array(
			'class_id'           => $class_id,
			'issue_id'           => $issue_id,
			'artefact_title'     => trim( 'Chained Assessment: ' . $class_title . ' / ' . $issue_title ),
			'issue_description'  => $issue_description,
			'tried'              => $tried,
			'success_failure'    => $success_failure,
			'success_looks_like' => $success_looks_like,
			'other_relevant'     => $other_relevant,
			'input_json'         => $input_json,
			'status'             => 'complete',
			'updated_at'         => current_time( 'mysql' ),
		)
	);

	$updated = $wpdb->update(
		$artefacts_table,
		$artefact_data,
		array( 'id' => $parent_artefact_id )
	);

	if ( false === $updated ) {
		return 0;
	}

	$wpdb->delete(
		$perspectives_table,
		array( 'artefact_id' => $parent_artefact_id ),
		array( '%d' )
	);

	foreach ( $perspectives as $question_id ) {
		$perspective_data = glandore_sa_chain_pick_columns(
			$perspectives_table,
			array(
				'artefact_id' => $parent_artefact_id,
				'question_id' => $question_id,
				'created_at'  => current_time( 'mysql' ),
			)
		);

		$wpdb->insert( $perspectives_table, $perspective_data );
	}

	$new_snapshot_id = 0;

	if ( function_exists( 'glandore_sa_create_snapshot_row' ) ) {
		$new_snapshot_id = glandore_sa_create_snapshot_row( $parent_artefact_id, '', $parent_snapshot_id );
	}

	if ( ! $new_snapshot_id ) {
		return 0;
	}

	/*
	 * Add explicit chain metadata to the newly created snapshot so display code
	 * never has to guess whether an ID is an artefact ID or a snapshot ID.
	 */
	$snapshot_json = (string) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT snapshot_json FROM {$snapshots_table} WHERE id = %d AND artefact_id = %d LIMIT 1",
			$new_snapshot_id,
			$parent_artefact_id
		)
	);

	$snapshot_data = json_decode( $snapshot_json, true );

	if ( is_array( $snapshot_data ) ) {
		if ( empty( $snapshot_data['metadata'] ) || ! is_array( $snapshot_data['metadata'] ) ) {
			$snapshot_data['metadata'] = array();
		}

		$snapshot_data['metadata']['chain_type']          = 'domain_reinterpretation';
		$snapshot_data['metadata']['source_artefact_id']  = $parent_artefact_id;
		$snapshot_data['metadata']['source_snapshot_id']  = $parent_snapshot_id;
		$snapshot_data['metadata']['based_on_snapshot_id'] = $parent_snapshot_id;

		$wpdb->update(
			$snapshots_table,
			array(
				'snapshot_json' => wp_json_encode( $snapshot_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
			),
			array(
				'id'          => $new_snapshot_id,
				'artefact_id' => $parent_artefact_id,
			),
			array( '%s' ),
			array( '%d', '%d' )
		);
	}

	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $chains_table ) ) === $chains_table ) {
		$chain_data = glandore_sa_chain_pick_columns(
			$chains_table,
			array(
				'parent_artefact_id' => $parent_artefact_id,
				'parent_snapshot_id' => $parent_snapshot_id,
				'child_artefact_id'  => $parent_artefact_id,
				'child_snapshot_id'  => $new_snapshot_id,
				'chain_type'         => 'domain_reinterpretation',
				'created_at'         => current_time( 'mysql' ),
			)
		);

		$wpdb->insert( $chains_table, $chain_data );
	}

	return $parent_artefact_id;
}

function glandore_sa_chain_redirect_from_issue_input_if_needed( int $class_id, int $issue_id, array $perspectives ) : void {
	$parent_artefact_id = isset( $_GET['chain_from_artefact_id'] ) ? absint( $_GET['chain_from_artefact_id'] ) : 0;
	$parent_snapshot_id = isset( $_GET['chain_from_snapshot_id'] ) ? absint( $_GET['chain_from_snapshot_id'] ) : 0;

	if ( ! $parent_artefact_id || ! $parent_snapshot_id ) {
		return;
	}

	$child_artefact_id = glandore_sa_chain_create_assessment(
		$class_id,
		$issue_id,
		$perspectives,
		$parent_artefact_id,
		$parent_snapshot_id
	);

	if ( ! $child_artefact_id ) {
		wp_die( esc_html__( 'Issue Studio could not create the chained assessment.', 'glandore' ) );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'artefact_id' => $child_artefact_id,
			),
			home_url( '/issue-studio-result/' )
		)
	);
	exit;
}
