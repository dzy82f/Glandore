<?php
/**
 * Template Name: Method
 */

get_header();

/**
 * Artefacts surfaced on the Use Cases page.
 * Hierarchy is expressed via `parent`.
 */
$artefacts = [

	[
        'title'  => 'Method',
        'parent' => null,
        'url'    => 'http://167.235.246.112/wp-content/uploads/2025/12/Method_v0_1.pdf',
    ],

    // Public sector
    [
        'title'  => 'Public sector',
        'parent' => null,
        'url'    => 'http://167.235.246.112/public-sector/',
    ],
    [
        'title'  => 'NHS',
        'parent' => 'Public sector',
        'url'    => 'http://167.235.246.112/nhs/',
    ],

    // Private sector
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
            <?php get_template_part('template_parts/content', 'method'); ?>
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
