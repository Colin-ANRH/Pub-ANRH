<?php
/**
 * Re-commande en 1 clic depuis une commande passée.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tente de résoudre un product_id depuis une ligne commande.
 *
 * @param array $line Line.
 * @return int
 */
function anrhpub_resolve_product_id_from_order_line( $line ) {
	if ( ! empty( $line['product_id'] ) ) {
		return (int) $line['product_id'];
	}

	if ( ! empty( $line['ref'] ) ) {
		$posts = get_posts(
			array(
				'post_type'      => 'anr_product',
				'posts_per_page' => 1,
				'meta_key'       => 'anr_reference',
				'meta_value'     => (string) $line['ref'],
				'fields'         => 'ids',
			)
		);
		if ( $posts ) {
			return (int) $posts[0];
		}
	}

	return 0;
}

/**
 * Panier depuis lignes commande.
 *
 * @param int $order_id Order ID.
 * @return array
 */
function anrhpub_order_lines_to_cart_items( $order_id ) {
	$items = array();

	foreach ( anrhpub_get_order_lines( $order_id ) as $line ) {
		$product_id = anrhpub_resolve_product_id_from_order_line( $line );

		if ( $product_id <= 0 ) {
			continue;
		}

		$items[] = array(
			'product_id' => $product_id,
			'qty'        => max( 1, (int) ( $line['qty'] ?? 1 ) ),
		);
	}

	return $items;
}

/**
 * Traitement re-commande.
 */
function anrhpub_handle_reorder() {
	if ( empty( $_GET['anrhpub_reorder'] ) || empty( $_GET['order_id'] ) ) {
		return;
	}

	$order_id = absint( $_GET['order_id'] );
	$nonce    = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

	if ( ! wp_verify_nonce( $nonce, 'anrhpub_reorder_' . $order_id ) ) {
		wp_die( esc_html__( 'Lien invalide.', 'anrhpub_theme' ), 403 );
	}

	if ( ! anrhpub_client_owns_order( $order_id ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'anrhpub_theme' ), 403 );
	}

	$items = anrhpub_order_lines_to_cart_items( $order_id );

	if ( empty( $items ) ) {
		wp_safe_redirect( add_query_arg( 'reorder_error', '1', anrhpub_account_url() ) );
		exit;
	}

	if ( anrhpub_is_client_logged_in() ) {
		anrhpub_save_user_quote_cart( $items );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'reorder_ok' => '1',
				'cart'       => rawurlencode( wp_json_encode( $items ) ),
			),
			anrhpub_quote_cart_url()
		)
	);
	exit;
}
add_action( 'template_redirect', 'anrhpub_handle_reorder', 5 );

/**
 * URL re-commande.
 *
 * @param int $order_id Order ID.
 * @return string
 */
function anrhpub_get_reorder_url( $order_id ) {
	return wp_nonce_url(
		add_query_arg(
			array(
				'anrhpub_reorder' => '1',
				'order_id'        => (int) $order_id,
			),
			home_url( '/' )
		),
		'anrhpub_reorder_' . (int) $order_id
	);
}
