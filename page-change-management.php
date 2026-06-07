<?php
/**
 * Template Name: Change Management
 */

get_header();

/**
 * Artefacts surfaced on the Change Management page.
 * Hierarchy is expressed via `parent`.
 */
$artefacts = [	
	
	[
        'title'  => "Why Organisations Misdiagnose People",
        'parent' => null,
		'url'	 => '/wp-content/uploads/2026/04/Why_organisations_misdiagnose_people_v0_1.pdf'
    ],
	[
        'title'  => "Change Management toolkit",
        'parent' => null,
		'url'	 => '/wp-content/uploads/2026/04/Change_Management_Toolkit_v0_1.pdf'
    ],
	[
        'title'  => "Change Acceleration process",
        'parent' => null,
		'url'	 => '/wp-content/uploads/2026/04/Change_acceleration_process_v0_1.pdf'
    ],
	[
        'title'  => "The Tychevia Process",
        'parent' => null,
		'url'	 => '/wp-content/uploads/2026/04/Method_v0_3.pdf'
    ],
	
	
];
?>

<main id="main" class="split-layout">
    <div class="page-split">

        <!-- LEFT: main content -->
        <div class="page-split-left">
            <?php get_template_part('template_parts/content', 'change-management'); ?>
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