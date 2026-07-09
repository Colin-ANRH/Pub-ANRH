<?php
/**
 * Template — Techniques de marquage.
 *
 * @package anrhpub_theme
 */

get_header();
?>

<main id="main-content" class="page-marquage">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		anrhpub_page_hero(
			array(
				'title' => get_the_title(),
				'lead'  => __( 'Tous nos marquages sont réalisés dans nos locaux. Nous choisissons la technique la plus adaptée à votre produit, votre logo et votre quantité.', 'anrhpub_theme' ),
			)
		);
		?>

		<section class="section section--marquage-intro" data-animate>
			<div class="container marquage-intro">
				<p class="marquage-intro__text">
					<?php esc_html_e( 'Monochrome ou quadri, discret ou visible : le rendu dépend de la matière et de la forme de l’objet publicitaire. Notre équipe vous conseille pour chaque projet.', 'anrhpub_theme' ); ?>
				</p>
				<a class="btn btn--primary" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>"><?php esc_html_e( 'Parcourir le catalogue', 'anrhpub_theme' ); ?></a>
			</div>
		</section>

		<section class="section section--marquage-detail">
			<div class="container">
				<?php get_template_part( 'template-parts/marquage', 'techniques', array( 'mode' => 'detail' ) ); ?>
			</div>
		</section>

		<section class="section page-cta page-cta--marquage" data-animate>
			<div class="container page-cta__card">
				<div class="page-cta__copy">
					<h2><?php esc_html_e( 'Un projet à marquer ?', 'anrhpub_theme' ); ?></h2>
					<p><?php esc_html_e( 'Indiquez votre produit, votre logo et la quantité souhaitée — nous vous proposons la technique adaptée et un devis personnalisé.', 'anrhpub_theme' ); ?></p>
				</div>
				<div class="page-cta__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>"><?php esc_html_e( 'Ajouter des produits au panier', 'anrhpub_theme' ); ?></a>
					<a class="btn btn--outline" href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>" data-quote-cart-link><?php esc_html_e( 'Mon panier devis', 'anrhpub_theme' ); ?></a>
				</div>
			</div>
		</section>
	<?php endwhile; ?>
</main>

<?php
get_footer();
