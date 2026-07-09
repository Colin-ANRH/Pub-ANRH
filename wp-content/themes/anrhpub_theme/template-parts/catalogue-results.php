<?php
/**
 * Zone résultats catalogue (grille + pagination).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
$total       = (int) $wp_query->found_posts;
$search_term = anrhpub_get_catalogue_search_term();
$is_search   = anrhpub_catalogue_is_search_active();

if ( $is_search ) {
	get_template_part(
		'template-parts/catalogue',
		'search-hints',
		array( 'terms' => anrhpub_catalogue_search_matching_categories( $search_term ) )
	);
}
?>
<?php get_template_part( 'template-parts/catalogue', 'notice' ); ?>

<?php if ( have_posts() ) : ?>
	<div class="catalogue-toolbar">
		<p class="catalogue-toolbar__count">
			<?php
			if ( $is_search ) {
				echo wp_kses_post(
					sprintf(
						/* translators: 1: number of products, 2: search term */
						_n(
							'<strong>%1$d</strong> référence pour « %2$s »',
							'<strong>%1$d</strong> références pour « %2$s »',
							$total,
							'anrhpub_theme'
						),
						$total,
						esc_html( $search_term )
					)
				);
			} else {
				echo wp_kses_post(
					sprintf(
						/* translators: %d: product count */
						_n( '<strong>%d</strong> référence', '<strong>%d</strong> références', $total, 'anrhpub_theme' ),
						$total
					)
				);
			}
			?>
		</p>
	</div>
	<div class="product-grid">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/product', 'card' );
		endwhile;
		?>
	</div>
	<?php
	the_posts_pagination(
		array(
			'mid_size'  => 2,
			'add_args'  => $is_search ? array( 'catalogue_q' => $search_term ) : false,
		)
	);
	?>
<?php else : ?>
	<p class="catalogue-empty">
		<?php
		if ( $is_search ) {
			printf(
				/* translators: %s: search keywords */
				esc_html__( 'Aucun produit trouvé pour « %s ». Essayez un autre mot-clé ou parcourez les catégories.', 'anrhpub_theme' ),
				esc_html( $search_term )
			);
		} else {
			esc_html_e( 'Aucun produit dans cette catégorie pour le moment.', 'anrhpub_theme' );
		}
		?>
	</p>
<?php endif; ?>
