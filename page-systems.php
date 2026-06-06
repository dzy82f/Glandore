<?php
/**
 * Template Name: Systems
 */

get_header();

/**
 * Artefacts surfaced on the Systems page.
 * Canonical hierarchy enforced via explicit parent.
 */
$artefacts = [
    [
        'title'  => 'Systems',
        'parent' => null,
        'url'    => '/systems/',
    ],
    [
        'title'  => 'Complex Adaptive Systems',
        'parent' => 'Systems',
        'url'    => '/complex-adaptive-systems/',
    ],
    [
        'title'  => 'Wicked Problems',
        'parent' => 'Systems',
        'url'    => '/wicked-problems/',
    ],
    [
        'title'  => 'Causal Loop Diagrams',
        'parent' => 'Systems',
        'url'    => '/system-dynamics-and-causal-loop-diagrams/',
    ],
    [
        'title'  => 'Leadership in a VUCA World',
        'parent' => 'Systems',
        'url'    => '/wp-content/uploads/2025/11/Leadership_v0_2.pdf',
    ],
    [
        'title'  => 'The Adaptive Organisation',
        'parent' => 'Systems',
        'url'    => '/wp-content/uploads/2025/10/The_Well_Run_Organisation_v0_1.pdf',
    ],
    [
        'title'  => 'Reflections on Innovation and Credibility',
        'parent' => 'Systems',
        'url'    => '/wp-content/uploads/2025/10/Lessons_on_innovation_and_credibility_v1_0-1.pdf',
    ],
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'systems'); ?>
        </div>

        <!-- RIGHT -->
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
