<?php
/**
 * Migration PrestaShop -> WordPress ANRPUB (v2 — migration complète).
 *
 * @package anrhpub_theme
 *
 * CLI:
 *   php wp-content/themes/anrhpub_theme/tools/migrate-prestashop.php --run=1 --step=diagnose
 *   php wp-content/themes/anrhpub_theme/tools/migrate-prestashop.php --run=1 --step=all --dry-run=1
 *   php wp-content/themes/anrhpub_theme/tools/migrate-prestashop.php --run=1 --step=all
 *
 * HTTP (staging seulement):
 *   .../migrate-prestashop.php?run=1&token=VOTRE_TOKEN&step=diagnose
 */

declare( strict_types=1 );

// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared

const ANRH_MIG_VERSION     = '2.0.0';
const ANRH_MIG_MAP_OPTION  = 'anrhpub_prestashop_migration_map';
const ANRH_MIG_REDIR_OPTION = 'anrhpub_prestashop_url_redirects';

const SOURCE_DB_HOST     = '127.0.0.1';
const SOURCE_DB_PORT     = 3306;
const SOURCE_DB_NAME     = 'prestashop_db';
const SOURCE_DB_USER     = 'root';
const SOURCE_DB_PASS     = '';
const SOURCE_DB_PREFIX   = 'ps_';
const SOURCE_SHOP_ID     = 1;
const SOURCE_LANG_ID     = 1;
const SOURCE_BASE_URL    = 'https://anr-pub.fr';
const HTTP_TOKEN         = 'CHANGE_ME';

/** @var array<int, string> Presta order_state id => WP status */
const ORDER_STATUS_MAP = array(
	1  => 'pending',
	2  => 'processing',
	3  => 'processing',
	4  => 'shipped',
	5  => 'delivered',
	6  => 'cancelled',
	7  => 'cancelled',
	8  => 'cancelled',
);

if ( ! defined( 'ABSPATH' ) ) {
	$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
	if ( ! file_exists( $wp_load ) ) {
		exit( "wp-load.php introuvable.\n" );
	}
	require_once $wp_load;
}

if ( ! function_exists( 'wp_insert_post' ) ) {
	exit( "WordPress non charge.\n" );
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// -------------------------------------------------------------------------
// CLI / HTTP args
// -------------------------------------------------------------------------

function anrh_mig_arg( string $key, $default = null ) {
	static $cli = null;
	if ( null === $cli ) {
		$cli = array();
		if ( 'cli' === PHP_SAPI && isset( $_SERVER['argv'] ) ) {
			foreach ( array_slice( $_SERVER['argv'], 1 ) as $arg ) {
				if ( 0 !== strpos( $arg, '--' ) ) {
					continue;
				}
				$parts = explode( '=', substr( $arg, 2 ), 2 );
				$cli[ $parts[0] ] = $parts[1] ?? '1';
			}
		}
	}
	if ( array_key_exists( $key, $cli ) ) {
		return $cli[ $key ];
	}
	return isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : $default;
}

function anrh_mig_log( string $message ): void {
	$line = '[' . gmdate( 'Y-m-d H:i:s' ) . ' ] ' . $message;
	if ( 'cli' === PHP_SAPI ) {
		echo $line . PHP_EOL;
	} else {
		echo esc_html( $line ) . "<br>\n";
	}
}

function anrh_mig_is_dry_run(): bool {
	return in_array( (string) anrh_mig_arg( 'dry-run', '0' ), array( '1', 'true', 'yes' ), true )
		|| in_array( (string) anrh_mig_arg( 'dry_run', '0' ), array( '1', 'true', 'yes' ), true );
}

function anrh_mig_prefix(): string {
	return SOURCE_DB_PREFIX;
}

function anrh_mig_pdo(): PDO {
	static $pdo = null;
	if ( $pdo instanceof PDO ) {
		return $pdo;
	}
	$dsn = sprintf(
		'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
		SOURCE_DB_HOST,
		SOURCE_DB_PORT,
		SOURCE_DB_NAME
	);
	$pdo = new PDO(
		$dsn,
		SOURCE_DB_USER,
		SOURCE_DB_PASS,
		array(
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		)
	);
	return $pdo;
}

function anrh_mig_table_exists( string $table ): bool {
	$pdo  = anrh_mig_pdo();
	$stmt = $pdo->prepare( 'SHOW TABLES LIKE :t' );
	$stmt->execute( array( ':t' => $table ) );
	return (bool) $stmt->fetchColumn();
}

function anrh_mig_count( string $table ): int {
	if ( ! anrh_mig_table_exists( $table ) ) {
		return 0;
	}
	return (int) anrh_mig_pdo()->query( 'SELECT COUNT(*) FROM `' . $table . '`' )->fetchColumn();
}

function anrh_mig_column_exists( string $table, string $column ): bool {
	$stmt = anrh_mig_pdo()->prepare( 'SHOW COLUMNS FROM `' . $table . '` LIKE :col' );
	$stmt->execute( array( ':col' => $column ) );
	return (bool) $stmt->fetchColumn();
}

/**
 * @return array{category: array<int, int>, color: array<int, int>}
 */
function anrh_mig_load_taxonomy_maps(): array {
	$full_map  = anrh_mig_get_map();
	$cat_map   = array();
	$color_map = array();
	if ( ! empty( $full_map['category'] ) ) {
		foreach ( $full_map['category'] as $ps => $wp ) {
			$cat_map[ (int) $ps ] = (int) $wp;
		}
	}
	if ( ! empty( $full_map['color'] ) ) {
		foreach ( $full_map['color'] as $ps => $wp ) {
			$color_map[ (int) $ps ] = (int) $wp;
		}
	}
	return array(
		'category' => $cat_map,
		'color'    => $color_map,
	);
}

function anrh_mig_save_product_gallery( int $post_id, array $attachment_ids ): void {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', $attachment_ids ) ) ) );
	if ( empty( $ids ) ) {
		return;
	}
	$thumb   = (int) $ids[0];
	$gallery = array_slice( array_values( array_diff( $ids, array( $thumb ) ) ), 0, 20 );
	set_post_thumbnail( $post_id, $thumb );
	$meta_key = defined( 'ANRHPUB_PRODUCT_GALLERY_META' ) ? ANRHPUB_PRODUCT_GALLERY_META : 'anr_product_gallery';
	update_post_meta( $post_id, $meta_key, wp_json_encode( $gallery ) );
}

// -------------------------------------------------------------------------
// Mapping Presta ID -> WP ID (reprise / idempotence)
// -------------------------------------------------------------------------

function anrh_mig_get_map(): array {
	$map = get_option( ANRH_MIG_MAP_OPTION, array() );
	return is_array( $map ) ? $map : array();
}

function anrh_mig_save_map( array $map ): void {
	update_option( ANRH_MIG_MAP_OPTION, $map, false );
}

function anrh_mig_map_set( string $type, int $ps_id, int $wp_id ): void {
	$map = anrh_mig_get_map();
	if ( ! isset( $map[ $type ] ) ) {
		$map[ $type ] = array();
	}
	$map[ $type ][ (string) $ps_id ] = $wp_id;
	anrh_mig_save_map( $map );
}

function anrh_mig_map_get( string $type, int $ps_id ): int {
	$map = anrh_mig_get_map();
	return isset( $map[ $type ][ (string) $ps_id ] ) ? (int) $map[ $type ][ (string) $ps_id ] : 0;
}

function anrh_mig_add_redirect( string $old_path, string $new_url ): void {
	$old_path = trim( $old_path, '/' );
	if ( '' === $old_path ) {
		return;
	}
	$redirs = get_option( ANRH_MIG_REDIR_OPTION, array() );
	if ( ! is_array( $redirs ) ) {
		$redirs = array();
	}
	$redirs[ $old_path ] = $new_url;
	update_option( ANRH_MIG_REDIR_OPTION, $redirs, false );
}

// -------------------------------------------------------------------------
// Diagnose
// -------------------------------------------------------------------------

function anrh_mig_diagnose(): void {
	$p = anrh_mig_prefix();
	$tables = array(
		'product'              => 'Produits',
		'category'             => 'Categories',
		'customer'             => 'Clients',
		'address'              => 'Adresses',
		'orders'               => 'Commandes',
		'order_detail'         => 'Lignes commande',
		'order_slip'           => 'Avoirs',
		'newsletter'           => 'Newsletter',
		'cms'                  => 'Pages CMS',
		'image'                => 'Images produit',
		'product_attribute'      => 'Declinaisons',
		'stock_available'      => 'Stock',
		'cart'                 => 'Paniers (devis potentiels)',
	);

	anrh_mig_log( '=== DIAGNOSTIC PrestaShop (prefix: ' . $p . ') ===' );
	foreach ( $tables as $suffix => $label ) {
		$table = $p . $suffix;
		$exists = anrh_mig_table_exists( $table );
		$count  = $exists ? anrh_mig_count( $table ) : 0;
		anrh_mig_log( sprintf( '%s [%s]: %s', $label, $suffix, $exists ? (string) $count : 'table absente' ) );
	}

	if ( anrh_mig_table_exists( $p . 'order_state' ) ) {
		$rows = anrh_mig_pdo()->query(
			'SELECT os.id_order_state, osl.name
			 FROM `' . $p . 'order_state` os
			 INNER JOIN `' . $p . 'order_state_lang` osl ON osl.id_order_state=os.id_order_state
			 WHERE osl.id_lang=' . (int) SOURCE_LANG_ID . '
			 ORDER BY os.id_order_state'
		)->fetchAll();
		anrh_mig_log( '--- Statuts commande Presta (ajuster ORDER_STATUS_MAP) ---' );
		foreach ( $rows as $row ) {
			$id = (int) $row['id_order_state'];
			$mapped = ORDER_STATUS_MAP[ $id ] ?? '?';
			anrh_mig_log( sprintf( '  %d => %s (%s)', $id, $mapped, $row['name'] ) );
		}
	}

	$map = anrh_mig_get_map();
	anrh_mig_log( 'Map WP deja importe: products=' . count( $map['product'] ?? array() ) . ', customers=' . count( $map['customer'] ?? array() ) . ', orders=' . count( $map['order'] ?? array() ) );
}

// -------------------------------------------------------------------------
// Images PrestaShop
// -------------------------------------------------------------------------

function anrh_mig_presta_image_url( int $id_image ): string {
	$digits = str_split( (string) $id_image );
	return rtrim( SOURCE_BASE_URL, '/' ) . '/img/p/' . implode( '/', $digits ) . '/' . $id_image . '.jpg';
}

function anrh_mig_sideload_image( string $url, int $post_id, string $title, bool $dry_run ): int {
	if ( $dry_run || '' === $url ) {
		return 0;
	}
	$att_id = media_sideload_image( $url, $post_id, $title, 'id' );
	if ( is_wp_error( $att_id ) ) {
		anrh_mig_log( 'Image KO: ' . $url . ' — ' . $att_id->get_error_message() );
		return 0;
	}
	return (int) $att_id;
}

// -------------------------------------------------------------------------
// Categories
// -------------------------------------------------------------------------

function anrh_mig_import_categories( bool $dry_run ): array {
	$p   = anrh_mig_prefix();
	$sql = 'SELECT c.id_category, cl.name, cl.link_rewrite
		FROM `' . $p . 'category` c
		INNER JOIN `' . $p . 'category_lang` cl ON cl.id_category=c.id_category AND cl.id_lang=:lang
		WHERE c.id_category NOT IN (1, 2)
		ORDER BY c.level_depth ASC, c.id_category ASC';
	$stmt = anrh_mig_pdo()->prepare( $sql );
	$stmt->execute( array( ':lang' => SOURCE_LANG_ID ) );
	$rows = $stmt->fetchAll();

	$map = array();
	foreach ( $rows as $row ) {
		$ps_id = (int) $row['id_category'];
		$name  = (string) $row['name'];
		$slug  = ! empty( $row['link_rewrite'] ) ? sanitize_title( $row['link_rewrite'] ) : sanitize_title( $name );

		$wp_id = anrh_mig_map_get( 'category', $ps_id );
		if ( ! $wp_id ) {
			$term = get_term_by( 'slug', $slug, 'anr_category' );
			if ( $term && ! is_wp_error( $term ) ) {
				$wp_id = (int) $term->term_id;
			}
		}

		if ( ! $dry_run && ! $wp_id ) {
			$created = wp_insert_term( $name, 'anr_category', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $created ) ) {
				$wp_id = (int) $created['term_id'];
			}
		}

		if ( $wp_id ) {
			anrh_mig_map_set( 'category', $ps_id, $wp_id );
			$map[ $ps_id ] = $wp_id;
			anrh_mig_add_redirect( 'categorie-produit/' . $slug, get_term_link( $wp_id, 'anr_category' ) );
		}
	}

	anrh_mig_log( 'Categories: ' . count( $rows ) . ' source.' );
	return $map;
}

// -------------------------------------------------------------------------
// Colors (attributes)
// -------------------------------------------------------------------------

function anrh_mig_import_colors( bool $dry_run ): array {
	$p = anrh_mig_prefix();
	if ( ! anrh_mig_table_exists( $p . 'attribute' ) ) {
		anrh_mig_log( 'Couleurs: tables attributs absentes, etape ignoree.' );
		return array();
	}

	$sql = 'SELECT a.id_attribute, al.name
		FROM `' . $p . 'attribute` a
		INNER JOIN `' . $p . 'attribute_lang` al ON al.id_attribute=a.id_attribute AND al.id_lang=:lang';
	if ( anrh_mig_table_exists( $p . 'attribute_group_lang' ) ) {
		$sql .= ' INNER JOIN `' . $p . 'attribute_group_lang` agl
			ON agl.id_attribute_group=a.id_attribute_group AND agl.id_lang=:lang
			AND (agl.name LIKE :color_fr OR agl.name LIKE :color_en)';
	}
	$sql .= ' ORDER BY a.id_attribute ASC';
	$stmt = anrh_mig_pdo()->prepare( $sql );
	$params = array( ':lang' => SOURCE_LANG_ID );
	if ( anrh_mig_table_exists( $p . 'attribute_group_lang' ) ) {
		$params[':color_fr'] = '%ouleur%';
		$params[':color_en'] = '%Color%';
	}
	$stmt->execute( $params );
	$rows = $stmt->fetchAll();
	if ( empty( $rows ) ) {
		$stmt = anrh_mig_pdo()->prepare(
			'SELECT a.id_attribute, al.name
			 FROM `' . $p . 'attribute` a
			 INNER JOIN `' . $p . 'attribute_lang` al ON al.id_attribute=a.id_attribute AND al.id_lang=:lang
			 ORDER BY a.id_attribute ASC'
		);
		$stmt->execute( array( ':lang' => SOURCE_LANG_ID ) );
		$rows = $stmt->fetchAll();
		anrh_mig_log( 'Couleurs: groupe "Couleur" introuvable, import de tous les attributs.' );
	}

	$map = array();
	foreach ( $rows as $row ) {
		$ps_id = (int) $row['id_attribute'];
		$name  = trim( (string) $row['name'] );
		if ( '' === $name ) {
			continue;
		}
		$slug  = sanitize_title( $name );
		$wp_id = anrh_mig_map_get( 'color', $ps_id );
		if ( ! $wp_id ) {
			$term = get_term_by( 'slug', $slug, 'anr_color' );
			if ( $term && ! is_wp_error( $term ) ) {
				$wp_id = (int) $term->term_id;
			}
		}
		if ( ! $dry_run && ! $wp_id ) {
			$created = wp_insert_term( $name, 'anr_color', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $created ) ) {
				$wp_id = (int) $created['term_id'];
				if ( function_exists( 'anrhpub_sanitize_color_hex' ) ) {
					update_term_meta( $wp_id, 'anr_color_hex', anrhpub_sanitize_color_hex( '#' . substr( md5( $name ), 0, 6 ) ) );
				}
			}
		}
		if ( $wp_id ) {
			anrh_mig_map_set( 'color', $ps_id, $wp_id );
			$map[ $ps_id ] = $wp_id;
		}
	}

	anrh_mig_log( 'Couleurs (attributs): ' . count( $map ) . ' mappees.' );
	return $map;
}

// -------------------------------------------------------------------------
// Products
// -------------------------------------------------------------------------

function anrh_mig_find_product_by_reference( string $ref ): int {
	$posts = get_posts(
		array(
			'post_type'      => 'anr_product',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => 'anr_reference',
			'meta_value'     => $ref,
		)
	);
	return ! empty( $posts ) ? (int) $posts[0] : 0;
}

function anrh_mig_import_product_features( int $post_id, int $ps_product_id, bool $dry_run ): void {
	$p = anrh_mig_prefix();
	if ( ! anrh_mig_table_exists( $p . 'feature_product' ) ) {
		return;
	}
	$sql = 'SELECT fl.name AS feature_name, fvl.value AS feature_value
		FROM `' . $p . 'feature_product` fp
		INNER JOIN `' . $p . 'feature_value` fv ON fv.id_feature_value=fp.id_feature_value
		INNER JOIN `' . $p . 'feature_value_lang` fvl ON fvl.id_feature_value=fv.id_feature_value AND fvl.id_lang=:lang
		INNER JOIN `' . $p . 'feature` f ON f.id_feature=fv.id_feature
		INNER JOIN `' . $p . 'feature_lang` fl ON fl.id_feature=f.id_feature AND fl.id_lang=:lang
		WHERE fp.id_product=:pid
		ORDER BY fl.name ASC';
	$stmt = anrh_mig_pdo()->prepare( $sql );
	$stmt->execute( array( ':lang' => SOURCE_LANG_ID, ':pid' => $ps_product_id ) );
	$rows = $stmt->fetchAll();
	if ( empty( $rows ) ) {
		return;
	}
	$html = '<ul class="product-features-migrated">';
	foreach ( $rows as $row ) {
		$html .= '<li><strong>' . esc_html( $row['feature_name'] ) . ':</strong> ' . esc_html( $row['feature_value'] ) . '</li>';
	}
	$html .= '</ul>';
	if ( ! $dry_run ) {
		update_post_meta( $post_id, 'anr_details', $html );
	}
}

function anrh_mig_import_product_stock_and_colors( int $post_id, int $ps_product_id, array $color_map, bool $dry_run ): void {
	$p = anrh_mig_prefix();
	if ( ! anrh_mig_table_exists( $p . 'stock_available' ) ) {
		return;
	}

	$sql = 'SELECT sa.id_product_attribute, sa.quantity, pa.id_product_attribute
		FROM `' . $p . 'stock_available` sa
		LEFT JOIN `' . $p . 'product_attribute` pa ON pa.id_product_attribute=sa.id_product_attribute
		WHERE sa.id_product=:pid';
	$stmt = anrh_mig_pdo()->prepare( $sql );
	$stmt->execute( array( ':pid' => $ps_product_id ) );
	$stocks = $stmt->fetchAll();

	$total_qty = 0;
	$color_rows = array();

	foreach ( $stocks as $row ) {
		$qty = max( 0, (int) $row['quantity'] );
		$total_qty += $qty;
		$id_pa = (int) $row['id_product_attribute'];
		if ( $id_pa <= 0 ) {
			continue;
		}
		if ( ! anrh_mig_table_exists( $p . 'product_attribute_combination' ) ) {
			continue;
		}
		$attr_sql = 'SELECT pac.id_attribute
			FROM `' . $p . 'product_attribute_combination` pac
			WHERE pac.id_product_attribute=:ipa';
		$astmt = anrh_mig_pdo()->prepare( $attr_sql );
		$astmt->execute( array( ':ipa' => $id_pa ) );
		$attrs = $astmt->fetchAll();
		foreach ( $attrs as $a ) {
			$attr_id = (int) $a['id_attribute'];
			if ( isset( $color_map[ $attr_id ] ) ) {
				$color_rows[] = array(
					'color_id' => (int) $color_map[ $attr_id ],
					'stock'    => $qty,
				);
			}
		}
	}

	if ( ! $dry_run ) {
		$status = $total_qty > 0 ? 'instock' : 'outofstock';
		update_post_meta( $post_id, 'anr_stock_status', $status );
		update_post_meta( $post_id, 'anr_stock_qty', $total_qty );
		if ( function_exists( 'anrhpub_save_product_color_stock_rows' ) && ! empty( $color_rows ) ) {
			anrhpub_save_product_color_stock_rows( $post_id, $color_rows );
		}
	}
}

function anrh_mig_import_products( bool $dry_run, array $cat_map, array $color_map ): void {
	$p   = anrh_mig_prefix();
	$sql = 'SELECT p.id_product, p.reference, p.price, p.active, p.on_sale, p.available_for_order,
		pl.name, pl.description_short, pl.description, pl.link_rewrite, p.id_category_default
		FROM `' . $p . 'product` p
		INNER JOIN `' . $p . 'product_lang` pl ON pl.id_product=p.id_product AND pl.id_lang=:lang AND pl.id_shop=:shop
		ORDER BY p.id_product ASC';
	$stmt = anrh_mig_pdo()->prepare( $sql );
	$stmt->execute( array( ':lang' => SOURCE_LANG_ID, ':shop' => SOURCE_SHOP_ID ) );
	$products = $stmt->fetchAll();

	$img_all = anrh_mig_pdo()->prepare(
		'SELECT id_image, cover FROM `' . $p . 'image` WHERE id_product=:pid ORDER BY cover DESC, position ASC, id_image ASC'
	);

	$cat_link = array();
	if ( anrh_mig_table_exists( $p . 'category_product' ) ) {
		$cstmt = anrh_mig_pdo()->prepare( 'SELECT id_category FROM `' . $p . 'category_product` WHERE id_product=:pid' );
	}

	$count = 0;
	foreach ( $products as $row ) {
		$ps_id = (int) $row['id_product'];
		$ref   = trim( (string) $row['reference'] );
		if ( '' === $ref ) {
			$ref = 'PS-' . $ps_id;
		}

		$wp_id = anrh_mig_map_get( 'product', $ps_id );
		if ( ! $wp_id ) {
			$wp_id = anrh_mig_find_product_by_reference( $ref );
		}

		$post_data = array(
			'post_type'    => 'anr_product',
			'post_title'   => (string) $row['name'],
			'post_name'    => ! empty( $row['link_rewrite'] ) ? sanitize_title( $row['link_rewrite'] ) : '',
			'post_excerpt' => wp_strip_all_tags( (string) $row['description_short'] ),
			'post_content' => (string) $row['description'],
			'post_status'  => (int) $row['active'] ? 'publish' : 'draft',
		);

		if ( $dry_run ) {
			++$count;
			continue;
		}

		if ( $wp_id > 0 ) {
			$post_data['ID'] = $wp_id;
			wp_update_post( $post_data );
		} else {
			$wp_id = (int) wp_insert_post( $post_data );
		}
		if ( $wp_id <= 0 ) {
			continue;
		}

		anrh_mig_map_set( 'product', $ps_id, $wp_id );
		++$count;

		update_post_meta( $wp_id, 'anr_reference', $ref );
		update_post_meta( $wp_id, 'anr_price_ht', round( (float) $row['price'], 4 ) );
		update_post_meta( $wp_id, 'anr_featured', '1' );
		update_post_meta( $wp_id, 'anrhpub_ps_product_id', $ps_id );

		if ( (int) $row['on_sale'] ) {
			update_post_meta( $wp_id, 'anr_badge', 'promo' );
		}

		$term_ids = array();
		if ( isset( $cstmt ) ) {
			$cstmt->execute( array( ':pid' => $ps_id ) );
			while ( $cr = $cstmt->fetch() ) {
				$cid = (int) $cr['id_category'];
				if ( isset( $cat_map[ $cid ] ) && $cat_map[ $cid ] > 0 ) {
					$term_ids[] = (int) $cat_map[ $cid ];
				}
			}
		} elseif ( isset( $cat_map[ (int) $row['id_category_default'] ] ) ) {
			$term_ids[] = (int) $cat_map[ (int) $row['id_category_default'] ];
		}
		if ( $term_ids ) {
			wp_set_object_terms( $wp_id, array_unique( $term_ids ), 'anr_category', false );
		}

		anrh_mig_import_product_features( $wp_id, $ps_id, $dry_run );
		anrh_mig_import_product_stock_and_colors( $wp_id, $ps_id, $color_map, $dry_run );

		$gallery_ids = array();
		$img_all->execute( array( ':pid' => $ps_id ) );
		$images = $img_all->fetchAll();
		$first = true;
		foreach ( $images as $img ) {
			$url = anrh_mig_presta_image_url( (int) $img['id_image'] );
			$att = anrh_mig_sideload_image( $url, $wp_id, (string) $row['name'], false );
			if ( $att > 0 ) {
				$gallery_ids[] = $att;
				if ( $first && (int) $img['cover'] ) {
					set_post_thumbnail( $wp_id, $att );
					$first = false;
				}
			}
		}
		if ( ! empty( $gallery_ids ) ) {
			anrh_mig_save_product_gallery( $wp_id, $gallery_ids );
		}

		$slug = $post_data['post_name'] ?: sanitize_title( $row['name'] );
		anrh_mig_add_redirect(
			( $ref ? $ref : $slug ),
			get_permalink( $wp_id )
		);
		if ( ! empty( $row['link_rewrite'] ) ) {
			anrh_mig_add_redirect( (string) $row['link_rewrite'], get_permalink( $wp_id ) );
		}
	}

	anrh_mig_log( 'Produits traites: ' . $count );
}

// -------------------------------------------------------------------------
// Clients + adresses + meta pro
// -------------------------------------------------------------------------

function anrh_mig_ps_address_to_row( array $addr ): array {
	return array(
		'id'         => 'ps-' . (int) $addr['id_address'],
		'label'      => ! empty( $addr['alias'] ) ? sanitize_text_field( (string) $addr['alias'] ) : __( 'Adresse PrestaShop', 'anrhpub_theme' ),
		'first_name' => sanitize_text_field( (string) ( $addr['firstname'] ?? '' ) ),
		'last_name'  => sanitize_text_field( (string) ( $addr['lastname'] ?? '' ) ),
		'company'    => sanitize_text_field( (string) ( $addr['company'] ?? '' ) ),
		'address_1'  => sanitize_text_field( (string) ( $addr['address1'] ?? '' ) ),
		'address_2'  => sanitize_text_field( (string) ( $addr['address2'] ?? '' ) ),
		'postcode'   => sanitize_text_field( (string) ( $addr['postcode'] ?? '' ) ),
		'city'       => sanitize_text_field( (string) ( $addr['city'] ?? '' ) ),
		'country'    => 'France',
		'phone'      => sanitize_text_field( (string) ( $addr['phone'] ?? $addr['phone_mobile'] ?? '' ) ),
	);
}

function anrh_mig_import_clients( bool $dry_run ): void {
	$p     = anrh_mig_prefix();
	$table = $p . 'customer';
	$cols  = array( 'id_customer', 'email', 'firstname', 'lastname', 'active' );
	foreach ( array( 'company', 'siret', 'ape' ) as $optional ) {
		if ( anrh_mig_column_exists( $table, $optional ) ) {
			$cols[] = $optional;
		}
	}
	$sql  = 'SELECT ' . implode( ', ', $cols ) . ' FROM `' . $table . '` ORDER BY id_customer ASC';
	$rows = anrh_mig_pdo()->query( $sql )->fetchAll();

	$addr_sql = null;
	if ( anrh_mig_table_exists( $p . 'address' ) ) {
		$addr_sql = anrh_mig_pdo()->prepare(
			'SELECT * FROM `' . $p . 'address` WHERE id_customer=:cid AND deleted=0 ORDER BY id_address ASC'
		);
	}

	$role = defined( 'ANRHPUB_CLIENT_ROLE' ) ? ANRHPUB_CLIENT_ROLE : 'subscriber';
	$count = 0;

	foreach ( $rows as $row ) {
		$ps_id = (int) $row['id_customer'];
		$email = sanitize_email( (string) $row['email'] );
		if ( ! is_email( $email ) ) {
			continue;
		}

		$wp_id = anrh_mig_map_get( 'customer', $ps_id );
		if ( ! $wp_id ) {
			$user = get_user_by( 'email', $email );
			$wp_id = $user ? (int) $user->ID : 0;
		}

		if ( $dry_run ) {
			++$count;
			continue;
		}

		if ( ! $wp_id ) {
			$username = sanitize_user( current( explode( '@', $email ) ), true );
			if ( username_exists( $username ) ) {
				$username = $username . '_' . $ps_id;
			}
			$new_id = wp_create_user( $username, wp_generate_password( 20, true ), $email );
			if ( is_wp_error( $new_id ) ) {
				anrh_mig_log( 'Client KO ' . $email . ': ' . $new_id->get_error_message() );
				continue;
			}
			$wp_id = (int) $new_id;
			update_user_meta( $wp_id, 'anrhpub_must_reset_password', '1' );
		}

		$wp_user = new WP_User( $wp_id );
		$wp_user->set_role( $role );

		$display = trim( sanitize_text_field( (string) $row['firstname'] ) . ' ' . sanitize_text_field( (string) $row['lastname'] ) );
		wp_update_user(
			array(
				'ID'           => $wp_id,
				'first_name'   => sanitize_text_field( (string) $row['firstname'] ),
				'last_name'    => sanitize_text_field( (string) $row['lastname'] ),
				'display_name' => $display ?: $email,
			)
		);

		$company = sanitize_text_field( (string) ( $row['company'] ?? '' ) );
		if ( $company && defined( 'ANRHPUB_COMPANY_META' ) ) {
			update_user_meta( $wp_id, ANRHPUB_COMPANY_META, $company );
		}
		if ( ! empty( $row['siret'] ) && defined( 'ANRHPUB_SIRET_META' ) ) {
			update_user_meta( $wp_id, ANRHPUB_SIRET_META, sanitize_text_field( (string) $row['siret'] ) );
		}
		if ( defined( 'ANRHPUB_ACCOUNT_STATUS_META' ) ) {
			$status = (int) $row['active'] ? 'approved' : 'rejected';
			update_user_meta( $wp_id, ANRHPUB_ACCOUNT_STATUS_META, $status );
		}

		if ( $addr_sql ) {
			$addr_sql->execute( array( ':cid' => $ps_id ) );
			$addresses = array();
			$delivery_id = '';
			while ( $a = $addr_sql->fetch() ) {
				$addresses[] = anrh_mig_ps_address_to_row( $a );
				if ( ! empty( $a['active'] ) ) {
					$delivery_id = 'ps-' . (int) $a['id_address'];
				}
			}
			if ( ! empty( $addresses ) && function_exists( 'anrhpub_save_client_addresses' ) ) {
				anrhpub_save_client_addresses( $wp_id, $addresses );
				if ( $delivery_id && defined( 'ANRHPUB_DELIVERY_ADDRESS_META' ) ) {
					update_user_meta( $wp_id, ANRHPUB_DELIVERY_ADDRESS_META, $delivery_id );
				}
			} elseif ( ! empty( $addresses ) ) {
				update_user_meta( $wp_id, 'anrhpub_addresses', $addresses );
			}
		}

		anrh_mig_map_set( 'customer', $ps_id, $wp_id );
		++$count;
	}

	anrh_mig_log( 'Clients traites: ' . $count . ' (mots de passe reinitialisation obligatoire).' );
}

// -------------------------------------------------------------------------
// Newsletter
// -------------------------------------------------------------------------

function anrh_mig_import_newsletter( bool $dry_run ): void {
	$p = anrh_mig_prefix();
	if ( ! anrh_mig_table_exists( $p . 'newsletter' ) ) {
		anrh_mig_log( 'Newsletter: table absente.' );
		return;
	}
	$rows  = anrh_mig_pdo()->query( 'SELECT email, newsletter_date_add FROM `' . $p . 'newsletter` WHERE active=1' )->fetchAll();
	$count = 0;
	foreach ( $rows as $row ) {
		$email = sanitize_email( (string) $row['email'] );
		if ( ! is_email( $email ) ) {
			continue;
		}
		++$count;
		if ( $dry_run ) {
			continue;
		}
		if ( function_exists( 'anrhpub_subscribe_newsletter' ) ) {
			anrhpub_subscribe_newsletter( $email );
		}
	}
	anrh_mig_log( 'Newsletter: ' . $count );
}

// -------------------------------------------------------------------------
// Orders
// -------------------------------------------------------------------------

function anrh_mig_map_order_status( int $ps_state_id ): string {
	return ORDER_STATUS_MAP[ $ps_state_id ] ?? 'pending';
}

function anrh_mig_ps_address_snapshot( int $address_id ): array {
	$p = anrh_mig_prefix();
	if ( $address_id <= 0 || ! anrh_mig_table_exists( $p . 'address' ) ) {
		return array();
	}
	$stmt = anrh_mig_pdo()->prepare( 'SELECT * FROM `' . $p . 'address` WHERE id_address=:id LIMIT 1' );
	$stmt->execute( array( ':id' => $address_id ) );
	$row = $stmt->fetch();
	if ( ! $row ) {
		return array();
	}
	$snap = anrh_mig_ps_address_to_row( $row );
	unset( $snap['id'] );
	return $snap;
}

function anrh_mig_find_order_wp_id( string $order_number, int $ps_order_id ): int {
	$wp_id = anrh_mig_map_get( 'order', $ps_order_id );
	if ( $wp_id ) {
		return $wp_id;
	}
	$posts = get_posts(
		array(
			'post_type'      => defined( 'ANRHPUB_ORDER_CPT' ) ? ANRHPUB_ORDER_CPT : 'anr_order',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => 'anr_order_number',
			'meta_value'     => $order_number,
		)
	);
	return ! empty( $posts ) ? (int) $posts[0] : 0;
}

function anrh_mig_import_orders( bool $dry_run ): void {
	$p = anrh_mig_prefix();
	$sql = 'SELECT o.id_order, o.reference, o.current_state, o.total_paid_tax_excl, o.total_products_wt,
		o.date_add, o.id_customer, o.id_address_delivery, o.id_address_invoice,
		c.email
		FROM `' . $p . 'orders` o
		LEFT JOIN `' . $p . 'customer` c ON c.id_customer=o.id_customer
		ORDER BY o.id_order ASC';
	$orders = anrh_mig_pdo()->query( $sql )->fetchAll();

	$line_stmt = anrh_mig_pdo()->prepare(
		'SELECT product_id, product_reference, product_name, product_quantity, product_attribute_id
		 FROM `' . $p . 'order_detail`
		 WHERE id_order=:oid'
	);

	$count = 0;
	foreach ( $orders as $row ) {
		$ps_oid = (int) $row['id_order'];
		$order_number = trim( (string) $row['reference'] );
		if ( '' === $order_number ) {
			$order_number = 'PS-' . $ps_oid;
		}

		$wp_id = anrh_mig_find_order_wp_id( $order_number, $ps_oid );
		$client_id = 0;
		$email   = sanitize_email( (string) ( $row['email'] ?? '' ) );
		if ( $email ) {
			$user = get_user_by( 'email', $email );
			if ( $user ) {
				$client_id = (int) $user->ID;
			}
		}
		if ( ! $client_id && (int) $row['id_customer'] > 0 ) {
			$client_id = anrh_mig_map_get( 'customer', (int) $row['id_customer'] );
		}

		$line_stmt->execute( array( ':oid' => $ps_oid ) );
		$source_lines = $line_stmt->fetchAll();
		$lines = array();
		foreach ( $source_lines as $l ) {
			$ref = (string) ( $l['product_reference'] ?? '' );
			if ( '' === $ref && ! empty( $l['product_id'] ) ) {
				$wp_prod = anrh_mig_map_get( 'product', (int) $l['product_id'] );
				if ( $wp_prod ) {
					$ref = (string) get_post_meta( $wp_prod, 'anr_reference', true );
				}
			}
			$lines[] = array(
				'ref'   => $ref,
				'label' => (string) ( $l['product_name'] ?? '' ),
				'qty'   => max( 1, (int) ( $l['product_quantity'] ?? 1 ) ),
			);
		}

		$delivery = anrh_mig_ps_address_snapshot( (int) $row['id_address_delivery'] );

		if ( $dry_run ) {
			++$count;
			continue;
		}

		$post_data = array(
			'post_type'   => defined( 'ANRHPUB_ORDER_CPT' ) ? ANRHPUB_ORDER_CPT : 'anr_order',
			'post_status' => 'publish',
			'post_title'  => $order_number,
			'post_date'   => (string) $row['date_add'],
		);
		if ( $wp_id > 0 ) {
			$post_data['ID'] = $wp_id;
			wp_update_post( $post_data );
		} else {
			$wp_id = (int) wp_insert_post( $post_data );
		}
		if ( $wp_id <= 0 ) {
			continue;
		}

		$total = (float) $row['total_paid_tax_excl'];
		if ( $total <= 0 ) {
			$total = (float) $row['total_products_wt'];
		}

		update_post_meta( $wp_id, 'anr_order_number', $order_number );
		update_post_meta( $wp_id, 'anr_client_id', $client_id );
		update_post_meta( $wp_id, 'anr_order_status', anrh_mig_map_order_status( (int) $row['current_state'] ) );
		update_post_meta( $wp_id, 'anr_order_total', round( $total, 2 ) );
		update_post_meta( $wp_id, 'anr_order_lines', wp_json_encode( $lines ) );
		update_post_meta( $wp_id, 'anrhpub_ps_order_id', $ps_oid );
		if ( ! empty( $delivery ) ) {
			update_post_meta( $wp_id, 'anr_delivery_address', wp_json_encode( $delivery ) );
		}

		anrh_mig_map_set( 'order', $ps_oid, $wp_id );
		++$count;
	}

	anrh_mig_log( 'Commandes: ' . $count );
}

// -------------------------------------------------------------------------
// Credits (avoirs) — order_slip
// -------------------------------------------------------------------------

function anrh_mig_import_credits( bool $dry_run ): void {
	$p = anrh_mig_prefix();
	if ( ! anrh_mig_table_exists( $p . 'order_slip' ) ) {
		anrh_mig_log( 'Avoirs: table order_slip absente.' );
		return;
	}

	$sql = 'SELECT os.id_order_slip, os.id_order, os.date_add, os.amount, o.reference
		FROM `' . $p . 'order_slip` os
		LEFT JOIN `' . $p . 'orders` o ON o.id_order=os.id_order
		ORDER BY os.id_order_slip ASC';
	$rows = anrh_mig_pdo()->query( $sql )->fetchAll();

	$count = 0;
	foreach ( $rows as $row ) {
		$ps_slip_id = (int) $row['id_order_slip'];
		$ps_order_id = (int) $row['id_order'];
		$wp_order_id = anrh_mig_map_get( 'order', $ps_order_id );
		$amount      = round( abs( (float) $row['amount'] ), 2 );
		if ( $amount <= 0 ) {
			continue;
		}

		$wp_credit_id = anrh_mig_map_get( 'credit', $ps_slip_id );
		if ( $dry_run ) {
			++$count;
			continue;
		}

		$credit_number = 'AVR-PS-' . $ps_slip_id;
		$client_id     = 0;
		if ( $wp_order_id ) {
			$client_id = (int) get_post_meta( $wp_order_id, 'anr_client_id', true );
		}

		$post_data = array(
			'post_type'   => defined( 'ANRHPUB_CREDIT_CPT' ) ? ANRHPUB_CREDIT_CPT : 'anr_credit',
			'post_status' => 'publish',
			'post_title'  => $credit_number,
			'post_date'   => (string) $row['date_add'],
		);

		if ( $wp_credit_id > 0 ) {
			$post_data['ID'] = $wp_credit_id;
			wp_update_post( $post_data );
		} else {
			$wp_credit_id = (int) wp_insert_post( $post_data );
		}

		if ( $wp_credit_id <= 0 ) {
			continue;
		}

		update_post_meta( $wp_credit_id, 'anr_credit_number', $credit_number );
		update_post_meta( $wp_credit_id, 'anr_credit_client_id', $client_id );
		update_post_meta( $wp_credit_id, 'anr_credit_order_id', $wp_order_id );
		update_post_meta( $wp_credit_id, 'anr_credit_amount', $amount );
		update_post_meta( $wp_credit_id, 'anr_credit_status', 'available' );
		update_post_meta( $wp_credit_id, 'anr_credit_reason', sprintf( 'Avoir PrestaShop commande %s', (string) $row['reference'] ) );

		anrh_mig_map_set( 'credit', $ps_slip_id, $wp_credit_id );
		++$count;
	}

	anrh_mig_log( 'Avoirs: ' . $count );
}

// -------------------------------------------------------------------------
// CMS pages
// -------------------------------------------------------------------------

function anrh_mig_import_cms_pages( bool $dry_run ): void {
	$p = anrh_mig_prefix();
	if ( ! anrh_mig_table_exists( $p . 'cms' ) ) {
		anrh_mig_log( 'CMS: table absente.' );
		return;
	}

	$sql = 'SELECT c.id_cms, cl.meta_title, cl.content, cl.link_rewrite
		FROM `' . $p . 'cms` c
		INNER JOIN `' . $p . 'cms_lang` cl ON cl.id_cms=c.id_cms AND cl.id_lang=:lang
		WHERE c.active=1
		ORDER BY c.id_cms ASC';
	$stmt = anrh_mig_pdo()->prepare( $sql );
	$stmt->execute( array( ':lang' => SOURCE_LANG_ID ) );
	$rows = $stmt->fetchAll();

	$skip_slugs = array( 'accueil', 'index', 'home' );
	$count = 0;

	foreach ( $rows as $row ) {
		$slug = sanitize_title( (string) $row['link_rewrite'] );
		if ( in_array( $slug, $skip_slugs, true ) ) {
			continue;
		}
		$title = (string) $row['meta_title'];
		if ( '' === $title ) {
			$title = $slug;
		}

		$existing = get_page_by_path( $slug );
		if ( $dry_run ) {
			++$count;
			continue;
		}

		$post_data = array(
			'post_type'    => 'page',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => (string) $row['content'],
			'post_status'  => 'publish',
		);

		if ( $existing ) {
			$post_data['ID'] = $existing->ID;
			wp_update_post( $post_data );
			$page_id = (int) $existing->ID;
		} else {
			$page_id = (int) wp_insert_post( $post_data );
		}

		if ( $page_id > 0 ) {
			anrh_mig_add_redirect( 'content/' . (int) $row['id_cms'], get_permalink( $page_id ) );
			anrh_mig_add_redirect( $slug, get_permalink( $page_id ) );
			++$count;
		}
	}

	anrh_mig_log( 'Pages CMS importees: ' . $count );
}

// -------------------------------------------------------------------------
// Carts -> devis brouillon (si paniers abandonnes avec client)
// -------------------------------------------------------------------------

function anrh_mig_import_carts_as_quotes( bool $dry_run ): void {
	$p = anrh_mig_prefix();
	if ( ! anrh_mig_table_exists( $p . 'cart' ) || ! function_exists( 'anrhpub_save_quote' ) ) {
		anrh_mig_log( 'Devis/paniers: tables ou fonctions absentes.' );
		return;
	}

	$sql = 'SELECT c.id_cart, c.id_customer, c.date_add, cu.email
		FROM `' . $p . 'cart` c
		LEFT JOIN `' . $p . 'customer` cu ON cu.id_customer=c.id_customer
		WHERE c.id_customer > 0
		ORDER BY c.id_cart ASC';
	$rows = anrh_mig_pdo()->query( $sql )->fetchAll();

	if ( ! anrh_mig_table_exists( $p . 'cart_product' ) ) {
		anrh_mig_log( 'Devis/paniers: cart_product absent.' );
		return;
	}

	$prod_stmt = anrh_mig_pdo()->prepare(
		'SELECT id_product, quantity FROM `' . $p . 'cart_product` WHERE id_cart=:cid'
	);

	$count = 0;
	foreach ( $rows as $row ) {
		$client_id = anrh_mig_map_get( 'customer', (int) $row['id_customer'] );
		if ( $client_id <= 0 && ! empty( $row['email'] ) ) {
			$user = get_user_by( 'email', sanitize_email( (string) $row['email'] ) );
			$client_id = $user ? (int) $user->ID : 0;
		}
		if ( $client_id <= 0 ) {
			continue;
		}

		$prod_stmt->execute( array( ':cid' => (int) $row['id_cart'] ) );
		$cart_lines = $prod_stmt->fetchAll();
		if ( empty( $cart_lines ) ) {
			continue;
		}

		$lines = array();
		foreach ( $cart_lines as $cl ) {
			$wp_prod = anrh_mig_map_get( 'product', (int) $cl['id_product'] );
			if ( $wp_prod <= 0 ) {
				continue;
			}
			$lines[] = array(
				'product_id' => $wp_prod,
				'label'      => get_the_title( $wp_prod ),
				'ref'        => (string) get_post_meta( $wp_prod, 'anr_reference', true ),
				'qty'        => max( 1, (int) $cl['quantity'] ),
			);
		}
		if ( empty( $lines ) ) {
			continue;
		}

		++$count;
		if ( $dry_run ) {
			continue;
		}

		anrhpub_save_quote(
			array(
				'client_id' => $client_id,
				'status'    => 'draft',
				'lines'     => $lines,
				'message'   => __( 'Panier PrestaShop migre (brouillon devis).', 'anrhpub_theme' ),
			)
		);
	}

	anrh_mig_log( 'Paniers convertis en brouillons devis: ' . $count );
}

// -------------------------------------------------------------------------
// Export redirects CSV hint
// -------------------------------------------------------------------------

function anrh_mig_export_redirects_summary(): void {
	$redirs = get_option( ANRH_MIG_REDIR_OPTION, array() );
	if ( ! is_array( $redirs ) || empty( $redirs ) ) {
		anrh_mig_log( 'Redirections: aucune enregistree.' );
		return;
	}
	$file = WP_CONTENT_DIR . '/uploads/anrhpub-prestashop-redirects.csv';
	if ( 'cli' === PHP_SAPI ) {
		$fp = fopen( $file, 'w' );
		if ( $fp ) {
			fputcsv( $fp, array( 'old_path', 'new_url' ), ';' );
			foreach ( $redirs as $old => $new ) {
				fputcsv( $fp, array( $old, $new ), ';' );
			}
			fclose( $fp );
			anrh_mig_log( 'Redirections exportees: ' . $file . ' (' . count( $redirs ) . ' lignes)' );
		}
	} else {
		anrh_mig_log( 'Redirections en option ' . ANRH_MIG_REDIR_OPTION . ': ' . count( $redirs ) . ' entrees.' );
	}
}

// -------------------------------------------------------------------------
// Runner
// -------------------------------------------------------------------------

function anrh_mig_run_step( string $step, bool $dry_run ): void {
	switch ( $step ) {
		case 'diagnose':
			anrh_mig_diagnose();
			break;
		case 'categories':
			anrh_mig_import_categories( $dry_run );
			break;
		case 'colors':
			anrh_mig_import_colors( $dry_run );
			break;
		case 'products':
			$maps = anrh_mig_load_taxonomy_maps();
			if ( empty( $maps['color'] ) ) {
				$maps['color'] = anrh_mig_import_colors( $dry_run );
			}
			if ( empty( $maps['category'] ) ) {
				$maps['category'] = anrh_mig_import_categories( $dry_run );
			}
			anrh_mig_import_products( $dry_run, $maps['category'], $maps['color'] );
			break;
		case 'clients':
			anrh_mig_import_clients( $dry_run );
			break;
		case 'newsletter':
			anrh_mig_import_newsletter( $dry_run );
			break;
		case 'orders':
			anrh_mig_import_orders( $dry_run );
			break;
		case 'credits':
			anrh_mig_import_credits( $dry_run );
			break;
		case 'cms':
			anrh_mig_import_cms_pages( $dry_run );
			break;
		case 'quotes':
			anrh_mig_import_carts_as_quotes( $dry_run );
			break;
		case 'redirects':
			anrh_mig_export_redirects_summary();
			break;
		case 'all':
			$cat_map   = anrh_mig_import_categories( $dry_run );
			$color_map = anrh_mig_import_colors( $dry_run );
			anrh_mig_import_products( $dry_run, $cat_map, $color_map );
			anrh_mig_import_clients( $dry_run );
			anrh_mig_import_newsletter( $dry_run );
			anrh_mig_import_orders( $dry_run );
			anrh_mig_import_credits( $dry_run );
			anrh_mig_import_cms_pages( $dry_run );
			anrh_mig_import_carts_as_quotes( $dry_run );
			if ( ! $dry_run ) {
				anrh_mig_export_redirects_summary();
			}
			break;
		default:
			anrh_mig_log( 'Etape inconnue: ' . $step );
	}
}

function anrh_mig_run(): void {
	$run    = (string) anrh_mig_arg( 'run', '0' );
	$token  = (string) anrh_mig_arg( 'token', '' );
	$step   = (string) anrh_mig_arg( 'step', 'diagnose' );
	$dry_run = anrh_mig_is_dry_run();

	if ( 'cli' !== PHP_SAPI ) {
		if ( 'CHANGE_ME' === HTTP_TOKEN || $token !== HTTP_TOKEN ) {
			wp_die( 'Token HTTP invalide.' );
		}
	}

	if ( '1' !== $run ) {
		anrh_mig_log( 'Migration PrestaShop v' . ANRH_MIG_VERSION );
		anrh_mig_log( 'Ajoute --run=1 --step=diagnose|all|products|... [--dry-run=1]' );
		return;
	}

	anrh_mig_log( '=== Migration PrestaShop -> ANRPUB v' . ANRH_MIG_VERSION . ' | step=' . $step . ' | dry_run=' . ( $dry_run ? '1' : '0' ) . ' ===' );

	try {
		anrh_mig_pdo();
	} catch ( Throwable $e ) {
		anrh_mig_log( 'ERREUR connexion DB PrestaShop: ' . $e->getMessage() );
		return;
	}

	anrh_mig_run_step( $step, $dry_run );
	anrh_mig_log( 'Termine.' );
}

anrh_mig_run();
