<?php
/** Admin taxonomie couleurs. @package anrhpub_theme */

defined( 'ABSPATH' ) || exit;

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
