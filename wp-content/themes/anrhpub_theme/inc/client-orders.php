<?php
/**
 * Commandes clients & avoirs — CPT gérés dans WordPress.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_ORDER_CPT', 'anr_order' );
define( 'ANRHPUB_CREDIT_CPT', 'anr_credit' );

define( 'ANRHPUB_ORDER_STATUSES', array(
	'pending'     => __( 'En attente', 'anrhpub_theme' ),
	'confirmed'   => __( 'Confirmée', 'anrhpub_theme' ),
	'processing'  => __( 'En préparation', 'anrhpub_theme' ),
	'shipped'     => __( 'Expédiée', 'anrhpub_theme' ),
	'delivered'   => __( 'Livrée', 'anrhpub_theme' ),
	'cancelled'   => __( 'Annulée', 'anrhpub_theme' ),
) );

define( 'ANRHPUB_CREDIT_STATUSES', array(
	'available' => __( 'Disponible', 'anrhpub_theme' ),
	'used'      => __( 'Utilisé', 'anrhpub_theme' ),
	'expired'   => __( 'Expiré', 'anrhpub_theme' ),
	'cancelled' => __( 'Annulé', 'anrhpub_theme' ),
) );

/**
 * Enregistrement CPT commandes & avoirs.
 */
function anrhpub_register_client_order_cpts() {
	register_post_type(
		ANRHPUB_ORDER_CPT,
		array(
			'labels'              => array(
				'name'               => __( 'Commandes clients', 'anrhpub_theme' ),
				'singular_name'      => __( 'Commande client', 'anrhpub_theme' ),
				'add_new'            => __( 'Ajouter', 'anrhpub_theme' ),
				'add_new_item'       => __( 'Nouvelle commande', 'anrhpub_theme' ),
				'edit_item'          => __( 'Modifier la commande', 'anrhpub_theme' ),
				'search_items'       => __( 'Rechercher une commande', 'anrhpub_theme' ),
				'not_found'          => __( 'Aucune commande.', 'anrhpub_theme' ),
				'menu_name'          => __( 'Commandes clients', 'anrhpub_theme' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=anr_product',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'rewrite'             => false,
		)
	);

	register_post_type(
		ANRHPUB_CREDIT_CPT,
		array(
			'labels'              => array(
				'name'               => __( 'Avoirs clients', 'anrhpub_theme' ),
				'singular_name'      => __( 'Avoir client', 'anrhpub_theme' ),
				'add_new'            => __( 'Ajouter', 'anrhpub_theme' ),
				'add_new_item'       => __( 'Nouvel avoir', 'anrhpub_theme' ),
				'edit_item'          => __( 'Modifier l’avoir', 'anrhpub_theme' ),
				'search_items'       => __( 'Rechercher un avoir', 'anrhpub_theme' ),
				'not_found'          => __( 'Aucun avoir.', 'anrhpub_theme' ),
				'menu_name'          => __( 'Avoirs', 'anrhpub_theme' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=anr_product',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'rewrite'             => false,
		)
	);
}
add_action( 'init', 'anrhpub_register_client_order_cpts', 12 );

/**
 * Numéro commande unique.
 *
 * @return string
 */
function anrhpub_generate_order_number() {
	return 'CMD-' . gmdate( 'Y' ) . '-' . str_pad( (string) wp_rand( 1, 99999 ), 5, '0', STR_PAD_LEFT );
}

/**
 * Numéro avoir unique.
 *
 * @return string
 */
function anrhpub_generate_credit_number() {
	return 'AVR-' . gmdate( 'Y' ) . '-' . str_pad( (string) wp_rand( 1, 99999 ), 5, '0', STR_PAD_LEFT );
}

/**
 * Libellé statut commande.
 *
 * @param string $status Code.
 * @return string
 */
function anrhpub_get_order_status_label( $status ) {
	$status = sanitize_key( (string) $status );
	return ANRHPUB_ORDER_STATUSES[ $status ] ?? $status;
}

/**
 * Libellé statut avoir.
 *
 * @param string $status Code.
 * @return string
 */
function anrhpub_get_credit_status_label( $status ) {
	$status = sanitize_key( (string) $status );
	return ANRHPUB_CREDIT_STATUSES[ $status ] ?? $status;
}

/**
 * Meta commande.
 *
 * @param int    $order_id Post ID.
 * @param string $key      Clé sans préfixe.
 * @param mixed  $default  Défaut.
 * @return mixed
 */
function anrhpub_get_order_meta( $order_id, $key, $default = '' ) {
	return get_post_meta( (int) $order_id, 'anr_' . $key, true ) ?: $default;
}

/**
 * Meta avoir.
 *
 * @param int    $credit_id Post ID.
 * @param string $key       Clé.
 * @param mixed  $default   Défaut.
 * @return mixed
 */
function anrhpub_get_credit_meta( $credit_id, $key, $default = '' ) {
	return get_post_meta( (int) $credit_id, 'anr_' . $key, true ) ?: $default;
}

/**
 * Commandes d’un client.
 *
 * @param int   $user_id User ID.
 * @param array $args    WP_Query args additionnels.
 * @return WP_Post[]
 */
function anrhpub_get_client_orders( $user_id = 0, $args = array() ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	if ( $user_id <= 0 ) {
		return array();
	}

	$query = new WP_Query(
		wp_parse_args(
			$args,
			array(
				'post_type'      => ANRHPUB_ORDER_CPT,
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'   => 'anr_client_id',
						'value' => $user_id,
					),
				),
			)
		)
	);

	return $query->posts;
}

/**
 * Avoirs d’un client.
 *
 * @param int   $user_id User ID.
 * @param array $args    Args.
 * @return WP_Post[]
 */
function anrhpub_get_client_credits( $user_id = 0, $args = array() ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	if ( $user_id <= 0 ) {
		return array();
	}

	$query = new WP_Query(
		wp_parse_args(
			$args,
			array(
				'post_type'      => ANRHPUB_CREDIT_CPT,
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'   => 'anr_credit_client_id',
						'value' => $user_id,
					),
				),
			)
		)
	);

	return $query->posts;
}

/**
 * Total avoirs disponibles (€).
 *
 * @param int $user_id User ID.
 * @return float
 */
function anrhpub_get_client_credit_balance( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();
	$total   = 0.0;

	foreach ( anrhpub_get_client_credits( $user_id ) as $credit ) {
		if ( 'available' !== anrhpub_get_credit_meta( $credit->ID, 'credit_status', 'available' ) ) {
			continue;
		}
		$total += (float) anrhpub_get_credit_meta( $credit->ID, 'credit_amount', 0 );
	}

	return round( $total, 2 );
}

/**
 * Commande appartient au client ?
 *
 * @param int $order_id Order ID.
 * @param int $user_id  User ID.
 * @return bool
 */
function anrhpub_client_owns_order( $order_id, $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	return (int) anrhpub_get_order_meta( $order_id, 'client_id', 0 ) === $user_id;
}

/**
 * Lignes commande décodées.
 *
 * @param int $order_id Order ID.
 * @return array<int, array{label: string, qty: int, ref: string}>
 */
function anrhpub_get_order_lines( $order_id ) {
	$raw = anrhpub_get_order_meta( $order_id, 'order_lines', '' );

	if ( is_array( $raw ) ) {
		return $raw;
	}

	if ( ! is_string( $raw ) || '' === $raw ) {
		return array();
	}

	$decoded = json_decode( $raw, true );

	return is_array( $decoded ) ? $decoded : array();
}

/**
 * Adresse livraison commande (snapshot).
 *
 * @param int $order_id Order ID.
 * @return array<string, string>
 */
function anrhpub_get_order_delivery_address( $order_id ) {
	$raw = anrhpub_get_order_meta( $order_id, 'delivery_address', '' );

	if ( is_array( $raw ) ) {
		return $raw;
	}

	if ( is_string( $raw ) && '' !== $raw ) {
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) ) {
			return $decoded;
		}
	}

	return array();
}

/**
 * Formate une adresse en texte.
 *
 * @param array<string, string> $address Address.
 * @return string
 */
function anrhpub_format_address_text( $address ) {
	if ( empty( $address ) ) {
		return '';
	}

	$parts = array();

	if ( ! empty( $address['label'] ) ) {
		$parts[] = $address['label'];
	}

	$name = trim( ( $address['first_name'] ?? '' ) . ' ' . ( $address['last_name'] ?? '' ) );
	if ( $name ) {
		$parts[] = $name;
	}
	if ( ! empty( $address['company'] ) ) {
		$parts[] = $address['company'];
	}
	if ( ! empty( $address['address_1'] ) ) {
		$parts[] = $address['address_1'];
	}
	if ( ! empty( $address['address_2'] ) ) {
		$parts[] = $address['address_2'];
	}
	$city_line = trim( ( $address['postcode'] ?? '' ) . ' ' . ( $address['city'] ?? '' ) );
	if ( $city_line ) {
		$parts[] = $city_line;
	}
	if ( ! empty( $address['country'] ) ) {
		$parts[] = $address['country'];
	}
	if ( ! empty( $address['phone'] ) ) {
		$parts[] = $address['phone'];
	}

	return implode( "\n", array_filter( $parts ) );
}

/**
 * Crée un avoir lié à une commande.
 *
 * @param int    $order_id Order ID.
 * @param float  $amount   Montant.
 * @param string $reason   Motif.
 * @return int|WP_Error Credit post ID.
 */
function anrhpub_create_credit_for_order( $order_id, $amount, $reason = '' ) {
	$order_id = (int) $order_id;
	$client_id = (int) anrhpub_get_order_meta( $order_id, 'client_id', 0 );

	if ( $client_id <= 0 ) {
		return new WP_Error( 'no_client', __( 'Commande sans client associé.', 'anrhpub_theme' ) );
	}

	$amount = round( max( 0, (float) $amount ), 2 );

	if ( $amount <= 0 ) {
		return new WP_Error( 'invalid_amount', __( 'Montant d’avoir invalide.', 'anrhpub_theme' ) );
	}

	$number = anrhpub_generate_credit_number();

	$credit_id = wp_insert_post(
		array(
			'post_type'   => ANRHPUB_CREDIT_CPT,
			'post_title'  => $number,
			'post_status' => 'publish',
		),
		true
	);

	if ( is_wp_error( $credit_id ) ) {
		return $credit_id;
	}

	update_post_meta( $credit_id, 'anr_credit_client_id', $client_id );
	update_post_meta( $credit_id, 'anr_credit_order_id', $order_id );
	update_post_meta( $credit_id, 'anr_credit_amount', $amount );
	update_post_meta( $credit_id, 'anr_credit_status', 'available' );
	update_post_meta( $credit_id, 'anr_credit_number', $number );
	update_post_meta( $credit_id, 'anr_credit_reason', sanitize_text_field( (string) $reason ) );

	return (int) $credit_id;
}

require_once ANRHPUB_THEME_DIR . '/inc/client-orders-admin.php';
