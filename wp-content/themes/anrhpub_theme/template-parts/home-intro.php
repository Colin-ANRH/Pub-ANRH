<?php
/**
 * Accueil — hero épuré : marque, promesse, visuel.
 *
 * @package anrhpub_theme
 * @var array $args { query?: WP_Query } Conservé pour compatibilité.
 */

defined( 'ABSPATH' ) || exit;

$slides = function_exists( 'anrhpub_get_home_slider_slides' ) ? anrhpub_get_home_slider_slides() : array();
$hero   = ! empty( $slides[0] ) ? $slides[0] : null;
$side_a = ! empty( $slides[1] ) ? $slides[1] : null;
$side_b = ! empty( $slides[2] ) ? $slides[2] : null;
?>
<section class="home-hero" data-animate aria-label="<?php esc_attr_e( 'Présentation ANRH Peyruis', 'anrhpub_theme' ); ?>">
	<div class="container home-hero__grid">
		<div class="home-hero__copy">
			<p class="home-hero__brand"><?php esc_html_e( 'ANRH Peyruis', 'anrhpub_theme' ); ?></p>
			<h1 class="home-hero__title">
				<?php esc_html_e( 'Objets publicitaires', 'anrhpub_theme' ); ?>
				<em><?php esc_html_e( 'personnalisés', 'anrhpub_theme' ); ?></em>
			</h1>
			<p class="home-hero__lead">
				<?php esc_html_e( 'Entreprise adaptée à Peyruis : catalogue, devis et marquage pour entreprises, collectivités et associations.', 'anrhpub_theme' ); ?>
			</p>
			<div class="home-hero__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
					<?php esc_html_e( 'Parcourir le catalogue', 'anrhpub_theme' ); ?>
				</a>
				<a class="btn btn--outline" href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>" data-quote-cart-link>
					<?php esc_html_e( 'Mon panier devis', 'anrhpub_theme' ); ?>
				</a>
			</div>
		</div>

		<?php if ( $hero ) : ?>
			<div class="home-hero__visual" aria-label="<?php esc_attr_e( 'Visuels catalogue', 'anrhpub_theme' ); ?>">
				<figure class="home-hero__figure home-hero__figure--main">
					<img
						src="<?php echo esc_url( $hero['src'] ); ?>"
						alt="<?php echo esc_attr( $hero['alt'] ); ?>"
						fetchpriority="high"
						decoding="async"
					/>
				</figure>
				<?php if ( $side_a ) : ?>
					<figure class="home-hero__figure home-hero__figure--a">
						<img
							src="<?php echo esc_url( $side_a['src'] ); ?>"
							alt="<?php echo esc_attr( $side_a['alt'] ); ?>"
							loading="lazy"
							decoding="async"
						/>
					</figure>
				<?php endif; ?>
				<?php if ( $side_b ) : ?>
					<figure class="home-hero__figure home-hero__figure--b">
						<img
							src="<?php echo esc_url( $side_b['src'] ); ?>"
							alt="<?php echo esc_attr( $side_b['alt'] ); ?>"
							loading="lazy"
							decoding="async"
						/>
					</figure>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="container home-hero__meta">
		<ul class="home-hero__for" aria-label="<?php esc_attr_e( 'Pour qui ?', 'anrhpub_theme' ); ?>">
			<li><?php esc_html_e( 'Entreprises & marques', 'anrhpub_theme' ); ?></li>
			<li><?php esc_html_e( 'Évènements & CSE', 'anrhpub_theme' ); ?></li>
			<li><?php esc_html_e( 'Collectivités', 'anrhpub_theme' ); ?></li>
		</ul>
		<nav class="home-hero__links" aria-label="<?php esc_attr_e( 'En savoir plus', 'anrhpub_theme' ); ?>">
			<a href="<?php echo esc_url( home_url( '/societe/' ) ); ?>"><?php esc_html_e( 'Notre activité', 'anrhpub_theme' ); ?></a>
			<a href="<?php echo esc_url( anrhpub_anrh_history_url() ); ?>"><?php esc_html_e( 'Histoire de l’ANRH', 'anrhpub_theme' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/marquage/' ) ); ?>"><?php esc_html_e( 'Marquage', 'anrhpub_theme' ); ?></a>
			<a href="https://www.anrh.fr" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'ANRH.fr', 'anrhpub_theme' ); ?></a>
		</nav>
	</div>
</section>
