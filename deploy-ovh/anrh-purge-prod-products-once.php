<?php
/**
 * Plugin Name: ANRH Purge produits PROD (one-shot)
 * Description: Supprime tous les anr_product en production, puis s'auto-supprime. NE PAS laisser en place.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function () {
		if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) || WP_ENVIRONMENT_TYPE !== 'production' ) {
			return;
		}

		$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) $_SERVER['HTTP_HOST'] ) : '';
		if ( ! in_array( $host, array( 'pub.anrh.fr', 'www.pub.anrh.fr' ), true ) ) {
			return;
		}

		// Évite une double exécution concurrente.
		if ( get_transient( 'anrh_purge_products_lock' ) ) {
			return;
		}
		set_transient( 'anrh_purge_products_lock', 1, 5 * MINUTE_IN_SECONDS );

		$ids = get_posts(
			array(
				'post_type'      => 'anr_product',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			)
		);

		$count = 0;
		foreach ( $ids as $id ) {
			if ( wp_delete_post( (int) $id, true ) ) {
				++$count;
			}
		}

		update_option(
			'anrh_purge_products_last',
			array(
				'deleted' => $count,
				'at'      => gmdate( 'c' ),
				'host'    => $host,
			),
			false
		);

		$self = __FILE__;
		@unlink( $self );
	},
	1
);
