<?php
/**
 * Plugin Name: ANRH Purge produits PROD (one-shot)
 * Description: Supprime tous les anr_product en production via SQL, puis s'auto-supprime.
 * Version: 1.1.0
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

		@set_time_limit( 300 );

		global $wpdb;

		// IDs des produits.
		$ids = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'anr_product'"
		);

		$count = is_array( $ids ) ? count( $ids ) : 0;

		if ( $count > 0 ) {
			$id_list = implode( ',', array_map( 'intval', $ids ) );

			// Meta.
			$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN ({$id_list})" ); // phpcs:ignore WordPress.DB.PreparedSQL

			// Termes.
			$wpdb->query( "DELETE FROM {$wpdb->term_relationships} WHERE object_id IN ({$id_list})" ); // phpcs:ignore WordPress.DB.PreparedSQL

			// Posts.
			$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE ID IN ({$id_list})" ); // phpcs:ignore WordPress.DB.PreparedSQL

			// Révisions / attachments enfants éventuels liés.
			$wpdb->query(
				"DELETE pm FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE p.post_parent IN ({$id_list})"
			); // phpcs:ignore WordPress.DB.PreparedSQL

			$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_parent IN ({$id_list})" ); // phpcs:ignore WordPress.DB.PreparedSQL
		}

		$remaining = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'anr_product'"
		);

		update_option(
			'anrh_purge_products_last',
			array(
				'deleted'   => $count,
				'remaining' => $remaining,
				'at'        => gmdate( 'c' ),
				'host'      => $host,
			),
			false
		);

		wp_cache_flush();

		if ( 0 === $remaining ) {
			@unlink( __FILE__ );
		}
	},
	0
);
