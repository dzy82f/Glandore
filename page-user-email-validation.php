<?php
/**
 * Glandore – Registration (URM email validation page)
 *
 * Layout:
 * - Left: standard landing intro over harbour background
 * - Right: registration card with URM form
 *
 * This file is picked up automatically for the page whose slug is
 * "user-email-validation".
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="registration-overlay-page">

    <div class="landing-background landing-background--registration">

        <div class="landing-overlay"></div>

        <main class="landing-content registration-overlay-layout">

            <div class="landing-intro-content registration-intro">
                <?php
                // Reuse the same intro block as the home page
                get_template_part( 'template_parts/intro' );
                ?>
            </div>

            <aside class="registration-panel-wrapper">
                <div class="registration-panel glandore-auth-card">

                    <h2 class="account-panel-title">User Registration</h2>

                    <p>
                        User registration is a two-stage process: first you verify your email,
                        then you will receive an email with a link to complete your registration.
                    </p>

                    <div class="registration-panel-form" style="margin-top: 1.5rem;">
                        <?php
                        echo do_shortcode( '[user_registration_form id="8273"]' );
                        ?>
                    </div>

                </div>
            </aside>

        </main>

    </div>
</div>

<?php
get_footer();
