<?php
/**
 * Couleurs produits — chargeur de modules.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_COLOR_HEX_META', 'anr_color_hex' );
define( 'ANRHPUB_COLORS_SEEDED_OPTION', 'anrhpub_colors_seeded' );
define( 'ANRHPUB_PRODUCT_COLOR_STOCK_META', 'anr_product_color_stock' );

$anrhpub_product_colors_dir = __DIR__;
require_once $anrhpub_product_colors_dir . '/taxonomy.php';
require_once $anrhpub_product_colors_dir . '/admin-term.php';
require_once $anrhpub_product_colors_dir . '/admin-product.php';
require_once $anrhpub_product_colors_dir . '/frontend.php';
