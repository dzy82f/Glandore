<?php
/**
 * Template Name: Issue Studio Print
 * Template Post Type: page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$artefact_id     = isset( $_GET['artefact_id'] ) ? absint( $_GET['artefact_id'] ) : 0;
$artefacts_table = $wpdb->prefix . 'sa_artefacts';
$snapshots_table = $wpdb->prefix . 'sa_artefact_snapshots';
$classes_table   = $wpdb->prefix . 'sa_classes';
$issues_table    = $wpdb->prefix . 'sa_issues';

$artefact = null;
$snapshot = null;

if ( $artefact_id ) {
	$artefact = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT
				a.*,
				c.class_title,
				i.issue_title
			 FROM {$artefacts_table} a
			 LEFT JOIN {$classes_table} c ON c.id = a.class_id
			 LEFT JOIN {$issues_table} i ON i.id = a.issue_id
			 WHERE a.id = %d
			 LIMIT 1",
			$artefact_id
		),
		ARRAY_A
	);

	$snapshot = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT *
			 FROM {$snapshots_table}
			 WHERE artefact_id = %d
			 ORDER BY created_at DESC, id DESC
			 LIMIT 1",
			$artefact_id
		),
		ARRAY_A
	);
}

function glandore_issue_studio_print_text( $value ) {
	if ( is_array( $value ) || is_object( $value ) ) {
		return wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}

	return (string) $value;
}

function glandore_issue_studio_print_paragraphs( $value ) {
	$text = glandore_issue_studio_print_text( $value );

	if ( '' === trim( $text ) ) {
		return;
	}

	echo wpautop( esc_html( $text ) );
}

function glandore_issue_studio_print_list( $items ) {
	if ( empty( $items ) || ! is_array( $items ) ) {
		return;
	}

	echo '<ul>';

	foreach ( $items as $item ) {
		if ( is_array( $item ) || is_object( $item ) ) {
			echo '<li><pre>' . esc_html( glandore_issue_studio_print_text( $item ) ) . '</pre></li>';
		} else {
			echo '<li>' . esc_html( glandore_issue_studio_print_text( $item ) ) . '</li>';
		}
	}

	echo '</ul>';
}

function glandore_issue_studio_print_snapshot_data( $snapshot ) {
	if ( empty( $snapshot['snapshot_json'] ) ) {
		return array();
	}

	$data = json_decode( $snapshot['snapshot_json'], true );

	if ( is_array( $data ) ) {
		return $data;
	}

	return array(
		'overview' => $snapshot['snapshot_json'],
	);
}

$data = $snapshot ? glandore_issue_studio_print_snapshot_data( $snapshot ) : array();

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Issue Studio Print</title>
	<link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/css/issue-studio-print.css' ); ?>">
</head>

<body class="issue-studio-print">
	<main class="isp-page">

		<?php if ( ! $artefact_id ) : ?>

			<h1Issue</h1>
			<p>No artefact ID was supplied.</p>

		<?php elseif ( ! $artefact ) : ?>

			<h1>Analysis and next steps</h1>
			<p>No artefact was found for this ID.</p>

		<?php elseif ( ! $snapshot ) : ?>

			<h1>Issue</h1>
			<p>No snapshot was found for this artefact.</p>

		<?php else : ?>

			<header class="isp-header">
				<p class="isp-kicker">Glandore Associates · Issue Studio</p>
				<h1>Issue</h1>

				<?php if ( ! empty( $artefact['class_title'] ) || ! empty( $artefact['issue_title'] ) ) : ?>
					<p class="isp-context">
						<?php if ( ! empty( $artefact['class_title'] ) ) : ?>
							<strong>Domain:</strong> <?php echo esc_html( $artefact['class_title'] ); ?>
						<?php endif; ?>

						<?php if ( ! empty( $artefact['class_title'] ) && ! empty( $artefact['issue_title'] ) ) : ?>
							<br>
						<?php endif; ?>

						<?php if ( ! empty( $artefact['issue_title'] ) ) : ?>
							<strong>Issue:</strong> <?php echo esc_html( $artefact['issue_title'] ); ?>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<p class="isp-meta">
					Artefact ID: <?php echo esc_html( (string) $artefact_id ); ?>
					<?php if ( ! empty( $snapshot['id'] ) ) : ?>
						· Snapshot ID: <?php echo esc_html( (string) $snapshot['id'] ); ?>
					<?php endif; ?>
					<?php if ( ! empty( $snapshot['created_at'] ) ) : ?>
						· Created: <?php echo esc_html( $snapshot['created_at'] ); ?>
					<?php endif; ?>
				</p>
			</header>

			<section class="isp-section">
				<h2>Submitted information</h2>

				<?php if ( ! empty( $artefact['issue_description'] ) ) : ?>
					<p><strong>Issue description</strong></p>
					<?php glandore_issue_studio_print_paragraphs( $artefact['issue_description'] ); ?>
				<?php endif; ?>

				<?php if ( ! empty( $artefact['tried'] ) ) : ?>
					<p><strong>What has been tried</strong></p>
					<?php glandore_issue_studio_print_paragraphs( $artefact['tried'] ); ?>
				<?php endif; ?>

				<?php if ( ! empty( $artefact['success_failure'] ) ) : ?>
					<p><strong>Success or failure</strong></p>
					<?php glandore_issue_studio_print_paragraphs( $artefact['success_failure'] ); ?>
				<?php endif; ?>

				<?php if ( ! empty( $artefact['success_looks_like'] ) ) : ?>
					<p><strong>Success would look like</strong></p>
					<?php glandore_issue_studio_print_paragraphs( $artefact['success_looks_like'] ); ?>
				<?php endif; ?>

				<?php if ( ! empty( $artefact['other_relevant'] ) ) : ?>
					<p><strong>Other relevant information</strong></p>
					<?php glandore_issue_studio_print_paragraphs( $artefact['other_relevant'] ); ?>
				<?php endif; ?>
			</section>

			<section class="isp-section">
				<h2>Assessment</h2>

				<?php if ( ! empty( $data['overview'] ) ) : ?>
					<div class="isp-subsection">
						<h3>Overview</h3>
						<?php glandore_issue_studio_print_paragraphs( $data['overview'] ); ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $data['perspectives'] ) && is_array( $data['perspectives'] ) ) : ?>
					<div class="isp-subsection">
						<h3>Perspective analysis</h3>

						<?php foreach ( $data['perspectives'] as $perspective ) : ?>
							<?php
							$question = '';
							$answer   = '';

							if ( is_array( $perspective ) ) {
								$question = $perspective['question'] ?? $perspective['title'] ?? '';
								$answer   = $perspective['answer'] ?? $perspective['analysis'] ?? $perspective['content'] ?? '';
							} else {
								$answer = $perspective;
							}
							?>

							<div class="isp-perspective">
								<?php if ( $question ) : ?>
									<h4><?php echo esc_html( $question ); ?></h4>
								<?php endif; ?>

								<?php glandore_issue_studio_print_paragraphs( $answer ); ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $data['ideas_for_consideration'] ) ) : ?>
					<div class="isp-subsection">
						<h3>Ideas for consideration</h3>

						<?php
						if ( is_array( $data['ideas_for_consideration'] ) ) {
							glandore_issue_studio_print_list( $data['ideas_for_consideration'] );
						} else {
							glandore_issue_studio_print_paragraphs( $data['ideas_for_consideration'] );
						}
						?>
					</div>
				<?php endif; ?>
			</section>

			<section class="isp-section isp-snapshot">
				<h2>Snapshot details</h2>

				<p>
					<strong>Snapshot version</strong><br>
					<?php echo esc_html( $snapshot['snapshot_version'] ?? $snapshot['version'] ?? 'Latest' ); ?>
				</p>

				<?php if ( ! empty( $snapshot['created_at'] ) ) : ?>
					<p>
						<strong>Created</strong><br>
						<?php echo esc_html( $snapshot['created_at'] ); ?>
					</p>
				<?php endif; ?>
			</section>

		<?php endif; ?>

	</main>

	<script src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/js/issue-studio-print.js' ); ?>"></script>
</body>
</html>