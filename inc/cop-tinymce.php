<?php
/**
 * CoP – TinyMCE editor styling for induction form.
 *
 * Force Fira Sans (matching front-end body font) inside the front-end editors.
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Fira Sans as the content font for TinyMCE on the CoP Induction page.
 *
 * This affects the front-end wp_editor() instances (cop_who, cop_work, etc.),
 * not the main admin editor.
 *
 * @param array $mce_init TinyMCE init settings.
 * @return array
 */
function glandore_cop_tinymce_content_style( $mce_init ) {

    // Only care about the front-end, and specifically the CoP Induction template.
    if ( is_admin() ) {
        return $mce_init;
    }

    if ( ! is_page_template( 'page-cop-about-dialogue.php' )
     && ! is_page_template( 'page-cop-post.php' ) ) {
    return $mce_init;
	}

    // Inline content style for the iframe.
    // Assumes Fira Sans is already enqueued on the front-end (which it is for the site).
    $style = "
        body {
            font-family: 'Fira Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #111;
        }
        p {
            margin: 0 0 0.75em;
        }
    ";

    if ( isset( $mce_init['content_style'] ) && $mce_init['content_style'] ) {
        $mce_init['content_style'] .= ' ' . $style;
    } else {
        $mce_init['content_style'] = $style;
    }

    return $mce_init;
}
add_filter( 'tiny_mce_before_init', 'glandore_cop_tinymce_content_style' );
