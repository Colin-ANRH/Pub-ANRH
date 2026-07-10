<?php
/**
 * Fiche produit — grille épurée.
 *
 * @package anrhpub_theme
 */
?>
<article <?php post_class( 'product-card' ); ?>>
	<div class="product-card__media">
		<?php anrhpub_render_favorite_button(); ?>
		<a class="product-card__media-link" href="<?php the_permalink(); ?>">
			<?php anrhpub_product_badge(); ?>
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail(
					'medium_large',
					array(
						'class' => 'product-card__img',
						'alt'   => function_exists( 'anrhpub_get_product_image_alt' ) ? anrhpub_get_product_image_alt( get_the_ID() ) : get_the_title(),
					)
				);
			} else {
				anrhpub_product_thumbnail( 0, 'product-card__img' );
			}
			?>
		</a>
	</div>
	<div class="product-card__body">
		<?php anrhpub_product_reference(); ?>
		<h3 class="product-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
		<div class="product-card__footer">
			<?php anrhpub_product_price(); ?>
			<button type="button" class="btn btn--outline btn--sm product-card__compare" data-compare-add data-product-id="<?php echo esc_attr( (string) get_the_ID() ); ?>" data-label-off="<?php esc_attr_e( 'Comparer', 'anrhpub_theme' ); ?>" data-label-on="<?php esc_attr_e( 'Dans le comparateur', 'anrhpub_theme' ); ?>" aria-pressed="false">
				<span data-compare-label><?php esc_html_e( 'Comparer', 'anrhpub_theme' ); ?></span>
			</button>
			<a class="product-card__detail" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Fiche', 'anrhpub_theme' ); ?></a>
			<a class="product-card__cta" href="<?php echo esc_url( anrhpub_get_product_add_url( get_the_ID() ) ); ?>"><?php esc_html_e( 'Ajouter', 'anrhpub_theme' ); ?></a>
		</div>
	</div>
</article>
