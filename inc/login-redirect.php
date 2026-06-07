<?php
/**
 * Glandore Login Redirect
 *
 * Redirect non-admin users to the front page after login.
 * Keep default behaviour for admins so you can still reach /wp-admin/.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function glandore_login_redirect( $redirect_to, $request, $user ) {
    // If something went wrong or we don't have a user object, leave it alone.
    if ( is_wp_error( $user ) || ! ( $user instanceof WP_User ) ) {
        return $redirect_to;
    }

    // Let administrators (and only administrators) keep the normal behaviour.
    if ( in_array( 'administrator', (array) $user->roles, true ) ) {
        return $redirect_to;
    }

    // Everyone else goes to the front page.
    return home_url( '/' );
}
add_filter( 'login_redirect', 'glandore_login_redirect', 10, 3 );
