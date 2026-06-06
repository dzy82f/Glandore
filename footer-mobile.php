<?php
/**
 * Mobile footer – policy links only (no © line).
 */
?>

<footer class="site-footer footer-mobile" role="contentinfo">
    <div class="footer-mobile__inner">
        <p>
            <a href="#" data-footer-panel="attribution">Attribution</a> ·
            <a href="#" data-footer-panel="accessibility">Accessibility</a> ·
            <a href="#" data-footer-panel="privacy">Privacy</a> ·
            <a href="#" data-cookie-open-modal="true">Cookies</a>
        </p>
    </div>
</footer>

<?php get_template_part( 'template_parts/modals/modal-cookies' ); ?>

<?php
echo "\n<!-- Glandore Account Modal START (mobile) -->\n";
get_template_part( 'template_parts/modals/modal-account' );
echo "\n<!-- Glandore Account Modal END (mobile) -->\n";
?>

<?php get_template_part( 'template_parts/modals/modal-registration-completion' ); ?>
<?php get_template_part( 'template_parts/modals/modal-registration-update' ); ?>

<?php wp_footer(); ?>

<?php get_template_part( 'template_parts/site-shell-close' ); ?>

</body>
</html>
