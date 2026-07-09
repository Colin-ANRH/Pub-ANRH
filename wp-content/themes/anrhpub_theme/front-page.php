<?php
/**
 * Accueil — vitrine ANRH Peyruis + entrée catalogue.
 *
 * @package anrhpub_theme
 */

get_header();

$spotlight_query = anrhpub_get_home_spotlight_query();

$catalogue_query = new WP_Query(
	array(
		'post_type'      => 'anr_product',
		'posts_per_page' => 6,
		'orderby'        => 'title',
		'order'          => 'ASC',
	)
);
?>

<main id="main-content" class="home-main">
	<?php
	get_template_part(
		'template-parts/home',
		'intro',
		array( 'query' => $spotlight_query )
	);
	?>

	<?php get_template_part( 'template-parts/home', 'story' ); ?>

	<?php
	if ( function_exists( 'anrhpub_render_home_trust_clients' ) ) {
		anrhpub_render_home_trust_clients();
	}
	?>

	<?php get_template_part( 'template-parts/category', 'tiles' ); ?>

	<section class="section section--catalogue" data-animate>
		<div class="container section-header section-header--row">
			<div>
				<h2><?php esc_html_e( 'Aperçu du catalogue', 'anrhpub_theme' ); ?></h2>
				<p class="section-header__lead"><?php esc_html_e( 'Quelques références — ouvrez une fiche et ajoutez au panier pour préparer votre devis.', 'anrhpub_theme' ); ?></p>
			</div>
			<a class="text-link" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>"><?php esc_html_e( 'Catalogue complet', 'anrhpub_theme' ); ?></a>
		</div>
		<div class="container">
			<div class="product-grid">
				<?php
				if ( $catalogue_query->have_posts() ) :
					while ( $catalogue_query->have_posts() ) :
						$catalogue_query->the_post();
						get_template_part( 'template-parts/product', 'card' );
					endwhile;
					wp_reset_postdata();
				endif;
				?>
			</div>
		</div>
	</section>

	<section class="home-strip home-strip--epure" data-animate aria-labelledby="home-strip-title">
		<div class="container">
			<div class="home-strip__inner">
				<div class="home-strip__main">
					<p class="home-strip__kicker"><?php esc_html_e( 'Entreprise adaptée · ANRH Peyruis', 'anrhpub_theme' ); ?></p>
					<h2 id="home-strip-title" class="home-strip__title"><?php esc_html_e( 'Qui est l’ANRH ?', 'anrhpub_theme' ); ?></h2>
					<p class="home-strip__explain">
						<?php esc_html_e( 'L’ANRH — Association Nationale pour l’insertion et la Réinsertion professionnelle des personnes Handicapées — est fondée en 1954 et reconnue d’utilité publique. Pionnière des ateliers protégés en France, elle agit aujourd’hui comme entreprise insérante au service de l’emploi durable et inclusif.', 'anrhpub_theme' ); ?>
					</p>
					<p class="home-strip__local">
						<span class="home-strip__brand">ANRH Peyruis</span>
						<span class="home-strip__sep" aria-hidden="true">—</span>
						<?php esc_html_e( 'fait partie de ce réseau : objets publicitaires personnalisés, insertion professionnelle,', 'anrhpub_theme' ); ?>
						<span class="home-strip__accent"><?php esc_html_e( 'qualité ISO 9001', 'anrhpub_theme' ); ?></span>.
					</p>
				</div>
				<div class="home-strip__aside">
					<a class="home-strip__link" href="<?php echo esc_url( anrhpub_anrh_history_url() ); ?>">
						<span><?php esc_html_e( 'Histoire de l’ANRH', 'anrhpub_theme' ); ?></span>
						<span class="home-strip__link-icon" aria-hidden="true">→</span>
					</a>
					<p class="home-strip__link-note"><?php esc_html_e( '70 ans d’engagement pour l’emploi inclusif', 'anrhpub_theme' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<?php
	if ( function_exists( 'anrhpub_render_home_trust_partners' ) ) {
		anrhpub_render_home_trust_partners();
	}
	?>

	<?php get_template_part( 'template-parts/home', 'cta' ); ?>
</main>

<?php
get_footer();
