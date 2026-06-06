<?php
/**
 * --------------------------------------------------------
 * Canonical Footer Content Provider
 * --------------------------------------------------------
 * File: /template_parts/footer-panels/{slug}.php
 *
 * Purpose:
 *  - Serve *inline* footer panel content (Attribution, Accessibility, Privacy)
 *  - No modals
 *  - No page navigation
 *  - No state
 *
 * This file is loaded via admin-ajax.php
 * It outputs RAW HTML fragments only.
 */

/**
 * Shared wrapper helper for footer panels.
 * File: template_parts/footer-panels/footer-panel-helper.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'glandore_footer_panel' ) ) {
    function glandore_footer_panel( string $title, string $body ) {
        ?>
        <section class="footer-panel">
            <header class="footer-panel-header">
                <h2 class="footer-panel-title">
                    <?php echo esc_html( $title ); ?>
                </h2>
            </header>

            <div class="footer-panel-body">
                <?php echo $body; ?>
            </div>
        </section>
        <?php
    }
}


