<?php
/**
 * Template Name: Tychevia
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

/**
 * Artefacts surfaced on the Tychevia page.
 */
$artefacts = [
	[
		'title'  => 'A Living System of Thought, Presence and Guided Design',
		'parent' => 'General',
		'url'    => '/wp-content/uploads/2025/12/Tychevia_v0_6.pdf',
	],
	[
		'title'  => 'A Brief Introduction',
		'parent' => 'General',
		'url'    => '/wp-content/uploads/2025/12/Tychevia_full_v0.1.pdf',
	],
	[
		'title'  => 'User Guide',
		'parent' => 'General',
		'url'    => '/wp-content/uploads/2025/11/Tychevia_User_Guide.pdf',
	],
	[
		'title'  => 'An alternative approach to AgenticAI',
		'parent' => 'General',
		'url'    => '/wp-content/uploads/2025/12/Agentic_AI_v0.1.pdf',
	],
	[
		'title'  => 'Dialogue as a Complex Adaptive System',
		'parent' => 'General',
		'url'    => '/wp-content/uploads/2025/12/SFI_v0_3.pdf',
	],
	[
		'title'  => 'Tychevia 2.0 - Empowering people to think',
		'parent' => 'General',
		'url'    => '/wp-content/uploads/2026/03/Tychevia_2_0_Working_Paper.pdf',
	],
	[
		'title'  => 'Developing Software in an Agentic Era',
		'parent' => 'General',
		'url'    => '/wp-content/uploads/2026/03/agentic_software_development_v0_3.pdf',
	],

	[
		'title'  => 'Use Cases',
		'parent' => null,
		'url'    => '/general/',
	],
	[
		'title'  => 'Managing Change',
		'parent' => 'General',
		'url'    => '/change-management/',
	],
	[
		'title'  => 'Influencing the Informal Organisation',
		'parent' => 'General',
		'url'    => '/wp-content/uploads/2026/04/Informal_Organisation_v0_3.pdf',
	],
	[
		'title'  => 'From Automating Ignorance to Emergent Intelligence (PDF)',
		'parent' => 'General',
		'url'    => '/wp-content/uploads/2025/10/Artificial_Ignorance_v1.1.pdf',
	],

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

		<div class="page-split-left">
			<?php get_template_part( 'template_parts/content', 'tychevia' ); ?>
		</div>

		<div class="page-split-right">
			<h4>Artefacts</h4>

			<?php
			if ( ! empty( $artefacts ) ) {
				get_template_part(
					'template_parts/artefact-list',
					null,
					[
						'artefacts' => $artefacts,
					]
				);
			} else {
				echo '<p class="artefact-empty">There are no additional artefacts currently.</p>';
			}
			?>
		</div>

	</div>
</main>

<?php
get_footer();