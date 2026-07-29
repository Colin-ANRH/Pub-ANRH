<?php
/**
 * One-shot purge produits PROD — ouvrir une fois puis le fichier s'auto-supprime.
 */
$token_expected = 'purge-anrh-20260729';
$token_given    = isset( $_GET['token'] ) ? (string) $_GET['token'] : '';

if ( $token_given === '' || ! hash_equals( $token_expected, $token_given ) ) {
	http_response_code( 403 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	exit( "Forbidden\n" );
}

require __DIR__ . '/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
@set_time_limit( 300 );

$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) $_SERVER['HTTP_HOST'] ) : '';
if ( ! in_array( $host, array( 'pub.anrh.fr', 'www.pub.anrh.fr' ), true ) ) {
	http_response_code( 400 );
	exit( "Refuse hors prod.\n" );
}

global $wpdb;

$before = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'anr_product'" );
$ids    = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'anr_product'" );

if ( $ids ) {
	$id_list = implode( ',', array_map( 'intval', $ids ) );
	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN ({$id_list})" );
	$wpdb->query( "DELETE FROM {$wpdb->term_relationships} WHERE object_id IN ({$id_list})" );
	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE ID IN ({$id_list})" );
}

$after = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'anr_product'" );
wp_cache_flush();

echo "Host: {$host}\n";
echo "WP_ENVIRONMENT_TYPE: " . ( defined( 'WP_ENVIRONMENT_TYPE' ) ? WP_ENVIRONMENT_TYPE : '?' ) . "\n";
echo "Avant: {$before}\n";
echo "Apres: {$after}\n";
echo "OK\n";

@unlink( __FILE__ );
