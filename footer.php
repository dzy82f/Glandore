<?php
/**
 * File: /wp-content/themes/glandore/footer.php
 *
 * Canonical Glandore footer.
 *
 * @package glandore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<footer class="glandore-footer" role="contentinfo">
	<div class="footer-desktop">
		<div class="footer-inner">
			<div class="footer-left">
				© <?php echo esc_html( wp_date( 'Y' ) ); ?> Glandore Associates · Reading, UK
			</div>

			<nav class="footer-nav" aria-label="Footer">
				<ul class="footer-menu">
					<li><a href="#" data-footer-panel="attribution">Attribution</a></li>
					<li><a href="#" data-footer-panel="accessibility">Accessibility</a></li>
					<li><a href="#" data-footer-panel="privacy">Privacy</a></li>
					<li><a href="#" data-cookie-open-modal="true">Cookies</a></li>
				</ul>
			</nav>
		</div>
	</div>

	<div class="footer-mobile">
		<p>© <?php echo esc_html( wp_date( 'Y' ) ); ?> Glandore Associates · Reading, UK</p>
		<p>
			<a href="#" data-footer-panel="attribution">Attribution</a> ·
			<a href="#" data-footer-panel="accessibility">Accessibility</a> ·
			<a href="#" data-footer-panel="privacy">Privacy</a> ·
			<a href="#" data-cookie-open-modal="true">Cookies</a>
		</p>
	</div>
</footer>

<?php get_template_part( 'template_parts/modals/modal-cookies' ); ?>
<?php get_template_part( 'template_parts/modals/modal-account' ); ?>
<?php get_template_part( 'template_parts/modals/modal-registration-completion' ); ?>
<?php get_template_part( 'template_parts/modals/modal-registration-update' ); ?>

<?php get_template_part( 'template_parts/site-shell-close' ); ?>

<script>
window.glandoreAjax = {
	ajaxUrl: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>
};
</script>

<?php wp_footer(); ?>
</body>
</html>