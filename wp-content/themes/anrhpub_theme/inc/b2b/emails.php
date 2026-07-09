<?php
/**
 * E-mails automatiques B2B.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * E-mail client : accusé devis.
 *
 * @param int $quote_id Quote ID.
 * @param int $client_id Client ID.
 */
function anrhpub_email_quote_submitted( $quote_id, $client_id ) {
	$user = get_userdata( $client_id );

	if ( ! $user || ! is_email( $user->user_email ) ) {
		return;
	}

	$number = anrhpub_get_quote_meta( $quote_id, 'number', get_the_title( $quote_id ) );

	wp_mail(
		$user->user_email,
		'[' . get_bloginfo( 'name' ) . '] ' . __( 'Demande de devis reçue', 'anrhpub_theme' ),
		sprintf(
			"%s\n\n%s: %s\n%s\n\n%s",
			__( 'Nous avons bien reçu votre demande de devis.', 'anrhpub_theme' ),
			__( 'Référence', 'anrhpub_theme' ),
			$number,
			anrhpub_get_quote_pdf_url( $quote_id ),
			__( 'Notre équipe commerciale vous répond sous 48 h ouvrées.', 'anrhpub_theme' )
		)
	);

	wp_mail(
		anrhpub_get_contact_email(),
		'[' . get_bloginfo( 'name' ) . '] ' . __( 'Nouveau devis client', 'anrhpub_theme' ),
		sprintf( "%s — %s\n\n%s", $number, $user->display_name, admin_url( 'post.php?post=' . $quote_id . '&action=edit' ) )
	);
}
add_action( 'anrhpub_quote_submitted', 'anrhpub_email_quote_submitted', 10, 2 );

/**
 * Changement statut devis → client.
 *
 * @param int    $quote_id Quote ID.
 * @param string $new_status New.
 * @param string $old_status Old.
 */
function anrhpub_email_quote_status_changed( $quote_id, $new_status, $old_status ) {
	unset( $old_status );

	$client_id = (int) anrhpub_get_quote_meta( $quote_id, 'client_id', 0 );
	$user      = get_userdata( $client_id );

	if ( ! $user || ! is_email( $user->user_email ) ) {
		return;
	}

	$number = anrhpub_get_quote_meta( $quote_id, 'number', get_the_title( $quote_id ) );

	wp_mail(
		$user->user_email,
		'[' . get_bloginfo( 'name' ) . '] ' . __( 'Mise à jour de votre devis', 'anrhpub_theme' ),
		sprintf(
			"%s %s\n\n%s: %s\n%s",
			__( 'Le statut de votre devis', 'anrhpub_theme' ),
			$number,
			__( 'Nouveau statut', 'anrhpub_theme' ),
			anrhpub_get_quote_status_label( $new_status ),
			anrhpub_get_quote_pdf_url( $quote_id )
		)
	);
}
add_action( 'anrhpub_quote_status_changed', 'anrhpub_email_quote_status_changed', 10, 3 );

/**
 * Changement statut commande → client.
 *
 * @param int    $order_id Order ID.
 * @param string $new_status New.
 * @param string $old_status Old.
 */
function anrhpub_email_order_status_changed( $order_id, $new_status, $old_status ) {
	unset( $old_status );

	$client_id = (int) anrhpub_get_order_meta( $order_id, 'client_id', 0 );
	$user      = get_userdata( $client_id );

	if ( ! $user || ! is_email( $user->user_email ) ) {
		return;
	}

	$number = anrhpub_get_order_meta( $order_id, 'order_number', get_the_title( $order_id ) );

	wp_mail(
		$user->user_email,
		'[' . get_bloginfo( 'name' ) . '] ' . __( 'Mise à jour de commande', 'anrhpub_theme' ),
		sprintf(
			"%s %s\n\n%s: %s",
			__( 'Votre commande', 'anrhpub_theme' ),
			$number,
			__( 'Statut', 'anrhpub_theme' ),
			anrhpub_get_order_status_label( $new_status )
		)
	);
}
add_action( 'anrhpub_order_status_changed', 'anrhpub_email_order_status_changed', 10, 3 );
