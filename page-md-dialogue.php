<?php
/**
 * Template Name: Markdown dialogue
 */

get_header();

/**
 * Artefacts surfaced on the JnJ page.
 * Hierarchy is expressed via `parent`.
 */
$artefacts = [	
	
	[
        'title'  => "How to use Iterative Dialogue to co-create task-driven .md files",
        'parent' => null,
		'url'	 => '/wp-content/uploads/2026/03/How_to_create_better__md_v0_2.pdf'
    ],
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT: main content -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'dialogue'); ?>
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