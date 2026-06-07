<?php
/**
 * Template Name: Public Sector
 */

get_header();

/**
 * Artefacts surfaced on the Use Cases page.
 * Hierarchy is expressed via `parent`.
 */
$artefacts = [	
	
	[
        'title'  => 'NHS',
        'parent' => 'Public sector',
        'url'    => '/nhs/',
    ],
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT: main content -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'public-sector'); ?>
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