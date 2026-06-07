<?php
/**
 * Remove XSL styling from WordPress sitemap
 * Forces raw XML output for Google
 */

add_filter('wp_sitemaps_stylesheet_url', '__return_false');