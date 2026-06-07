<?php
/**
 * Template Name: Corporate
 */

get_header();

/**
 * Artefacts surfaced on the Corporate page.
 * Hierarchy is expressed via `parent`.
 */
$artefacts = [

	// Corporate
    [
        'title'  => 'Corporate',
        'parent' => null,
        'url'    => '/corporate/',
    ],
    [
        'title'  => 'Shell',
        'parent' => 'Corporate',
        'url'    => '/shell/',
    ],
    [
        'title'  => 'Stellantis',
        'parent' => 'Corporate',
        'url'    => '/stellantis/',
    ],
    [
        'title'  => 'Johnson & Johnson',
        'parent' => 'Corporate',
        'url'    => '/johnson-johnson/',
    ],
    [
        'title'  => 'Premier Miton',
        'parent' => 'Corporate',
        'url'    => '/premier-miton/',
    ],
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT: main content -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'corporate'); ?>
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