<?php
/**
 * Template — Comparateur produits.
 *
 * @package anrhpub_theme
 */

get_header();

$catalogue_url = function_exists( 'anrhpub_catalogue_url' ) ? anrhpub_catalogue_url() : home_url( '/' );
?>
<main id="main-content" class="compare-page">
	<?php
	anrhpub_page_hero(
		array(
			'kicker' => __( 'Outil B2B', 'anrhpub_theme' ),
			'title'  => __( 'Comparateur produits', 'anrhpub_theme' ),
			'lead'   => __( 'Mettez jusqu’à 4 références côte à côte pour choisir la meilleure option avant votre devis.', 'anrhpub_theme' ),
			'class'  => 'page-hero--compare',
		)
	);
	?>
	<section class="section compare-section" data-animate>
		<div class="container compare-section__inner">
			<ol class="compare-steps" aria-label="<?php esc_attr_e( 'Comment utiliser le comparateur', 'anrhpub_theme' ); ?>">
				<li class="compare-steps__item">
					<span class="compare-steps__num" aria-hidden="true">1</span>
					<div>
						<strong><?php esc_html_e( 'Sélectionner', 'anrhpub_theme' ); ?></strong>
						<p><?php esc_html_e( 'Sur une fiche ou une vignette catalogue, cliquez sur « Comparer ».', 'anrhpub_theme' ); ?></p>
					</div>
				</li>
				<li class="compare-steps__item">
					<span class="compare-steps__num" aria-hidden="true">2</span>
					<div>
						<strong><?php esc_html_e( 'Analyser', 'anrhpub_theme' ); ?></strong>
						<p><?php esc_html_e( 'Consultez référence, disponibilité et tarifs (si votre compte est validé).', 'anrhpub_theme' ); ?></p>
					</div>
				</li>
				<li class="compare-steps__item">
					<span class="compare-steps__num" aria-hidden="true">3</span>
					<div>
						<strong><?php esc_html_e( 'Commander', 'anrhpub_theme' ); ?></strong>
						<p><?php esc_html_e( 'Ajoutez les produits retenus à votre panier devis en un clic.', 'anrhpub_theme' ); ?></p>
					</div>
				</li>
			</ol>

			<div id="anr-compare-app" class="compare-app" data-compare-app aria-live="polite">
				<div class="compare-app__empty">
					<div class="compare-app__empty-icon" aria-hidden="true">
						<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
							<rect x="6" y="10" width="14" height="28" rx="2" stroke="currentColor" stroke-width="2"/>
							<rect x="19" y="10" width="14" height="28" rx="2" stroke="currentColor" stroke-width="2"/>
							<rect x="32" y="10" width="10" height="28" rx="2" stroke="currentColor" stroke-width="2" opacity="0.35"/>
						</svg>
					</div>
					<p class="compare-app__empty-title"><?php esc_html_e( 'Votre comparateur est vide', 'anrhpub_theme' ); ?></p>
					<p class="compare-app__empty-text"><?php esc_html_e( 'Les produits ajoutés apparaîtront ici. Votre sélection est enregistrée dans ce navigateur.', 'anrhpub_theme' ); ?></p>
					<a class="btn btn--primary" href="<?php echo esc_url( $catalogue_url ); ?>"><?php esc_html_e( 'Voir le catalogue', 'anrhpub_theme' ); ?></a>
				</div>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
