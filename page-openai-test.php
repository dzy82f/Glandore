<?php
/**
 * Template Name: OpenAI Test
 *
 * Simple one-off check that the GL_OPENAI_API_KEY and gatekeeper helper work.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main id="main" class="site-main">
    <div class="page-container">
        <h1>OpenAI API Test</h1>

        <?php if ( ! current_user_can( 'manage_options' ) ) : ?>

            <p>This test page is only available to admins.</p>

        <?php elseif ( ! function_exists( 'glandore_cop_evaluate_about_you_profile' ) ) : ?>

            <p><strong>Error:</strong> helper function <code>glandore_cop_evaluate_about_you_profile()</code> is not loaded. Check the <code>require_once</code> in <code>functions.php</code>.</p>

        <?php else : ?>

            <?php
            // Dummy profile for the test call.
            $profile = array(
                'opening'   => 'I am a test user who works on imaginary systems and wants to understand how communities of practice help people learn.',
                'region'    => 'Europe',
                'industry'  => 'Professional, scientific and technical activities',
                'seniority' => 'Consultant',
                'interests' => 'how AI-enabled communities might support serious practitioners',
            );

            $result = glandore_cop_evaluate_about_you_profile( $profile );
            ?>

            <?php if ( is_wp_error( $result ) ) : ?>

                <h2>Result: ERROR</h2>
                <p><strong>Message:</strong></p>
                <pre><?php echo esc_html( $result->get_error_message() ); ?></pre>

            <?php else : ?>

                <h2>Result: SUCCESS</h2>
                <p><strong>Decision:</strong> <?php echo esc_html( $result['decision'] ); ?></p>
                <p><strong>Explanation:</strong> <?php echo esc_html( $result['explanation'] ); ?></p>

                <h3>Generated bio</h3>
                <p><?php echo nl2br( esc_html( $result['bio'] ) ); ?></p>

                <?php if ( ! empty( $result['questions'] ) && is_array( $result['questions'] ) ) : ?>
                    <h3>Questions they are carrying</h3>
                    <ul>
                        <?php foreach ( $result['questions'] as $q ) : ?>
                            <li><?php echo esc_html( $q ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

            <?php endif; ?>

        <?php endif; ?>

    </div>
</main>

<?php
get_footer();
