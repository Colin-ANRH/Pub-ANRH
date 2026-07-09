<?php
/**
 * Mise en avant nouveautés — accueil.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lien vers la section nouveautés du catalogue.
 *
 * @return string
 */
function anrhpub_home_nouveautes_url() {
	return function_exists( 'anrhpub_nouveautes_catalogue_url' )
		? anrhpub_nouveautes_catalogue_url()
		: anrhpub_catalogue_url();
}

/**
 * Produits « nouveauté » pour le carrousel accueil.
 *
 * @return WP_Query
 */
function anrhpub_get_home_spotlight_query() {
	$query = new WP_Query(
		array(
			'post_type'      => 'anr_product',
			'posts_per_page' => 20,
			'meta_key'       => 'anr_badge',
			'meta_value'     => 'nouveau',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	if ( $query->have_posts() ) {
		return $query;
	}

	wp_reset_postdata();

	return new WP_Query(
		array(
			'post_type'      => 'anr_product',
			'posts_per_page' => 20,
			'meta_key'       => 'anr_featured',
			'meta_value'     => '1',
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

/**
 * Intervalle carrousel nouveautés (ms).
 *
 * @return int
 */
function anrhpub_get_home_spotlight_interval() {
	return (int) apply_filters( 'anrhpub_home_spotlight_interval', 6000 );
}

/**
 * Données JS carrousel nouveautés.
 */
function anrhpub_enqueue_home_spotlight_assets() {
	if ( ! is_front_page() ) {
		return;
	}

	wp_localize_script(
		'anrhpub-main',
		'anrhpubHomeSpotlight',
		array(
			'interval' => anrhpub_get_home_spotlight_interval(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_home_spotlight_assets', 51 );
