<?php
/** Taxonomie couleurs, hex, seed par defaut. @package anrhpub_theme */

defined( 'ABSPATH' ) || exit;

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
