<?php
/**
 * Template Name: Situation Assessment
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="glandore-container situation-assessment">

    <!-- HEADER -->
    <section class="sa-header">
        <h1>Situation Assessment</h1>

        <p class="sa-intro">
            This framework is a compounding learning system. It helps you understand a situation more clearly over time by generating ideas based on a structured exploration of the issue.
        </p>

        <div class="sa-how-it-works">
            <p><strong>It works by:</strong></p>
            <ul>
                <li>Describing the issue as it is experienced</li>
                <li>Identifying underlying patterns, tensions, and dynamics</li>
                <li>Exploring the drivers behind recurring issues</li>
                <li>Clarifying constraints, limits, and where influence is possible</li>
            </ul>

            <p class="sa-tychevia">
                Tychevia then offers ideas for consideration, based on the understanding developed.
            </p>
        </div>
    </section>

    <!-- CURRENT ASSESSMENTS -->
    <section class="sa-current">
        <h2>Current Assessments</h2>

        <select id="sa-existing">
            <option value="">Select an assessment</option>
            <!-- future: populate from DB -->
        </select>
    </section>

    <!-- START NEW -->
    <?php
global $wpdb;

$classes_table = $wpdb->prefix . 'sa_classes';
$issues_table  = $wpdb->prefix . 'sa_issues';

$classes = $wpdb->get_results(
    "SELECT id, class_slug, class_title
     FROM {$classes_table}
     WHERE status = 'active'
     ORDER BY sort_order ASC, class_title ASC"
);

$issues = $wpdb->get_results(
    "SELECT id, class_id, issue_slug, issue_title
     FROM {$issues_table}
     WHERE status = 'active'
     ORDER BY sort_order ASC, issue_title ASC"
);
?>

<section class="sa-new">
    <h2>Start a new Assessment</h2>

    <form id="sa-form">

        <div class="sa-field">
            <label for="sa-class">Class</label>
            <select id="sa-class" name="class">
                <option value="">Select class</option>

                <?php foreach ( $classes as $class ) : ?>
                    <option value="<?php echo esc_attr( $class->id ); ?>">
                        <?php echo esc_html( $class->class_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="sa-field">
            <label for="sa-issue">Issue</label>
            <select id="sa-issue" name="issue">
                <option value="">Select issue</option>

                <?php foreach ( $issues as $issue ) : ?>
                    <option
                        value="<?php echo esc_attr( $issue->issue_slug ); ?>"
                        data-class-id="<?php echo esc_attr( $issue->class_id ); ?>"
                    >
                        <?php echo esc_html( $issue->issue_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="sa-submit">
            Start Assessment
        </button>

    </form>
</section>

            <button type="submit" class="sa-submit">
                Start Assessment
            </button>

        </form>
    </section>

</div>

<?php get_footer(); ?>