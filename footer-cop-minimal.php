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

<?php get_template_part( 'template_parts/modals/modal-account' ); ?>

<?php wp_footer(); ?>
</body>
</html>
