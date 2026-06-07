<?php
/**
 * Template Name: Home (Mobile Service)
 * Description: Mobile-oriented public home page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="g-mobile-home" role="main">
	<div class="g-mobile-home__inner">

		<section class="g-mobile-home__intro">

			<p>
				Mobile access is provided as a convenience. Registration and contribution features are available
				via desktop or laptop.
			</p>

			<p>
				Glandore is a living laboratory for exploring systems — especially Complex Adaptive Systems.
				It offers resources on:
			</p>

			<nav class="g-mobile-home__nav" aria-label="Site sections">
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/issue-studio/' ) ); ?>">The Issue Studio</a></li>
					<li><a href="<?php echo esc_url( home_url( '/community-discussions-mobile/' ) ); ?>">Community</a></li>					
					<li><a href="<?php echo esc_url( home_url( '/tychevia/' ) ); ?>">Tychevia</a></li>
					<li><a href="<?php echo esc_url( home_url( '/systems/' ) ); ?>">Systems</a></li>
					<li><a href="<?php echo esc_url( home_url( '/general-ai/' ) ); ?>">General AI</a></li>					
					<li><a href="<?php echo esc_url( home_url( '/papers/' ) ); ?>">Papers</a></li>
					<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About</a></li>					
				</ul>
			</nav>

			<p>
				Tychevia&reg; is our in-house exploratory AI framework: a collaborative dialogue between humans
				and digital proxies, designed to explore deeply, learn continuously and make sense of systems
				as they behave.
			</p>

			<p>
				This is a living space to think, to build, and to understand. Anyone curious about systems is welcome.
			</p>

		</section>

	</div>
</main>

<?php get_footer(); ?>