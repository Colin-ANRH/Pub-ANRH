<?php
/**
 * Panier devis — sélection produits + quantités (sans paiement en ligne).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_QUOTE_CART_META', 'anrhpub_quote_cart' );
define( 'ANRHPUB_QUOTE_CART_PAGES_VERSION', 1 );

/**
 * URL page panier devis.
 *
 * @return string
 */
function anrhpub_quote_cart_url() {
	return home_url( '/panier-devis/' );
}

/**
 * Crée la page panier si besoin.
 */
function anrhpub_ensure_quote_cart_page() {
	$existing = get_page_by_path( 'panier-devis' );

	if ( $existing ) {
		return (int) $existing->ID;
	}

	return (int) wp_insert_post(
		array(
			'post_title'   => __( 'Panier devis', 'anrhpub_theme' ),
			'post_name'    => 'panier-devis',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		)
	);
}

/**
 * Panier brut utilisateur connecté (meta).
 *
 * @param int $user_id User ID.
 * @return array<int, array{product_id: int, qty: int}>
 */
function anrhpub_get_user_quote_cart_raw( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	if ( $user_id <= 0 ) {
		return array();
	}

	$stored = get_user_meta( $user_id, ANRHPUB_QUOTE_CART_META, true );

	return anrhpub_sanitize_quote_cart_items( is_array( $stored ) ? $stored : array() );
}

/**
 * Enregistre le panier client.
 *
 * @param array $items Items.
 * @param int   $user_id User ID.
 */
function anrhpub_save_user_quote_cart( $items, $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	if ( $user_id <= 0 || ! anrhpub_user_has_client_role( $user_id ) ) {
		return;
	}

	update_user_meta( $user_id, ANRHPUB_QUOTE_CART_META, anrhpub_sanitize_quote_cart_items( $items ) );
}

/**
 * Quantité minimum de commande pour un produit (réglage WP).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function anrhpub_get_product_min_qty( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$min     = absint( get_post_meta( $post_id, 'anr_min_qty', true ) );

	if ( $min < 1 ) {
		$min = 1;
	}

	return min( 99999, $min );
}

/**
 * Ajuste une quantité au minimum produit.
 *
 * @param int $product_id Produit.
 * @param int $qty        Quantité demandée.
 * @return int
 */
function anrhpub_clamp_quote_qty( $product_id, $qty ) {
	$qty = max( 1, min( 99999, absint( $qty ) ) );

	return max( anrhpub_get_product_min_qty( $product_id ), $qty );
}

/**
 * Nettoie les lignes panier.
 *
 * @param array $items Raw items.
 * @return array<int, array{product_id: int, qty: int, color_id: int}>
 */
function anrhpub_sanitize_quote_cart_items( $items ) {
	$clean = array();
	$seen  = array();

	if ( ! is_array( $items ) ) {
		return $clean;
	}

	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$product_id = isset( $item['product_id'] ) ? absint( $item['product_id'] ) : 0;
		$color_id   = isset( $item['color_id'] ) ? absint( $item['color_id'] ) : 0;
		$qty        = isset( $item['qty'] ) ? absint( $item['qty'] ) : 0;

		if ( $product_id <= 0 || 'anr_product' !== get_post_type( $product_id ) ) {
			continue;
		}

		if ( 'publish' !== get_post_status( $product_id ) ) {
			continue;
		}

		$color_id = anrhpub_validate_product_color( $product_id, $color_id );

		if ( anrhpub_product_has_colors( $product_id ) && $color_id <= 0 ) {
			continue;
		}

		$line_key = anrhpub_quote_cart_line_key( $product_id, $color_id );

		if ( isset( $seen[ $line_key ] ) ) {
			$merged = $clean[ $seen[ $line_key ] ]['qty'] + max( 1, $qty );
			$clean[ $seen[ $line_key ] ]['qty'] = anrhpub_clamp_quote_qty_for_color( $product_id, $color_id, $merged );
			continue;
		}

		$clean[] = array(
			'product_id' => $product_id,
			'color_id'   => $color_id,
			'qty'        => anrhpub_clamp_quote_qty_for_color( $product_id, $color_id, $qty ),
		);

		$seen[ $line_key ] = count( $clean ) - 1;
	}

	return array_values( $clean );
}

/**
 * Enrichit le panier pour affichage.
 *
 * @param array $items Sanitized items.
 * @return array<int, array<string, mixed>>
 */
function anrhpub_enrich_quote_cart_items( $items ) {
	$lines = array();

	foreach ( $items as $item ) {
		$product_id = (int) $item['product_id'];
		$post       = get_post( $product_id );

		if ( ! $post ) {
			continue;
		}

		$min_qty   = anrhpub_get_product_min_qty( $product_id );
		$color_id  = isset( $item['color_id'] ) ? (int) $item['color_id'] : 0;
		$color_hex = $color_id ? anrhpub_get_color_hex( $color_id ) : '';
		$max_qty   = anrhpub_get_quote_max_qty_for_color( $product_id, $color_id );

		$lines[] = array(
			'product_id'  => $product_id,
			'color_id'    => $color_id,
			'color_name'  => $color_id ? anrhpub_get_color_name( $color_id ) : '',
			'color_hex'   => $color_hex,
			'color_stock' => $color_id ? anrhpub_get_product_color_stock( $product_id, $color_id ) : 0,
			'qty'         => (int) $item['qty'],
			'min_qty'     => $min_qty,
			'max_qty'     => $max_qty,
			'title'       => get_the_title( $product_id ),
			'ref'         => get_post_meta( $product_id, 'anr_reference', true ),
			'url'         => get_permalink( $product_id ),
			'thumb'       => get_the_post_thumbnail_url( $product_id, 'thumbnail' ),
			'price_label' => get_post_meta( $product_id, 'anr_price_label', true ) ?: __( 'Sur devis', 'anrhpub_theme' ),
		);
	}

	return $lines;
}

/**
 * Nombre d’articles (lignes) dans le panier.
 *
 * @param array $items Items.
 * @return int
 */
function anrhpub_quote_cart_count( $items ) {
	return count( anrhpub_sanitize_quote_cart_items( $items ) );
}

/**
 * Quantité totale.
 *
 * @param array $items Items.
 * @return int
 */
function anrhpub_quote_cart_total_qty( $items ) {
	$total = 0;

	foreach ( anrhpub_sanitize_quote_cart_items( $items ) as $item ) {
		$total += (int) $item['qty'];
	}

	return $total;
}

/**
 * Texte récapitulatif pour e-mail / contact.
 *
 * @param array $items Sanitized items.
 * @return string
 */
function anrhpub_quote_cart_summary_text( $items ) {
	$lines = array(
		__( 'Demande de devis — sélection catalogue :', 'anrhpub_theme' ),
		'',
	);

	foreach ( anrhpub_enrich_quote_cart_items( $items ) as $line ) {
		$parts   = anrhpub_get_devis_product_line_parts( $line );
		$lines[] = '- ' . $parts['title'];
		foreach ( $parts['details'] as $detail_line ) {
			$lines[] = '  • ' . $detail_line;
		}
	}

	$lines[] = '';
	$lines[] = sprintf(
		/* translators: %d: total quantity */
		__( 'Quantité totale : %d', 'anrhpub_theme' ),
		anrhpub_quote_cart_total_qty( $items )
	);

	return implode( "\n", $lines );
}

/**
 * URL contact avec récap panier.
 *
 * @param array $items Items.
 * @return string
 */
function anrhpub_quote_cart_contact_url( $items = array() ) {
	return add_query_arg(
		array(
			'devis' => 'panier',
		),
		home_url( '/contact/' )
	);
}

/**
 * Affiche les sections fiche produit (description, caractéristiques, technique, réassurance).
 *
 * @param int $post_id Post ID.
 */
function anrhpub_render_product_descriptions( $post_id = 0 ) {
	if ( function_exists( 'anrhpub_render_product_single_sections' ) ) {
		anrhpub_render_product_single_sections( $post_id );
	}
}

/**
 * Formulaire quantité + ajout panier (fiche produit).
 *
 * @param int $post_id Post ID.
 */
function anrhpub_render_product_quote_form( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$min_qty      = anrhpub_get_product_min_qty( $post_id );
	$has_colors   = anrhpub_product_has_colors( $post_id );
	$first_colors = $has_colors ? anrhpub_get_product_colors( $post_id ) : array();
	$initial_max  = 99999;
	?>
	<div id="product-quote" class="product-quote" data-product-quote data-product-id="<?php echo esc_attr( (string) $post_id ); ?>" data-quote-min-qty="<?php echo esc_attr( (string) $min_qty ); ?>" data-quote-max-qty="<?php echo esc_attr( (string) $initial_max ); ?>" data-requires-color="<?php echo $has_colors ? '1' : '0'; ?>">
		<?php anrhpub_render_product_color_picker( $post_id ); ?>
		<div class="product-quote__qty-block">
			<div class="product-quote__qty-head">
				<label class="product-quote__label" for="quote-qty-<?php echo esc_attr( (string) $post_id ); ?>"><?php esc_html_e( 'Quantité pour votre devis', 'anrhpub_theme' ); ?></label>
				<span class="product-quote__qty-min-badge" data-quote-min-badge <?php echo $min_qty > 1 ? '' : 'hidden'; ?>>
					<?php
					printf(
						/* translators: %d: minimum quantity */
						esc_html__( 'Min. %d', 'anrhpub_theme' ),
						(int) $min_qty
					);
					?>
				</span>
			</div>
			<?php if ( $min_qty > 1 ) : ?>
				<p class="product-quote__min" data-quote-min-notice>
					<?php
					printf(
						/* translators: %d: minimum quantity */
						esc_html__( 'Quantité minimum de commande : %d unités.', 'anrhpub_theme' ),
						(int) $min_qty
					);
					?>
				</p>
			<?php endif; ?>
			<div class="product-quote__row">
				<div class="product-quote__qty" data-quote-qty-control>
					<button type="button" class="product-quote__qty-btn" data-quote-qty-minus aria-label="<?php esc_attr_e( 'Diminuer la quantité', 'anrhpub_theme' ); ?>" disabled>−</button>
					<input
						type="number"
						id="quote-qty-<?php echo esc_attr( (string) $post_id ); ?>"
						class="product-quote__qty-input"
						data-quote-qty-input
						data-quote-min-qty="<?php echo esc_attr( (string) $min_qty ); ?>"
						min="<?php echo esc_attr( (string) $min_qty ); ?>"
						max="<?php echo esc_attr( (string) max( $min_qty, $initial_max ) ); ?>"
						step="1"
						value="<?php echo esc_attr( (string) $min_qty ); ?>"
						data-quote-max-qty="<?php echo esc_attr( (string) $initial_max ); ?>"
						inputmode="numeric"
						aria-describedby="<?php echo $min_qty > 1 ? esc_attr( 'quote-qty-hint-' . $post_id ) : ''; ?>"
					/>
					<button type="button" class="product-quote__qty-btn" data-quote-qty-plus aria-label="<?php esc_attr_e( 'Augmenter la quantité', 'anrhpub_theme' ); ?>">+</button>
				</div>
				<button type="button" class="btn btn--primary product-quote__add" data-quote-add>
					<?php esc_html_e( 'Ajouter au panier', 'anrhpub_theme' ); ?>
				</button>
			</div>
			<?php if ( $min_qty > 1 ) : ?>
				<p class="product-quote__qty-hint" id="quote-qty-hint-<?php echo esc_attr( (string) $post_id ); ?>">
					<?php esc_html_e( 'Le sélecteur ne peut pas descendre en dessous de cette quantité minimum.', 'anrhpub_theme' ); ?>
				</p>
			<?php endif; ?>
		</div>
		<p class="product-quote__hint">
			<?php esc_html_e( 'Pas de paiement en ligne : ajoutez vos produits au panier, puis envoyez votre demande de devis depuis le panier.', 'anrhpub_theme' ); ?>
			<a href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>"><?php esc_html_e( 'Voir mon panier', 'anrhpub_theme' ); ?></a>
		</p>
	</div>
	<?php
}

/**
 * AJAX — synchroniser panier client connecté.
 */
function anrhpub_ajax_sync_quote_cart() {
	check_ajax_referer( 'anrhpub_quote_cart', 'nonce' );

	$raw = isset( $_POST['cart'] ) ? wp_unslash( $_POST['cart'] ) : '[]';
	$decoded = json_decode( $raw, true );

	if ( ! is_array( $decoded ) ) {
		wp_send_json_error( array( 'message' => __( 'Panier invalide.', 'anrhpub_theme' ) ), 400 );
	}

	$items = anrhpub_sanitize_quote_cart_items( $decoded );

	if ( anrhpub_is_client_logged_in() ) {
		anrhpub_save_user_quote_cart( $items );
	}

	$client_id     = function_exists( 'anrhpub_get_client_user_id' ) ? anrhpub_get_client_user_id() : 0;
	$product_lines = array();

	foreach ( anrhpub_enrich_quote_cart_items( $items ) as $line ) {
		$product_id = (int) $line['product_id'];
		$product_lines[] = array(
			'product_id' => $product_id,
			'title'      => $line['title'],
			'ref'        => $line['ref'],
			'color_name' => $line['color_name'],
			'qty'        => (int) $line['qty'],
			'min_qty'    => (int) $line['min_qty'],
			'has_colors' => function_exists( 'anrhpub_product_has_colors' ) && anrhpub_product_has_colors( $product_id ),
		);
	}

	$devis_message = '';
	if ( function_exists( 'anrhpub_build_devis_contact_message' ) ) {
		$devis_message = anrhpub_build_devis_contact_message( $client_id, $items );
	}

	wp_send_json_success(
		array(
			'items'         => $items,
			'count'         => anrhpub_quote_cart_count( $items ),
			'total_qty'     => anrhpub_quote_cart_total_qty( $items ),
			'html'          => anrhpub_get_quote_cart_html( $items ),
			'summary'       => anrhpub_quote_cart_summary_text( $items ),
			'product_lines' => $product_lines,
			'devis_message' => $devis_message,
		)
	);
}
add_action( 'wp_ajax_anrhpub_sync_quote_cart', 'anrhpub_ajax_sync_quote_cart' );
add_action( 'wp_ajax_nopriv_anrhpub_sync_quote_cart', 'anrhpub_ajax_sync_quote_cart' );

/**
 * HTML tableau panier.
 *
 * @param array $items Items.
 * @return string
 */
function anrhpub_get_quote_cart_html( $items ) {
	ob_start();
	anrhpub_render_quote_cart_table( anrhpub_sanitize_quote_cart_items( $items ) );
	return ob_get_clean();
}

/**
 * Affiche le panier.
 *
 * @param array $items Items.
 */
function anrhpub_render_quote_cart_table( $items ) {
	$items = anrhpub_sanitize_quote_cart_items( $items );
	$lines = anrhpub_enrich_quote_cart_items( $items );

	if ( empty( $lines ) ) {
		?>
		<div class="quote-cart-empty" data-quote-cart-empty>
			<p><?php esc_html_e( 'Votre panier devis est vide.', 'anrhpub_theme' ); ?></p>
			<a class="btn btn--primary" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>"><?php esc_html_e( 'Parcourir le catalogue', 'anrhpub_theme' ); ?></a>
		</div>
		<?php
		return;
	}
	?>
	<div class="quote-cart-table-wrap" data-quote-cart-table>
		<table class="quote-cart-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Produit', 'anrhpub_theme' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Réf.', 'anrhpub_theme' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Couleur', 'anrhpub_theme' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Quantité', 'anrhpub_theme' ); ?></th>
					<th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Actions', 'anrhpub_theme' ); ?></span></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $lines as $line ) : ?>
					<tr data-quote-line data-product-id="<?php echo esc_attr( (string) $line['product_id'] ); ?>" data-color-id="<?php echo esc_attr( (string) $line['color_id'] ); ?>" data-quote-min-qty="<?php echo esc_attr( (string) $line['min_qty'] ); ?>" data-quote-max-qty="<?php echo esc_attr( (string) $line['max_qty'] ); ?>">
						<td class="quote-cart-table__product">
							<?php if ( $line['thumb'] ) : ?>
								<img src="<?php echo esc_url( $line['thumb'] ); ?>" alt="" width="48" height="48" loading="lazy" />
							<?php endif; ?>
							<a href="<?php echo esc_url( $line['url'] ); ?>"><?php echo esc_html( $line['title'] ); ?></a>
						</td>
						<td><?php echo $line['ref'] ? esc_html( $line['ref'] ) : '—'; ?></td>
						<td class="quote-cart-table__color">
							<?php if ( $line['color_name'] ) : ?>
								<span class="quote-cart-table__color-swatch" style="background-color:<?php echo esc_attr( $line['color_hex'] ); ?>;" title="<?php echo esc_attr( $line['color_name'] ); ?>"></span>
								<span><?php echo esc_html( $line['color_name'] ); ?></span>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
						<td class="quote-cart-table__qty-cell">
							<input
								type="number"
								class="quote-cart-table__qty"
								data-quote-line-qty
								min="<?php echo esc_attr( (string) $line['min_qty'] ); ?>"
								max="<?php echo esc_attr( (string) $line['max_qty'] ); ?>"
								value="<?php echo esc_attr( (string) $line['qty'] ); ?>"
								data-quote-max-qty="<?php echo esc_attr( (string) $line['max_qty'] ); ?>"
								aria-label="<?php esc_attr_e( 'Quantité', 'anrhpub_theme' ); ?>"
							/>
							<?php if ( (int) $line['min_qty'] > 1 ) : ?>
								<span class="quote-cart-table__min"><?php printf( esc_html__( 'min. %d', 'anrhpub_theme' ), (int) $line['min_qty'] ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<button type="button" class="quote-cart-table__remove" data-quote-line-remove aria-label="<?php esc_attr_e( 'Retirer du panier', 'anrhpub_theme' ); ?>">×</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p class="quote-cart-table__total">
			<?php
			printf(
				/* translators: 1: line count, 2: total quantity */
				esc_html__( '%1$d référence(s) — %2$d unité(s) au total', 'anrhpub_theme' ),
				count( $lines ),
				anrhpub_quote_cart_total_qty( $items )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Bloc récap contact (page contact).
 */
function anrhpub_render_contact_devis_notice() {
	?>
	<div class="quote-cart-contact-notice" id="quote-cart-contact-notice" hidden>
		<h2><?php esc_html_e( 'Votre sélection de devis', 'anrhpub_theme' ); ?></h2>
		<pre class="quote-cart-contact-notice__text" data-quote-contact-summary></pre>
		<p class="quote-cart-contact-notice__hint"><?php esc_html_e( 'Communiquez ce récapitulatif par téléphone ou par e-mail à notre équipe.', 'anrhpub_theme' ); ?></p>
	</div>
	<?php
}

/**
 * Scripts & données panier.
 */
function anrhpub_enqueue_quote_cart_assets() {
	$server_cart = array();

	if ( anrhpub_is_client_logged_in() ) {
		$server_cart = anrhpub_get_user_quote_cart_raw();
	}

	$quote_deps = array( 'anrhpub-charte' );
	if ( wp_style_is( 'anrhpub-b2b', 'enqueued' ) ) {
		$quote_deps[] = 'anrhpub-b2b';
	} elseif ( wp_style_is( 'anrhpub-pages', 'enqueued' ) ) {
		$quote_deps[] = 'anrhpub-pages';
	}

	wp_enqueue_style(
		'anrhpub-quote-cart',
		ANRHPUB_THEME_URI . '/assets/css/quote-cart.css',
		$quote_deps,
		ANRHPUB_THEME_VERSION
	);

	wp_localize_script(
		'anrhpub-main',
		'anrhpubQuoteCart',
		array(
			'storageKey'   => 'anrhpub_quote_cart',
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'anrhpub_quote_cart' ),
			'cartUrl'       => anrhpub_quote_cart_url(),
			'catalogueUrl'  => anrhpub_catalogue_url(),
			'contactUrl'    => home_url( '/contact/' ),
			'phone'        => '0492612713',
			'phoneDisplay' => '04 92 61 27 13',
			'serverCart'   => $server_cart,
			'syncOnLoad'   => anrhpub_is_client_logged_in(),
			'i18n'         => array(
				'added'       => __( 'Produit ajouté au panier.', 'anrhpub_theme' ),
				'updated'     => __( 'Panier mis à jour.', 'anrhpub_theme' ),
				'removed'     => __( 'Produit retiré du panier.', 'anrhpub_theme' ),
				'empty'       => __( 'Votre panier devis est vide.', 'anrhpub_theme' ),
				'error'       => __( 'Impossible de mettre à jour le panier.', 'anrhpub_theme' ),
				'qtyInvalid'  => __( 'Quantité invalide (minimum 1).', 'anrhpub_theme' ),
				'copied'      => __( 'Récapitulatif copié.', 'anrhpub_theme' ),
				'minQty'      => __( 'Quantité minimum pour ce produit : %d unités.', 'anrhpub_theme' ),
				'qtyAdjusted' => __( 'La quantité a été ajustée au minimum de commande.', 'anrhpub_theme' ),
				'colorRequired' => __( 'Veuillez choisir une couleur pour ce produit.', 'anrhpub_theme' ),
				'stockExceeded' => __( 'Quantité supérieure au stock disponible pour cette couleur.', 'anrhpub_theme' ),
				'stockCapped'     => __( 'La quantité a été limitée au stock disponible.', 'anrhpub_theme' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_quote_cart_assets', 40 );

/**
 * Init panier devis.
 */
function anrhpub_init_quote_cart() {
	if ( (int) get_option( 'anrhpub_quote_cart_page_version', 0 ) < ANRHPUB_QUOTE_CART_PAGES_VERSION ) {
		anrhpub_ensure_quote_cart_page();
		update_option( 'anrhpub_quote_cart_page_version', ANRHPUB_QUOTE_CART_PAGES_VERSION );
	}
}
add_action( 'init', 'anrhpub_init_quote_cart', 14 );
