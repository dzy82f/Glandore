<?php
/**
 * Template Name: NHS
 */

get_header();

/**
 * Artefacts surfaced on the NHS page.
 * Hierarchy is expressed via `parent`.
 */
$artefacts = [	
	
	[
        'title'  => "It's broken, so what might be done?",
        'parent' => null,
        'url'    => '/wp-content/uploads/2025/12/NHS_summary_v0_1.pdf',
    ],
	[
        'title'  => 'Foundation Paper.',
        'parent' => null,
        'url'    => '/wp-content/uploads/2025/12/NHS___Foundation_paper_v1.6.pdf',
    ],
	[
        'title'  => 'Is the NHS well run?',
        'parent' => null,
        'url'    => '/wp-content/uploads/2025/12/NHS_Well_Run_v0_1.pdf',
    ],
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT: main content -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'nhs'); ?>
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