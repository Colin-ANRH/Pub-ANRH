<?php
/**
 * Grille des catégories parentes — entrée catalogue (accueil).
 *
 * @package anrhpub_theme
 *
 * @var array $args { limit: int, title: string }
 */

defined( 'ABSPATH' ) || exit;

$limit = isset( $args['limit'] ) ? (int) $args['limit'] : 0;
$title = $args['title'] ?? __( 'Explorer le catalogue', 'anrhpub_theme' );
$terms = anrhpub_get_parent_categories( false );

if ( empty( $terms ) ) {
	return;
}

if ( $limit > 0 ) {
	$terms = array_slice( $terms, 0, $limit );
}

$product_total = 0;

foreach ( $terms as $term ) {
	$product_total += (int) $term->count;
}
?>
<section class="section section--categories" data-animate aria-labelledby="catalogue-explore-title">
	<div class="container section-header section-header--row">
		<div>
			<h2 id="catalogue-explore-title"><?php echo esc_html( $title ); ?></h2>
			<p class="section-header__lead">
				<?php
				if ( $product_total > 0 ) {
					printf(
						/* translators: %s: approximate product count */
						esc_html__( 'Plus de %s références classées par univers — choisissez une catégorie pour parcourir les produits.', 'anrhpub_theme' ),
						esc_html( number_format_i18n( max( $product_total, 450 ) ) )
					);
				} else {
					esc_html_e( 'Plus de 450 références classées par univers — choisissez une catégorie pour parcourir les produits.', 'anrhpub_theme' );
				}
				?>
			</p>
		</div>
		<a class="text-link" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
			<?php esc_html_e( 'Catalogue complet', 'anrhpub_theme' ); ?>
		</a>
	</div>
	<div class="container">
		<ul class="home-categories" role="list">
			<?php foreach ( $terms as $term ) : ?>
				<?php
				$link = get_term_link( $term );

				if ( is_wp_error( $link ) ) {
					continue;
				}

				$count    = (int) $term->count;
				$glyph    = function_exists( 'mb_substr' ) ? mb_substr( $term->name, 0, 1 ) : substr( $term->name, 0, 1 );
				$featured = ( function_exists( 'anrhpub_nouveautes_category_slug' ) && anrhpub_nouveautes_category_slug() === $term->slug )
					|| 'les-nouveautes-objets-pubs' === $term->slug;
				$item_cls = array( 'home-categories__item', 'home-categories__item--' . sanitize_html_class( $term->slug ) );
				if ( $featured ) {
					$item_cls[] = 'home-categories__item--highlight';
				}
				?>
				<li class="<?php echo esc_attr( implode( ' ', $item_cls ) ); ?>">
					<a class="home-categories__link" href="<?php echo esc_url( $link ); ?>">
						<span class="home-categories__glyph" aria-hidden="true"><?php echo esc_html( $glyph ); ?></span>
						<span class="home-categories__content">
							<span class="home-categories__name"><?php echo esc_html( $term->name ); ?></span>
							<?php if ( $count > 0 ) : ?>
								<span class="home-categories__count">
									<?php
									printf(
										/* translators: %d: number of products */
										esc_html( _n( '%d produit', '%d produits', $count, 'anrhpub_theme' ) ),
										(int) $count
									);
									?>
								</span>
							<?php else : ?>
								<span class="home-categories__count home-categories__count--empty">
									<?php esc_html_e( 'Parcourir', 'anrhpub_theme' ); ?>
								</span>
							<?php endif; ?>
						</span>
						<span class="home-categories__arrow" aria-hidden="true">→</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
