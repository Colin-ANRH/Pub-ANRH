<?php
/**
 * Stock, délais et filtres facettes catalogue.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_STOCK_STATUS_META', 'anr_stock_status' );
define( 'ANRHPUB_STOCK_QTY_META', 'anr_stock_qty' );
define( 'ANRHPUB_LEAD_TIME_META', 'anr_lead_time_days' );

/**
 * Taxonomies facettes.
 */
function anrhpub_register_facet_taxonomies() {
	register_taxonomy(
		'anr_material',
		'anr_product',
		array(
			'labels'       => array(
				'name'          => __( 'Matières', 'anrhpub_theme' ),
				'singular_name' => __( 'Matière', 'anrhpub_theme' ),
			),
			'hierarchical' => false,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'matiere' ),
		)
	);

	register_taxonomy(
		'anr_product_badge',
		'anr_product',
		array(
			'labels'       => array(
				'name'          => __( 'Labels produit', 'anrhpub_theme' ),
				'singular_name' => __( 'Label', 'anrhpub_theme' ),
			),
			'hierarchical' => false,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'label-produit' ),
		)
	);
}
add_action( 'init', 'anrhpub_register_facet_taxonomies', 11 );

/**
 * Statuts stock.
 *
 * @return array<string, string>
 */
function anrhpub_stock_statuses() {
	return array(
		'instock'     => __( 'En stock', 'anrhpub_theme' ),
		'outofstock'  => __( 'Rupture', 'anrhpub_theme' ),
		'onbackorder' => __( 'Sur commande', 'anrhpub_theme' ),
		'preorder'    => __( 'Précommande', 'anrhpub_theme' ),
	);
}

/**
 * Libellé stock produit.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function anrhpub_get_product_stock_label( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$status  = (string) get_post_meta( $post_id, ANRHPUB_STOCK_STATUS_META, true );

	if ( ! $status ) {
		$status = 'instock';
	}

	$label = anrhpub_stock_statuses()[ $status ] ?? $status;
	$qty   = (int) get_post_meta( $post_id, ANRHPUB_STOCK_QTY_META, true );
	$days  = (int) get_post_meta( $post_id, ANRHPUB_LEAD_TIME_META, true );

	if ( $qty > 0 && 'instock' === $status ) {
		$label .= ' (' . sprintf(
			/* translators: %d: quantity */
			__( '%d dispo.', 'anrhpub_theme' ),
			$qty
		) . ')';
	}

	if ( $days > 0 && in_array( $status, array( 'onbackorder', 'preorder' ), true ) ) {
		$label .= ' — ' . sprintf(
			/* translators: %d: days */
			__( 'délai ~%d j.', 'anrhpub_theme' ),
			$days
		);
	}

	return $label;
}

/**
 * Affiche badge stock fiche produit.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_render_product_stock_badge( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$status  = (string) get_post_meta( $post_id, ANRHPUB_STOCK_STATUS_META, true );

	if ( ! $status ) {
		$status = 'instock';
	}

	printf(
		'<p class="product-stock product-stock--%1$s"><span class="product-stock__dot" aria-hidden="true"></span>%2$s</p>',
		esc_attr( $status ),
		esc_html( anrhpub_get_product_stock_label( $post_id ) )
	);
}

/**
 * Meta box stock admin.
 */
function anrhpub_product_stock_meta_box() {
	add_meta_box(
		'anrhpub_product_stock',
		__( 'Stock & délai', 'anrhpub_theme' ),
		'anrhpub_product_stock_meta_box_render',
		'anr_product',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_product_stock_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function anrhpub_product_stock_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_product_stock', 'anrhpub_product_stock_nonce' );
	$status = (string) get_post_meta( $post->ID, ANRHPUB_STOCK_STATUS_META, true );
	$status = $status ? $status : 'instock';
	?>
	<p>
		<label for="anr_stock_status"><?php esc_html_e( 'Disponibilité', 'anrhpub_theme' ); ?></label>
		<select name="anr_stock_status" id="anr_stock_status" style="width:100%;">
			<?php foreach ( anrhpub_stock_statuses() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="anr_stock_qty"><?php esc_html_e( 'Quantité en stock', 'anrhpub_theme' ); ?></label>
		<input type="number" min="0" name="anr_stock_qty" id="anr_stock_qty" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, ANRHPUB_STOCK_QTY_META, true ) ); ?>" style="width:100%;" />
	</p>
	<p>
		<label for="anr_lead_time_days"><?php esc_html_e( 'Délai (jours)', 'anrhpub_theme' ); ?></label>
		<input type="number" min="0" name="anr_lead_time_days" id="anr_lead_time_days" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, ANRHPUB_LEAD_TIME_META, true ) ); ?>" style="width:100%;" />
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function anrhpub_save_product_stock_meta( $post_id ) {
	if ( ! isset( $_POST['anrhpub_product_stock_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_product_stock_nonce'] ) ), 'anrhpub_save_product_stock' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['anr_stock_status'] ) ) {
		$st = sanitize_key( wp_unslash( $_POST['anr_stock_status'] ) );
		update_post_meta( $post_id, ANRHPUB_STOCK_STATUS_META, isset( anrhpub_stock_statuses()[ $st ] ) ? $st : 'instock' );
	}
	if ( isset( $_POST['anr_stock_qty'] ) ) {
		update_post_meta( $post_id, ANRHPUB_STOCK_QTY_META, max( 0, absint( $_POST['anr_stock_qty'] ) ) );
	}
	if ( isset( $_POST['anr_lead_time_days'] ) ) {
		update_post_meta( $post_id, ANRHPUB_LEAD_TIME_META, max( 0, absint( $_POST['anr_lead_time_days'] ) ) );
	}
}
add_action( 'save_post_anr_product', 'anrhpub_save_product_stock_meta' );

/**
 * Filtre facettes sur archive catalogue.
 *
 * @param WP_Query $query Query.
 */
function anrhpub_catalogue_facet_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! is_post_type_archive( 'anr_product' ) && ! is_tax( array( 'anr_category', 'anr_material', 'anr_product_badge' ) ) ) {
		return;
	}

	$tax_query = (array) $query->get( 'tax_query' );

	foreach ( array( 'anr_material', 'anr_product_badge' ) as $tax ) {
		if ( empty( $_GET[ $tax ] ) ) {
			continue;
		}
		$slug = sanitize_title( wp_unslash( (string) $_GET[ $tax ] ) );
		if ( $slug ) {
			$tax_query[] = array(
				'taxonomy' => $tax,
				'field'    => 'slug',
				'terms'    => $slug,
			);
		}
	}

	if ( ! empty( $_GET['stock'] ) ) {
		$stock = sanitize_key( wp_unslash( (string) $_GET['stock'] ) );
		if ( isset( anrhpub_stock_statuses()[ $stock ] ) ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'   => ANRHPUB_STOCK_STATUS_META,
						'value' => $stock,
					),
				)
			);
		}
	}

	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}
}
add_action( 'pre_get_posts', 'anrhpub_catalogue_facet_query' );

/**
 * Rendu filtres facettes (sidebar).
 */
function anrhpub_render_catalogue_facets() {
	$materials = get_terms( array( 'taxonomy' => 'anr_material', 'hide_empty' => true ) );
	$badges    = get_terms( array( 'taxonomy' => 'anr_product_badge', 'hide_empty' => true ) );
	$current_m = isset( $_GET['anr_material'] ) ? sanitize_title( wp_unslash( (string) $_GET['anr_material'] ) ) : '';
	$current_b = isset( $_GET['anr_product_badge'] ) ? sanitize_title( wp_unslash( (string) $_GET['anr_product_badge'] ) ) : '';
	$current_s = isset( $_GET['stock'] ) ? sanitize_key( wp_unslash( (string) $_GET['stock'] ) ) : '';

	echo '<div class="catalogue-facets">';
	echo '<h3 class="catalogue-facets__title">' . esc_html__( 'Filtres', 'anrhpub_theme' ) . '</h3>';
	echo '<form method="get" class="catalogue-facets__form">';

	if ( ! empty( $materials ) && ! is_wp_error( $materials ) ) {
		echo '<p><label for="facet-material">' . esc_html__( 'Matière', 'anrhpub_theme' ) . '</label>';
		echo '<select name="anr_material" id="facet-material"><option value="">' . esc_html__( 'Toutes', 'anrhpub_theme' ) . '</option>';
		foreach ( $materials as $term ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $term->slug ), selected( $current_m, $term->slug, false ), esc_html( $term->name ) );
		}
		echo '</select></p>';
	}

	if ( ! empty( $badges ) && ! is_wp_error( $badges ) ) {
		echo '<p><label for="facet-badge">' . esc_html__( 'Label', 'anrhpub_theme' ) . '</label>';
		echo '<select name="anr_product_badge" id="facet-badge"><option value="">' . esc_html__( 'Tous', 'anrhpub_theme' ) . '</option>';
		foreach ( $badges as $term ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $term->slug ), selected( $current_b, $term->slug, false ), esc_html( $term->name ) );
		}
		echo '</select></p>';
	}

	echo '<p><label for="facet-stock">' . esc_html__( 'Disponibilité', 'anrhpub_theme' ) . '</label>';
	echo '<select name="stock" id="facet-stock"><option value="">' . esc_html__( 'Toutes', 'anrhpub_theme' ) . '</option>';
	foreach ( anrhpub_stock_statuses() as $key => $label ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $current_s, $key, false ), esc_html( $label ) );
	}
	echo '</select></p>';

	echo '<button type="submit" class="btn btn--outline btn--sm">' . esc_html__( 'Appliquer', 'anrhpub_theme' ) . '</button>';
	echo '</form></div>';
}
