<?php
/**
 * Template — Conditions d’utilisation / CGV.
 *
 * @package anrhpub_theme
 */

get_header();
?>

<main id="main-content" class="page-terms">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		anrhpub_page_hero(
			array(
				'kicker' => __( 'Informations légales', 'anrhpub_theme' ),
				'title'  => get_the_title(),
				'lead'   => __( 'Conditions générales de vente applicables aux produits et prestations proposés par l’ANRH — enseigne MARVILLE PROVENCE.', 'anrhpub_theme' ),
				'class'  => 'page-hero--epure page-hero--legal',
			)
		);
		?>

		<section class="section section--legal" data-animate>
			<div class="container legal-layout">
				<aside class="legal-layout__nav" aria-label="<?php esc_attr_e( 'Sommaire', 'anrhpub_theme' ); ?>">
					<nav class="legal-toc card-epure">
						<p class="legal-toc__title"><?php esc_html_e( 'Sommaire', 'anrhpub_theme' ); ?></p>
						<ol class="legal-toc__list">
							<li><a href="#cgv-partie-1"><?php esc_html_e( 'Partie 1 — Vente de produits (art. 1 à 6)', 'anrhpub_theme' ); ?></a></li>
							<li><a href="#cgv-partie-2"><?php esc_html_e( 'Partie 2 — Livraison & garanties (art. 7 à 11)', 'anrhpub_theme' ); ?></a></li>
							<li><a href="#cgv-partie-3"><?php esc_html_e( 'Partie 3 — Prestations de services (art. 12 à 15)', 'anrhpub_theme' ); ?></a></li>
						</ol>
					</nav>
				</aside>

				<div class="legal-layout__content">
					<?php get_template_part( 'template-parts/terms', 'content' ); ?>
				</div>
			</div>
		</section>
	<?php endwhile; ?>
</main>

<?php
get_footer();
