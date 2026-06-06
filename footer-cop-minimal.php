<?php
/**
 * Minimal footer for CoP forms – no visible site footer,
 * but keeps structure + wp_footer() for scripts (TinyMCE).
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

    </div><!-- #content -->
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
