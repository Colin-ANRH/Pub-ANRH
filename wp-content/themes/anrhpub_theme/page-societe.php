<?php
/**
 * Template — Notre activité (/societe/).
 *
 * @package anrhpub_theme
 */

get_header();

$catalogue_product_count  = function_exists( 'anrhpub_get_catalogue_product_count' ) ? anrhpub_get_catalogue_product_count() : 0;
$catalogue_category_count = function_exists( 'anrhpub_get_parent_category_count' ) ? anrhpub_get_parent_category_count( false ) : 0;
?>

<main id="main-content" class="page-societe">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		anrhpub_page_hero(
			array(
				'title' => get_the_title(),
				'lead'  => __( 'Entreprise adaptée et activité commerciale dédiée aux objets publicitaires personnalisés pour les professionnels.', 'anrhpub_theme' ),
			)
		);
		?>

		<section class="section section--societe">
			<div class="container societe-layout">
				<div class="societe-layout__main entry-content">
					<?php the_content(); ?>

					<div class="societe-marquage-teaser" data-animate>
						<h2><?php esc_html_e( 'Nos marquages', 'anrhpub_theme' ); ?></h2>
						<p><?php esc_html_e( 'Plusieurs techniques sont utilisées en fonction du produit choisi — toutes réalisées dans nos locaux.', 'anrhpub_theme' ); ?></p>
						<?php get_template_part( 'template-parts/marquage', 'techniques', array( 'mode' => 'compact' ) ); ?>
						<p class="societe-marquage-teaser__link">
							<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/marquage/' ) ); ?>">
								<?php esc_html_e( 'Découvrir les techniques de marquage', 'anrhpub_theme' ); ?>
							</a>
						</p>
					</div>
				</div>

				<aside class="societe-layout__aside" aria-label="<?php esc_attr_e( 'En bref', 'anrhpub_theme' ); ?>">
					<div class="societe-aside-card" data-animate>
						<span class="societe-aside-card__num"><?php echo esc_html( number_format_i18n( $catalogue_product_count ) ); ?></span>
						<h3><?php esc_html_e( 'Produits', 'anrhpub_theme' ); ?></h3>
						<p>
							<?php
							if ( $catalogue_category_count > 0 ) {
								printf(
									/* translators: %d: number of parent product categories */
									esc_html( _n( '%d catégorie, du classique au hi-tech.', '%d catégories, du classique au hi-tech.', $catalogue_category_count, 'anrhpub_theme' ) ),
									(int) $catalogue_category_count
								);
							} else {
								esc_html_e( 'Du classique au hi-tech.', 'anrhpub_theme' );
							}
							?>
						</p>
					</div>
					<div class="societe-aside-card societe-aside-card--iso" data-animate>
						<h3><?php esc_html_e( 'ISO 9001', 'anrhpub_theme' ); ?></h3>
						<p><?php esc_html_e( 'Certification groupe version 2015 — qualité et économie solidaire.', 'anrhpub_theme' ); ?></p>
					</div>
					<div class="societe-aside-card societe-aside-card--link" data-animate>
						<h3><?php esc_html_e( 'L’association ANRH', 'anrhpub_theme' ); ?></h3>
						<p>
							<a href="https://www.anrh.fr" target="_blank" rel="noopener noreferrer">www.anrh.fr</a>
						</p>
					</div>
					<div class="societe-aside-card societe-aside-card--cta" data-animate>
						<a class="btn btn--primary btn--block" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
							<?php esc_html_e( 'Catalogue produits', 'anrhpub_theme' ); ?>
						</a>
						<a class="btn btn--outline btn--block" href="<?php echo esc_url( anrhpub_anrh_history_url() ); ?>">
							<?php esc_html_e( 'Histoire de l’ANRH', 'anrhpub_theme' ); ?>
						</a>
						<a class="btn btn--outline btn--block" href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>" data-quote-cart-link>
							<?php esc_html_e( 'Mon panier', 'anrhpub_theme' ); ?>
						</a>
					</div>
				</aside>
			</div>
		</section>
	<?php endwhile; ?>
</main>

<?php
get_footer();
