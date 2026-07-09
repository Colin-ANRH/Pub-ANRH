<?php
/**
 * Formulaire contact / demande de devis + envoi e-mail.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_CONTACT_EMAIL_OPTION', 'anrhpub_contact_email' );

/**
 * E-mail destinataire des demandes.
 *
 * @return string
 */
function anrhpub_get_contact_email() {
	$email = get_option( ANRHPUB_CONTACT_EMAIL_OPTION, 'contact-peyruis@anrh.fr' );

	if ( ! is_email( $email ) ) {
		$email = get_option( 'admin_email' );
	}

	return sanitize_email( $email );
}

/**
 * Texte lisible de l’adresse de livraison pour devis.
 *
 * @param int $client_id Client ID.
 * @return string
 */
function anrhpub_get_delivery_address_summary( $client_id = 0 ) {
	$address = anrhpub_get_delivery_address( $client_id );

	if ( ! $address ) {
		return '';
	}

	return anrhpub_format_address_text( $address );
}

/**
 * Coordonnées client pour le message de devis.
 *
 * @param int $client_id Client ID.
 * @return array{name: string, email: string, phone: string, company: string}
 */
function anrhpub_get_devis_client_profile( $client_id = 0 ) {
	$client_id = $client_id ? (int) $client_id : anrhpub_get_client_user_id();
	$profile   = array(
		'name'    => '',
		'email'   => '',
		'phone'   => '',
		'company' => '',
	);

	if ( $client_id <= 0 ) {
		return $profile;
	}

	$user = get_userdata( $client_id );

	if ( $user ) {
		$profile['name']    = trim( $user->first_name . ' ' . $user->last_name ) ?: $user->display_name;
		$profile['email']   = $user->user_email;
		$profile['company'] = anrhpub_get_client_company( $client_id );
	}

	$address = anrhpub_get_delivery_address( $client_id );

	if ( $address && ! empty( $address['phone'] ) ) {
		$profile['phone'] = $address['phone'];
	}

	return $profile;
}

/**
 * Détail lisible d’une ligne produit (titre + puces quantité / couleur).
 *
 * @param array<string, mixed> $line Ligne enrichie panier.
 * @return array{title: string, details: array<int, string>}
 */
function anrhpub_get_devis_product_line_parts( $line ) {
	$title = (string) ( $line['title'] ?? '' );

	if ( ! empty( $line['ref'] ) ) {
		$title .= ' — ' . sprintf(
			/* translators: %s: product reference */
			__( 'réf. %s', 'anrhpub_theme' ),
			$line['ref']
		);
	}

	$qty     = max( 1, (int) ( $line['qty'] ?? 1 ) );
	$details = array(
		sprintf(
			/* translators: 1: quantity number, 2: unit label */
			__( 'Quantité : %1$d %2$s', 'anrhpub_theme' ),
			$qty,
			_n( 'unité', 'unités', $qty, 'anrhpub_theme' )
		),
	);

	if ( ! empty( $line['color_name'] ) ) {
		$details[] = sprintf(
			/* translators: %s: color name */
			__( 'Couleur : %s', 'anrhpub_theme' ),
			$line['color_name']
		);
	} else {
		$product_id = isset( $line['product_id'] ) ? (int) $line['product_id'] : 0;

		if ( $product_id > 0 && function_exists( 'anrhpub_product_has_colors' ) && anrhpub_product_has_colors( $product_id ) ) {
			$details[] = __( 'Couleur : à définir avec vous', 'anrhpub_theme' );
		}
	}

	$min_qty = isset( $line['min_qty'] ) ? (int) $line['min_qty'] : 0;

	if ( $min_qty > 1 ) {
		$details[] = sprintf(
			/* translators: %d: minimum order quantity */
			__( 'Quantité minimum catalogue : %d unités', 'anrhpub_theme' ),
			$min_qty
		);
	}

	return array(
		'title'   => $title,
		'details' => $details,
	);
}

/**
 * Message de devis structuré pour le formulaire contact.
 *
 * @param int        $client_id Client ID.
 * @param array|null $items     Lignes panier (sanitized).
 * @return string
 */
function anrhpub_build_devis_contact_message( $client_id = 0, $items = null ) {
	$client_id = $client_id ? (int) $client_id : anrhpub_get_client_user_id();
	$profile   = anrhpub_get_devis_client_profile( $client_id );

	if ( null === $items && $client_id > 0 ) {
		$items = anrhpub_get_user_quote_cart_raw( $client_id );
	}

	$items    = is_array( $items ) ? anrhpub_sanitize_quote_cart_items( $items ) : array();
	$products = ! empty( $items ) && function_exists( 'anrhpub_enrich_quote_cart_items' )
		? anrhpub_enrich_quote_cart_items( $items )
		: array();

	$lines   = array();
	$lines[] = __( 'Bonjour,', 'anrhpub_theme' );
	$lines[] = '';

	if ( ! empty( $products ) ) {
		$lines[] = __( 'Je vous contacte pour obtenir un devis sur la sélection d’objets publicitaires ci-dessous. Voici le détail de ma demande.', 'anrhpub_theme' );
	} else {
		$lines[] = __( 'Je vous contacte pour obtenir un devis sur des objets publicitaires. Mon panier est encore vide sur le site : pourriez-vous m’aider à finaliser ma sélection ?', 'anrhpub_theme' );
	}

	$lines[] = '';

	if ( $profile['name'] || $profile['email'] || $profile['company'] || $profile['phone'] ) {
		$lines[] = __( 'Mes coordonnées', 'anrhpub_theme' );

		if ( $profile['name'] ) {
			$lines[] = sprintf( __( 'Nom : %s', 'anrhpub_theme' ), $profile['name'] );
		}
		if ( $profile['company'] ) {
			$lines[] = sprintf( __( 'Société : %s', 'anrhpub_theme' ), $profile['company'] );
		}
		if ( $profile['email'] ) {
			$lines[] = sprintf( __( 'E-mail : %s', 'anrhpub_theme' ), $profile['email'] );
		}
		if ( $profile['phone'] ) {
			$lines[] = sprintf( __( 'Téléphone : %s', 'anrhpub_theme' ), $profile['phone'] );
		}

		$lines[] = '';
	}

	$lines[] = __( 'Adresse de livraison souhaitée', 'anrhpub_theme' );
	$address = $client_id > 0 ? anrhpub_get_delivery_address( $client_id ) : null;

	if ( $address ) {
		foreach ( preg_split( '/\r\n|\r|\n/', anrhpub_format_address_text( $address ) ) as $address_line ) {
			$address_line = trim( $address_line );
			if ( '' !== $address_line ) {
				$lines[] = $address_line;
			}
		}
	} else {
		$lines[] = __( 'Je n’ai pas encore renseigné d’adresse de livraison dans mon compte. Merci de me recontacter pour la valider avant l’établissement du devis.', 'anrhpub_theme' );
	}

	$lines[] = '';
	$lines[] = __( 'Marquage (logo / personnalisation)', 'anrhpub_theme' );

	$logo_url = $client_id > 0 ? anrhpub_get_client_brand_logo_url( $client_id ) : '';

	if ( $logo_url ) {
		$lines[] = __( 'Mon logo est enregistré sur mon compte client — lien pour téléchargement :', 'anrhpub_theme' );
		$lines[] = $logo_url;
		$lines[] = __( 'Si vous avez besoin d’un fichier vectoriel (AI, EPS, PDF), je peux vous l’envoyer par e-mail.', 'anrhpub_theme' );
	} else {
		$lines[] = __( 'Je n’ai pas encore déposé de logo sur mon compte. Indiquez-moi comment vous souhaitez le recevoir pour chiffrer le marquage.', 'anrhpub_theme' );
	}

	$lines[] = '';
	$lines[] = __( 'Détail des articles demandés', 'anrhpub_theme' );
	$lines[] = '';

	if ( ! empty( $products ) ) {
		$index = 1;

		foreach ( $products as $product_line ) {
			$parts = anrhpub_get_devis_product_line_parts( $product_line );
			$lines[] = $index . '. ' . $parts['title'];

			foreach ( $parts['details'] as $detail_line ) {
				$lines[] = '   • ' . $detail_line;
			}

			$lines[] = '';
			++$index;
		}

		array_pop( $lines );

		$lines[] = '';
		$lines[] = sprintf(
			/* translators: %d: total quantity */
			__( 'Total : %d unités sur l’ensemble de la commande.', 'anrhpub_theme' ),
			anrhpub_quote_cart_total_qty( $items )
		);
		$lines[] = sprintf(
			/* translators: %d: number of product lines */
			__( 'Nombre de références : %d.', 'anrhpub_theme' ),
			count( $products )
		);
	} else {
		$lines[] = __( 'Aucun article dans le panier pour le moment.', 'anrhpub_theme' );
	}

	$lines[] = '';
	$lines[] = __( 'Pourriez-vous me faire parvenir vos tarifs, les délais (fabrication et livraison) ainsi que les options de marquage disponibles (emplacements, couleurs, etc.) ?', 'anrhpub_theme' );
	$lines[] = '';
	$lines[] = __( 'Merci d’avance pour votre retour.', 'anrhpub_theme' );
	$lines[] = '';
	$lines[] = __( 'Cordialement,', 'anrhpub_theme' );

	if ( $profile['name'] ) {
		$lines[] = $profile['name'];
	}

	if ( $profile['company'] ) {
		$lines[] = $profile['company'];
	}

	return implode( "\n", $lines );
}

/**
 * Valeurs par défaut du formulaire.
 *
 * @return array<string, string>
 */
function anrhpub_get_contact_form_defaults() {
	$is_devis  = ! empty( $_GET['devis'] );
	$client_id = anrhpub_get_client_user_id();

	$defaults = array(
		'name'              => '',
		'email'             => '',
		'phone'             => '',
		'company'           => '',
		'subject'           => $is_devis ? __( 'Demande de devis catalogue', 'anrhpub_theme' ) : __( 'Message depuis le site', 'anrhpub_theme' ),
		'message'           => '',
		'delivery_summary'  => '',
		'brand_logo_url'    => '',
		'brand_logo_alt'    => '',
		'is_devis'          => $is_devis ? '1' : '',
	);

	if ( $client_id > 0 ) {
		$user = get_userdata( $client_id );

		if ( $user ) {
			$defaults['name']    = trim( $user->first_name . ' ' . $user->last_name ) ?: $user->display_name;
			$defaults['email']   = $user->user_email;
			$defaults['company'] = anrhpub_get_client_company( $client_id );
		}

		$address = anrhpub_get_delivery_address( $client_id );

		if ( $address && ! empty( $address['phone'] ) ) {
			$defaults['phone'] = $address['phone'];
		}

		$defaults['delivery_summary'] = anrhpub_get_delivery_address_summary( $client_id );
		$defaults['brand_logo_url']    = anrhpub_get_client_brand_logo_url( $client_id );
		$defaults['brand_logo_alt']    = $defaults['company']
			? sprintf( __( 'Logo %s', 'anrhpub_theme' ), $defaults['company'] )
			: __( 'Logo de mon entreprise', 'anrhpub_theme' );
	}

	if ( $is_devis ) {
		$defaults['message'] = anrhpub_build_devis_contact_message( $client_id );
	}

	return $defaults;
}

/**
 * Traitement envoi formulaire contact.
 */
function anrhpub_handle_contact_form() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		return;
	}

	if ( empty( $_POST['anrhpub_contact_submit'] ) ) {
		return;
	}

	if ( ! empty( $_POST['anrhpub_website'] ) ) {
		return;
	}

	check_admin_referer( 'anrhpub_contact_form' );

	$name    = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
	$email   = isset( $_POST['contact_email'] ) ? sanitize_email( wp_unslash( $_POST['contact_email'] ) ) : '';
	$phone   = isset( $_POST['contact_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_phone'] ) ) : '';
	$company = isset( $_POST['contact_company'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_company'] ) ) : '';
	$subject = isset( $_POST['contact_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_subject'] ) ) : '';
	$message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ) ) : '';
	$is_devis = ! empty( $_POST['contact_is_devis'] );

	if ( '' === $name || ! is_email( $email ) || '' === $message ) {
		$GLOBALS['anrhpub_contact_error'] = __( 'Veuillez remplir votre nom, un e-mail valide et votre message.', 'anrhpub_theme' );
		return;
	}

	if ( ! $subject ) {
		$subject = $is_devis
			? __( 'Demande de devis catalogue', 'anrhpub_theme' )
			: __( 'Message depuis le site', 'anrhpub_theme' );
	}

	$to      = anrhpub_get_contact_email();
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $name . ' <' . $email . '>',
	);

	$body_lines = array(
		$is_devis ? __( '=== DEMANDE DE DEVIS ===', 'anrhpub_theme' ) : __( '=== MESSAGE CONTACT ===', 'anrhpub_theme' ),
		'',
		sprintf( __( 'Nom : %s', 'anrhpub_theme' ), $name ),
		sprintf( __( 'E-mail : %s', 'anrhpub_theme' ), $email ),
	);

	if ( $phone ) {
		$body_lines[] = sprintf( __( 'Téléphone : %s', 'anrhpub_theme' ), $phone );
	}
	if ( $company ) {
		$body_lines[] = sprintf( __( 'Société : %s', 'anrhpub_theme' ), $company );
	}

	$client_id = function_exists( 'anrhpub_get_client_user_id' ) ? anrhpub_get_client_user_id() : 0;

	if ( $client_id > 0 ) {
		$body_lines[] = sprintf( __( 'Compte client n° %d', 'anrhpub_theme' ), $client_id );
		$logo_url = anrhpub_get_client_brand_logo_url( $client_id );
		if ( $logo_url ) {
			$body_lines[] = sprintf( __( 'Logo marque : %s', 'anrhpub_theme' ), $logo_url );
		}
		$delivery_block = isset( $_POST['contact_delivery_summary'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contact_delivery_summary'] ) ) : '';
		if ( $delivery_block ) {
			$body_lines[] = '';
			$body_lines[] = $delivery_block;
		}
		$logo_url_post = isset( $_POST['contact_brand_logo_url'] ) ? esc_url_raw( wp_unslash( $_POST['contact_brand_logo_url'] ) ) : '';
		if ( $logo_url_post ) {
			$body_lines[] = '';
			$body_lines[] = sprintf( __( 'Logo marque (pièce jointe visuelle) : %s', 'anrhpub_theme' ), $logo_url_post );
		}
	}

	$body_lines[] = '';
	$body_lines[] = __( 'Message :', 'anrhpub_theme' );
	$body_lines[] = $message;
	$body_lines[] = '';
	$body_lines[] = '— ' . home_url();

	$mail_subject = '[' . wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) . '] ' . $subject;
	$sent         = wp_mail( $to, $mail_subject, implode( "\n", $body_lines ), $headers );

	if ( $sent ) {
		if ( $is_devis && function_exists( 'anrhpub_on_devis_submitted' ) ) {
			$cart_items = array();
			if ( $client_id <= 0 && $email ) {
				$by_email = get_user_by( 'email', $email );
				if ( $by_email ) {
					$client_id = (int) $by_email->ID;
				}
			}
			if ( $client_id > 0 && function_exists( 'anrhpub_get_user_quote_cart_raw' ) ) {
				$cart_items = anrhpub_get_user_quote_cart_raw( $client_id );
			} elseif ( ! empty( $_POST['contact_cart_json'] ) ) {
				$decoded = json_decode( (string) wp_unslash( $_POST['contact_cart_json'] ), true );
				if ( is_array( $decoded ) && function_exists( 'anrhpub_sanitize_quote_cart_items' ) ) {
					$cart_items = anrhpub_sanitize_quote_cart_items( $decoded );
				}
			}

			if ( ! empty( $cart_items ) ) {
				$quote_id = anrhpub_on_devis_submitted( $client_id, $cart_items, $message );
				if ( $quote_id && $client_id > 0 ) {
					anrhpub_save_user_quote_cart( array(), $client_id );
				}
			}
		}

		$redirect_url = $is_devis && function_exists( 'anrhpub_quote_cart_url' )
			? add_query_arg( 'devis_sent', '1', anrhpub_quote_cart_url() )
			: add_query_arg( 'contact_sent', '1', home_url( '/contact/' ) );

		wp_safe_redirect( $redirect_url );
		exit;
	}

	$GLOBALS['anrhpub_contact_error'] = __( 'L’envoi a échoué. Réessayez ou contactez-nous par téléphone.', 'anrhpub_theme' );
}
add_action( 'init', 'anrhpub_handle_contact_form', 15 );

/**
 * Affiche le formulaire contact.
 */
function anrhpub_render_contact_form() {
	$defaults = anrhpub_get_contact_form_defaults();
	$error    = ! empty( $GLOBALS['anrhpub_contact_error'] ) ? (string) $GLOBALS['anrhpub_contact_error'] : '';
	$sent     = isset( $_GET['contact_sent'] ) && '1' === $_GET['contact_sent'];

	get_template_part(
		'template-parts/contact',
		'form',
		array(
			'defaults' => $defaults,
			'error'    => $error,
			'sent'     => $sent,
		)
	);
}

/**
 * Réglage e-mail contact (admin).
 */
function anrhpub_register_contact_settings() {
	register_setting(
		'general',
		ANRHPUB_CONTACT_EMAIL_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_email',
		)
	);

	add_settings_field(
		ANRHPUB_CONTACT_EMAIL_OPTION,
		__( 'E-mail demandes devis / contact', 'anrhpub_theme' ),
		function () {
			printf(
				'<input type="email" name="%1$s" id="%1$s" value="%2$s" class="regular-text" />',
				esc_attr( ANRHPUB_CONTACT_EMAIL_OPTION ),
				esc_attr( anrhpub_get_contact_email() )
			);
			echo '<p class="description">' . esc_html__( 'Boîte qui reçoit les formulaires du site (page Contact et panier devis).', 'anrhpub_theme' ) . '</p>';
		},
		'general'
	);
}
add_action( 'admin_init', 'anrhpub_register_contact_settings' );

/**
 * Données devis pour le JS (page contact).
 */
function anrhpub_enqueue_contact_form_assets() {
	if ( ! is_page( 'contact' ) ) {
		return;
	}

	$client_id = anrhpub_get_client_user_id();
	$items     = $client_id > 0 ? anrhpub_get_user_quote_cart_raw( $client_id ) : array();

	wp_localize_script(
		'anrhpub-main',
		'anrhpubContactDevis',
		array(
			'isDevis'          => ! empty( $_GET['devis'] ),
			'deliveryAddress'  => anrhpub_get_delivery_address_summary( $client_id ),
			'serverMessage'    => ! empty( $_GET['devis'] ) ? anrhpub_build_devis_contact_message( $client_id, $items ) : '',
			'clientProfile'    => anrhpub_get_devis_client_profile( $client_id ),
			'noDeliveryHint'   => __( 'Je n’ai pas encore renseigné d’adresse de livraison dans mon compte. Merci de me recontacter pour la valider avant l’établissement du devis.', 'anrhpub_theme' ),
			'brandLogoUrl'     => anrhpub_get_client_brand_logo_url( $client_id ),
			'brandLogoAlt'     => __( 'Logo de mon entreprise', 'anrhpub_theme' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_contact_form_assets', 45 );
