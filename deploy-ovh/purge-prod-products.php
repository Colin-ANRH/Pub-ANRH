<?php
/**
 * One-shot : supprime TOUS les produits (anr_product) en PRODUCTION uniquement.
 * Supprimer ce fichier après usage.
 */

$token_expected = 'CHANGE_ME';
$token_given    = '';

if ( isset( $_POST['token'] ) ) {
	$token_given = (string) $_POST['token'];
} elseif ( isset( $_GET['token'] ) ) {
	$token_given = (string) $_GET['token'];
}

if ( $token_given === '' || ! hash_equals( $token_expected, $token_given ) ) {
	http_response_code( 403 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	exit( "Forbidden\n" );
}

require __DIR__ . '/wp-load.php';

header( 'Content-Type: text/plain; charset=utf-8' );

$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) $_SERVER['HTTP_HOST'] ) : '';
$is_prod_host = in_array( $host, array( 'pub.anrh.fr', 'www.pub.anrh.fr' ), true );
$is_prod_env  = defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE === 'production';

if ( ! $is_prod_host || ! $is_prod_env ) {
	http_response_code( 400 );
	echo "Refuse : ce script ne tourne que sur la production (pub.anrh.fr).\n";
	echo 'Host: ' . $host . "\n";
	echo 'WP_ENVIRONMENT_TYPE: ' . ( defined( 'WP_ENVIRONMENT_TYPE' ) ? WP_ENVIRONMENT_TYPE : '?' ) . "\n";
	exit;
}

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
	$result = wp_delete_post( (int) $id, true );
	if ( $result ) {
		++$count;
	}
}

$remaining = wp_count_posts( 'anr_product' );
$publish   = (int) ( $remaining->publish ?? 0 );
$draft     = (int) ( $remaining->draft ?? 0 );
$trash     = (int) ( $remaining->trash ?? 0 );

echo "Purge produits PROD terminee.\n";
echo "Supprimes: {$count}\n";
echo "Restants publish={$publish} draft={$draft} trash={$trash}\n";

@unlink( __FILE__ );
echo "Script auto-supprime.\n";
