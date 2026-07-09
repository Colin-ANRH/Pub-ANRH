<?php
/**
 * Accueil — hero épuré : visuels, présentation, nouveautés.
 *
 * @package anrhpub_theme
 * @var array $args { query?: WP_Query }
 */

defined( 'ABSPATH' ) || exit;

$spotlight_query = isset( $args['query'] ) && $args['query'] instanceof WP_Query ? $args['query'] : null;
?>
<section class="home-intro home-intro--epure hero hero--home" data-animate aria-label="<?php esc_attr_e( 'Présentation ANRH Peyruis', 'anrhpub_theme' ); ?>">
	<div class="container home-intro__inner">
		<div class="home-intro__gallery">
			<?php anrhpub_render_home_slider(); ?>
		</div>

		<div class="home-intro__content">
			<div class="home-intro__copy hero__main">
				<p class="hero__kicker"><?php esc_html_e( 'ANRH Peyruis — activité objets publicitaires', 'anrhpub_theme' ); ?></p>
				<h1 class="hero__title">
					<?php esc_html_e( 'Objets publicitaires', 'anrhpub_theme' ); ?>
					<em><?php esc_html_e( 'pour les professionnels', 'anrhpub_theme' ); ?></em>
				</h1>
				<p class="hero__lead">
					<?php esc_html_e( 'Entreprise adaptée à Peyruis : objets publicitaires personnalisés pour entreprises, collectivités et associations. Parcourez le catalogue, composez votre panier et demandez un devis — marquage logo, texte ou visuel dans nos locaux.', 'anrhpub_theme' ); ?>
				</p>
				<ul class="hero__for" aria-label="<?php esc_attr_e( 'Pour qui ?', 'anrhpub_theme' ); ?>">
					<li><?php esc_html_e( 'Entreprises & marques', 'anrhpub_theme' ); ?></li>
					<li><?php esc_html_e( 'Évènements & CSE', 'anrhpub_theme' ); ?></li>
					<li><?php esc_html_e( 'Collectivités', 'anrhpub_theme' ); ?></li>
				</ul>
				<div class="hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>"><?php esc_html_e( 'Parcourir le catalogue', 'anrhpub_theme' ); ?></a>
					<a class="btn btn--outline" href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>" data-quote-cart-link><?php esc_html_e( 'Mon panier', 'anrhpub_theme' ); ?></a>
				</div>
				<p class="hero__trust">
					<a href="<?php echo esc_url( home_url( '/societe/' ) ); ?>"><?php esc_html_e( 'Notre activité', 'anrhpub_theme' ); ?></a>
					<span aria-hidden="true">·</span>
					<a href="<?php echo esc_url( anrhpub_anrh_history_url() ); ?>"><?php esc_html_e( 'Histoire de l’ANRH', 'anrhpub_theme' ); ?></a>
					<span aria-hidden="true">·</span>
					<a href="<?php echo esc_url( home_url( '/marquage/' ) ); ?>"><?php esc_html_e( 'Marquage', 'anrhpub_theme' ); ?></a>
					<span aria-hidden="true">·</span>
					<a href="https://www.anrh.fr" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'ANRH.fr', 'anrhpub_theme' ); ?></a>
				</p>
			</div>

			<?php
			if ( $spotlight_query && $spotlight_query->have_posts() ) {
				?>
				<div class="home-intro__aside">
					<?php
					get_template_part(
						'template-parts/hero',
						'spotlight-carousel',
						array(
							'query'   => $spotlight_query,
							'compact' => true,
						)
					);
					?>
				</div>
				<?php
			}
			?>
		</div>

		<p class="home-intro__scroll">
			<a class="home-intro__scroll-link" href="#catalogue-explore-title">
				<span><?php esc_html_e( 'Explorer le catalogue', 'anrhpub_theme' ); ?></span>
				<span class="home-intro__scroll-icon" aria-hidden="true">↓</span>
			</a>
		</p>
	</div>
</section>
