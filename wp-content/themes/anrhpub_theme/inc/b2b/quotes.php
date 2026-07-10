<?php
/**
 * Devis clients — historique, brouillons, PDF.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_QUOTE_CPT', 'anr_quote' );

define( 'ANRHPUB_QUOTE_STATUSES', array(
	'draft'    => __( 'Brouillon', 'anrhpub_theme' ),
	'pending'  => __( 'En attente', 'anrhpub_theme' ),
	'accepted' => __( 'Accepté', 'anrhpub_theme' ),
	'rejected' => __( 'Refusé', 'anrhpub_theme' ),
	'expired'  => __( 'Expiré', 'anrhpub_theme' ),
) );

/**
 * Enregistrement CPT devis.
 */
function anrhpub_register_quote_cpt() {
	register_post_type(
		ANRHPUB_QUOTE_CPT,
		array(
			'labels'              => array(
				'name'          => __( 'Devis', 'anrhpub_theme' ),
				'singular_name' => __( 'Devis', 'anrhpub_theme' ),
				'menu_name'     => __( 'Devis clients', 'anrhpub_theme' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=anr_product',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
		)
	);
}
add_action( 'init', 'anrhpub_register_quote_cpt', 12 );

/**
 * Meta devis.
 *
 * @param int    $quote_id Quote ID.
 * @param string $key      Key.
 * @param mixed  $default  Default.
 * @return mixed
 */
function anrhpub_get_quote_meta( $quote_id, $key, $default = '' ) {
	$val = get_post_meta( (int) $quote_id, 'anr_quote_' . $key, true );

	return ( '' === $val || null === $val ) ? $default : $val;
}

/**
 * Met à jour meta devis.
 *
 * @param int    $quote_id Quote ID.
 * @param string $key      Key.
 * @param mixed  $value    Value.
 */
function anrhpub_update_quote_meta( $quote_id, $key, $value ) {
	update_post_meta( (int) $quote_id, 'anr_quote_' . $key, $value );
}

/**
 * Libellé statut devis.
 *
 * @param string $status Status.
 * @return string
 */
function anrhpub_get_quote_status_label( $status ) {
	return ANRHPUB_QUOTE_STATUSES[ $status ] ?? $status;
}

/**
 * Génère numéro devis.
 *
 * @return string
 */
function anrhpub_generate_quote_number() {
	return 'DEV-' . gmdate( 'Ymd' ) . '-' . wp_rand( 1000, 9999 );
}

/**
 * Lignes devis.
 *
 * @param int $quote_id Quote ID.
 * @return array
 */
function anrhpub_get_quote_lines( $quote_id ) {
	$raw = anrhpub_get_quote_meta( $quote_id, 'lines', '' );

	if ( is_array( $raw ) ) {
		return $raw;
	}

	$decoded = is_string( $raw ) ? json_decode( $raw, true ) : array();

	return is_array( $decoded ) ? $decoded : array();
}

/**
 * Crée ou met à jour un devis.
 *
 * @param array $args Arguments.
 * @return int|WP_Error
 */
function anrhpub_save_quote( $args ) {
	$defaults = array(
		'quote_id'   => 0,
		'client_id'  => 0,
		'status'     => 'draft',
		'lines'      => array(),
		'notes'      => '',
		'message'    => '',
	);
	$args     = wp_parse_args( $args, $defaults );

	$client_id = (int) $args['client_id'];
	$status    = sanitize_key( $args['status'] );

	if ( ! isset( ANRHPUB_QUOTE_STATUSES[ $status ] ) ) {
		$status = 'draft';
	}

	$lines = is_array( $args['lines'] ) ? $args['lines'] : array();
	$title = anrhpub_generate_quote_number();

	$post_id = (int) $args['quote_id'];

	if ( $post_id > 0 ) {
		$existing = get_post( $post_id );
		if ( ! $existing || ANRHPUB_QUOTE_CPT !== $existing->post_type ) {
			return new WP_Error( 'invalid_quote', __( 'Devis introuvable.', 'anrhpub_theme' ) );
		}
		$title = $existing->post_title;
	} else {
		$post_id = wp_insert_post(
			array(
				'post_type'   => ANRHPUB_QUOTE_CPT,
				'post_status' => 'publish',
				'post_title'  => $title,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		anrhpub_update_quote_meta( $post_id, 'number', $title );
	}

	anrhpub_update_quote_meta( $post_id, 'client_id', $client_id );
	anrhpub_update_quote_meta( $post_id, 'status', $status );
	anrhpub_update_quote_meta( $post_id, 'lines', wp_json_encode( $lines ) );
	anrhpub_update_quote_meta( $post_id, 'notes', sanitize_textarea_field( (string) $args['notes'] ) );
	anrhpub_update_quote_meta( $post_id, 'message', sanitize_textarea_field( (string) $args['message'] ) );
	anrhpub_update_quote_meta( $post_id, 'updated_at', current_time( 'mysql' ) );

	return $post_id;
}

/**
 * Devis d’un client.
 *
 * @param int   $user_id User ID.
 * @param array $args    Query args.
 * @return WP_Post[]
 */
function anrhpub_get_client_quotes( $user_id = 0, $args = array() ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	if ( $user_id <= 0 ) {
		return array();
	}

	$query = new WP_Query(
		wp_parse_args(
			$args,
			array(
				'post_type'      => ANRHPUB_QUOTE_CPT,
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'   => 'anr_quote_client_id',
						'value' => $user_id,
					),
				),
			)
		)
	);

	return $query->posts;
}

/**
 * Le client possède ce devis ?
 *
 * @param int $quote_id Quote ID.
 * @param int $user_id  User ID.
 * @return bool
 */
function anrhpub_client_owns_quote( $quote_id, $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	if ( $user_id <= 0 ) {
		return false;
	}

	return (int) anrhpub_get_quote_meta( $quote_id, 'client_id', 0 ) === $user_id;
}

/**
 * URL PDF / impression devis.
 *
 * @param int $quote_id Quote ID.
 * @return string
 */
function anrhpub_get_quote_pdf_url( $quote_id ) {
	return add_query_arg(
		array(
			'anrhpub_download' => 'quote_pdf',
			'quote_id'         => (int) $quote_id,
			'nonce'            => wp_create_nonce( 'anr_quote_pdf_' . (int) $quote_id ),
		),
		home_url( '/' )
	);
}

/**
 * Panier → lignes devis enrichies.
 *
 * @param array $items Cart items.
 * @return array
 */
function anrhpub_cart_items_to_quote_lines( $items ) {
	$lines = array();

	if ( ! function_exists( 'anrhpub_enrich_quote_cart_items' ) ) {
		return $lines;
	}

	foreach ( anrhpub_enrich_quote_cart_items( $items ) as $line ) {
		$product_id = (int) ( $line['product_id'] ?? 0 );
		$qty        = max( 1, (int) ( $line['qty'] ?? 1 ) );
		$unit_ht    = function_exists( 'anrhpub_get_unit_price_ht' ) ? anrhpub_get_unit_price_ht( $product_id, $qty ) : null;

		$lines[] = array(
			'product_id' => $product_id,
			'label'      => (string) ( $line['title'] ?? '' ),
			'ref'        => (string) ( $line['ref'] ?? '' ),
			'qty'        => $qty,
			'color_name' => (string) ( $line['color_name'] ?? '' ),
			'unit_ht'    => $unit_ht,
			'line_ht'    => null !== $unit_ht ? round( $unit_ht * $qty, 2 ) : null,
		);
	}

	return $lines;
}

/**
 * Crée devis depuis panier.
 *
 * @param int    $client_id Client ID.
 * @param string $status    Status.
 * @param array  $items     Cart items.
 * @param string $message   Message.
 * @return int|WP_Error
 */
function anrhpub_create_quote_from_cart( $client_id, $status, $items, $message = '' ) {
	return anrhpub_save_quote(
		array(
			'client_id' => (int) $client_id,
			'status'    => $status,
			'lines'     => anrhpub_cart_items_to_quote_lines( $items ),
			'message'   => $message,
		)
	);
}

/**
 * Autorisation téléchargement PDF devis.
 *
 * @param int $quote_id Quote ID.
 * @return bool
 */
function anrhpub_user_can_download_quote_pdf( $quote_id ) {
	$quote_id = (int) $quote_id;

	if ( $quote_id <= 0 ) {
		return false;
	}

	return current_user_can( 'edit_post', $quote_id ) || anrhpub_client_owns_quote( $quote_id );
}

/**
 * Téléchargement PDF devis.
 */
function anrhpub_handle_quote_download() {
	if ( empty( $_GET['anrhpub_download'] ) || 'quote_pdf' !== $_GET['anrhpub_download'] ) {
		return;
	}

	$quote_id = isset( $_GET['quote_id'] ) ? absint( $_GET['quote_id'] ) : 0;
	$nonce    = isset( $_GET['nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['nonce'] ) ) : '';

	if ( ! $quote_id || ! wp_verify_nonce( $nonce, 'anr_quote_pdf_' . $quote_id ) ) {
		wp_die( esc_html__( 'Lien invalide.', 'anrhpub_theme' ), 403 );
	}

	if ( ! anrhpub_user_can_download_quote_pdf( $quote_id ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'anrhpub_theme' ), 403 );
	}

	$quote  = get_post( $quote_id );
	$number = anrhpub_get_quote_meta( $quote_id, 'number', $quote ? $quote->post_title : '' );
	$lines  = anrhpub_get_quote_lines( $quote_id );
	$status = anrhpub_get_quote_meta( $quote_id, 'status', 'pending' );

	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $number ) . '.pdf"' );

	anrhpub_output_simple_quote_pdf( $number, $status, $lines, $quote_id );
	exit;
}
add_action( 'template_redirect', 'anrhpub_handle_quote_download', 1 );

/**
 * PDF minimal (texte) sans dépendance externe.
 *
 * @param string $number  Quote number.
 * @param string $status  Status.
 * @param array  $lines   Lines.
 * @param int    $quote_id Quote ID.
 */
function anrhpub_output_simple_quote_pdf( $number, $status, $lines, $quote_id ) {
	$client_id = (int) anrhpub_get_quote_meta( $quote_id, 'client_id', 0 );
	$user      = $client_id ? get_userdata( $client_id ) : null;
	$rows      = array();
	$total_ht  = 0.0;

	$rows[] = 'Devis ' . $number;
	$rows[] = get_bloginfo( 'name' ) . ' — ' . gmdate( 'd/m/Y' );
	$rows[] = 'Statut : ' . anrhpub_get_quote_status_label( $status );

	if ( $user ) {
		$rows[] = 'Client : ' . $user->display_name . ' (' . $user->user_email . ')';
		$company = anrhpub_get_client_company( $client_id );
		if ( $company ) {
			$rows[] = 'Societe : ' . $company;
		}
	}

	$rows[] = '';
	$rows[] = 'Lignes :';

	foreach ( $lines as $line ) {
		$label = ( $line['ref'] ?? '' ) ? ( $line['ref'] . ' — ' ) : '';
		$label .= $line['label'] ?? '';
		$qty   = (int) ( $line['qty'] ?? 1 );
		$row   = ' - ' . $label . ' x' . $qty;

		if ( isset( $line['line_ht'] ) && null !== $line['line_ht'] ) {
			$row      .= ' = ' . number_format( (float) $line['line_ht'], 2, ',', ' ' ) . ' EUR HT';
			$total_ht += (float) $line['line_ht'];
		}

		$rows[] = $row;
	}

	if ( $total_ht > 0 ) {
		$rows[] = '';
		$rows[] = 'Total HT indicatif : ' . number_format( $total_ht, 2, ',', ' ' ) . ' EUR';
	}

	$message = (string) anrhpub_get_quote_meta( $quote_id, 'message', '' );
	if ( $message ) {
		$rows[] = '';
		$rows[] = 'Message :';
		$rows[] = $message;
	}

	$text = implode( "\n", $rows );
	$text = wp_strip_all_tags( $text );
	$text = preg_replace( '/[^\x09\x0A\x0D\x20-\x7E\xA0-\xFF]/', '', $text );

	$lines_pdf = explode( "\n", wordwrap( $text, 90, "\n", true ) );
	$content   = "BT\n/F1 10 Tf\n50 800 Td\n";
	$y         = 0;

	foreach ( $lines_pdf as $line ) {
		$line = str_replace( array( '\\', '(', ')' ), array( '\\\\', '\\(', '\\)' ), $line );
		$content .= '(' . $line . ") Tj\n0 -14 Td\n";
		++$y;
		if ( $y > 50 ) {
			break;
		}
	}

	$content .= "ET";
	$len      = strlen( $content );

	$pdf  = "%PDF-1.4\n";
	$pdf .= "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n";
	$pdf .= "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n";
	$pdf .= "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources<< /Font<< /F1 5 0 R >> >> >>endobj\n";
	$pdf .= "4 0 obj<< /Length $len >>stream\n$content\nendstream endobj\n";
	$pdf .= "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n";
	$pdf .= "xref\n0 6\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000270 00000 n \n0000000" . ( 330 + $len ) . " 00000 n \n";
	$pdf .= "trailer<< /Size 6 /Root 1 0 R >>\nstartxref\n" . ( 380 + $len ) . "\n%%EOF";

	echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * AJAX — sauvegarder brouillon devis.
 */
function anrhpub_ajax_save_quote_draft() {
	check_ajax_referer( 'anrhpub_quote_draft', 'nonce' );

	if ( ! anrhpub_is_client_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'Connectez-vous pour enregistrer un brouillon.', 'anrhpub_theme' ) ), 401 );
	}

	$raw   = isset( $_POST['cart'] ) ? wp_unslash( $_POST['cart'] ) : '[]';
	$items = json_decode( is_string( $raw ) ? $raw : '[]', true );

	if ( ! is_array( $items ) ) {
		$items = array();
	}

	if ( function_exists( 'anrhpub_sanitize_quote_cart_items' ) ) {
		$items = anrhpub_sanitize_quote_cart_items( $items );
	}

	$quote_id = isset( $_POST['quote_id'] ) ? absint( $_POST['quote_id'] ) : 0;

	if ( $quote_id && ! anrhpub_client_owns_quote( $quote_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Devis introuvable.', 'anrhpub_theme' ) ), 403 );
	}

	$result = anrhpub_save_quote(
		array(
			'quote_id'  => $quote_id,
			'client_id' => anrhpub_get_client_user_id(),
			'status'    => 'draft',
			'lines'     => anrhpub_cart_items_to_quote_lines( is_array( $items ) ? $items : array() ),
		)
	);

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
	}

	wp_send_json_success(
		array(
			'quote_id' => $result,
			'pdf_url'  => anrhpub_get_quote_pdf_url( $result ),
			'message'  => __( 'Brouillon enregistré.', 'anrhpub_theme' ),
		)
	);
}
add_action( 'wp_ajax_anrhpub_save_quote_draft', 'anrhpub_ajax_save_quote_draft' );

/**
 * Crée devis « en attente » après envoi formulaire contact.
 *
 * @param int    $client_id Client ID.
 * @param array  $items     Cart.
 * @param string $message   Message.
 * @return int
 */
function anrhpub_on_devis_submitted( $client_id, $items, $message ) {
	$quote_id = anrhpub_create_quote_from_cart( $client_id, 'pending', $items, $message );

	if ( is_wp_error( $quote_id ) ) {
		return 0;
	}

	do_action( 'anrhpub_quote_submitted', $quote_id, $client_id );

	return (int) $quote_id;
}
