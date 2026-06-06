<?php
/**
 * Template Name: Shell
 */

get_header();

/**
 * Artefacts surfaced on the Shell page.
 * Hierarchy is expressed via `parent`.
 */
$artefacts = [	
	
	[
        'title'  => "Strategic Coherence",
        'parent' => null,
        'url'    => 'http://167.235.246.112/wp-content/uploads/2025/12/Shell_v0_2.pdf',
    ],
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT: main content -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'shell'); ?>
        </div>

        <!-- RIGHT: artefacts -->
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
                echo '<p class="artefact-empty">There are no artefacts currently.</p>';
            }
            ?>
        </div>

    </div>
</main>	