<?php
/**
 * Template Name: Issue Input
 *
 * Issue Studio handoff contract:
 * - Receives class_id, issue_id, perspectives[] from Issue Studio.
 * - Optionally receives boilerplate_artefact_id to prefill the form.
 * - Submits od_issue, od_tried, od_results, od_success, od_other.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Chain mode:
 * If Issue Input is reached from a chained Issue Studio selection,
 * create the new chained assessment immediately and redirect to its result.
 * This must run before get_header(), otherwise redirects may fail.
 */
$glandore_chain_from_artefact_id = isset( $_GET['chain_from_artefact_id'] ) ? absint( $_GET['chain_from_artefact_id'] ) : 0;
$glandore_chain_from_snapshot_id = isset( $_GET['chain_from_snapshot_id'] ) ? absint( $_GET['chain_from_snapshot_id'] ) : 0;

if ( $glandore_chain_from_artefact_id && $glandore_chain_from_snapshot_id ) {
	$glandore_chain_class_id = isset( $_GET['class_id'] ) ? absint( $_GET['class_id'] ) : 0;
	$glandore_chain_issue_id = isset( $_GET['issue_id'] ) ? absint( $_GET['issue_id'] ) : 0;
	$glandore_chain_perspectives = array();

	if ( isset( $_GET['perspectives'] ) ) {
		$glandore_raw_perspectives = wp_unslash( $_GET['perspectives'] );

		if ( is_array( $glandore_raw_perspectives ) ) {
			$glandore_chain_perspectives = array_map( 'absint', $glandore_raw_perspectives );
		} else {
			$glandore_chain_perspectives = array_filter(
				array_map(
					'absint',
					explode( ',', sanitize_text_field( $glandore_raw_perspectives ) )
				)
			);
		}
	}

	if (
		function_exists( 'glandore_sa_chain_create_assessment' )
		&& $glandore_chain_class_id
		&& $glandore_chain_issue_id
	) {
		$glandore_child_artefact_id = glandore_sa_chain_create_assessment(
			$glandore_chain_class_id,
			$glandore_chain_issue_id,
			$glandore_chain_perspectives,
			$glandore_chain_from_artefact_id,
			$glandore_chain_from_snapshot_id
		);

		if ( $glandore_child_artefact_id ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'artefact_id' => $glandore_child_artefact_id,
					),
					home_url( '/issue-studio-result/' )
				)
			);
			exit;
		}
	}

	wp_die( esc_html__( 'Issue Studio could not create the chained assessment.', 'glandore' ) );
}

get_header();

global $wpdb;

$class_id                = isset( $_GET['class_id'] ) ? absint( $_GET['class_id'] ) : 0;
$issue_id                = isset( $_GET['issue_id'] ) ? absint( $_GET['issue_id'] ) : 0;
$boilerplate_artefact_id = isset( $_GET['boilerplate_artefact_id'] ) ? absint( $_GET['boilerplate_artefact_id'] ) : 0;

$perspectives = array();

if ( isset( $_GET['perspectives'] ) ) {
	$raw_perspectives = wp_unslash( $_GET['perspectives'] );

	if ( is_array( $raw_perspectives ) ) {
		$perspectives = array_map( 'absint', $raw_perspectives );
	} else {
		$perspectives = array_filter(
			array_map(
				'absint',
				explode( ',', sanitize_text_field( $raw_perspectives ) )
			)
		);
	}
}

function glandore_od_load_boilerplate( int $artefact_id ) : array {
	global $wpdb;

	$empty = array(
		'od_issue'   => '',
		'od_tried'   => '',
		'od_results' => '',
		'od_success' => '',
		'od_other'   => '',
	);

	if ( $artefact_id <= 0 ) {
		return $empty;
	}

	$table = $wpdb->prefix . 'sa_artefacts';

	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT *
			 FROM {$table}
			 WHERE id = %d
			 LIMIT 1",
			$artefact_id
		),
		ARRAY_A
	);

	if ( ! $row ) {
		return $empty;
	}

	$from_json = array();

	if ( ! empty( $row['input_json'] ) ) {
		$decoded = json_decode( (string) $row['input_json'], true );

		if ( is_array( $decoded ) ) {
			$from_json = $decoded;
		}
	}

	return array(
		'od_issue'   => (string) ( $row['issue_description'] ?? $from_json['issue_description'] ?? '' ),
		'od_tried'   => (string) ( $row['tried'] ?? $from_json['tried'] ?? '' ),
		'od_results' => (string) ( $row['success_failure'] ?? $from_json['success_failure'] ?? '' ),
		'od_success' => (string) ( $row['success_looks_like'] ?? $from_json['success_looks_like'] ?? '' ),
		'od_other'   => (string) ( $row['other_relevant'] ?? $from_json['other_relevant'] ?? '' ),
	);
}

function glandore_od_editor( string $id, string $label, string $content = '' ) : void {
	?>
	<div class="cop-post-field">
		<label for="<?php echo esc_attr( $id ); ?>" class="cop-post-label">
			<?php echo esc_html( $label ); ?>
		</label>

		<?php
		wp_editor(
			$content,
			$id,
			array(
				'textarea_name' => $id,
				'media_buttons' => false,
				'textarea_rows' => 3,
				'teeny'         => true,
				'quicktags'     => false,
				'tinymce'       => array(
					'toolbar1' => 'bold,italic,blockquote,bullist,numlist,link,undo,redo,removeformat',
					'toolbar2' => '',
					'height'   => 110,
					'setup'    => 'function(editor) {
						editor.on("focus", function() {
							editor.theme.resizeTo(null, 220);
						});
						editor.on("blur", function() {
							if (!editor.getContent({ format: "text" }).trim()) {
								editor.theme.resizeTo(null, 110);
							}
						});
					}',
				),
			)
		);
		?>
	</div>
	<?php
}

$boilerplate = glandore_od_load_boilerplate( $boilerplate_artefact_id );

$class_title = '';

if ( $class_id > 0 ) {
	$classes_table = $wpdb->prefix . 'sa_classes';

	$class_title = (string) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT class_title
			 FROM {$classes_table}
			 WHERE id = %d
			 LIMIT 1",
			$class_id
		)
	);
}

if ( '' === trim( $class_title ) ) {
	$class_title = 'Issue Input';
}

if ( function_exists( 'glandore_sa_chain_redirect_from_issue_input_if_needed' ) ) {
	glandore_sa_chain_redirect_from_issue_input_if_needed(
		$class_id,
		$issue_id,
		$perspectives
	);
}
?>

<div class="cop-post-page">
	<div class="glandore-auth-card cop-post-card">

		<h1 class="account-panel-title">
			<?php echo esc_html( $class_title ); ?>
		</h1>

		<p class="cop-post-mode">
			<?php esc_html_e( 'Please describe in as much detail as possible.', 'glandore' ); ?>
		</p>

		<?php if ( $boilerplate_artefact_id > 0 ) : ?>
			<p class="cop-post-mode">
				<?php esc_html_e( 'Boilerplate text has been generated for review. You can edit it before submitting.', 'glandore' ); ?>
			</p>
		<?php endif; ?>

		<section class="cop-post-form-section">
			<form
				method="post"
				action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
				class="cop-post-form"
				id="glandore-sa-submit-form"
			>
				<input type="hidden" name="action" value="glandore_sa_submit">
				<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>">
				<input type="hidden" name="issue_id" value="<?php echo esc_attr( $issue_id ); ?>">
				<input type="hidden" name="boilerplate_artefact_id" value="<?php echo esc_attr( $boilerplate_artefact_id ); ?>">

				<?php foreach ( $perspectives as $perspective_id ) : ?>
					<input type="hidden" name="perspectives[]" value="<?php echo esc_attr( $perspective_id ); ?>">
				<?php endforeach; ?>

				<?php wp_nonce_field( 'glandore_sa_submit', 'glandore_sa_nonce' ); ?>

				<?php
				glandore_od_editor( 'od_issue', 'The issue you’d like help with.', $boilerplate['od_issue'] );
				glandore_od_editor( 'od_tried', 'What you’ve tried so far.', $boilerplate['od_tried'] );
				glandore_od_editor( 'od_results', 'The successes and failures you’ve encountered.', $boilerplate['od_results'] );
				glandore_od_editor( 'od_success', 'What success would look like.', $boilerplate['od_success'] );
				glandore_od_editor( 'od_other', 'Anything else that you think is relevant.', $boilerplate['od_other'] );
				?>

				<div class="cop-post-actions">
					<button type="submit" class="cop-post-submit" id="glandore-sa-submit-button">
						<?php esc_html_e( 'Submit', 'glandore' ); ?>
					</button>
				</div>

				<div class="glandore-sa-loading" id="glandore-sa-loading" role="status" aria-live="polite" hidden>
					<strong><?php esc_html_e( 'Analysing your issue…', 'glandore' ); ?></strong>
					<?php esc_html_e( 'This may take a little while. Please keep this page open while Issue Studio prepares the analysis and next steps.', 'glandore' ); ?>
				</div>
			</form>
		</section>

	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const form = document.getElementById('glandore-sa-submit-form');
	const button = document.getElementById('glandore-sa-submit-button');
	const loading = document.getElementById('glandore-sa-loading');

	if (!form || !button || !loading) {
		return;
	}

	form.addEventListener('submit', function () {
		if (window.tinyMCE && typeof window.tinyMCE.triggerSave === 'function') {
			window.tinyMCE.triggerSave();
		}

		button.disabled = true;
		button.textContent = 'Analysing…';
		loading.hidden = false;
		loading.classList.add('is-active');
	});
});
</script>

<?php
get_footer( 'cop-minimal' );
