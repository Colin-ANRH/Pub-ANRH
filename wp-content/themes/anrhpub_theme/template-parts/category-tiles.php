<?php
/**
 * Accueil — explorer le catalogue par univers.
 *
 * @package anrhpub_theme
 *
 * @var array $args { limit: int, title: string }
 */

defined( 'ABSPATH' ) || exit;

$limit = isset( $args['limit'] ) ? (int) $args['limit'] : 0;
$title = $args['title'] ?? __( 'Explorer par univers', 'anrhpub_theme' );
$terms = anrhpub_get_parent_categories( false );

if ( empty( $terms ) ) {
	return;
}

if ( $limit > 0 ) {
	$terms = array_slice( $terms, 0, $limit );
}

$product_total = 0;
$featured      = null;
$regular       = array();

foreach ( $terms as $term ) {
	$product_total += (int) $term->count;
	$is_featured    = ( function_exists( 'anrhpub_nouveautes_category_slug' ) && anrhpub_nouveautes_category_slug() === $term->slug )
		|| 'les-nouveautes-objets-pubs' === $term->slug;

	if ( $is_featured && null === $featured ) {
		$featured = $term;
	} else {
		$regular[] = $term;
	}
}

/**
 * Affiche un lien catégorie.
 *
 * @param WP_Term $term     Term.
 * @param string  $modifier Extra BEM modifier (spotlight|tile).
 */
$render_term_link = static function ( $term, $modifier = 'tile' ) {
	$link = get_term_link( $term );

	if ( is_wp_error( $link ) ) {
		return;
	}

	$count = (int) $term->count;
	$glyph = function_exists( 'mb_substr' ) ? mb_strtoupper( mb_substr( $term->name, 0, 1 ) ) : strtoupper( substr( $term->name, 0, 1 ) );
	$cls   = array(
		'home-explore__link',
		'home-explore__link--' . sanitize_html_class( $modifier ),
		'home-explore__link--' . sanitize_html_class( $term->slug ),
	);
	?>
	<a class="<?php echo esc_attr( implode( ' ', $cls ) ); ?>" href="<?php echo esc_url( $link ); ?>">
		<span class="home-explore__glyph" aria-hidden="true"><?php echo esc_html( $glyph ); ?></span>
		<span class="home-explore__body">
			<span class="home-explore__name"><?php echo esc_html( $term->name ); ?></span>
			<?php if ( $count > 0 ) : ?>
				<span class="home-explore__meta">
					<?php
					printf(
						/* translators: %d: number of products */
						esc_html( _n( '%d produit', '%d produits', $count, 'anrhpub_theme' ) ),
						$count
					);
					?>
				</span>
			<?php else : ?>
				<span class="home-explore__meta"><?php esc_html_e( 'Voir la catégorie', 'anrhpub_theme' ); ?></span>
			<?php endif; ?>
		</span>
		<span class="home-explore__go" aria-hidden="true">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" focusable="false">
				<path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</span>
	</a>
	<?php
};
?>
<section class="section section--categories home-explore" data-animate aria-labelledby="catalogue-explore-title">
	<div class="container">
		<header class="home-explore__header">
			<div class="home-explore__intro">
				<p class="home-explore__kicker"><?php esc_html_e( 'Catalogue', 'anrhpub_theme' ); ?></p>
				<h2 id="catalogue-explore-title" class="home-explore__title"><?php echo esc_html( $title ); ?></h2>
				<p class="home-explore__lead">
					<?php
					if ( $product_total > 0 ) {
						printf(
							/* translators: %s: approximate product count */
							esc_html__( 'Plus de %s références. Choisissez un univers pour afficher les produits.', 'anrhpub_theme' ),
							esc_html( number_format_i18n( max( $product_total, 450 ) ) )
						);
					} else {
						esc_html_e( 'Plus de 450 références. Choisissez un univers pour afficher les produits.', 'anrhpub_theme' );
					}
					?>
				</p>
			</div>
			<a class="btn btn--outline home-explore__cta" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
				<?php esc_html_e( 'Catalogue complet', 'anrhpub_theme' ); ?>
			</a>
		</header>

		<?php if ( $featured ) : ?>
			<div class="home-explore__spotlight">
				<p class="home-explore__spotlight-label"><?php esc_html_e( 'À la une', 'anrhpub_theme' ); ?></p>
				<?php $render_term_link( $featured, 'spotlight' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $regular ) ) : ?>
			<ul class="home-explore__grid" role="list">
				<?php foreach ( $regular as $term ) : ?>
					<li class="home-explore__item">
						<?php $render_term_link( $term, 'tile' ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
