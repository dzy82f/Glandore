<?php
/**
 * Artefact List
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$artefacts = $args['artefacts'] ?? [];

if ( empty( $artefacts ) || ! is_array( $artefacts ) ) {
	return;
}
?>

<ul class="artefact-list">
	<?php foreach ( $artefacts as $artefact ) : ?>
		<?php
		$title  = isset( $artefact['title'] ) ? (string) $artefact['title'] : '';
		$url    = isset( $artefact['url'] ) ? (string) $artefact['url'] : '';
		$parent = $artefact['parent'] ?? null;

		if ( '' === $title || '' === $url ) {
			continue;
		}

		$is_section = is_null( $parent );
		?>

		<li class="<?php echo $is_section ? 'depth-0 artefact-section-title' : 'depth-1 artefact-link-item'; ?>">
			<a href="<?php echo esc_url( $url ); ?>">
				<?php echo esc_html( $title ); ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>