<?php
/**
 * Admin — tarifs produits & prix négociés client.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta box tarifs.
 */
function anrhpub_product_pricing_meta_box() {
	add_meta_box(
		'anrhpub_product_pricing',
		__( 'Tarification B2B', 'anrhpub_theme' ),
		'anrhpub_product_pricing_meta_box_render',
		'anr_product',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_product_pricing_meta_box' );

/**
 * Rendu meta box tarifs.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_product_pricing_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_product_pricing', 'anrhpub_product_pricing_nonce' );

	$ht    = get_post_meta( $post->ID, ANRHPUB_PRICE_HT_META, true );
	$tiers = anrhpub_get_product_price_tiers( $post->ID );
	$lines = '';

	foreach ( $tiers as $tier ) {
		$lines .= $tier['min'] . ';' . $tier['price'] . "\n";
	}
	?>
	<p>
		<label for="anr_price_ht"><strong><?php esc_html_e( 'Prix unitaire HT (base)', 'anrhpub_theme' ); ?></strong></label><br>
		<input type="number" step="0.0001" min="0" name="anr_price_ht" id="anr_price_ht" value="<?php echo esc_attr( (string) $ht ); ?>" style="width:100%;margin-top:0.35rem;" />
	</p>
	<p>
		<label for="anr_price_tiers"><strong><?php esc_html_e( 'Grille quantité (min;prix HT par ligne)', 'anrhpub_theme' ); ?></strong></label>
		<textarea name="anr_price_tiers" id="anr_price_tiers" rows="6" style="width:100%;margin-top:0.35rem;font-family:monospace;font-size:12px;"><?php echo esc_textarea( trim( $lines ) ); ?></textarea>
	</p>
	<p class="description"><?php esc_html_e( 'Ex. : 50;1.85 puis 100;1.60 — visible uniquement pour les comptes validés.', 'anrhpub_theme' ); ?></p>
	<?php
}

/**
 * Sauvegarde tarifs produit.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_save_product_pricing_meta( $post_id ) {
	if ( ! isset( $_POST['anrhpub_product_pricing_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_product_pricing_nonce'] ) ), 'anrhpub_save_product_pricing' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['anr_price_ht'] ) ) {
		$ht = wp_unslash( $_POST['anr_price_ht'] );
		update_post_meta( $post_id, ANRHPUB_PRICE_HT_META, '' === $ht ? '' : (float) $ht );
	}

	$tiers = array();
	if ( isset( $_POST['anr_price_tiers'] ) ) {
		$raw_lines = preg_split( '/\r\n|\r|\n/', (string) wp_unslash( $_POST['anr_price_tiers'] ) );
		foreach ( $raw_lines as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts = preg_split( '/[;,]/', $line );
			if ( count( $parts ) >= 2 ) {
				$tiers[] = array(
					'min'   => absint( $parts[0] ),
					'price' => (float) $parts[1],
				);
			}
		}
	}

	update_post_meta( $post_id, ANRHPUB_PRICE_TIERS_META, wp_json_encode( $tiers ) );
}
add_action( 'save_post_anr_product', 'anrhpub_save_product_pricing_meta' );

/**
 * Prix négociés par produit (profil client admin).
 *
 * @param WP_User $user User.
 */
function anrhpub_user_client_prices_field( $user ) {
	if ( ! current_user_can( 'edit_users' ) || ! in_array( ANRHPUB_CLIENT_ROLE, (array) $user->roles, true ) ) {
		return;
	}

	$stored = get_user_meta( $user->ID, ANRHPUB_CLIENT_PRICES_META, true );
	$lines  = '';

	if ( is_array( $stored ) ) {
		foreach ( $stored as $product_id => $price ) {
			$lines .= (int) $product_id . ';' . (float) $price . "\n";
		}
	}
	?>
	<h2><?php esc_html_e( 'Tarifs négociés (ID produit ; prix HT)', 'anrhpub_theme' ); ?></h2>
	<p class="description"><?php esc_html_e( 'Une ligne par produit. Prioritaire sur la grille catalogue.', 'anrhpub_theme' ); ?></p>
	<textarea name="anrhpub_client_prices" rows="8" class="large-text code"><?php echo esc_textarea( trim( $lines ) ); ?></textarea>
	<?php
}
add_action( 'show_user_profile', 'anrhpub_user_client_prices_field', 15 );
add_action( 'edit_user_profile', 'anrhpub_user_client_prices_field', 15 );

/**
 * Sauvegarde tarifs négociés.
 *
 * @param int $user_id User ID.
 */
function anrhpub_save_user_client_prices_field( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['anrhpub_client_prices'] ) ) {
		return;
	}

	$map   = array();
	$lines = preg_split( '/\r\n|\r|\n/', (string) wp_unslash( $_POST['anrhpub_client_prices'] ) );

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = preg_split( '/[;,]/', $line );
		if ( count( $parts ) >= 2 ) {
			$pid = absint( $parts[0] );
			if ( $pid > 0 && 'anr_product' === get_post_type( $pid ) ) {
				$map[ $pid ] = (float) $parts[1];
			}
		}
	}

	update_user_meta( $user_id, ANRHPUB_CLIENT_PRICES_META, $map );
}
add_action( 'personal_options_update', 'anrhpub_save_user_client_prices_field' );
add_action( 'edit_user_profile_update', 'anrhpub_save_user_client_prices_field' );

/**
 * Réglage taux TVA.
 */
function anrhpub_register_vat_settings() {
	register_setting( 'general', ANRHPUB_VAT_RATE_OPTION, array(
		'type'              => 'number',
		'sanitize_callback' => function ( $v ) {
			return max( 0, min( 100, (float) $v ) );
		},
		'default'           => 20,
	) );

	add_settings_field(
		ANRHPUB_VAT_RATE_OPTION,
		__( 'Taux TVA catalogue (%)', 'anrhpub_theme' ),
		function () {
			$val = anrhpub_get_vat_rate();
			echo '<input type="number" step="0.1" min="0" max="100" name="' . esc_attr( ANRHPUB_VAT_RATE_OPTION ) . '" value="' . esc_attr( (string) $val ) . '" />';
		},
		'general'
	);
}
add_action( 'admin_init', 'anrhpub_register_vat_settings' );
