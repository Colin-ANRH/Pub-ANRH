<?php
/**
 * One-time CLI helper: php bin/activate-theme.php (from theme folder)
 * Activates theme and seeds demo catalogue.
 */

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php introuvable.\n" );
	exit( 1 );
}

require $wp_load;

if ( ! function_exists( 'switch_theme' ) ) {
	fwrite( STDERR, "WordPress non chargé.\n" );
	exit( 1 );
}

switch_theme( 'anrhpub_theme' );

delete_option( 'anrhpub_categories_version' );
if ( function_exists( 'anrhpub_maybe_upgrade_categories' ) ) {
	anrhpub_maybe_upgrade_categories();
}
if ( function_exists( 'anrhpub_seed_demo_data' ) ) {
	anrhpub_seed_demo_data();
}
anrhpub_register_product_cpt();
flush_rewrite_rules();

echo "Thème anrhpub_theme activé. Catalogue démo : OK.\n";
