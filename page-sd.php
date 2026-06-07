<?php
/**
 * Template Name: SD
 */

get_header();

/**
 * Artefacts surfaced on the Systems page.
 */
$artefacts = [
    [
        'title' => 'System Dynamics and Causal Loop Diagrams',
		'url'   => '/wp-content/uploads/2025/12/SD_and_CLD_v0_1.pdf',
    ],
	[
        'title' => 'System Dynamics (RPubs by RStudio)',
		'url'   => 'https://rpubs.com/rsmard05/sysDynR',
    ],
	[
        'title' => 'Insight Maker',
		'url'   => 'https://insightmaker.com/',
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
