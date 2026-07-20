<?php
/**
 * Comparateur, listes partagées, export Excel panier.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_COMPARE_META', 'anrhpub_compare_list' );
define( 'ANRHPUB_SHARED_LISTS_META', 'anrhpub_shared_lists' );
define( 'ANRHPUB_COMPARE_PAGE_VERSION', 1 );

/**
 * URL page comparateur.
 *
 * @return string
 */
function anrhpub_compare_url() {
	$page = get_page_by_path( 'comparateur' );

	if ( $page ) {
		return get_permalink( $page );
	}

	return home_url( '/comparateur/' );
}

/**
 * Crée la page comparateur si absente.
 */
function anrhpub_ensure_compare_page() {
	if ( (int) get_option( 'anrhpub_compare_page_version', 0 ) >= ANRHPUB_COMPARE_PAGE_VERSION ) {
		return;
	}

	$existing = get_page_by_path( 'comparateur' );

	if ( ! $existing ) {
		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => __( 'Comparateur produits', 'anrhpub_theme' ),
				'post_name'    => 'comparateur',
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);
	}

	update_option( 'anrhpub_compare_page_version', ANRHPUB_COMPARE_PAGE_VERSION );
	flush_rewrite_rules( false );
}
add_action( 'init', 'anrhpub_ensure_compare_page', 14 );

/**
 * Données produits pour le comparateur (côté serveur).
 *
 * @param int[] $ids Product post IDs (max 4).
 * @return array<int, array<string, mixed>>
 */
function anrhpub_get_compare_products_payload( array $ids ) {
	$ids  = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
	$ids  = array_slice( $ids, 0, 4 );
	$rows = array();

	foreach ( $ids as $id ) {
		if ( $id <= 0 || 'anr_product' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
			continue;
		}

		$ref   = (string) get_post_meta( $id, 'anr_reference', true );
		$cats  = get_the_terms( $id, 'anr_category' );
		$cat_names = array();
		if ( $cats && ! is_wp_error( $cats ) ) {
			foreach ( $cats as $term ) {
				$cat_names[] = $term->name;
			}
		}
		$materials = get_the_terms( $id, 'anr_material' );
		$mat_names   = array();
		if ( $materials && ! is_wp_error( $materials ) ) {
			foreach ( $materials as $term ) {
				$mat_names[] = $term->name;
			}
		}

		$row = array(
			'id'         => $id,
			'title'      => get_the_title( $id ),
			'link'       => get_permalink( $id ),
			'reference'  => $ref,
			'image'      => function_exists( 'anrhpub_get_product_image_url' ) ? anrhpub_get_product_image_url( $id, 'medium_large' ) : ( get_the_post_thumbnail_url( $id, 'medium_large' ) ?: '' ),
			'excerpt'        => wp_trim_words( wp_strip_all_tags( get_the_excerpt( $id ) ), 24, '…' ),
			'category'       => implode( ', ', $cat_names ),
			'material'       => implode( ', ', $mat_names ),
			'min_qty'        => function_exists( 'anrhpub_get_product_min_qty' ) ? anrhpub_get_product_min_qty( $id ) : 1,
			'requires_color' => function_exists( 'anrhpub_product_has_colors' ) && anrhpub_product_has_colors( $id ),
			'stock'          => function_exists( 'anrhpub_get_product_stock_label' ) ? anrhpub_get_product_stock_label( $id ) : '',
		);

		if ( function_exists( 'anrhpub_get_unit_price_ht' ) && function_exists( 'anrhpub_can_view_prices' ) && anrhpub_can_view_prices() ) {
			$ht  = anrhpub_get_unit_price_ht( $id, 1 );
			$ttc = function_exists( 'anrhpub_ht_to_ttc' ) ? anrhpub_ht_to_ttc( $ht ) : null;
			$row['price_ht']  = null !== $ht ? anrhpub_format_price( $ht ) : '';
			$row['price_ttc'] = null !== $ttc ? anrhpub_format_price( $ttc ) : '';
		}

		$rows[] = $row;
	}

	return $rows;
}

/**
 * AJAX — charge les produits du comparateur.
 */
function anrhpub_ajax_compare_products() {
	check_ajax_referer( 'anrhpub_compare', 'nonce' );

	$raw = isset( $_GET['ids'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['ids'] ) ) : '';
	$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );

	wp_send_json_success(
		array(
			'products' => anrhpub_get_compare_products_payload( $ids ),
		)
	);
}
add_action( 'wp_ajax_anrhpub_compare_products', 'anrhpub_ajax_compare_products' );
add_action( 'wp_ajax_nopriv_anrhpub_compare_products', 'anrhpub_ajax_compare_products' );

/**
 * Export Excel (CSV) du panier.
 */
function anrhpub_handle_cart_export() {
	if ( empty( $_GET['anrhpub_download'] ) || 'cart_xlsx' !== $_GET['anrhpub_download'] ) {
		return;
	}

	if ( ! wp_verify_nonce( isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '', 'anrhpub_cart_export' ) ) {
		wp_die( esc_html__( 'Lien invalide.', 'anrhpub_theme' ), 403 );
	}

	if ( ! function_exists( 'anrhpub_is_client_logged_in' ) || ! anrhpub_is_client_logged_in() ) {
		wp_die( esc_html__( 'Connexion requise pour exporter le panier.', 'anrhpub_theme' ), 403 );
	}

	if ( ! function_exists( 'anrhpub_can_view_prices' ) || ! anrhpub_can_view_prices() ) {
		wp_die( esc_html__( 'Export non autorisé pour votre compte.', 'anrhpub_theme' ), 403 );
	}

	$items = anrhpub_get_user_quote_cart_raw();

	$rows   = array( array( 'Référence', 'Produit', 'Quantité', 'Couleur', 'Prix HT' ) );
	$lines  = function_exists( 'anrhpub_enrich_quote_cart_items' ) ? anrhpub_enrich_quote_cart_items( $items ) : array();

	foreach ( $lines as $line ) {
		$pid = (int) ( $line['product_id'] ?? 0 );
		$qty = (int) ( $line['qty'] ?? 1 );
		$ht  = function_exists( 'anrhpub_get_unit_price_ht' ) ? anrhpub_get_unit_price_ht( $pid, $qty ) : null;

		$rows[] = array(
			$line['ref'] ?? '',
			$line['title'] ?? '',
			$qty,
			$line['color_name'] ?? '',
			null !== $ht ? number_format( $ht, 2, '.', '' ) : '',
		);
	}

	header( 'Content-Type: application/vnd.ms-excel; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="panier-anrh-' . gmdate( 'Y-m-d' ) . '.csv"' );
	echo "\xEF\xBB\xBF";
	$out = fopen( 'php://output', 'w' );
	foreach ( $rows as $row ) {
		fputcsv( $out, $row, ';' );
	}
	fclose( $out );
	exit;
}
add_action( 'template_redirect', 'anrhpub_handle_cart_export', 1 );

/**
 * URL export panier.
 *
 * @param array $items Optional cart override.
 * @return string
 */
function anrhpub_get_cart_export_url( $items = null ) {
	if ( ! function_exists( 'anrhpub_is_client_logged_in' ) || ! anrhpub_is_client_logged_in() ) {
		return '';
	}

	if ( ! function_exists( 'anrhpub_can_view_prices' ) || ! anrhpub_can_view_prices() ) {
		return '';
	}

	return add_query_arg(
		array(
			'anrhpub_download' => 'cart_xlsx',
			'nonce'            => wp_create_nonce( 'anrhpub_cart_export' ),
		),
		home_url( '/' )
	);
}

/**
 * AJAX — sauvegarder liste partagée.
 */
function anrhpub_ajax_save_shared_list() {
	check_ajax_referer( 'anrhpub_shared_list', 'nonce' );

	if ( ! anrhpub_is_client_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Connexion requise.', 'anrhpub_theme' ) ), 401 );
	}

	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$raw   = isset( $_POST['cart'] ) ? wp_unslash( $_POST['cart'] ) : '[]';
	$items = json_decode( is_string( $raw ) ? $raw : '[]', true );

	if ( ! $name ) {
		wp_send_json_error( array( 'message' => __( 'Nom de liste requis.', 'anrhpub_theme' ) ), 400 );
	}

	$user_id = anrhpub_get_client_user_id();
	$lists   = get_user_meta( $user_id, ANRHPUB_SHARED_LISTS_META, true );

	if ( ! is_array( $lists ) ) {
		$lists = array();
	}

	$lists[] = array(
		'id'         => 'list_' . wp_generate_password( 8, false ),
		'name'       => $name,
		'items'      => function_exists( 'anrhpub_sanitize_quote_cart_items' ) ? anrhpub_sanitize_quote_cart_items( $items ) : array(),
		'created_at' => current_time( 'mysql' ),
	);

	update_user_meta( $user_id, ANRHPUB_SHARED_LISTS_META, $lists );

	wp_send_json_success( array( 'message' => __( 'Liste enregistrée.', 'anrhpub_theme' ), 'lists' => $lists ) );
}
add_action( 'wp_ajax_anrhpub_save_shared_list', 'anrhpub_ajax_save_shared_list' );

/**
 * Listes partagées client.
 *
 * @param int $user_id User ID.
 * @return array
 */
function anrhpub_get_client_shared_lists( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();
	$lists   = get_user_meta( $user_id, ANRHPUB_SHARED_LISTS_META, true );

	return is_array( $lists ) ? $lists : array();
}

/**
 * Scripts outils B2B.
 */
function anrhpub_enqueue_b2b_tools() {
	$payload = array(
		'compareKey'      => 'anrhpub_compare',
		'compareApiUrl'   => add_query_arg( 'action', 'anrhpub_compare_products', admin_url( 'admin-ajax.php' ) ),
		'compareNonce'    => wp_create_nonce( 'anrhpub_compare' ),
		'restProductsUrl' => rest_url( 'wp/v2/anr_product' ),
		'quoteDraftNonce' => wp_create_nonce( 'anrhpub_quote_draft' ),
		'sharedListNonce' => wp_create_nonce( 'anrhpub_shared_list' ),
		'cartExportUrl'   => anrhpub_get_cart_export_url(),
		'compareUrl'      => anrhpub_compare_url(),
		'catalogueUrl'    => function_exists( 'anrhpub_catalogue_url' ) ? anrhpub_catalogue_url() : home_url( '/' ),
		'compareMax'      => 4,
		'i18n'            => array(
			'compareAdded'      => __( 'Ajouté au comparateur.', 'anrhpub_theme' ),
			'compareRemoved'    => __( 'Retiré du comparateur.', 'anrhpub_theme' ),
			'compareMax'        => __( 'Maximum 4 produits dans le comparateur.', 'anrhpub_theme' ),
			'compareEmpty'      => __( 'Aucun produit sélectionné pour le moment.', 'anrhpub_theme' ),
			'compareEmptyHint'  => __( 'Ouvrez une fiche produit et cliquez sur « Comparer », ou utilisez le bouton sur les vignettes du catalogue.', 'anrhpub_theme' ),
			'compareError'      => __( 'Impossible de charger les produits. Rechargez la page.', 'anrhpub_theme' ),
			'compareLoading'    => __( 'Chargement du comparateur…', 'anrhpub_theme' ),
			'compareCount'      => __( '%1$d / %2$d produits', 'anrhpub_theme' ),
			'compareClear'      => __( 'Tout effacer', 'anrhpub_theme' ),
			'compareCatalogue'  => __( 'Parcourir le catalogue', 'anrhpub_theme' ),
			'compareRemove'     => __( 'Retirer', 'anrhpub_theme' ),
			'compareView'       => __( 'Voir la fiche', 'anrhpub_theme' ),
			'compareAddCart'    => __( 'Ajouter au panier', 'anrhpub_theme' ),
			'compareChooseColor' => __( 'Choisir une couleur', 'anrhpub_theme' ),
			'compareSpecs'      => __( 'Caractéristiques comparées', 'anrhpub_theme' ),
			'rowReference'      => __( 'Référence', 'anrhpub_theme' ),
			'rowCategory'       => __( 'Catégorie', 'anrhpub_theme' ),
			'rowMaterial'       => __( 'Matière', 'anrhpub_theme' ),
			'rowStock'          => __( 'Disponibilité', 'anrhpub_theme' ),
			'rowPriceHt'        => __( 'Prix HT', 'anrhpub_theme' ),
			'rowPriceTtc'       => __( 'Prix TTC', 'anrhpub_theme' ),
			'rowExcerpt'        => __( 'Description', 'anrhpub_theme' ),
			'draftSaved'        => __( 'Brouillon enregistré.', 'anrhpub_theme' ),
		),
	);

	wp_localize_script( 'anrhpub-main', 'anrhpubB2b', $payload );
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_b2b_tools', 40 );
