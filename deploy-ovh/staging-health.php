<?php
/**
 * Diagnostic staging — à supprimer en production.
 * Accès : https://pub.anrh.fr/staging-health.php (après login staging)
 */
require __DIR__ . '/wp-load.php';

if ( ! function_exists( 'anrh_staging_gate_is_authenticated' ) || ! anrh_staging_gate_is_authenticated() ) {
	status_header( 401 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo "Connectez-vous d'abord via le verrou staging.\n";
	exit;
}

header( 'Content-Type: text/plain; charset=utf-8' );

echo "=== ANRH Staging Health ===\n\n";
echo 'WP_ENVIRONMENT_TYPE: ' . ( defined( 'WP_ENVIRONMENT_TYPE' ) ? WP_ENVIRONMENT_TYPE : '?' ) . "\n";
echo 'home: ' . get_option( 'home' ) . "\n";
echo 'siteurl: ' . get_option( 'siteurl' ) . "\n";
echo 'template: ' . get_option( 'template' ) . "\n";
echo 'stylesheet: ' . get_option( 'stylesheet' ) . "\n";
echo 'show_on_front: ' . get_option( 'show_on_front' ) . "\n";
echo 'page_on_front: ' . get_option( 'page_on_front' ) . "\n";
echo 'blogname: ' . get_option( 'blogname' ) . "\n\n";

$products = wp_count_posts( 'anr_product' );
echo "Produits (anr_product) publies: " . (int) ( $products->publish ?? 0 ) . "\n";

$theme_dir = get_template_directory();
echo 'Theme dir exists: ' . ( is_dir( $theme_dir ) ? 'yes' : 'no' ) . " ($theme_dir)\n";
echo 'main.css exists: ' . ( file_exists( $theme_dir . '/assets/css/main.css' ) ? 'yes' : 'no' ) . "\n";
echo 'uploads dir exists: ' . ( is_dir( WP_CONTENT_DIR . '/uploads' ) ? 'yes' : 'no' ) . "\n";

$upload_count = 0;
if ( is_dir( WP_CONTENT_DIR . '/uploads' ) ) {
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( WP_CONTENT_DIR . '/uploads', FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $file ) {
		if ( $file->isFile() ) {
			++$upload_count;
		}
	}
}
echo "Fichiers uploads: $upload_count\n";

if ( get_option( 'stylesheet' ) !== 'anrhpub_theme' ) {
	echo "\n!! PROBLEME: le theme actif n'est pas anrhpub_theme — importez export-pubanrh-ovh.sql\n";
}
if ( (int) ( $products->publish ?? 0 ) < 1 ) {
	echo "\n!! PROBLEME: aucun produit catalogue — importez export-pubanrh-ovh.sql\n";
}
