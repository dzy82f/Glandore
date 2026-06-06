<?php
/**
 * Analytics consent helpers
 * Canonical source of truth for analytics permission
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check whether a user allows analytics.
 *
 * Rules:
 * - Logged-out users: false (we only track logged-in users in this model).
 * - Logged-in users:
 *     - If no preference stored yet -> default ALLOWED (true).
 *     - If preference stored        -> honour 0/1.
 *
 * @param int|null $user_id
 * @return bool
 */
function glandore_user_allows_analytics( $user_id = null ): bool {
    $user_id = $user_id ?: get_current_user_id();

    if ( ! $user_id ) {
        // No logged-in user: do not allow analytics.
        return false;
    }

    $meta_key = 'glandore_analytics_allowed';

    $value = get_user_meta( $user_id, $meta_key, true );

    // Default for new / untouched users: allow analytics.
    if ( $value === '' || $value === null ) {
        return true;
    }

    // Honour stored preference (0 / 1).
    return (bool) $value;
}

/**
 * Set analytics preference for a user.
 *
 * @param int  $user_id
 * @param bool $allowed
 * @return void
 */
function glandore_set_user_analytics_preference( $user_id, $allowed ): void {
    if ( ! $user_id ) {
        return;
    }

    $meta_key = 'glandore_analytics_allowed';

    update_user_meta(
        $user_id,
        $meta_key,
        $allowed ? 1 : 0
    );
}

/**
 * Seed default analytics preference for new users.
 * Called on registration; sets preference to "allowed" (1) if not already set.
 *
 * @param int $user_id
 * @return void
 */
function glandore_seed_default_analytics_preference( $user_id ): void {
    if ( ! $user_id ) {
        return;
    }

    $meta_key = 'glandore_analytics_allowed';
    $current  = get_user_meta( $user_id, $meta_key, true );

    if ( $current === '' || $current === null ) {
        glandore_set_user_analytics_preference( $user_id, true );
    }
}

/**
 * Hooks for user creation.
 * - Core WP registration.
 * - WPEverest "User Registration" plugin (harmless if plugin not active).
 */
add_action( 'user_register', 'glandore_seed_default_analytics_preference', 20, 1 );
add_action( 'user_registration_after_register_user_action', 'glandore_seed_default_analytics_preference', 20, 1 );

