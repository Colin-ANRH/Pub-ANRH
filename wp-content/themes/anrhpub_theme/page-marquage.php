<?php
/**
 * Template — Techniques de marquage.
 *
 * @package anrhpub_theme
 */

get_header();
?>

<main id="main-content" class="page-marquage page-atelier">
	<?php while ( have_posts() ) : the_post(); ?>

		<section class="atelier-hero" data-animate aria-labelledby="marquage-hero-title">
			<div class="container atelier-hero__inner">
				<p class="atelier-hero__brand">ANRH Peyruis</p>
				<p class="atelier-hero__kicker"><?php esc_html_e( 'Atelier', 'anrhpub_theme' ); ?></p>
				<h1 id="marquage-hero-title" class="atelier-hero__title">
					<?php esc_html_e( 'Nos marquages', 'anrhpub_theme' ); ?>
				</h1>
				<p class="atelier-hero__lead">
					<?php esc_html_e( 'Tous nos marquages sont réalisés dans nos locaux. Nous choisissons la technique la plus adaptée à votre produit, votre logo et votre quantité.', 'anrhpub_theme' ); ?>
				</p>
				<div class="atelier-hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
						<?php esc_html_e( 'Parcourir le catalogue', 'anrhpub_theme' ); ?>
					</a>
					<a class="btn btn--outline" href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>" data-quote-cart-link>
						<?php esc_html_e( 'Mon panier devis', 'anrhpub_theme' ); ?>
					</a>
				</div>
			</div>
		</section>

		<section class="atelier-chapter" data-animate aria-labelledby="marquage-intro-title">
			<div class="container atelier-chapter__grid">
				<div class="atelier-chapter__intro">
					<p class="atelier-chapter__kicker"><?php esc_html_e( 'Conseil', 'anrhpub_theme' ); ?></p>
					<h2 id="marquage-intro-title" class="atelier-chapter__title"><?php esc_html_e( 'La bonne technique', 'anrhpub_theme' ); ?></h2>
				</div>
				<div class="atelier-chapter__body">
					<p>
						<?php esc_html_e( 'Monochrome ou quadri, discret ou visible : le rendu dépend de la matière et de la forme de l’objet publicitaire. Notre équipe vous conseille pour chaque projet.', 'anrhpub_theme' ); ?>
					</p>
				</div>
			</div>
		</section>

		<section class="atelier-chapter atelier-chapter--tint marquage-atelier-detail" data-animate>
			<div class="container">
				<header class="atelier-chapter__header">
					<p class="atelier-chapter__kicker"><?php esc_html_e( 'Techniques', 'anrhpub_theme' ); ?></p>
					<h2 class="atelier-chapter__title"><?php esc_html_e( 'Cinq savoir-faire en atelier', 'anrhpub_theme' ); ?></h2>
					<p class="atelier-chapter__lead">
						<?php esc_html_e( 'Sélectionnez une technique pour en lire le détail — ou demandez-nous conseil au moment du devis.', 'anrhpub_theme' ); ?>
					</p>
				</header>
				<?php get_template_part( 'template-parts/marquage', 'techniques', array( 'mode' => 'detail' ) ); ?>
			</div>
		</section>

		<section class="atelier-footer-cta" data-animate>
			<div class="container atelier-footer-cta__inner">
				<div class="atelier-footer-cta__copy">
					<p class="atelier-footer-cta__brand">ANRH Peyruis</p>
					<h2 class="atelier-footer-cta__title"><?php esc_html_e( 'Un projet à marquer ?', 'anrhpub_theme' ); ?></h2>
					<p class="atelier-footer-cta__lead">
						<?php esc_html_e( 'Indiquez votre produit, votre logo et la quantité — nous proposons la technique adaptée et un devis personnalisé.', 'anrhpub_theme' ); ?>
					</p>
				</div>
				<div class="atelier-footer-cta__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
						<?php esc_html_e( 'Ajouter au panier', 'anrhpub_theme' ); ?>
					</a>
					<a class="btn btn--outline" href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>" data-quote-cart-link>
						<?php esc_html_e( 'Mon panier devis', 'anrhpub_theme' ); ?>
					</a>
				</div>
			</div>
		</section>

	<?php endwhile; ?>
</main>

<?php
get_footer();
