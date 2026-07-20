<?php
/**
 * Single product template.
 *
 * @package anrhpub_theme
 */

get_header();
?>

<main id="main-content" class="product-single">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class(); ?> data-animate>
			<div class="container product-single__layout">
				<div class="product-single__gallery-wrap">
					<?php anrhpub_render_favorite_button(); ?>
					<?php
					if ( function_exists( 'anrhpub_render_product_gallery' ) ) {
						anrhpub_render_product_gallery();
					} elseif ( has_post_thumbnail() ) {
						?>
						<div class="product-single__gallery">
							<?php the_post_thumbnail( 'large', array( 'class' => 'product-single__img' ) ); ?>
						</div>
						<?php
					} else {
						?>
						<div class="product-single__gallery product-single__gallery--empty">
							<div class="product-single__placeholder">
								<?php anrhpub_product_thumbnail(); ?>
							</div>
						</div>
						<?php
					}
					?>
					<?php anrhpub_product_badge(); ?>
				</div>

				<div class="product-single__info">
					<p class="product-single__catalogue-note">
						<?php esc_html_e( 'Fiche catalogue — tarif et marquage (logo, texte) établis sur devis selon votre quantité.', 'anrhpub_theme' ); ?>
					</p>
					<?php anrhpub_product_reference(); ?>
					<h1><?php the_title(); ?></h1>
					<?php anrhpub_product_price(); ?>
					<?php if ( function_exists( 'anrhpub_render_product_stock_badge' ) ) : ?>
						<?php anrhpub_render_product_stock_badge(); ?>
					<?php endif; ?>
					<?php if ( function_exists( 'anrhpub_can_view_prices' ) && anrhpub_can_view_prices() && function_exists( 'anrhpub_get_client_payment_terms_label' ) ) : ?>
						<p class="product-single__payment-terms"><small><?php echo esc_html( anrhpub_get_client_payment_terms_label() ); ?></small></p>
					<?php else : ?>
						<span class="product-single__price-note">
							<?php
							if ( function_exists( 'anrhpub_can_view_prices' ) && ! anrhpub_can_view_prices() ) {
								esc_html_e( 'Tarifs réservés aux comptes clients validés. Connectez-vous pour les consulter.', 'anrhpub_theme' );
							} else {
								esc_html_e( 'Tarif établi sur devis selon quantité et marquage.', 'anrhpub_theme' );
							}
							?>
						</span>
					<?php endif; ?>

					<?php anrhpub_render_product_quote_form(); ?>

					<div class="product-single__actions">
						<button type="button" class="btn btn--outline" data-compare-add data-product-id="<?php echo esc_attr( (string) get_the_ID() ); ?>" data-label-off="<?php esc_attr_e( 'Comparer', 'anrhpub_theme' ); ?>" data-label-on="<?php esc_attr_e( 'Dans le comparateur', 'anrhpub_theme' ); ?>" aria-pressed="false">
							<span data-compare-label><?php esc_html_e( 'Comparer', 'anrhpub_theme' ); ?></span>
						</button>
						<a class="btn btn--outline" href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>" data-quote-cart-link>
							<?php esc_html_e( 'Mon panier devis', 'anrhpub_theme' ); ?>
						</a>
						<a class="btn btn--outline" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
							<?php esc_html_e( 'Retour au catalogue', 'anrhpub_theme' ); ?>
						</a>
					</div>

					<?php
					$terms = get_the_terms( get_the_ID(), 'anr_category' );
					if ( $terms && ! is_wp_error( $terms ) ) :
						?>
						<div class="product-single__categories">
							<strong><?php esc_html_e( 'Catégorie :', 'anrhpub_theme' ); ?></strong>
							<?php
							$links = array();
							foreach ( $terms as $term ) {
								$links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
							}
							echo wp_kses_post( implode( ', ', $links ) );
							?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="container product-single__below">
				<?php
				if ( function_exists( 'anrhpub_render_product_single_sections' ) ) {
					anrhpub_render_product_single_sections();
				} else {
					anrhpub_render_product_descriptions();
				}
				?>
			</div>
		</article>

		<?php
		$product_terms = get_the_terms( get_the_ID(), 'anr_category' );
		$related_args  = array(
			'post_type'      => 'anr_product',
			'posts_per_page' => 4,
			'post__not_in'   => array( get_the_ID() ),
		);
		if ( $product_terms && ! is_wp_error( $product_terms ) ) {
			$related_args['tax_query'] = array(
				array(
					'taxonomy' => 'anr_category',
					'field'    => 'term_id',
					'terms'    => wp_list_pluck( $product_terms, 'term_id' ),
				),
			);
		}
		$related = new WP_Query( $related_args );
		if ( $related->have_posts() ) :
			?>
			<section class="section section--alt" data-animate>
				<div class="container">
					<h2><?php esc_html_e( 'Produits similaires', 'anrhpub_theme' ); ?></h2>
					<div class="product-grid">
						<?php
						while ( $related->have_posts() ) :
							$related->the_post();
							get_template_part( 'template-parts/product', 'card' );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	<?php endwhile; ?>
</main>

<?php
get_footer();
