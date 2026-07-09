<?php
/**
 * Recherche dynamique du catalogue (produits, références, catégories).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Paramètre GET de recherche catalogue.
 *
 * @return string
 */
function anrhpub_get_catalogue_search_term() {
	if ( ! isset( $_GET['catalogue_q'] ) ) {
		return '';
	}

	return trim( sanitize_text_field( wp_unslash( (string) $_GET['catalogue_q'] ) ) );
}

/**
 * Recherche catalogue active (2 caractères minimum).
 *
 * @return bool
 */
function anrhpub_catalogue_is_search_active() {
	$term = anrhpub_get_catalogue_search_term();

	return strlen( $term ) >= 2;
}

/**
 * Catégories dont le nom ou le slug correspond à la recherche.
 *
 * @param string $term Search term.
 * @return WP_Term[]
 */
function anrhpub_catalogue_search_matching_categories( $term ) {
	$term = trim( $term );
	if ( strlen( $term ) < 2 ) {
		return array();
	}

	$all = get_terms(
		array(
			'taxonomy'   => 'anr_category',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $all ) || empty( $all ) ) {
		return array();
	}

	$needle   = mb_strtolower( $term );
	$matched  = array();

	foreach ( $all as $cat ) {
		if (
			false !== mb_strpos( mb_strtolower( $cat->name ), $needle )
			|| false !== mb_strpos( mb_strtolower( $cat->slug ), $needle )
		) {
			$matched[] = $cat;
		}
	}

	return $matched;
}

/**
 * IDs produits correspondant à la recherche (titre, contenu, référence, catégories).
 *
 * @param string $term Search term.
 * @return int[]
 */
function anrhpub_catalogue_search_product_ids( $term ) {
	$term = trim( $term );
	if ( strlen( $term ) < 2 ) {
		return array();
	}

	$ids = array();

	$by_text = new WP_Query(
		array(
			'post_type'              => 'anr_product',
			'post_status'            => 'publish',
			's'                      => $term,
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	if ( ! empty( $by_text->posts ) ) {
		$ids = array_merge( $ids, $by_text->posts );
	}

	$by_ref = new WP_Query(
		array(
			'post_type'              => 'anr_product',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'     => 'anr_reference',
					'value'   => $term,
					'compare' => 'LIKE',
				),
			),
		)
	);
	if ( ! empty( $by_ref->posts ) ) {
		$ids = array_merge( $ids, $by_ref->posts );
	}

	foreach ( anrhpub_catalogue_search_matching_categories( $term ) as $cat ) {
		$by_cat = new WP_Query(
			array(
				'post_type'              => 'anr_product',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'tax_query'              => array(
					array(
						'taxonomy'         => 'anr_category',
						'field'            => 'term_id',
						'terms'            => (int) $cat->term_id,
						'include_children' => true,
					),
				),
			)
		);
		if ( ! empty( $by_cat->posts ) ) {
			$ids = array_merge( $ids, $by_cat->posts );
		}
	}

	return array_values( array_unique( array_map( 'intval', $ids ) ) );
}

/**
 * Applique la recherche sur la requête principale du catalogue.
 *
 * @param WP_Query $query Query.
 */
function anrhpub_catalogue_apply_search_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! is_post_type_archive( 'anr_product' ) && ! is_tax( 'anr_category' ) ) {
		return;
	}

	if ( ! anrhpub_catalogue_is_search_active() ) {
		return;
	}

	$term = anrhpub_get_catalogue_search_term();
	$ids  = anrhpub_catalogue_search_product_ids( $term );

	$query->set( 'post_type', 'anr_product' );
	$query->set( 's', '' );

	if ( empty( $ids ) ) {
		$query->set( 'post__in', array( 0 ) );
		return;
	}

	$query->set( 'post__in', $ids );
	$query->set( 'orderby', 'title' );
	$query->set( 'order', 'ASC' );

	if ( is_tax( 'anr_category' ) ) {
		$query->set( 'tax_query', array() );
	}
}
add_action( 'pre_get_posts', 'anrhpub_catalogue_apply_search_query', 20 );

/**
 * URL catalogue avec recherche éventuelle.
 *
 * @param string $search_term Optional search.
 * @param int    $paged       Page number.
 * @return string
 */
function anrhpub_catalogue_search_url( $search_term = '', $paged = 1 ) {
	$url = anrhpub_catalogue_url();

	if ( $search_term && strlen( trim( $search_term ) ) >= 2 ) {
		$url = add_query_arg( 'catalogue_q', rawurlencode( trim( $search_term ) ), $url );
	}

	$paged = max( 1, (int) $paged );
	if ( $paged > 1 ) {
		$url = trailingslashit( remove_query_arg( 'paged', $url ) );
		$url = $url . 'page/' . $paged . '/';
		if ( $search_term && strlen( trim( $search_term ) ) >= 2 ) {
			$url = add_query_arg( 'catalogue_q', rawurlencode( trim( $search_term ) ), $url );
		}
	}

	return $url;
}

/**
 * Titre de page pour une recherche catalogue.
 *
 * @param string $title Default title.
 * @return string
 */
function anrhpub_catalogue_search_document_title( $title ) {
	if (
		anrhpub_catalogue_is_search_active()
		&& ( is_post_type_archive( 'anr_product' ) || is_tax( 'anr_category' ) )
	) {
		return sprintf(
			/* translators: 1: search keywords, 2: site name */
			__( 'Recherche « %1$s » — %2$s', 'anrhpub_theme' ),
			anrhpub_get_catalogue_search_term(),
			get_bloginfo( 'name' )
		);
	}

	return $title;
}
add_filter( 'pre_get_document_title', 'anrhpub_catalogue_search_document_title', 20 );

/**
 * Résultats compacts pour l’autocomplétion (header).
 *
 * @param string $term Search term.
 * @param int    $limit Max items.
 * @return array{products: array, categories: array}
 */
function anrhpub_catalogue_search_suggest_payload( $term, $limit = 8 ) {
	$term = trim( $term );
	if ( strlen( $term ) < 2 ) {
		return array(
			'products'   => array(),
			'categories' => array(),
		);
	}

	$limit   = max( 1, min( 12, (int) $limit ) );
	$ids     = array_slice( anrhpub_catalogue_search_product_ids( $term ), 0, $limit );
	$products = array();

	foreach ( $ids as $post_id ) {
		$ref = (string) get_post_meta( $post_id, 'anr_reference', true );
		$products[] = array(
			'id'        => $post_id,
			'title'     => get_the_title( $post_id ),
			'reference' => $ref,
			'url'       => get_permalink( $post_id ),
			'image'     => function_exists( 'anrhpub_get_product_image_url' )
				? anrhpub_get_product_image_url( $post_id, 'thumbnail' )
				: ( get_the_post_thumbnail_url( $post_id, 'thumbnail' ) ?: '' ),
		);
	}

	$categories = array();
	foreach ( array_slice( anrhpub_catalogue_search_matching_categories( $term ), 0, 4 ) as $cat ) {
		$link = get_term_link( $cat );
		if ( is_wp_error( $link ) ) {
			continue;
		}
		$categories[] = array(
			'id'   => (int) $cat->term_id,
			'name' => $cat->name,
			'url'  => (string) $link,
		);
	}

	return array(
		'products'   => $products,
		'categories' => $categories,
	);
}

/**
 * AJAX — suggestions recherche produit.
 */
function anrhpub_ajax_catalogue_search_suggest() {
	check_ajax_referer( 'anrhpub_product_search', 'nonce' );

	$term = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['q'] ) ) : '';

	wp_send_json_success(
		array_merge(
			anrhpub_catalogue_search_suggest_payload( $term ),
			array(
				'catalogue_url' => anrhpub_catalogue_search_url( $term ),
				'count'         => count( anrhpub_catalogue_search_product_ids( $term ) ),
			)
		)
	);
}
add_action( 'wp_ajax_anrhpub_product_search_suggest', 'anrhpub_ajax_catalogue_search_suggest' );
add_action( 'wp_ajax_nopriv_anrhpub_product_search_suggest', 'anrhpub_ajax_catalogue_search_suggest' );

/**
 * Affiche la recherche produit dans le header.
 */
function anrhpub_render_header_product_search() {
	$current_q = '';
	if ( function_exists( 'anrhpub_get_catalogue_search_term' ) ) {
		$current_q = anrhpub_get_catalogue_search_term();
	}
	get_template_part(
		'template-parts/header',
		'product-search',
		array(
			'current_q' => $current_q,
		)
	);
}

/**
 * Script + config recherche globale (toutes les pages).
 */
function anrhpub_enqueue_product_search_assets() {
	wp_localize_script(
		'anrhpub-main',
		'anrhpubProductSearch',
		array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'anrhpub_product_search' ),
			'catalogueUrl' => anrhpub_catalogue_url(),
			'searchKey'    => 'catalogue_q',
			'minSearch'    => 2,
			'debounceMs'   => 320,
			'i18n'         => array(
				'label'       => __( 'Rechercher un produit', 'anrhpub_theme' ),
				'placeholder' => __( 'Produit, référence…', 'anrhpub_theme' ),
				'clear'       => __( 'Effacer', 'anrhpub_theme' ),
				'loading'     => __( 'Recherche…', 'anrhpub_theme' ),
				'empty'       => __( 'Aucun produit trouvé.', 'anrhpub_theme' ),
				'categories'  => __( 'Catégories', 'anrhpub_theme' ),
				'products'    => __( 'Produits', 'anrhpub_theme' ),
				'viewAll'     => __( 'Voir tous les résultats', 'anrhpub_theme' ),
			),
		)
	);

	if ( is_post_type_archive( 'anr_product' ) || is_tax( 'anr_category' ) ) {
		wp_localize_script(
			'anrhpub-main',
			'anrhpubCatalogue',
			array(
				'partialKey'   => 'catalogue_partial',
				'partialVal'   => '1',
				'searchKey'    => 'catalogue_q',
				'catalogueUrl' => anrhpub_catalogue_url(),
				'minSearch'    => 2,
				'i18n'         => array(
					'loading' => __( 'Chargement du catalogue…', 'anrhpub_theme' ),
					'error'   => __( 'Impossible de charger cette catégorie. Rechargement de la page…', 'anrhpub_theme' ),
				),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_product_search_assets', 25 );
