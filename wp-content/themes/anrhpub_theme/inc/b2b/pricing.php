<?php
/**
 * Tarification B2B — grilles, tarifs client, HT/TTC.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_PRICE_HT_META', 'anr_price_ht' );
define( 'ANRHPUB_PRICE_TIERS_META', 'anr_price_tiers' );
define( 'ANRHPUB_CLIENT_PRICES_META', 'anrhpub_client_product_prices' );
define( 'ANRHPUB_VAT_RATE_OPTION', 'anrhpub_vat_rate' );

/**
 * Taux TVA site (%).
 *
 * @return float
 */
function anrhpub_get_vat_rate() {
	return max( 0, (float) get_option( ANRHPUB_VAT_RATE_OPTION, 20 ) );
}

/**
 * Grille tarifaire produit décodée.
 *
 * @param int $post_id Post ID.
 * @return array<int, array{min: int, price: float}>
 */
function anrhpub_get_product_price_tiers( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$raw     = get_post_meta( $post_id, ANRHPUB_PRICE_TIERS_META, true );
	$tiers   = array();

	if ( is_string( $raw ) && '' !== $raw ) {
		$decoded = json_decode( $raw, true );
		$raw     = is_array( $decoded ) ? $decoded : array();
	}

	if ( ! is_array( $raw ) ) {
		return array();
	}

	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$min   = isset( $row['min'] ) ? absint( $row['min'] ) : 0;
		$price = isset( $row['price'] ) ? (float) $row['price'] : 0;
		if ( $min > 0 && $price > 0 ) {
			$tiers[] = array(
				'min'   => $min,
				'price' => $price,
			);
		}
	}

	usort(
		$tiers,
		function ( $a, $b ) {
			return $a['min'] <=> $b['min'];
		}
	);

	return $tiers;
}

/**
 * Prix HT unitaire pour une quantité.
 *
 * @param int $post_id Post ID.
 * @param int $qty     Quantity.
 * @param int $user_id User ID.
 * @return float|null Null si pas de prix.
 */
function anrhpub_get_unit_price_ht( $post_id, $qty = 1, $user_id = 0 ) {
	$post_id = (int) $post_id;
	$qty     = max( 1, (int) $qty );
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	$client_prices = array();
	if ( $user_id > 0 ) {
		$stored = get_user_meta( $user_id, ANRHPUB_CLIENT_PRICES_META, true );
		if ( is_array( $stored ) && isset( $stored[ $post_id ] ) ) {
			$custom = (float) $stored[ $post_id ];
			if ( $custom > 0 ) {
				return $custom;
			}
		}
	}

	$tiers = anrhpub_get_product_price_tiers( $post_id );
	$price = null;

	foreach ( $tiers as $tier ) {
		if ( $qty >= (int) $tier['min'] ) {
			$price = (float) $tier['price'];
		}
	}

	if ( null === $price ) {
		$base = get_post_meta( $post_id, ANRHPUB_PRICE_HT_META, true );
		$price = ( '' !== $base && null !== $base ) ? (float) $base : null;
	}

	if ( null === $price || $price <= 0 ) {
		return null;
	}

	if ( $user_id > 0 ) {
		$disc = (float) get_user_meta( $user_id, ANRHPUB_DISCOUNT_META, true );
		if ( $disc > 0 ) {
			$price = $price * ( 1 - ( min( 100, $disc ) / 100 ) );
		}
	}

	return round( $price, 4 );
}

/**
 * Prix TTC unitaire.
 *
 * @param int $post_id Post ID.
 * @param int $qty     Qty.
 * @param int $user_id User ID.
 * @return float|null
 */
function anrhpub_get_unit_price_ttc( $post_id, $qty = 1, $user_id = 0 ) {
	$ht = anrhpub_get_unit_price_ht( $post_id, $qty, $user_id );

	if ( null === $ht ) {
		return null;
	}

	return round( $ht * ( 1 + anrhpub_get_vat_rate() / 100 ), 2 );
}

/**
 * Libellé prix affiché (fiche / carte).
 *
 * @param int $post_id Post ID.
 * @param int $qty     Qty.
 * @return string
 */
function anrhpub_get_product_price_label( $post_id = 0, $qty = 1 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! anrhpub_can_view_prices() ) {
		return __( 'Sur devis — connectez-vous (compte validé)', 'anrhpub_theme' );
	}

	$ht = anrhpub_get_unit_price_ht( $post_id, $qty );

	if ( null === $ht ) {
		$legacy = get_post_meta( $post_id, 'anr_price_label', true );
		return $legacy ? (string) $legacy : __( 'Sur devis', 'anrhpub_theme' );
	}

	$ttc = anrhpub_get_unit_price_ttc( $post_id, $qty );
	$vat = anrhpub_get_vat_rate();

	return sprintf(
		/* translators: 1: HT price, 2: TTC price, 3: VAT rate */
		__( '%1$s € HT — %2$s € TTC (TVA %3$s%%)', 'anrhpub_theme' ),
		number_format_i18n( $ht, 2 ),
		number_format_i18n( $ttc, 2 ),
		number_format_i18n( $vat, 1 )
	);
}

/**
 * Conditions paiement client.
 *
 * @param int $user_id User ID.
 * @return string
 */
function anrhpub_get_client_payment_terms_label( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();
	$terms   = (string) get_user_meta( $user_id, ANRHPUB_PAYMENT_TERMS_META, true );

	return $terms ? $terms : __( '30 jours fin de mois (par défaut)', 'anrhpub_theme' );
}

/**
 * Affichage prix enrichi (hook template-tags).
 *
 * @param int $post_id Post ID.
 */
function anrhpub_product_price_b2b_extras( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( anrhpub_can_view_prices() && anrhpub_get_product_price_tiers( $post_id ) ) {
		echo '<span class="product-card__price-tiers-hint">' . esc_html__( 'Prix dégressifs selon quantité', 'anrhpub_theme' ) . '</span>';
	}
}
