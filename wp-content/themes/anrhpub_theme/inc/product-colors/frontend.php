<?php
/** Meta REST et selecteur front. @package anrhpub_theme */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistrement meta couleurs / stock produit.
 */
function anrhpub_register_product_color_stock_meta() {
	register_post_meta(
		'anr_product',
		ANRHPUB_PRODUCT_COLOR_STOCK_META,
		array(
			'type'              => 'array',
			'single'            => true,
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'color_id' => array( 'type' => 'integer' ),
							'stock'    => array( 'type' => 'integer' ),
						),
					),
				),
			),
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
			'sanitize_callback' => function ( $value ) {
				if ( ! is_array( $value ) ) {
					return array();
				}

				$rows = array();

				foreach ( $value as $row ) {
					if ( ! is_array( $row ) ) {
						continue;
					}

					$color_id = isset( $row['color_id'] ) ? absint( $row['color_id'] ) : 0;
					$stock    = isset( $row['stock'] ) ? absint( $row['stock'] ) : 0;

					if ( $color_id <= 0 ) {
						continue;
					}

					$rows[] = array(
						'color_id' => $color_id,
						'stock'    => min( 999999, $stock ),
					);
				}

				return $rows;
			},
		)
	);
}
add_action( 'init', 'anrhpub_register_product_color_stock_meta', 12 );

/**
 * Sélecteur couleur sur la fiche produit.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_render_product_color_picker( $post_id = 0 ) {
	$colors = anrhpub_get_product_colors( $post_id );

	if ( empty( $colors ) ) {
		return;
	}
	?>
	<div class="product-color-picker" data-product-colors>
		<div class="product-color-picker__head">
			<span class="product-color-picker__label"><?php esc_html_e( 'Couleur disponible', 'anrhpub_theme' ); ?></span>
			<span class="product-color-picker__required"><?php esc_html_e( 'Obligatoire', 'anrhpub_theme' ); ?></span>
		</div>
		<div class="product-color-picker__grid" role="radiogroup" aria-label="<?php esc_attr_e( 'Choisir une couleur parmi les couleurs disponibles', 'anrhpub_theme' ); ?>">
			<?php foreach ( $colors as $index => $color ) : ?>
				<label class="product-color-picker__option<?php echo 0 === $index ? ' is-selected' : ''; ?>" data-color-stock="<?php echo esc_attr( (string) $color['stock'] ); ?>">
					<input
						type="radio"
						class="product-color-picker__input"
						name="quote_color_<?php echo esc_attr( (string) $post_id ); ?>"
						value="<?php echo esc_attr( (string) $color['id'] ); ?>"
						data-quote-color-input
						data-color-stock="<?php echo esc_attr( (string) $color['stock'] ); ?>"
						<?php checked( 0 === $index ); ?>
						required
					/>
					<span class="product-color-picker__swatch" style="--color-hex: <?php echo esc_attr( $color['hex'] ); ?>;" aria-hidden="true"></span>
					<span class="product-color-picker__meta">
						<span class="product-color-picker__name"><?php echo esc_html( $color['name'] ); ?></span>
						<span class="product-color-picker__stock">
							<?php
							printf(
								/* translators: %d: available quantity */
								esc_html__( '%d en stock', 'anrhpub_theme' ),
								(int) $color['stock']
							);
							?>
						</span>
					</span>
					<span class="product-color-picker__check" aria-hidden="true"></span>
				</label>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
