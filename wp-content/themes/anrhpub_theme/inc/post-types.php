<?php
/**
 * Custom post types and taxonomies for the product catalogue.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register product post type.
 */
function anrhpub_register_product_cpt() {
	register_post_type(
		'anr_product',
		array(
			'labels'              => array(
				'name'               => __( 'Produits', 'anrhpub_theme' ),
				'singular_name'      => __( 'Produit', 'anrhpub_theme' ),
				'add_new'            => __( 'Ajouter', 'anrhpub_theme' ),
				'add_new_item'       => __( 'Ajouter un produit', 'anrhpub_theme' ),
				'edit_item'          => __( 'Modifier le produit', 'anrhpub_theme' ),
				'new_item'           => __( 'Nouveau produit', 'anrhpub_theme' ),
				'view_item'          => __( 'Voir le produit', 'anrhpub_theme' ),
				'search_items'       => __( 'Rechercher des produits', 'anrhpub_theme' ),
				'not_found'          => __( 'Aucun produit trouvé', 'anrhpub_theme' ),
				'not_found_in_trash' => __( 'Aucun produit dans la corbeille', 'anrhpub_theme' ),
				'all_items'          => __( 'Tous les produits', 'anrhpub_theme' ),
				'menu_name'          => __( 'Catalogue', 'anrhpub_theme' ),
			),
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'catalogue' ),
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
			'menu_icon'           => 'dashicons-store',
			'show_in_rest'        => true,
			'exclude_from_search' => false,
		)
	);

	register_taxonomy(
		'anr_category',
		'anr_product',
		array(
			'labels'            => array(
				'name'          => __( 'Catégories produits', 'anrhpub_theme' ),
				'singular_name' => __( 'Catégorie', 'anrhpub_theme' ),
				'search_items'  => __( 'Rechercher une catégorie', 'anrhpub_theme' ),
				'all_items'     => __( 'Toutes les catégories', 'anrhpub_theme' ),
				'edit_item'     => __( 'Modifier la catégorie', 'anrhpub_theme' ),
				'add_new_item'  => __( 'Ajouter une catégorie', 'anrhpub_theme' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'rewrite'           => array( 'slug' => 'categorie-produit' ),
			'show_admin_column' => true,
			'show_in_rest'      => true,
		)
	);
}
add_action( 'init', 'anrhpub_register_product_cpt' );

/**
 * Register product meta.
 */
function anrhpub_register_product_meta() {
	register_post_meta(
		'anr_product',
		'anr_reference',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'anr_product',
		'anr_badge',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'anr_product',
		'anr_price_label',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'anr_product',
		'anr_featured',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'anr_product',
		'anr_details',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'wp_kses_post',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'anr_product',
		'anr_min_qty',
		array(
			'type'              => 'integer',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => function ( $value ) {
				$min = absint( $value );
				return $min > 0 ? min( 99999, $min ) : 1;
			},
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'anrhpub_register_product_meta' );

/**
 * Meta box — fiche catalogue (détails + quantité minimum).
 */
function anrhpub_product_catalog_meta_box() {
	add_meta_box(
		'anrhpub_product_catalog',
		__( 'Fiche catalogue & devis', 'anrhpub_theme' ),
		'anrhpub_product_catalog_meta_box_render',
		'anr_product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_product_catalog_meta_box' );

/**
 * Rendu meta box catalogue.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_product_catalog_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_product_catalog', 'anrhpub_product_catalog_nonce' );
	$details = get_post_meta( $post->ID, 'anr_details', true );
	$min_qty = get_post_meta( $post->ID, 'anr_min_qty', true );
	$min_qty = $min_qty ? (int) $min_qty : 1;
	?>
	<p>
		<label for="anr_reference"><strong><?php esc_html_e( 'Référence catalogue', 'anrhpub_theme' ); ?></strong></label><br>
		<input type="text" name="anr_reference" id="anr_reference" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, 'anr_reference', true ) ); ?>" class="regular-text" style="margin-top:0.35rem;" />
	</p>
	<p>
		<label for="anr_min_qty"><strong><?php esc_html_e( 'Quantité minimum de commande', 'anrhpub_theme' ); ?></strong></label><br>
		<input type="number" name="anr_min_qty" id="anr_min_qty" value="<?php echo esc_attr( (string) $min_qty ); ?>" min="1" max="99999" step="1" style="width:8rem;margin-top:0.35rem;" />
	</p>
	<p class="description"><? esc_html_e( 'Bloque le minimum du sélecteur de quantité sur la fiche produit (ex. 50 pour un lot textile). Laissez 1 s’il n’y a pas de minimum.', 'anrhpub_theme' ); ?></p>
	<hr style="margin:1.25rem 0;">
	<p class="description"><? esc_html_e( 'Caractéristiques techniques, matières, tailles, normes… Affiché dans l’onglet « Caractéristiques » sur la fiche produit.', 'anrhpub_theme' ); ?></p>
	<textarea name="anr_details" id="anr_details" rows="8" style="width:100%;"><?php echo esc_textarea( $details ); ?></textarea>
	<?php
}

/**
 * Sauvegarde meta fiche catalogue.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_save_product_catalog_meta( $post_id ) {
	if ( ! isset( $_POST['anrhpub_product_catalog_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_product_catalog_nonce'] ) ), 'anrhpub_save_product_catalog' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'anr_product' !== get_post_type( $post_id ) ) {
		return;
	}

	$min_qty = isset( $_POST['anr_min_qty'] ) ? absint( $_POST['anr_min_qty'] ) : 1;
	$min_qty = max( 1, min( 99999, $min_qty ) );

	update_post_meta( $post_id, 'anr_min_qty', $min_qty );

	if ( isset( $_POST['anr_reference'] ) ) {
		update_post_meta( $post_id, 'anr_reference', sanitize_text_field( wp_unslash( $_POST['anr_reference'] ) ) );
	}

	$details = isset( $_POST['anr_details'] ) ? wp_kses_post( wp_unslash( $_POST['anr_details'] ) ) : '';
	update_post_meta( $post_id, 'anr_details', $details );
}
add_action( 'save_post_anr_product', 'anrhpub_save_product_catalog_meta' );
