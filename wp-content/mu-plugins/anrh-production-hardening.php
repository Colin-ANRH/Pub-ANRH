<?php
/**
 * Plugin Name: ANRH Production Hardening
 * Description: Réglages de sécurité / SEO pour l'environnement de production pub.anrh.fr.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) || WP_ENVIRONMENT_TYPE !== 'production' ) {
	return;
}

/**
 * XML-RPC désactivé (surface d'attaque).
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Garantir l'indexation SEO en production.
 */
add_action(
	'init',
	static function () {
		if ( (string) get_option( 'blog_public' ) !== '1' ) {
			update_option( 'blog_public', '1' );
		}
	},
	0
);

/**
 * Masquer la version WordPress dans le HTML.
 */
remove_action( 'wp_head', 'wp_generator' );

/**
 * En-têtes sécurité complémentaires.
 */
add_action(
	'send_headers',
	static function () {
		if ( headers_sent() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff', false );
		header( 'X-Frame-Options: SAMEORIGIN', false );
		header( 'Referrer-Policy: strict-origin-when-cross-origin', false );
		// S'assurer qu'aucun noindex résiduel n'est envoyé par erreur.
		header_remove( 'X-Robots-Tag' );
	},
	0
);

/**
 * Bannière admin discrète.
 */
add_action(
	'admin_bar_menu',
	static function ( $wp_admin_bar ) {
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		$wp_admin_bar->add_node(
			array(
				'id'    => 'anrh-prod-env',
				'title' => 'PROD',
				'href'  => admin_url(),
				'meta'  => array(
					'title' => 'Environnement de production',
				),
			)
		);
	},
	1
);

add_action(
	'admin_head',
	static function () {
		echo '<style>#wp-admin-bar-anrh-prod-env>.ab-item{background:#1b7a4b!important;color:#fff!important;}</style>';
	}
);

add_action(
	'wp_head',
	static function () {
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		echo '<style>#wp-admin-bar-anrh-prod-env>.ab-item{background:#1b7a4b!important;color:#fff!important;}</style>';
	}
);
