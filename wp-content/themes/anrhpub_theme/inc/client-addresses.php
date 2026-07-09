<?php
/**
 * Adresses client — plusieurs adresses & livraison par défaut.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_ADDRESSES_META', 'anrhpub_addresses' );
define( 'ANRHPUB_DELIVERY_ADDRESS_META', 'anrhpub_delivery_address_id' );

/**
 * Enregistrement meta utilisateur adresses.
 */
function anrhpub_register_address_user_meta() {
	register_meta(
		'user',
		ANRHPUB_ADDRESSES_META,
		array(
			'type'              => 'array',
			'single'            => true,
			'show_in_rest'      => false,
			'auth_callback'     => function () {
				return current_user_can( 'edit_users' );
			},
		)
	);

	register_meta(
		'user',
		ANRHPUB_DELIVERY_ADDRESS_META,
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => false,
			'auth_callback'     => function () {
				return current_user_can( 'edit_users' );
			},
		)
	);
}
add_action( 'init', 'anrhpub_register_address_user_meta', 11 );

/**
 * URL onglet adresses (sans paramètres d’édition).
 *
 * @return string
 */
function anrhpub_account_addresses_url() {
	return anrhpub_account_url() . '#panel-addresses';
}

/**
 * Toutes les adresses du client.
 *
 * @param int $user_id User ID.
 * @return array<int, array<string, string>>
 */
function anrhpub_get_client_addresses( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	if ( $user_id <= 0 ) {
		return array();
	}

	$raw = get_user_meta( $user_id, ANRHPUB_ADDRESSES_META, true );

	if ( ! is_array( $raw ) ) {
		return array();
	}

	$addresses = array();

	foreach ( $raw as $address ) {
		if ( ! is_array( $address ) || empty( $address['id'] ) ) {
			continue;
		}
		$addresses[] = anrhpub_sanitize_address_row( $address );
	}

	return $addresses;
}

/**
 * Nettoie une ligne adresse.
 *
 * @param array $address Raw.
 * @return array<string, string>
 */
function anrhpub_sanitize_address_row( $address ) {
	return array(
		'id'         => sanitize_key( (string) ( $address['id'] ?? '' ) ),
		'label'      => sanitize_text_field( (string) ( $address['label'] ?? '' ) ),
		'first_name' => sanitize_text_field( (string) ( $address['first_name'] ?? '' ) ),
		'last_name'  => sanitize_text_field( (string) ( $address['last_name'] ?? '' ) ),
		'company'    => sanitize_text_field( (string) ( $address['company'] ?? '' ) ),
		'address_1'  => sanitize_text_field( (string) ( $address['address_1'] ?? '' ) ),
		'address_2'  => sanitize_text_field( (string) ( $address['address_2'] ?? '' ) ),
		'postcode'   => sanitize_text_field( (string) ( $address['postcode'] ?? '' ) ),
		'city'       => sanitize_text_field( (string) ( $address['city'] ?? '' ) ),
		'country'    => sanitize_text_field( (string) ( $address['country'] ?? 'France' ) ),
		'phone'      => sanitize_text_field( (string) ( $address['phone'] ?? '' ) ),
	);
}

/**
 * ID adresse de livraison préférée.
 *
 * @param int $user_id User ID.
 * @return string
 */
function anrhpub_get_delivery_address_id( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	return sanitize_key( (string) get_user_meta( $user_id, ANRHPUB_DELIVERY_ADDRESS_META, true ) );
}

/**
 * Adresse de livraison active.
 *
 * @param int $user_id User ID.
 * @return array<string, string>|null
 */
function anrhpub_get_delivery_address( $user_id = 0 ) {
	$delivery_id = anrhpub_get_delivery_address_id( $user_id );

	foreach ( anrhpub_get_client_addresses( $user_id ) as $address ) {
		if ( $address['id'] === $delivery_id ) {
			return $address;
		}
	}

	$addresses = anrhpub_get_client_addresses( $user_id );

	return ! empty( $addresses ) ? $addresses[0] : null;
}

/**
 * Une adresse par ID.
 *
 * @param string $address_id Address ID.
 * @param int    $user_id    User ID.
 * @return array<string, string>|null
 */
function anrhpub_get_client_address_by_id( $address_id, $user_id = 0 ) {
	$address_id = sanitize_key( (string) $address_id );

	foreach ( anrhpub_get_client_addresses( $user_id ) as $address ) {
		if ( $address['id'] === $address_id ) {
			return $address;
		}
	}

	return null;
}

/**
 * Enregistre les adresses.
 *
 * @param int   $user_id   User ID.
 * @param array $addresses Liste.
 */
function anrhpub_save_client_addresses( $user_id, $addresses ) {
	$user_id = (int) $user_id;
	$clean   = array();

	foreach ( (array) $addresses as $address ) {
		if ( ! is_array( $address ) ) {
			continue;
		}
		$row = anrhpub_sanitize_address_row( $address );
		if ( '' === $row['id'] || '' === $row['address_1'] || '' === $row['city'] ) {
			continue;
		}
		$clean[] = $row;
	}

	update_user_meta( $user_id, ANRHPUB_ADDRESSES_META, $clean );

	$delivery_id = anrhpub_get_delivery_address_id( $user_id );
	$ids         = wp_list_pluck( $clean, 'id' );

	if ( $delivery_id && ! in_array( $delivery_id, $ids, true ) ) {
		update_user_meta( $user_id, ANRHPUB_DELIVERY_ADDRESS_META, $ids[0] ?? '' );
	} elseif ( ! $delivery_id && ! empty( $ids ) ) {
		update_user_meta( $user_id, ANRHPUB_DELIVERY_ADDRESS_META, $ids[0] );
	}
}

/**
 * Génère un ID adresse.
 *
 * @return string
 */
function anrhpub_new_address_id() {
	return 'addr' . strtolower( wp_generate_password( 10, false, false ) );
}

/**
 * Traitement formulaires adresses (compte client).
 */
function anrhpub_handle_address_forms() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		return;
	}

	if ( ! anrhpub_is_client_logged_in() ) {
		return;
	}

	$user_id = anrhpub_get_client_user_id();

	if ( isset( $_POST['anrhpub_save_address'] ) ) {
		check_admin_referer( 'anrhpub_address' );

		$address_id = isset( $_POST['address_id'] ) ? sanitize_key( wp_unslash( $_POST['address_id'] ) ) : '';
		$row        = array(
			'id'         => $address_id ? $address_id : anrhpub_new_address_id(),
			'label'      => isset( $_POST['address_label'] ) ? wp_unslash( $_POST['address_label'] ) : '',
			'first_name' => isset( $_POST['address_first_name'] ) ? wp_unslash( $_POST['address_first_name'] ) : '',
			'last_name'  => isset( $_POST['address_last_name'] ) ? wp_unslash( $_POST['address_last_name'] ) : '',
			'company'    => isset( $_POST['address_company'] ) ? wp_unslash( $_POST['address_company'] ) : '',
			'address_1'  => isset( $_POST['address_1'] ) ? wp_unslash( $_POST['address_1'] ) : '',
			'address_2'  => isset( $_POST['address_2'] ) ? wp_unslash( $_POST['address_2'] ) : '',
			'postcode'   => isset( $_POST['address_postcode'] ) ? wp_unslash( $_POST['address_postcode'] ) : '',
			'city'       => isset( $_POST['address_city'] ) ? wp_unslash( $_POST['address_city'] ) : '',
			'country'    => isset( $_POST['address_country'] ) ? wp_unslash( $_POST['address_country'] ) : 'France',
			'phone'      => isset( $_POST['address_phone'] ) ? wp_unslash( $_POST['address_phone'] ) : '',
		);

		$row = anrhpub_sanitize_address_row( $row );

		if ( '' === $row['address_1'] || '' === $row['city'] ) {
			anrhpub_set_account_error( __( 'Adresse et ville sont obligatoires.', 'anrhpub_theme' ) );
			return;
		}

		$addresses = anrhpub_get_client_addresses( $user_id );
		$found     = false;

		foreach ( $addresses as $i => $existing ) {
			if ( $existing['id'] === $row['id'] ) {
				$addresses[ $i ] = $row;
				$found           = true;
				break;
			}
		}

		if ( ! $found ) {
			$addresses[] = $row;
		}

		anrhpub_save_client_addresses( $user_id, $addresses );

		if ( ! empty( $_POST['set_as_delivery'] ) ) {
			update_user_meta( $user_id, ANRHPUB_DELIVERY_ADDRESS_META, $row['id'] );
		}

		anrhpub_account_redirect(
			remove_query_arg( array( 'add_address', 'edit_address' ), anrhpub_account_url() ) . '#panel-addresses',
			'address_ok'
		);
	}

	if ( isset( $_POST['anrhpub_delete_address'] ) ) {
		check_admin_referer( 'anrhpub_delete_address' );

		$address_id = isset( $_POST['address_id'] ) ? sanitize_key( wp_unslash( $_POST['address_id'] ) ) : '';
		$addresses  = array_filter(
			anrhpub_get_client_addresses( $user_id ),
			function ( $a ) use ( $address_id ) {
				return $a['id'] !== $address_id;
			}
		);

		anrhpub_save_client_addresses( $user_id, array_values( $addresses ) );
		anrhpub_account_redirect(
			remove_query_arg( array( 'add_address', 'edit_address' ), anrhpub_account_url() ) . '#panel-addresses',
			'address_deleted'
		);
	}

	if ( isset( $_POST['anrhpub_set_delivery_address'] ) ) {
		check_admin_referer( 'anrhpub_delivery_address' );

		$address_id = isset( $_POST['delivery_address_id'] ) ? sanitize_key( wp_unslash( $_POST['delivery_address_id'] ) ) : '';

		if ( anrhpub_get_client_address_by_id( $address_id, $user_id ) ) {
			update_user_meta( $user_id, ANRHPUB_DELIVERY_ADDRESS_META, $address_id );
			anrhpub_account_redirect(
				remove_query_arg( array( 'add_address', 'edit_address' ), anrhpub_account_url() ) . '#panel-addresses',
				'delivery_ok'
			);
		}
	}
}
add_action( 'init', 'anrhpub_handle_address_forms', 21 );
