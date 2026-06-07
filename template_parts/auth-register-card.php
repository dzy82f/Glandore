<?php
/**
 * Glandore – Registration card for modal context.
 * This is returned when /registration/?register_modal=1 is requested.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="glandore-auth-card">
    <h2 class="account-panel-title">Join Glandore</h2>

    <p>
        User registration is a two-stage process: first you verify your email,
        then you will receive an email with a link to complete your registration.
    </p>

    <div class="glandore-auth-card__form">
        <?php
        // Render the normal page content (which already contains the URM form shortcode).
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                the_content();
            }
        }
        ?>
    </div>
</div>
