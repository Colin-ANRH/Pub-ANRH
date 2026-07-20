<?php
/**
 * Archive catalogue produits.
 *
 * @package anrhpub_theme
 */

get_header();

$hero = anrhpub_get_catalogue_hero_args();
?>

<main id="main-content" class="catalogue-page">
	<section id="catalogue-hero" class="page-hero page-hero--epure page-hero--catalogue" data-animate>
		<div class="container page-hero__inner">
			<p class="page-hero__kicker"><?php echo esc_html( $hero['kicker'] ); ?></p>
			<h1 class="page-hero__title"><?php echo esc_html( $hero['title'] ); ?></h1>
			<p class="page-hero__lead"><?php echo esc_html( $hero['lead'] ); ?></p>
		</div>
	</section>

	<div class="container catalogue-layout">
		<aside class="catalogue-filters catalogue-filters--accordion" aria-label="<?php esc_attr_e( 'Catégories du catalogue', 'anrhpub_theme' ); ?>">
			<h2><?php esc_html_e( 'Parcourir', 'anrhpub_theme' ); ?></h2>
			<?php get_template_part( 'template-parts/catalogue', 'filters-list' ); ?>
			<?php if ( function_exists( 'anrhpub_render_catalogue_facets' ) ) : ?>
				<?php anrhpub_render_catalogue_facets(); ?>
			<?php endif; ?>
		</aside>

		<div class="catalogue-main">
			<?php if ( anrhpub_catalogue_is_search_active() ) : ?>
				<p class="catalogue-main__search-active">
					<?php
					printf(
						/* translators: %s: search keywords */
						esc_html__( 'Résultats pour « %s » — modifiez la recherche via la barre en haut du site.', 'anrhpub_theme' ),
						esc_html( anrhpub_get_catalogue_search_term() )
					);
					?>
				</p>
			<?php endif; ?>
			<div id="catalogue-results" class="catalogue-results" aria-live="polite" aria-busy="false">
				<?php get_template_part( 'template-parts/catalogue', 'results' ); ?>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
