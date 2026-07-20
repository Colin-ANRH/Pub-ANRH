<?php
/**
 * Accueil — hero épuré : marque, promesse, slider visuel.
 *
 * @package anrhpub_theme
 * @var array $args { query?: WP_Query } Conservé pour compatibilité.
 */

defined( 'ABSPATH' ) || exit;

$slides = function_exists( 'anrhpub_get_home_slider_slides' ) ? anrhpub_get_home_slider_slides() : array();
$count  = count( $slides );
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

		<?php if ( $count > 0 ) : ?>
			<div
				class="home-hero__visual home-hero-slider<?php echo $count > 1 ? ' home-hero-slider--multi' : ''; ?>"
				<?php echo $count > 1 ? ' data-home-hero-slider' : ''; ?>
				aria-roledescription="<?php echo $count > 1 ? 'carousel' : 'img'; ?>"
				aria-label="<?php esc_attr_e( 'Visuels catalogue', 'anrhpub_theme' ); ?>"
			>
				<div class="home-hero-slider__viewport">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<figure
							class="home-hero-slider__slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
							<?php echo 0 !== $index ? ' hidden' : ''; ?>
							data-carousel-slide
						>
							<img
								src="<?php echo esc_url( $slide['src'] ); ?>"
								alt="<?php echo esc_attr( $slide['alt'] ); ?>"
								<?php echo 0 === $index ? ' fetchpriority="high"' : ' loading="lazy"'; ?>
								decoding="async"
							/>
						</figure>
					<?php endforeach; ?>
				</div>

				<?php if ( $count > 1 ) : ?>
					<div class="home-hero-slider__controls">
						<button type="button" class="home-hero-slider__nav home-hero-slider__nav--prev" data-home-slider-prev aria-label="<?php esc_attr_e( 'Image précédente', 'anrhpub_theme' ); ?>">
							<span aria-hidden="true">‹</span>
						</button>
						<div class="home-hero-slider__dots" role="tablist" aria-label="<?php esc_attr_e( 'Choisir une image', 'anrhpub_theme' ); ?>">
							<?php for ( $i = 0; $i < $count; $i++ ) : ?>
								<button
									type="button"
									class="home-hero-slider__dot<?php echo 0 === $i ? ' is-active' : ''; ?>"
									data-home-slider-dot
									data-slide-index="<?php echo esc_attr( (string) $i ); ?>"
									role="tab"
									aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
									aria-label="<?php echo esc_attr( sprintf( __( 'Image %d', 'anrhpub_theme' ), $i + 1 ) ); ?>"
								></button>
							<?php endfor; ?>
						</div>
						<button type="button" class="home-hero-slider__nav home-hero-slider__nav--next" data-home-slider-next aria-label="<?php esc_attr_e( 'Image suivante', 'anrhpub_theme' ); ?>">
							<span aria-hidden="true">›</span>
						</button>
					</div>
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
