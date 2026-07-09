<?php
/**
 * Page Histoire de l’ANRH.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_ANRH_HISTORY_PAGE_VERSION', 1 );
define( 'ANRHPUB_ANRH_URL', 'https://www.anrh.fr/' );

/**
 * URL page histoire ANRH.
 *
 * @return string
 */
function anrhpub_anrh_history_url() {
	$page = get_page_by_path( 'histoire-anrh' );

	if ( $page ) {
		return get_permalink( $page );
	}

	return home_url( '/histoire-anrh/' );
}

/**
 * Crée la page si absente.
 */
function anrhpub_ensure_anrh_history_page() {
	if ( (int) get_option( 'anrhpub_anrh_history_page_version', 0 ) >= ANRHPUB_ANRH_HISTORY_PAGE_VERSION ) {
		return;
	}

	$existing = get_page_by_path( 'histoire-anrh' );

	if ( ! $existing ) {
		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => __( 'Histoire de l’ANRH', 'anrhpub_theme' ),
				'post_name'    => 'histoire-anrh',
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);
	}

	update_option( 'anrhpub_anrh_history_page_version', ANRHPUB_ANRH_HISTORY_PAGE_VERSION );
}
add_action( 'init', 'anrhpub_ensure_anrh_history_page', 14 );
