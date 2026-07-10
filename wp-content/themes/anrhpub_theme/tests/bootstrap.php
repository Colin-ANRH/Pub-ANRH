<?php
/**
 * Environnement de test PHPUnit (stubs WordPress minimaux).
 *
 * @package anrhpub_theme
 */

declare(strict_types=1);

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

$GLOBALS['anrhpub_test_user_meta']    = array();
$GLOBALS['anrhpub_test_current_user'] = 0;
$GLOBALS['anrhpub_test_caps']         = array();
$GLOBALS['anrhpub_test_posts']        = array();

require dirname( __DIR__ ) . '/tests/wp-stubs.php';

require dirname( __DIR__ ) . '/inc/b2b/client-pro.php';
require dirname( __DIR__ ) . '/inc/product-colors/taxonomy.php';
require dirname( __DIR__ ) . '/inc/b2b/quotes.php';
require dirname( __DIR__ ) . '/inc/quote-cart.php';
