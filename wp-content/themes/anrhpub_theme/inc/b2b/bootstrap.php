<?php
/**
 * Modules B2B — chargement.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_B2B_VERSION', 2 );

$b2b_dir = get_template_directory() . '/inc/b2b';

require_once $b2b_dir . '/client-pro.php';
require_once $b2b_dir . '/client-pro-admin.php';
require_once $b2b_dir . '/pricing.php';
require_once $b2b_dir . '/pricing-admin.php';
require_once $b2b_dir . '/stock-facets.php';
require_once $b2b_dir . '/quotes.php';
require_once $b2b_dir . '/quotes-admin.php';
require_once $b2b_dir . '/emails.php';
require_once $b2b_dir . '/reorder.php';
require_once $b2b_dir . '/tools.php';
require_once $b2b_dir . '/security.php';
require_once $b2b_dir . '/performance.php';

/**
 * Activation B2B (pages, règles, rôles).
 */
function anrhpub_b2b_activate() {
	if ( function_exists( 'anrhpub_register_quote_cpt' ) ) {
		anrhpub_register_quote_cpt();
	}
	if ( function_exists( 'anrhpub_register_facet_taxonomies' ) ) {
		anrhpub_register_facet_taxonomies();
	}
	if ( function_exists( 'anrhpub_ensure_privacy_page' ) ) {
		anrhpub_ensure_privacy_page();
	}
	if ( function_exists( 'anrhpub_ensure_compare_page' ) ) {
		anrhpub_ensure_compare_page();
	}
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'anrhpub_b2b_activate', 25 );

/**
 * Flush réécritures si version B2B change.
 */
function anrhpub_b2b_maybe_flush() {
	if ( (int) get_option( 'anrhpub_b2b_version', 0 ) >= ANRHPUB_B2B_VERSION ) {
		return;
	}
	anrhpub_b2b_activate();
	update_option( 'anrhpub_b2b_version', ANRHPUB_B2B_VERSION );
}
add_action( 'init', 'anrhpub_b2b_maybe_flush', 99 );
