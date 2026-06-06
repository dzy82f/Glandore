<?php
/**
 * Template Name: Issue NHS
 *
 * NHS Issue Studio entry page.
 *
 * Creates or confirms:
 * - class_title: NHS - specific
 * - issue_title values listed below
 *
 * Then renders links into Issue Studio using:
 * - class_id
 * - issue_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wpdb;

$classes_table = $wpdb->prefix . 'sa_classes';
$issues_table  = $wpdb->prefix . 'sa_issues';

$class_title = 'NHS - specific';
$class_slug  = 'nhs-specific';

$issue_titles = array(
	'Workforce Capacity, Morale & Moral Injury',
	'Demand Escalation vs Capacity Implosion',
	'Fragmented Governance & Accountability',
	'Policy Churn & Reform Fatigue',
	'Loss of Institutional Memory',
	'Access to Care & Flow Breakdown',
	'Digital Fragmentation & Poor Interoperability',
	'Financial Volatility & Short-Termism',
	'Integration Across Organisational Boundaries',
	'Public Trust, Legitimacy & Narrative Collapse',
	'Estates Degradation & Infrastructure Risk',
	'Workforce Retention & Professional Identity',
	'Administrative Burden on Clinical Time',
	'Inequality, Prevention & Social Determinants of Health',
);

$class_id = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT id FROM {$classes_table} WHERE class_slug = %s LIMIT 1",
		$class_slug
	)
);

if ( ! $class_id ) {
	$wpdb->insert(
		$classes_table,
		array(
			'class_slug'  => $class_slug,
			'class_title' => $class_title,
			'status'      => 'active',
			'sort_order'  => 20,
		),
		array( '%s', '%s', '%s', '%d' )
	);

	$class_id = (int) $wpdb->insert_id;
}

foreach ( $issue_titles as $index => $issue_title ) {
	$issue_slug = sanitize_title( $issue_title );

	$existing_issue_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT id
			 FROM {$issues_table}
			 WHERE class_id = %d
			   AND issue_slug = %s
			 LIMIT 1",
			$class_id,
			$issue_slug
		)
	);

	if ( $existing_issue_id ) {
		continue;
	}

	$wpdb->insert(
		$issues_table,
		array(
			'class_id'    => $class_id,
			'issue_slug'  => $issue_slug,
			'issue_title' => $issue_title,
			'status'      => 'active',
			'sort_order'  => ( $index + 1 ) * 10,
		),
		array( '%d', '%s', '%s', '%s', '%d' )
	);
}

$issues = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT id, issue_title
		 FROM {$issues_table}
		 WHERE class_id = %d
		   AND status = 'active'
		 ORDER BY sort_order ASC, issue_title ASC",
		$class_id
	)
);
?>

<div class="cop-post-page">
	<div class="glandore-auth-card cop-post-card">

		<h1 class="account-panel-title">
			<?php esc_html_e( 'NHS Issues', 'glandore' ); ?>
		</h1>

		<p class="cop-post-mode">
			<?php esc_html_e( 'Choose the NHS issue you would like to explore through Issue Studio.', 'glandore' ); ?>
		</p>

		<?php if ( empty( $issues ) ) : ?>

			<p class="cop-post-mode">
				<?php esc_html_e( 'No NHS issues are currently available.', 'glandore' ); ?>
			</p>

		<?php else : ?>

			<div class="cop-discussion-list">
				<?php foreach ( $issues as $issue ) : ?>
					<?php
					$url = add_query_arg(
						array(
							'class_id' => $class_id,
							'issue_id' => (int) $issue->id,
						),
						home_url( '/issue-studio/' )
					);
					?>

					<article class="cop-discussion-card">
						<h2 class="cop-discussion-title">
							<a href="<?php echo esc_url( $url ); ?>">
								<?php echo esc_html( $issue->issue_title ); ?>
							</a>
						</h2>

						<p class="cop-discussion-excerpt">
							<?php esc_html_e( 'Explore this issue using the NHS-specific Issue Studio framework.', 'glandore' ); ?>
						</p>
					</article>
				<?php endforeach; ?>
			</div>

		<?php endif; ?>

	</div>
</div>

<?php
get_footer( 'cop-minimal' );
