<?php
/**
 * Template Name: CAS
 */

get_header();

/**
 * Artefacts surfaced on the Systems page.
 */
$artefacts = [
    [
        'title' => 'Complex Adaptive Systems (.pdf)',
        'url'   => '/wp-content/uploads/2025/12/complex_adaptive_systems_v0_1.pdf',
    ],
	[
        'title' => 'Thinking within Complexity (.pdf)',
        'url'   => '/wp-content/uploads/2026/03/SFI_va_3.pdf',
    ],
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT: narrative -->
        <div class="page-split-left">
            <article class="page-content">
                <?php
                while (have_posts()) :
                    the_post();
                    the_content();
                endwhile;
                ?>
            </article>
        </div>

 <!-- RIGHT: additional artefacts -->
 
		<div class="page-split-right">
			<h4>Artefacts</h4>

			<?php
			if (!empty($artefacts)) {
				get_template_part(
					'template_parts/artefact-list',
					null,
					['artefacts' => $artefacts]
				);
			} else {
				echo '<p class="artefact-empty">There are no additional artefacts currently.</p>';
			}
			?>
		</div>

    </div>
</main>
