<?php
/**
 * Accueil — bandeau nouveautés (hors hero).
 *
 * @package anrhpub_theme
 * @var array $args { query?: WP_Query }
 */

defined( 'ABSPATH' ) || exit;

$query = isset( $args['query'] ) && $args['query'] instanceof WP_Query ? $args['query'] : null;

if ( ! $query || ! $query->have_posts() ) {
	return;
}
?>
<section class="home-nouveautes" data-animate aria-labelledby="home-nouveautes-title">
	<div class="container home-nouveautes__inner">
		<header class="home-nouveautes__header">
			<p class="home-nouveautes__kicker"><?php esc_html_e( 'Catalogue', 'anrhpub_theme' ); ?></p>
			<h2 id="home-nouveautes-title" class="home-nouveautes__title"><?php esc_html_e( 'Nouveautés', 'anrhpub_theme' ); ?></h2>
			<p class="home-nouveautes__lead"><?php esc_html_e( 'Une sélection récente à personnaliser — ajoutez au panier pour préparer votre devis.', 'anrhpub_theme' ); ?></p>
		</header>
		<div class="home-nouveautes__spotlight">
			<?php
			get_template_part(
				'template-parts/hero',
				'spotlight-carousel',
				array(
					'query'   => $query,
					'compact' => true,
				)
			);
			?>
		</div>
	</div>
</section>
