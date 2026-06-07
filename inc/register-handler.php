<?php
/**
 * Core request / AJAX handlers for the Glandore theme.
 */
 
 if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once get_template_directory() . '/template_parts/footer-panels/footer-panel-helper.php';

/**
 * --------------------------------------------------------
 * REGISTRATION HANDLER (STUB)
 * --------------------------------------------------------
 *
 * This keeps the admin-post wiring alive so forms posting to
 * ?action=glandore_register do not fatal. You can replace the
 * body with the real implementation when ready.
 */

if ( ! function_exists( 'glandore_handle_register_stage1' ) ) {

    function glandore_handle_register_stage1() {

        // TODO: implement real registration flow.
        // For now, just stop cleanly so we do not break the site.
        wp_die( 'Registration handler not implemented.' );
    }

    add_action( 'admin_post_nopriv_glandore_register', 'glandore_handle_register_stage1' );
    add_action( 'admin_post_glandore_register',        'glandore_handle_register_stage1' );
}

/**
 * --------------------------------------------------------
 * FOOTER PANELS (AJAX)
 * --------------------------------------------------------
 *
 * JS calls look like:
 *   ?action=glandore_attribution
 *   ?action=glandore_accessibility
 *   ?action=glandore_privacy
 *   ?action=glandore_cookies
 *
 * Optionally we can also support:
 *   ?action=glandore_footer_panel&panel=attribution
 */

/**
 * Render a specific footer panel template.
 *
 * Valid slugs map to:
 *   template_parts/footer-panels/{slug}.php
 */
function glandore_render_footer_panel( $panel_slug ) {

    $panel_slug = sanitize_key( $panel_slug );

    $allowed = [
        'attribution'   => 'attribution',
        'accessibility' => 'accessibility',
        'privacy'       => 'privacy',
        'cookies'       => 'cookies',
    ];

    if ( ! isset( $allowed[ $panel_slug ] ) ) {
        wp_die(); // silent fail
    }

    get_template_part(
        'template_parts/footer-panels/' . $allowed[ $panel_slug ]
    );
}

/**
 * Main AJAX handler for footer panels.
 */
function glandore_footer_panel_ajax() {

    // 1) Prefer explicit ?panel=attribution
    $panel = '';
    if ( isset( $_REQUEST['panel'] ) ) {
        $panel = sanitize_key( $_REQUEST['panel'] );
    }

    // 2) Fallback: derive from action=glandore_{slug}
    if ( ! $panel && isset( $_REQUEST['action'] ) ) {
        $action = sanitize_key( $_REQUEST['action'] );

        if ( strpos( $action, 'glandore_' ) === 0 ) {
            $panel = substr( $action, strlen( 'glandore_' ) );
        }
    } 

    if ( ! $panel ) {
        wp_die(); // nothing to do
    }

    glandore_render_footer_panel( $panel );
    wp_die(); // important for admin-ajax
}

// Main “panel” action (if you ever call ?action=glandore_footer_panel&panel=...)
add_action( 'wp_ajax_glandore_footer_panel',        'glandore_footer_panel_ajax' );
add_action( 'wp_ajax_nopriv_glandore_footer_panel', 'glandore_footer_panel_ajax' );

// Direct aliases for the current JS behaviour (?action=glandore_attribution, etc.)
add_action( 'wp_ajax_glandore_attribution',        'glandore_footer_panel_ajax' );
add_action( 'wp_ajax_nopriv_glandore_attribution', 'glandore_footer_panel_ajax' );

add_action( 'wp_ajax_glandore_accessibility',        'glandore_footer_panel_ajax' );
add_action( 'wp_ajax_nopriv_glandore_accessibility', 'glandore_footer_panel_ajax' );

add_action( 'wp_ajax_glandore_privacy',        'glandore_footer_panel_ajax' );
add_action( 'wp_ajax_nopriv_glandore_privacy', 'glandore_footer_panel_ajax' );

add_action( 'wp_ajax_glandore_cookies',        'glandore_footer_panel_ajax' );
add_action( 'wp_ajax_nopriv_glandore_cookies', 'glandore_footer_panel_ajax' );
