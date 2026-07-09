<?php
/**
 * Page Conditions d’utilisation / CGV.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_TERMS_PAGE_VERSION', 1 );

/**
 * URL page conditions d’utilisation.
 *
 * @return string
 */
function anrhpub_terms_url() {
	$page = get_page_by_path( 'conditions-utilisation' );

	if ( $page ) {
		return get_permalink( $page );
	}

	return home_url( '/conditions-utilisation/' );
}

/**
 * Crée la page si absente.
 */
function anrhpub_ensure_terms_page() {
	if ( (int) get_option( 'anrhpub_terms_page_version', 0 ) >= ANRHPUB_TERMS_PAGE_VERSION ) {
		return;
	}

	$existing = get_page_by_path( 'conditions-utilisation' );

	if ( ! $existing ) {
		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => __( 'Conditions d’utilisation', 'anrhpub_theme' ),
				'post_name'    => 'conditions-utilisation',
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);
	}

	update_option( 'anrhpub_terms_page_version', ANRHPUB_TERMS_PAGE_VERSION );
}
add_action( 'init', 'anrhpub_ensure_terms_page', 14 );
