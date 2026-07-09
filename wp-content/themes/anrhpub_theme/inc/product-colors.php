<?php
/**
 * Couleurs produits — taxonomie gérée dans WordPress.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_COLOR_HEX_META', 'anr_color_hex' );
define( 'ANRHPUB_COLORS_SEEDED_OPTION', 'anrhpub_colors_seeded' );
define( 'ANRHPUB_PRODUCT_COLOR_STOCK_META', 'anr_product_color_stock' );

/**
 * Taxonomie couleurs catalogue.
 */
function anrhpub_register_product_color_taxonomy() {
	register_taxonomy(
		'anr_color',
		'anr_product',
		array(
			'labels'            => array(
				'name'                       => __( 'Couleurs', 'anrhpub_theme' ),
				'singular_name'              => __( 'Couleur', 'anrhpub_theme' ),
				'search_items'               => __( 'Rechercher une couleur', 'anrhpub_theme' ),
				'all_items'                  => __( 'Toutes les couleurs', 'anrhpub_theme' ),
				'edit_item'                  => __( 'Modifier la couleur', 'anrhpub_theme' ),
				'update_item'                => __( 'Mettre à jour la couleur', 'anrhpub_theme' ),
				'add_new_item'               => __( 'Ajouter une couleur', 'anrhpub_theme' ),
				'new_item_name'              => __( 'Nom de la couleur', 'anrhpub_theme' ),
				'menu_name'                  => __( 'Couleurs', 'anrhpub_theme' ),
				'not_found'                  => __( 'Aucune couleur.', 'anrhpub_theme' ),
				'no_terms'                   => __( 'Aucune couleur pour le moment.', 'anrhpub_theme' ),
				'items_list_navigation'      => __( 'Navigation liste des couleurs', 'anrhpub_theme' ),
				'item_link'                  => __( 'Lien couleur', 'anrhpub_theme' ),
				'item_link_description'      => __( 'Lien vers une couleur du catalogue.', 'anrhpub_theme' ),
			),
			'hierarchical'      => false,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => false,
			'meta_box_cb'       => false,
			'show_in_rest'      => true,
			'rewrite'           => false,
		)
	);

	register_term_meta(
		'anr_color',
		ANRHPUB_COLOR_HEX_META,
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'anrhpub_sanitize_color_hex',
		)
	);
}
add_action( 'init', 'anrhpub_register_product_color_taxonomy', 11 );

/**
 * Code couleur hex (#RRGGBB).
 *
 * @param string $hex Valeur.
 * @return string
 */
function anrhpub_sanitize_color_hex( $hex ) {
	$hex = sanitize_hex_color( (string) $hex );

	if ( ! $hex ) {
		return '#888888';
	}

	return $hex;
}

/**
 * Hex d’une couleur (terme).
 *
 * @param int $term_id Term ID.
 * @return string
 */
function anrhpub_get_color_hex( $term_id ) {
	$hex = get_term_meta( (int) $term_id, ANRHPUB_COLOR_HEX_META, true );

	return anrhpub_sanitize_color_hex( $hex ? $hex : '#888888' );
}

/**
 * Toutes les couleurs du catalogue (référentiel WP).
 *
 * @return array<int, WP_Term>
 */
function anrhpub_get_all_catalog_colors() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'anr_color',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	usort(
		$terms,
		function ( $a, $b ) {
			return strcasecmp( $a->name, $b->name );
		}
	);

	return $terms;
}

/**
 * Lignes couleur / stock enregistrées pour un produit (admin + logique).
 *
 * @param int $post_id Post ID.
 * @return array<int, array{color_id: int, stock: int}>
 */
function anrhpub_get_product_color_stock_rows( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$raw     = get_post_meta( $post_id, ANRHPUB_PRODUCT_COLOR_STOCK_META, true );

	if ( ! is_array( $raw ) ) {
		anrhpub_maybe_migrate_product_colors_from_taxonomy( $post_id );
		$raw = get_post_meta( $post_id, ANRHPUB_PRODUCT_COLOR_STOCK_META, true );
	}

	if ( ! is_array( $raw ) ) {
		return array();
	}

	$rows = array();

	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$color_id = isset( $row['color_id'] ) ? (int) $row['color_id'] : 0;
		$stock    = isset( $row['stock'] ) ? (int) $row['stock'] : 0;

		if ( $color_id <= 0 ) {
			continue;
		}

		$term = get_term( $color_id, 'anr_color' );

		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}

		$rows[] = array(
			'color_id' => $color_id,
			'stock'    => max( 0, min( 999999, $stock ) ),
		);
	}

	usort(
		$rows,
		function ( $a, $b ) {
			return strcasecmp( anrhpub_get_color_name( $a['color_id'] ), anrhpub_get_color_name( $b['color_id'] ) );
		}
	);

	return $rows;
}

/**
 * Migration : anciennes cases à cocher taxonomie → meta stock.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_maybe_migrate_product_colors_from_taxonomy( $post_id ) {
	$post_id = (int) $post_id;

	if ( $post_id <= 0 || get_post_meta( $post_id, ANRHPUB_PRODUCT_COLOR_STOCK_META, true ) ) {
		return;
	}

	$term_ids = wp_get_object_terms( $post_id, 'anr_color', array( 'fields' => 'ids' ) );

	if ( is_wp_error( $term_ids ) || empty( $term_ids ) ) {
		return;
	}

	$rows = array();

	foreach ( $term_ids as $term_id ) {
		$rows[] = array(
			'color_id' => (int) $term_id,
			'stock'    => 100,
		);
	}

	anrhpub_save_product_color_stock_rows( $post_id, $rows );
}

/**
 * Enregistre les couleurs / stocks d’un produit.
 *
 * @param int   $post_id Post ID.
 * @param array $rows    Rows color_id + stock.
 */
function anrhpub_save_product_color_stock_rows( $post_id, $rows ) {
	$post_id = (int) $post_id;
	$clean   = array();

	foreach ( (array) $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$color_id = isset( $row['color_id'] ) ? (int) $row['color_id'] : 0;
		$stock    = isset( $row['stock'] ) ? (int) $row['stock'] : 0;

		if ( $color_id <= 0 ) {
			continue;
		}

		$term = get_term( $color_id, 'anr_color' );

		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}

		$clean[] = array(
			'color_id' => $color_id,
			'stock'    => max( 0, min( 999999, $stock ) ),
		);
	}

	update_post_meta( $post_id, ANRHPUB_PRODUCT_COLOR_STOCK_META, $clean );

	$term_ids = wp_list_pluck( $clean, 'color_id' );
	wp_set_object_terms( $post_id, array_map( 'intval', $term_ids ), 'anr_color', false );
}

/**
 * Stock disponible pour une couleur sur un produit.
 *
 * @param int $post_id  Post ID.
 * @param int $color_id Color term ID.
 * @return int 0 si couleur non proposée.
 */
function anrhpub_get_product_color_stock( $post_id, $color_id ) {
	$color_id = (int) $color_id;

	foreach ( anrhpub_get_product_color_stock_rows( $post_id ) as $row ) {
		if ( (int) $row['color_id'] === $color_id ) {
			return (int) $row['stock'];
		}
	}

	return 0;
}

/**
 * Couleurs proposées sur le site (activées sur le produit).
 *
 * @param int $post_id Post ID.
 * @return array<int, array{id: int, name: string, slug: string, hex: string, stock: int}>
 */
function anrhpub_get_product_colors( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$colors  = array();

	foreach ( anrhpub_get_product_color_stock_rows( $post_id ) as $row ) {
		$term = get_term( (int) $row['color_id'], 'anr_color' );

		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}

		$colors[] = array(
			'id'    => (int) $term->term_id,
			'name'  => $term->name,
			'slug'  => $term->slug,
			'hex'   => anrhpub_get_color_hex( $term->term_id ),
			'stock' => (int) $row['stock'],
		);
	}

	return $colors;
}

/**
 * Quantité max devis pour une ligne (pas de plafond couleur pour l’instant).
 *
 * @param int $product_id Product ID.
 * @param int $color_id   Color ID (ignoré).
 * @return int
 */
function anrhpub_get_quote_max_qty_for_color( $product_id, $color_id = 0 ) {
	unset( $product_id, $color_id );

	return 99999;
}

/**
 * Ajuste une quantité (minimum produit uniquement).
 *
 * @param int $product_id Product ID.
 * @param int $color_id   Color ID (ignoré pour le plafond).
 * @param int $qty        Quantity.
 * @return int
 */
function anrhpub_clamp_quote_qty_for_color( $product_id, $color_id, $qty ) {
	unset( $color_id );

	return anrhpub_clamp_quote_qty( $product_id, $qty );
}

/**
 * Le produit a-t-il des couleurs à choisir ?
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function anrhpub_product_has_colors( $post_id = 0 ) {
	return ! empty( anrhpub_get_product_colors( $post_id ) );
}

/**
 * Valide une couleur pour un produit.
 *
 * @param int $product_id Product ID.
 * @param int $color_id   Color term ID.
 * @return int 0 si invalide.
 */
function anrhpub_validate_product_color( $product_id, $color_id ) {
	$product_id = (int) $product_id;
	$color_id   = (int) $color_id;

	if ( ! anrhpub_product_has_colors( $product_id ) ) {
		return 0;
	}

	if ( $color_id <= 0 ) {
		return 0;
	}

	foreach ( anrhpub_get_product_color_stock_rows( $product_id ) as $row ) {
		if ( (int) $row['color_id'] === $color_id ) {
			return $color_id;
		}
	}

	return 0;
}

/**
 * Nom de couleur pour affichage.
 *
 * @param int $color_id Term ID.
 * @return string
 */
function anrhpub_get_color_name( $color_id ) {
	$color_id = (int) $color_id;

	if ( $color_id <= 0 ) {
		return '';
	}

	$term = get_term( $color_id, 'anr_color' );

	if ( ! $term || is_wp_error( $term ) ) {
		return '';
	}

	return $term->name;
}

/**
 * Clé unique ligne panier.
 *
 * @param int $product_id Product ID.
 * @param int $color_id   Color ID.
 * @return string
 */
function anrhpub_quote_cart_line_key( $product_id, $color_id = 0 ) {
	return (int) $product_id . ':' . (int) $color_id;
}

/**
 * Couleurs par défaut au premier lancement.
 */
function anrhpub_seed_default_colors() {
	if ( get_option( ANRHPUB_COLORS_SEEDED_OPTION ) ) {
		return;
	}

	$defaults = array(
		'Blanc'       => '#FFFFFF',
		'Noir'        => '#1A1A1A',
		'Gris'        => '#6B6B6B',
		'Rouge'       => '#C41E3A',
		'Bordeaux'    => '#6B2737',
		'Orange'      => '#E85D04',
		'Jaune'       => '#F4C430',
		'Vert'        => '#2D6A4F',
		'Bleu marine' => '#1D3557',
		'Bleu ciel'   => '#4DA3FF',
		'Violet'      => '#7B2CBF',
		'Rose'        => '#E899AD',
		'Beige'       => '#D4B896',
		'Marine'      => '#003049',
	);

	foreach ( $defaults as $name => $hex ) {
		if ( term_exists( $name, 'anr_color' ) ) {
			continue;
		}

		$created = wp_insert_term( $name, 'anr_color' );

		if ( is_wp_error( $created ) ) {
			continue;
		}

		update_term_meta( (int) $created['term_id'], ANRHPUB_COLOR_HEX_META, anrhpub_sanitize_color_hex( $hex ) );
	}

	update_option( ANRHPUB_COLORS_SEEDED_OPTION, 1 );
}
add_action( 'init', 'anrhpub_seed_default_colors', 20 );

/**
 * Champs admin — ajout couleur.
 */
function anrhpub_color_add_form_fields() {
	?>
	<div class="form-field anrhpub-color-hex-field">
		<label for="anr_color_hex"><?php esc_html_e( 'Pastille couleur', 'anrhpub_theme' ); ?></label>
		<div class="anrhpub-color-hex-controls">
			<input type="color" class="anrhpub-hex-picker" value="#888888" aria-label="<?php esc_attr_e( 'Choisir une couleur', 'anrhpub_theme' ); ?>" />
			<input type="text" name="anr_color_hex" id="anr_color_hex" class="anrhpub-hex-text" value="#888888" placeholder="#RRGGBB" />
			<span class="anrhpub-color-swatch-preview anrhpub-color-swatch-preview--lg" data-hex-preview style="background-color:#888888"></span>
		</div>
		<p class="description"><?php esc_html_e( 'Cliquez sur la pastille ou utilisez le sélecteur — plus besoin de saisir le code à la main.', 'anrhpub_theme' ); ?></p>
	</div>
	<?php
}
add_action( 'anr_color_add_form_fields', 'anrhpub_color_add_form_fields' );

/**
 * Champs admin — édition couleur.
 *
 * @param WP_Term $term Term.
 */
function anrhpub_color_edit_form_fields( $term ) {
	$hex = anrhpub_get_color_hex( $term->term_id );
	?>
	<tr class="form-field anrhpub-color-hex-field">
		<th scope="row"><label for="anr_color_hex"><?php esc_html_e( 'Pastille couleur', 'anrhpub_theme' ); ?></label></th>
		<td>
			<div class="anrhpub-color-hex-controls">
				<input type="color" class="anrhpub-hex-picker" value="<?php echo esc_attr( $hex ); ?>" aria-label="<?php esc_attr_e( 'Choisir une couleur', 'anrhpub_theme' ); ?>" />
				<input type="text" name="anr_color_hex" id="anr_color_hex" class="anrhpub-hex-text" value="<?php echo esc_attr( $hex ); ?>" placeholder="#RRGGBB" />
				<span class="anrhpub-color-swatch-preview anrhpub-color-swatch-preview--lg" data-hex-preview style="background-color:<?php echo esc_attr( $hex ); ?>"></span>
			</div>
			<p class="description"><?php esc_html_e( 'Pastille affichée sur la fiche produit, le panier et la sélection admin.', 'anrhpub_theme' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'anr_color_edit_form_fields', 'anrhpub_color_edit_form_fields' );

/**
 * Sauvegarde hex à la création.
 *
 * @param int $term_id Term ID.
 */
function anrhpub_save_color_hex_create( $term_id ) {
	if ( isset( $_POST['anr_color_hex'] ) ) {
		update_term_meta( $term_id, ANRHPUB_COLOR_HEX_META, anrhpub_sanitize_color_hex( wp_unslash( $_POST['anr_color_hex'] ) ) );
	}
}
add_action( 'created_anr_color', 'anrhpub_save_color_hex_create' );

/**
 * Sauvegarde hex à l’édition.
 *
 * @param int $term_id Term ID.
 */
function anrhpub_save_color_hex_edit( $term_id ) {
	if ( isset( $_POST['anr_color_hex'] ) ) {
		update_term_meta( $term_id, ANRHPUB_COLOR_HEX_META, anrhpub_sanitize_color_hex( wp_unslash( $_POST['anr_color_hex'] ) ) );
	}
}
add_action( 'edited_anr_color', 'anrhpub_save_color_hex_edit' );

/**
 * Colonne pastille dans la liste des couleurs.
 *
 * @param array $columns Columns.
 * @return array
 */
function anrhpub_color_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'name' === $key ) {
			$new['anr_color_hex'] = __( 'Pastille', 'anrhpub_theme' );
		}
	}
	return $new;
}
add_filter( 'manage_edit-anr_color_columns', 'anrhpub_color_columns' );

/**
 * Rendu colonne pastille.
 *
 * @param string $content Content.
 * @param string $column  Column.
 * @param int    $term_id Term ID.
 * @return string
 */
function anrhpub_color_column_content( $content, $column, $term_id ) {
	if ( 'anr_color_hex' !== $column ) {
		return $content;
	}

	$hex = anrhpub_get_color_hex( $term_id );

	return '<span class="anrhpub-color-swatch-preview" style="background-color:' . esc_attr( $hex ) . '" title="' . esc_attr( $hex ) . '"></span>';
}
add_filter( 'manage_anr_color_custom_column', 'anrhpub_color_column_content', 10, 3 );

/**
 * Styles admin couleurs.
 */
function anrhpub_color_admin_styles() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'edit-anr_color' !== $screen->id ) {
		return;
	}
	?>
	<style>
		.anrhpub-color-swatch-preview {
			display: inline-block;
			width: 1.5rem;
			height: 1.5rem;
			margin-left: 0.5rem;
			vertical-align: middle;
			border: 1px solid #ccc;
			border-radius: 3px;
		}
	</style>
	<?php
}
add_action( 'admin_head', 'anrhpub_color_admin_styles' );

/**
 * Ajoute une couleur catalogue à un produit (sans doublon).
 *
 * @param int $post_id    Produit.
 * @param int $color_id   Terme anr_color.
 * @param int $stock      Stock initial si nouvelle ligne.
 * @return bool True si ajoutée ou déjà présente.
 */
function anrhpub_add_color_to_product( $post_id, $color_id, $stock = 1 ) {
	$post_id  = (int) $post_id;
	$color_id = (int) $color_id;
	$stock    = max( 0, min( 999999, (int) $stock ) );

	if ( $post_id <= 0 || $color_id <= 0 ) {
		return false;
	}

	$term = get_term( $color_id, 'anr_color' );
	if ( ! $term || is_wp_error( $term ) ) {
		return false;
	}

	$rows    = anrhpub_get_product_color_stock_rows( $post_id );
	$updated = false;

	foreach ( $rows as $i => $row ) {
		if ( (int) $row['color_id'] === $color_id ) {
			if ( $stock > 0 && (int) $row['stock'] < 1 ) {
				$rows[ $i ]['stock'] = $stock;
				$updated             = true;
			}
			if ( ! $updated ) {
				return true;
			}
			anrhpub_save_product_color_stock_rows( $post_id, $rows );
			return true;
		}
	}

	$rows[] = array(
		'color_id' => $color_id,
		'stock'    => $stock > 0 ? $stock : 1,
	);
	anrhpub_save_product_color_stock_rows( $post_id, $rows );

	return true;
}

/**
 * Actions groupées — liste Couleurs catalogue.
 *
 * @param array $actions Actions.
 * @return array
 */
function anrhpub_color_bulk_actions( $actions ) {
	$actions['anrhpub_add_to_all_products'] = __( 'Ajouter à tous les produits', 'anrhpub_theme' );
	return $actions;
}
add_filter( 'bulk_actions-edit-anr_color', 'anrhpub_color_bulk_actions' );

/**
 * Traitement action groupée « Ajouter à tous les produits ».
 *
 * @param string $redirect_to Redirect URL.
 * @param string $action      Action.
 * @param array  $term_ids    Term IDs.
 * @return string
 */
function anrhpub_handle_color_bulk_add_to_all_products( $redirect_to, $action, $term_ids ) {
	if ( 'anrhpub_add_to_all_products' !== $action ) {
		return $redirect_to;
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		return $redirect_to;
	}

	$term_ids = array_filter( array_map( 'intval', (array) $term_ids ) );

	if ( empty( $term_ids ) ) {
		return $redirect_to;
	}

	$product_ids = get_posts(
		array(
			'post_type'      => 'anr_product',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	$added_links = 0;

	foreach ( $product_ids as $post_id ) {
		foreach ( $term_ids as $color_id ) {
			if ( anrhpub_add_color_to_product( (int) $post_id, (int) $color_id, 1 ) ) {
				++$added_links;
			}
		}
	}

	return add_query_arg(
		array(
			'anrhpub_color_bulk'  => 'added_all',
			'anrhpub_color_count' => $added_links,
			'anrhpub_color_terms' => count( $term_ids ),
			'anrhpub_color_posts' => count( $product_ids ),
		),
		$redirect_to
	);
}
add_filter( 'handle_bulk_actions-edit-anr_color', 'anrhpub_handle_color_bulk_add_to_all_products', 10, 3 );

/**
 * Notice admin après action groupée couleurs.
 */
function anrhpub_color_bulk_admin_notice() {
	if ( ! isset( $_GET['anrhpub_color_bulk'] ) || 'added_all' !== $_GET['anrhpub_color_bulk'] ) {
		return;
	}

	$links = isset( $_GET['anrhpub_color_count'] ) ? (int) $_GET['anrhpub_color_count'] : 0;
	$terms = isset( $_GET['anrhpub_color_terms'] ) ? (int) $_GET['anrhpub_color_terms'] : 0;
	$posts = isset( $_GET['anrhpub_color_posts'] ) ? (int) $_GET['anrhpub_color_posts'] : 0;

	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: 1: color count, 2: product count, 3: links count */
				__( '%1$d couleur(s) ajoutée(s) sur %2$d produit(s) (%3$d association(s) au total).', 'anrhpub_theme' ),
				$terms,
				$posts,
				$links
			)
		)
	);
}
add_action( 'admin_notices', 'anrhpub_color_bulk_admin_notice' );

/**
 * Meta couleurs / stock sur la fiche produit.
 */
function anrhpub_product_color_stock_meta_box() {
	add_meta_box(
		'anrhpub_product_color_stock',
		__( 'Couleurs & disponibilités', 'anrhpub_theme' ),
		'anrhpub_product_color_stock_meta_box_render',
		'anr_product',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_product_color_stock_meta_box' );

/**
 * Rendu metabox couleurs par produit.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_product_color_stock_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_product_color_stock', 'anrhpub_product_color_stock_nonce' );

	$all_colors = anrhpub_get_all_catalog_colors();
	$saved      = array();

	foreach ( anrhpub_get_product_color_stock_rows( $post->ID ) as $row ) {
		$saved[ (int) $row['color_id'] ] = (int) $row['stock'];
	}

	if ( empty( $all_colors ) ) {
		echo '<p>' . esc_html__( 'Aucune couleur dans le catalogue. Ajoutez-en via Catalogue → Couleurs.', 'anrhpub_theme' ) . '</p>';
		return;
	}
	?>
	<div class="anrhpub-color-picker-bar">
		<label for="anrhpub-color-picker-select" class="anrhpub-color-picker-bar__label">
			<?php esc_html_e( 'Ajouter une couleur au produit', 'anrhpub_theme' ); ?>
		</label>
		<div class="anrhpub-color-picker-bar__controls">
			<span class="anrhpub-color-picker-bar__preview" data-color-select-preview aria-hidden="true"></span>
			<select id="anrhpub-color-picker-select" class="anrhpub-color-picker-select">
				<option value=""><?php esc_html_e( '— Choisir une couleur —', 'anrhpub_theme' ); ?></option>
				<?php foreach ( $all_colors as $term ) : ?>
					<?php
					$color_id = (int) $term->term_id;
					$hex      = anrhpub_get_color_hex( $color_id );
					?>
					<option value="<?php echo esc_attr( (string) $color_id ); ?>" data-hex="<?php echo esc_attr( $hex ); ?>">
						<?php echo esc_html( $term->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button button-primary" id="anrhpub-color-picker-add">
				<?php esc_html_e( 'Ajouter', 'anrhpub_theme' ); ?>
			</button>
		</div>
		<p class="description">
			<?php esc_html_e( 'Sélectionnez une couleur dans la liste ou cliquez sur une pastille ci-dessous, puis indiquez la quantité disponible.', 'anrhpub_theme' ); ?>
			<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=anr_color&post_type=anr_product' ) ); ?>">
				<?php esc_html_e( 'Gérer le catalogue de couleurs', 'anrhpub_theme' ); ?>
			</a>
		</p>
	</div>
	<div class="anrhpub-admin-colors-grid" role="group" aria-label="<?php esc_attr_e( 'Couleurs disponibles pour ce produit', 'anrhpub_theme' ); ?>">
		<?php foreach ( $all_colors as $term ) : ?>
			<?php
			$color_id = (int) $term->term_id;
			$hex      = anrhpub_get_color_hex( $color_id );
			$enabled  = array_key_exists( $color_id, $saved );
			$stock    = $enabled ? (int) $saved[ $color_id ] : 0;
			?>
			<div
				class="anrhpub-admin-color-card<?php echo $enabled ? ' is-active' : ''; ?>"
				data-admin-color-card
				data-color-id="<?php echo esc_attr( (string) $color_id ); ?>"
			>
				<label class="anrhpub-admin-color-card__pick">
					<input
						type="checkbox"
						name="anr_product_colors[<?php echo esc_attr( (string) $color_id ); ?>][enabled]"
						value="1"
						class="anrhpub-product-color-enabled"
						<?php checked( $enabled ); ?>
					/>
					<span class="anrhpub-admin-color-card__swatch" style="background-color:<?php echo esc_attr( $hex ); ?>;" title="<?php echo esc_attr( $term->name ); ?>"></span>
					<span class="anrhpub-admin-color-card__name"><?php echo esc_html( $term->name ); ?></span>
				</label>
				<div class="anrhpub-admin-color-card__stock" data-admin-color-stock>
					<label class="screen-reader-text" for="anr_color_stock_<?php echo esc_attr( (string) $color_id ); ?>">
						<?php
						printf(
							/* translators: %s: color name */
							esc_html__( 'Quantité pour %s', 'anrhpub_theme' ),
							esc_html( $term->name )
						);
						?>
					</label>
					<input
						type="number"
						id="anr_color_stock_<?php echo esc_attr( (string) $color_id ); ?>"
						class="anrhpub-product-color-stock"
						name="anr_product_colors[<?php echo esc_attr( (string) $color_id ); ?>][stock]"
						value="<?php echo esc_attr( (string) $stock ); ?>"
						min="0"
						max="999999"
						step="1"
						<?php disabled( ! $enabled ); ?>
					/>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Sauvegarde metabox couleurs produit.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_save_product_color_stock_meta( $post_id ) {
	if ( ! isset( $_POST['anrhpub_product_color_stock_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_product_color_stock_nonce'] ) ), 'anrhpub_save_product_color_stock' ) ) {
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

	$posted = isset( $_POST['anr_product_colors'] ) && is_array( $_POST['anr_product_colors'] )
		? wp_unslash( $_POST['anr_product_colors'] )
		: array();

	$rows = array();

	foreach ( $posted as $color_id => $data ) {
		$color_id = (int) $color_id;

		if ( $color_id <= 0 || ! is_array( $data ) ) {
			continue;
		}

		if ( empty( $data['enabled'] ) ) {
			continue;
		}

		$rows[] = array(
			'color_id' => $color_id,
			'stock'    => isset( $data['stock'] ) ? absint( $data['stock'] ) : 0,
		);
	}

	anrhpub_save_product_color_stock_rows( $post_id, $rows );
}
add_action( 'save_post_anr_product', 'anrhpub_save_product_color_stock_meta' );

/**
 * Scripts admin fiche produit (couleurs).
 */
function anrhpub_product_color_admin_scripts( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'anr_product' !== $screen->post_type ) {
		return;
	}
	?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var select = document.getElementById('anrhpub-color-picker-select');
			var preview = document.querySelector('[data-color-select-preview]');
			var addBtn = document.getElementById('anrhpub-color-picker-add');

			function cardForColorId(colorId) {
				return document.querySelector('[data-admin-color-card][data-color-id="' + colorId + '"]');
			}

			function enableColorOnProduct(colorId) {
				var card = cardForColorId(colorId);
				if (!card) {
					return false;
				}
				var checkbox = card.querySelector('.anrhpub-product-color-enabled');
				if (!checkbox) {
					return false;
				}
				if (!checkbox.checked) {
					checkbox.checked = true;
					checkbox.dispatchEvent(new Event('change', { bubbles: true }));
				}
				card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
				return true;
			}

			function syncSelectPreview() {
				if (!select || !preview) {
					return;
				}
				var option = select.options[select.selectedIndex];
				var hex = option && option.getAttribute('data-hex') ? option.getAttribute('data-hex') : '#e8e8e8';
				preview.style.backgroundColor = hex;
				preview.hidden = !select.value;
			}

			if (select) {
				select.addEventListener('change', syncSelectPreview);
				syncSelectPreview();
			}

			if (addBtn && select) {
				addBtn.addEventListener('click', function () {
					var colorId = select.value;
					if (!colorId) {
						select.focus();
						return;
					}
					enableColorOnProduct(colorId);
				});
			}

			document.querySelectorAll('[data-admin-color-card]').forEach(function (card) {
				var checkbox = card.querySelector('.anrhpub-product-color-enabled');
				var stockInput = card.querySelector('.anrhpub-product-color-stock');

				if (!checkbox) {
					return;
				}

				function sync() {
					card.classList.toggle('is-active', checkbox.checked);
					if (stockInput) {
						stockInput.disabled = !checkbox.checked;
					}
					if (!checkbox.checked) {
						if (stockInput) {
							stockInput.value = '0';
						}
					} else if (stockInput && parseInt(stockInput.value, 10) < 1) {
						stockInput.value = '1';
						stockInput.focus();
					}
				}

				checkbox.addEventListener('change', sync);
				if (stockInput) {
					stockInput.addEventListener('click', function (e) {
						e.stopPropagation();
					});
				}
				sync();
			});
		});
	</script>
	<?php
}
add_action( 'admin_footer', 'anrhpub_product_color_admin_scripts' );

/**
 * Sélecteur visuel hex (écran Couleurs catalogue).
 */
function anrhpub_color_hex_picker_admin_scripts() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'edit-anr_color' !== $screen->id ) {
		return;
	}
	?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('.anrhpub-color-hex-field').forEach(function (wrap) {
				var picker = wrap.querySelector('.anrhpub-hex-picker');
				var text = wrap.querySelector('.anrhpub-hex-text');
				var preview = wrap.querySelector('[data-hex-preview]');
				if (!picker || !text) {
					return;
				}
				function normalizeHex(value) {
					var v = (value || '').trim();
					if (/^#[0-9A-Fa-f]{6}$/.test(v)) {
						return v.toUpperCase();
					}
					return '';
				}
				function apply(hex) {
					var clean = normalizeHex(hex) || '#888888';
					picker.value = clean;
					text.value = clean;
					if (preview) {
						preview.style.backgroundColor = clean;
					}
				}
				picker.addEventListener('input', function () {
					apply(picker.value);
				});
				text.addEventListener('input', function () {
					var clean = normalizeHex(text.value);
					if (clean) {
						apply(clean);
					}
				});
				text.addEventListener('blur', function () {
					apply(text.value);
				});
				apply(text.value || picker.value);
			});
		});
	</script>
	<?php
}
add_action( 'admin_footer', 'anrhpub_color_hex_picker_admin_scripts' );

/**
 * Styles admin couleurs (liste + fiche produit).
 */
function anrhpub_color_admin_styles_extended() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen ) {
		return;
	}

	$allowed = array( 'edit-anr_color', 'anr_product' );

	if ( ! in_array( $screen->id, $allowed, true ) && 'anr_product' !== $screen->post_type ) {
		return;
	}
	?>
	<style>
		.anrhpub-color-swatch-preview {
			display: inline-block;
			width: 1.5rem;
			height: 1.5rem;
			margin-right: 0.5rem;
			vertical-align: middle;
			border: 1px solid #ccc;
			border-radius: 3px;
		}
		.anrhpub-color-swatch-preview--lg {
			width: 2.5rem;
			height: 2.5rem;
			margin-right: 0;
		}
		.anrhpub-color-hex-controls {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 0.5rem;
		}
		.anrhpub-hex-picker {
			width: 3rem;
			height: 2.5rem;
			padding: 0;
			border: 1px solid #8c8f94;
			border-radius: 4px;
			cursor: pointer;
			background: transparent;
		}
		.anrhpub-hex-text {
			width: 7rem;
		}
		.anrhpub-admin-colors-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(7.5rem, 1fr));
			gap: 0.65rem;
			margin-top: 0.75rem;
		}
		.anrhpub-admin-color-card {
			border: 2px solid #dcdcde;
			border-radius: 8px;
			padding: 0.5rem;
			background: #fff;
			transition: border-color 0.15s ease, box-shadow 0.15s ease;
		}
		.anrhpub-admin-color-card.is-active {
			border-color: #2271b1;
			box-shadow: 0 0 0 1px #2271b1;
		}
		.anrhpub-admin-color-card__pick {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 0.35rem;
			margin: 0;
			cursor: pointer;
			text-align: center;
		}
		.anrhpub-admin-color-card__pick input[type="checkbox"] {
			position: absolute;
			opacity: 0;
			pointer-events: none;
		}
		.anrhpub-admin-color-card__swatch {
			display: block;
			width: 3rem;
			height: 3rem;
			border-radius: 50%;
			border: 2px solid rgba(0, 0, 0, 0.12);
			box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
		}
		.anrhpub-admin-color-card__name {
			font-size: 0.75rem;
			font-weight: 600;
			line-height: 1.25;
			color: #1d2327;
		}
		.anrhpub-admin-color-card__stock {
			margin-top: 0.45rem;
			text-align: center;
		}
		.anrhpub-admin-color-card__stock input {
			width: 100%;
			max-width: 5.5rem;
			text-align: center;
		}
		.anrhpub-admin-color-card:not(.is-active) .anrhpub-admin-color-card__stock {
			opacity: 0.45;
		}
		.anrhpub-color-picker-bar {
			margin: 0 0 1rem;
			padding: 0.75rem 0.85rem;
			background: #f6f7f7;
			border: 1px solid #dcdcde;
			border-radius: 6px;
		}
		.anrhpub-color-picker-bar__label {
			display: block;
			margin-bottom: 0.5rem;
			font-weight: 600;
		}
		.anrhpub-color-picker-bar__controls {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 0.5rem;
		}
		.anrhpub-color-picker-bar__preview {
			display: inline-block;
			width: 2.25rem;
			height: 2.25rem;
			border-radius: 50%;
			border: 2px solid #c3c4c7;
			flex-shrink: 0;
		}
		.anrhpub-color-picker-bar__preview[hidden] {
			display: none;
		}
		.anrhpub-color-picker-select {
			min-width: 14rem;
			max-width: 100%;
		}
	</style>
	<?php
}
add_action( 'admin_head', 'anrhpub_color_admin_styles_extended' );

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
