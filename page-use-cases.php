<?php
/**
 * Template Name: Use Cases
 */

get_header();

/**
 * Artefacts surfaced on the Use Cases page.
 * Hierarchy is expressed via `parent`.
 */
$artefacts = [

    // Top level
	[
        'title' => 'General',
		'parent' => null,
        'url'   => '/general/',
    ],
    [
        'title' => 'Managing Change',
		'parent' => 'General',
        'url'   => '/change-management/',
    ],
	[
        'title' => 'Influencing the Informal Organisation',
		'parent' => 'General',
        'url'   => '/wp-content/uploads/2026/04/Informal_Organisation_v0_3.pdf',
    ],	
	[
        'title' => 'From Automating Ignorance to Emergent Intelligence (PDF)',
		'parent' => 'General',
        'url'   => '/wp-content/uploads/2025/10/Artificial_Ignorance_v1.1.pdf',
    ],	

    // Public sector
    [
        'title'  => 'Public sector',
        'parent' => null,
        'url'    => '/public-sector/',
    ],
    [
        'title'  => 'NHS',
        'parent' => 'Public sector',
        'url'    => '/nhs/',
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
            <?php get_template_part('template_parts/content', 'use-cases'); ?>
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
