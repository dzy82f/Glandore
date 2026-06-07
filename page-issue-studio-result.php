<?php
/**
 * Template Name: Issue Studio Result
 * Template Post Type: page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$artefact_id        = isset( $_GET['artefact_id'] ) ? absint( $_GET['artefact_id'] ) : 0;
$issue_history_mode = isset( $_GET['issue_history'] ) ? sanitize_text_field( wp_unslash( $_GET['issue_history'] ) ) : '';

$snapshots_table = $wpdb->prefix . 'sa_artefact_snapshots';
$artefacts_table = $wpdb->prefix . 'sa_artefacts';
$classes_table   = $wpdb->prefix . 'sa_classes';
$issues_table    = $wpdb->prefix . 'sa_issues';

$snapshot      = null;
$all_snapshots = array();
$artefact_meta = null;

if ( $artefact_id ) {
	$snapshot = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT *
			 FROM {$snapshots_table}
			 WHERE artefact_id = %d
			 ORDER BY created_at DESC, id DESC
			 LIMIT 1",
			$artefact_id
		)
	);

	$all_snapshots = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
			 FROM {$snapshots_table}
			 WHERE artefact_id = %d
			 ORDER BY created_at ASC, id ASC",
			$artefact_id
		)
	) ?: array();

	$artefact_meta = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT
				a.id,
				a.status,
				a.created_at,
				c.class_title,
				i.issue_title
			 FROM {$artefacts_table} a
			 LEFT JOIN {$classes_table} c ON c.id = a.class_id
			 LEFT JOIN {$issues_table} i ON i.id = a.issue_id
			 WHERE a.id = %d
			 LIMIT 1",
			$artefact_id
		)
	);
}

function glandore_sa_result_text( $value ) : string {
	if ( is_array( $value ) || is_object( $value ) ) {
		$value = wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}

	return trim( wp_strip_all_tags( (string) $value ) );
}

function glandore_sa_result_paragraph( $value ) : void {
	$text = glandore_sa_result_text( $value );

	if ( '' === $text ) {
		return;
	}


	/*
	 * Render markdown-style content when detected.
	 */
	if (
		str_contains( $text, '# ' )
		|| str_contains( $text, '## ' )
		|| str_contains( $text, '### ' )
		|| str_contains( $text, '- ' )
	) {
		echo '<div class="glandore-sa-markdown">';
		echo wp_kses_post( glandore_sa_render_markdown( $text ) );
		echo '</div>';
		return;
	}

	echo '<p>' . nl2br( esc_html( $text ) ) . '</p>';

}

function glandore_sa_result_input_item( string $label, $value ) : void {
	$text = glandore_sa_result_text( $value );

	if ( '' === $text ) {
		return;
	}
	?>
	<div class="glandore-sa-input-item">
		<h3><?php echo esc_html( $label ); ?></h3>
		<?php glandore_sa_result_paragraph( $text ); ?>
	</div>
	<?php
}

function glandore_sa_snapshot_data( $snapshot_row ) : array {
	if ( empty( $snapshot_row->snapshot_json ) ) {
		return array();
	}

	$data = json_decode( (string) $snapshot_row->snapshot_json, true );

	return is_array( $data ) ? $data : array();
}

function glandore_sa_input_label( string $key ) : string {
	$labels = array(
		'issue_description'  => 'Issue',
		'tried'              => 'What has been tried',
		'success_failure'    => 'Successes and failures',
		'success_looks_like' => 'What success would look like',
		'other_relevant'     => 'Anything else relevant',
	);

	return $labels[ $key ] ?? ucwords( str_replace( '_', ' ', $key ) );
}

function glandore_sa_render_snapshot_sections( $snapshot_row, bool $history_mode = false ) : void {
	$data = glandore_sa_snapshot_data( $snapshot_row );

	$input        = isset( $data['input'] ) && is_array( $data['input'] ) ? $data['input'] : array();
	$overview     = $data['overview'] ?? '';
	$perspectives = isset( $data['perspectives'] ) && is_array( $data['perspectives'] ) ? $data['perspectives'] : array();
	$ideas        = isset( $data['ideas_for_consideration'] ) && is_array( $data['ideas_for_consideration'] ) ? $data['ideas_for_consideration'] : array();
	$metadata     = isset( $data['metadata'] ) && is_array( $data['metadata'] ) ? $data['metadata'] : array();
	?>

	<?php if ( ! empty( $input ) ) : ?>
		<section class="glandore-sa-section">
			<h2><?php esc_html_e( 'Submitted information', 'glandore' ); ?></h2>

			<div class="glandore-sa-input-grid">
				<?php foreach ( $input as $key => $value ) : ?>
					<?php glandore_sa_result_input_item( glandore_sa_input_label( (string) $key ), $value ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( '' !== glandore_sa_result_text( $overview ) ) : ?>
		<section class="glandore-sa-section glandore-sa-overview-section">
			<h2><?php esc_html_e( 'Overview', 'glandore' ); ?></h2>
			<?php glandore_sa_result_paragraph( $overview ); ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $perspectives ) ) : ?>
		<section class="glandore-sa-section">
			<h2><?php esc_html_e( 'Perspective analysis', 'glandore' ); ?></h2>

			<?php if ( $history_mode ) : ?>
				<?php foreach ( $perspectives as $perspective ) : ?>
					<div class="glandore-sa-history-perspective">
						<h3><?php echo esc_html( glandore_sa_result_text( $perspective['question'] ?? 'Perspective' ) ); ?></h3>
						<?php glandore_sa_result_paragraph( $perspective['analysis'] ?? '' ); ?>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="glandore-sa-perspectives">
					<?php foreach ( $perspectives as $index => $perspective ) : ?>
						<?php
						$question    = $perspective['question'] ?? 'Perspective';
						$analysis    = $perspective['analysis'] ?? '';
						$checkbox_id = 'glandore-sa-perspective-' . absint( $index );
						?>

						<input
							type="checkbox"
							class="glandore-sa-perspective-checkbox"
							id="<?php echo esc_attr( $checkbox_id ); ?>"
							<?php checked( 0, $index ); ?>
						>

						<div class="glandore-sa-perspective">
							<label class="glandore-sa-perspective-label" for="<?php echo esc_attr( $checkbox_id ); ?>">
								<?php echo esc_html( $question ); ?>
							</label>

							<div class="glandore-sa-perspective-body">
								<?php glandore_sa_result_paragraph( $analysis ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $ideas ) ) : ?>
		<section class="glandore-sa-section">
			<h2><?php esc_html_e( 'Ideas for your consideration', 'glandore' ); ?></h2>

			<ul class="glandore-sa-ideas">
				<?php foreach ( $ideas as $idea ) : ?>
					<?php $idea_text = glandore_sa_result_text( $idea ); ?>
					<?php if ( '' !== $idea_text ) : ?>
						<li><?php echo esc_html( $idea_text ); ?></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

	<section class="glandore-sa-section glandore-sa-snapshot-details">
		<h2><?php esc_html_e( 'Snapshot details', 'glandore' ); ?></h2>

		<div class="glandore-sa-meta">
			<?php if ( ! empty( $snapshot_row->snapshot_version ) ) : ?>
				<span><?php echo esc_html( 'Snapshot version ' . $snapshot_row->snapshot_version ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $snapshot_row->created_at ) ) : ?>
				<span><?php echo esc_html( 'Created ' . $snapshot_row->created_at ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $metadata['based_on_snapshot_id'] ) ) : ?>
				<span><?php echo esc_html( 'Based on snapshot #' . $metadata['based_on_snapshot_id'] ); ?></span>
			<?php endif; ?>
		</div>
	</section>
	<?php
}


function glandore_sa_render_markdown( string $markdown ) : string {
	$markdown = trim( $markdown );

	if ( '' === $markdown ) {
		return '';
	}

	/*
	 * Normalise line endings.
	 */
	$markdown = str_replace( array( "\r\n", "\r" ), "\n", $markdown );

	/*
	 * Headings.
	 */
	$markdown = preg_replace( '/^### (.+)$/m', '<h5>$1</h5>', $markdown );
	$markdown = preg_replace( '/^## (.+)$/m', '<h4>$1</h4>', $markdown );
	$markdown = preg_replace( '/^# (.+)$/m', '<h3>$1</h3>', $markdown );

	/*
	 * Bullet lists.
	 */
	$markdown = preg_replace( '/^- (.+)$/m', '<li>$1</li>', $markdown );
	$markdown = preg_replace( '/(<li>.*<\/li>)/sU', '<ul>$1</ul>', $markdown );

	/*
	 * Paragraphs.
	 */
	$blocks = preg_split( '/\n\s*\n/', $markdown );

	$html = array();

	foreach ( $blocks as $block ) {
		$block = trim( $block );

		if ( '' === $block ) {
			continue;
		}

		if (
			preg_match( '/^<h[1-6]>/', $block )
			|| preg_match( '/^<ul>/', $block )
		) {
			$html[] = $block;
			continue;
		}

		$html[] = '<p>' . nl2br( esc_html( wp_strip_all_tags( $block ) ) ) . '</p>';
	}

	return implode( "\n", $html );
}


function glandore_sa_history_document( array $snapshots, int $artefact_id, $artefact_meta ) : void {
	?>
	<main class="glandore-page glandore-issue-studio-result">
		<section class="glandore-sa-result-hero">
			<p class="glandore-sa-result-eyebrow"><?php esc_html_e( 'Issue Studio', 'glandore' ); ?></p>
			<h1><?php esc_html_e( 'Issue History', 'glandore' ); ?></h1>

			<div class="glandore-sa-meta glandore-sa-meta--stacked">
				<span><?php echo esc_html( 'Artefact: ' . $artefact_id ); ?></span>
				<span><?php echo esc_html( 'Domain: ' . ( $artefact_meta->class_title ?? 'Unknown Domain' ) ); ?></span>
				<span><?php echo esc_html( 'Issue: ' . ( $artefact_meta->issue_title ?? 'Unknown Issue' ) ); ?></span>
				<span><?php echo esc_html( 'Status: ' . ucfirst( $artefact_meta->status ?? 'Unknown' ) ); ?></span>
				<span><?php echo esc_html( 'Created: ' . ( $artefact_meta->created_at ?? 'Unknown' ) ); ?></span>
				<span><?php echo esc_html( count( $snapshots ) . ' snapshots' ); ?></span>
			</div>
		</section>

		<?php if ( empty( $snapshots ) ) : ?>
			<section class="glandore-sa-section">
				<p><?php esc_html_e( 'No snapshots were found for this issue.', 'glandore' ); ?></p>
			</section>
		<?php else : ?>
			<?php foreach ( $snapshots as $history_snapshot ) : ?>
				<article class="glandore-sa-history-snapshot">
					<section class="glandore-sa-result-hero glandore-sa-history-snapshot-hero">
						<p class="glandore-sa-result-eyebrow"><?php esc_html_e( 'Snapshot', 'glandore' ); ?></p>
						<h1><?php echo esc_html( 'Snapshot #' . $history_snapshot->id ); ?></h1>

						<div class="glandore-sa-meta">
							<?php if ( ! empty( $history_snapshot->snapshot_version ) ) : ?>
								<span><?php echo esc_html( 'Version ' . $history_snapshot->snapshot_version ); ?></span>
							<?php endif; ?>

							<?php if ( ! empty( $history_snapshot->created_at ) ) : ?>
								<span><?php echo esc_html( 'Created ' . $history_snapshot->created_at ); ?></span>
							<?php endif; ?>
						</div>
					</section>

					<?php glandore_sa_render_snapshot_sections( $history_snapshot, true ); ?>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>
	</main>
	<?php
}

if ( 'html' === $issue_history_mode ) {
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="issue-studio-history-' . $artefact_id . '.html"' );
	?>
	<!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<title><?php echo esc_html( 'Issue History - Artefact #' . $artefact_id ); ?></title>
		
	</head>
	<body>
		<?php glandore_sa_history_document( $all_snapshots, $artefact_id, $artefact_meta ); ?>
	</body>
	</html>
	<?php
	exit;
}

if ( 'print' === $issue_history_mode ) {
	?>
	<!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo esc_html( 'Issue History - Artefact #' . $artefact_id ); ?></title>

		<style id="glandore-sa-print-history-styles">
		@media print {

			.glandore-sa-history-snapshot .glandore-sa-overview-section {
				page-break-before: always;
				break-before: page;
			}

		}
		</style>

		
	</head>
	<body>
		<?php glandore_sa_history_document( $all_snapshots, $artefact_id, $artefact_meta ); ?>

		<script>
			window.addEventListener('load', function () {
				window.print();
			});
		</script>
	</body>
	</html>
	<?php
	exit;
}

$history_print_url = add_query_arg(
	array(
		'artefact_id'    => $artefact_id,
		'issue_history' => 'print',
	),
	home_url( '/issue-studio-result/' )
);

$history_html_url = add_query_arg(
	array(
		'artefact_id'    => $artefact_id,
		'issue_history' => 'html',
	),
	home_url( '/issue-studio-result/' )
);

$print_url = add_query_arg(
	'artefact_id',
	$artefact_id,
	home_url( '/issue-studio-print/' )
);

get_header();
?>

<main class="glandore-page glandore-issue-studio-result" id="glandore-sa-export-root">

	<?php if ( ! $snapshot ) : ?>

		<section class="glandore-sa-empty">
			<h1><?php esc_html_e( 'Issue Studio Result', 'glandore' ); ?></h1>
			<p><?php esc_html_e( 'No snapshot was found for this artefact.', 'glandore' ); ?></p>
		</section>

	<?php else : ?>

		<section class="glandore-sa-result-hero">
			<p class="glandore-sa-result-eyebrow"><?php esc_html_e( 'Issue Studio', 'glandore' ); ?></p>
			<h1><?php esc_html_e( 'Assessment', 'glandore' ); ?></h1>

			<p class="glandore-sa-result-intro">
				<?php esc_html_e( 'This analysis preserves the original issue description, explores the selected perspectives, and offers practical ideas for your consideration.', 'glandore' ); ?>
			</p>

			<div class="glandore-sa-meta">
				<span><?php echo esc_html( 'Artefact #' . $artefact_id ); ?></span>
				<span><?php echo esc_html( 'Snapshot #' . $snapshot->id ); ?></span>
				<?php if ( ! empty( $snapshot->snapshot_version ) ) : ?>
					<span><?php echo esc_html( 'Version ' . $snapshot->snapshot_version ); ?></span>
				<?php endif; ?>
			</div>
		</section>

		<?php glandore_sa_render_snapshot_sections( $snapshot, false ); ?>

		<section class="glandore-sa-section glandore-sa-actions-panel">
			<h2><?php esc_html_e( 'Next steps', 'glandore' ); ?></h2>

			<div class="glandore-sa-action-buttons">
				<button type="button" class="glandore-sa-action-button" onclick="window.open('<?php echo esc_url( $print_url ); ?>', '_blank', 'noopener');">
					<?php esc_html_e( 'Print / save as PDF', 'glandore' ); ?>
				</button>

				<button type="button" class="glandore-sa-action-button" id="glandore-sa-export-html">
					<?php esc_html_e( 'Download HTML', 'glandore' ); ?>
				</button>

				<a class="glandore-sa-action-button" href="<?php echo esc_url( $chain_url ); ?>">
					<?php esc_html_e( 'Chain', 'glandore' ); ?>
				</a>
			</div>

			<form
				method="post"
				action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
				class="glandore-sa-rerun-form"
				id="glandore-sa-rerun-form"
			>
				<input type="hidden" name="action" value="glandore_sa_rerun_with_comment">
				<input type="hidden" name="artefact_id" value="<?php echo esc_attr( $artefact_id ); ?>">
				<input type="hidden" name="snapshot_id" value="<?php echo esc_attr( $snapshot->id ); ?>">

				<?php wp_nonce_field( 'glandore_sa_rerun_with_comment', 'glandore_sa_rerun_nonce' ); ?>

				<label class="glandore-sa-comment-label" for="sa_comment">
					<?php esc_html_e( 'Refine issue from this comment', 'glandore' ); ?>
				</label>

				<?php
				wp_editor(
					'',
					'sa_comment',
					array(
						'textarea_name' => 'sa_comment',
						'media_buttons' => false,
						'textarea_rows' => 5,
						'teeny'         => true,
						'quicktags'     => false,
						'tinymce'       => array(
							'toolbar1' => 'bold,italic,blockquote,bullist,numlist,link,undo,redo,removeformat',
							'toolbar2' => '',
							'height'   => 180,
						),
					)
				);
				?>

				<button type="submit" class="glandore-sa-action-button glandore-sa-rerun-button" id="glandore-sa-rerun-button">
					<?php esc_html_e( 'Refine issue from this comment', 'glandore' ); ?>
				</button>

				<div class="glandore-sa-loading" id="glandore-sa-rerun-loading" role="status" aria-live="polite">
					<strong><?php esc_html_e( 'Rerunning the analysis…', 'glandore' ); ?></strong>
					<?php esc_html_e( 'This creates a new snapshot. Please keep this page open while Issue Studio prepares the revised analysis.', 'glandore' ); ?>
				</div>

				<p class="glandore-sa-rerun-note">
					<?php esc_html_e( 'This creates a new snapshot using your comment as the latest issue input. The previous analysis is preserved.', 'glandore' ); ?>
				</p>
			</form>
		</section>

		<section class="glandore-sa-section glandore-sa-history-panel">
			<h2><?php esc_html_e( 'History', 'glandore' ); ?></h2>

			<div class="glandore-sa-action-buttons">
				<a class="glandore-sa-action-button" href="<?php echo esc_url( $history_print_url ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'Print / save as PDF', 'glandore' ); ?>
				</a>

				<a class="glandore-sa-action-button" href="<?php echo esc_url( $history_html_url ); ?>">
					<?php esc_html_e( 'Download HTML', 'glandore' ); ?>
				</a>
			</div>
		</section>

	<?php endif; ?>

</main>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		const exportButton = document.getElementById('glandore-sa-export-html');
		const rerunForm = document.getElementById('glandore-sa-rerun-form');
		const rerunButton = document.getElementById('glandore-sa-rerun-button');
		const loading = document.getElementById('glandore-sa-rerun-loading');

		function downloadHtml(rootId, fileName, removeSelector) {
			const root = document.getElementById(rootId);

			if (!root) {
				return;
			}

			const cloned = root.cloneNode(true);

			if (removeSelector) {
				const removable = cloned.querySelector(removeSelector);

				if (removable) {
					removable.remove();
				}
			}

			const html = '<!doctype html><html><head><meta charset="utf-8"><title>Issue Studio Export</title></head><body>' + cloned.outerHTML + '</body></html>';
			const blob = new Blob([html], { type: 'text/html' });
			const url = URL.createObjectURL(blob);
			const link = document.createElement('a');

			link.href = url;
			link.download = fileName;
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
			URL.revokeObjectURL(url);
		}

		if (exportButton) {
			exportButton.addEventListener('click', function () {
				downloadHtml(
					'glandore-sa-export-root',
					'issue-studio-artefact-<?php echo esc_js( (string) $artefact_id ); ?>.html',
					'.glandore-sa-actions-panel'
				);
			});
		}

		if (rerunForm) {
			rerunForm.addEventListener('submit', function () {
				if (window.tinyMCE && typeof window.tinyMCE.triggerSave === 'function') {
					window.tinyMCE.triggerSave();
				}

				if (rerunButton) {
					rerunButton.disabled = true;
					rerunButton.textContent = 'Rerunning…';
				}

				if (loading) {
					loading.classList.add('is-active');
				}
			});
		}
	});
</script>

<style id="glandore-chain-help-inline-v1">
.glandore-chain-inline-help {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 1.35rem;
	height: 1.35rem;
	margin-left: 0.55rem;
	border: 1px solid #9ca3af;
	border-radius: 999px;
	background: #fff;
	color: #374151;
	font-size: 0.85rem;
	font-weight: 700;
	line-height: 1;
	cursor: help;
	vertical-align: middle;
	flex-shrink: 0;
}

.glandore-chain-inline-help:hover,
.glandore-chain-inline-help:focus {
	border-color: #2563eb;
	color: #2563eb;
	outline: none;
}
</style>

<script id="glandore-chain-help-inline-v1">
document.addEventListener('DOMContentLoaded', function () {

	const helpText =
		'Creates a new chained assessment using the selected Domain, Issue and Perspectives, while preserving the previous assessment as historical context.';

	const controls = Array.from(document.querySelectorAll('a, button'));

	const chainButton = controls.find(function (el) {
		const text = (el.textContent || el.value || '')
			.trim()
			.toLowerCase();

		return text === 'chain';
	});

	if (!chainButton) {
		return;
	}

	/*
	 * Prevent duplicate injection.
	 */
	if (
		chainButton.nextElementSibling &&
		chainButton.nextElementSibling.classList.contains('glandore-chain-inline-help')
	) {
		return;
	}

	const help = document.createElement('span');

	help.className = 'glandore-chain-inline-help';
	help.textContent = '?';

	help.setAttribute('title', helpText);
	help.setAttribute('tabindex', '0');
	help.setAttribute('role', 'img');
	help.setAttribute('aria-label', helpText);

	chainButton.insertAdjacentElement('afterend', help);
});
</script>

<?php get_footer(); ?>
