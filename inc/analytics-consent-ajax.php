<?php
/**
 * AJAX handlers for analytics consent
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function glandore_handle_analytics_preference_ajax() {

    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'Not logged in', 403 );
    }

    check_ajax_referer( 'glandore_analytics_nonce', 'nonce' );

    $allowed = isset( $_POST['allowed'] ) && $_POST['allowed'] === '1';

    glandore_set_user_analytics_preference(
        get_current_user_id(),
        $allowed
    );

    wp_send_json_success( [
        'allowed' => $allowed,
    ] );
}

add_action(
    'wp_ajax_glandore_set_analytics_preference',
    'glandore_handle_analytics_preference_ajax'
);

add_action(
    'wp_ajax_nopriv_glandore_set_analytics_preference',
    'glandore_handle_analytics_preference_ajax'
);
