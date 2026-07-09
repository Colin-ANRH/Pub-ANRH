<?php
/**
 * Barre d’administration front + séparation compte WP / client catalogue.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Utilisateur connecté à WordPress avec droits d’administration ?
 *
 * @return bool
 */
function anrhpub_is_wp_admin_user() {
	return is_user_logged_in() && current_user_can( 'manage_options' );
}

/**
 * Compte staff (back-office) — doit voir la barre wp-admin sur le site.
 *
 * @return bool
 */
function anrhpub_user_can_see_wp_admin_bar() {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) ) {
		return true;
	}

	$user = wp_get_current_user();

	if ( ! $user instanceof WP_User ) {
		return false;
	}

	$roles = (array) $user->roles;

	if ( 1 === count( $roles ) && defined( 'ANRHPUB_CLIENT_ROLE' ) && in_array( ANRHPUB_CLIENT_ROLE, $roles, true ) ) {
		return false;
	}

	return false;
}

/**
 * Affiche la barre WP admin sur le site pour le staff ; masquée pour les clients seuls.
 *
 * @param bool $show Affichage par défaut.
 * @return bool
 */
function anrhpub_show_admin_bar_on_front( $show ) {
	if ( is_admin() ) {
		return $show;
	}

	if ( anrhpub_user_can_see_wp_admin_bar() ) {
		return true;
	}

	if ( is_user_logged_in() && function_exists( 'anrhpub_user_has_client_role' ) && anrhpub_user_has_client_role( get_current_user_id() ) ) {
		return false;
	}

	return $show;
}
add_filter( 'show_admin_bar', 'anrhpub_show_admin_bar_on_front', 99999 );

/**
 * Force la barre avant l’initialisation WordPress (template_redirect).
 */
function anrhpub_prepare_admin_bar() {
	if ( is_admin() || wp_doing_ajax() || wp_is_json_request() ) {
		return;
	}

	if ( ! anrhpub_user_can_see_wp_admin_bar() ) {
		return;
	}

	show_admin_bar( true );

	$user_id = get_current_user_id();

	if ( $user_id && 'true' !== get_user_option( 'show_admin_bar_front', $user_id ) ) {
		update_user_option( $user_id, 'show_admin_bar_front', 'true', true );
	}
}
add_action( 'template_redirect', 'anrhpub_prepare_admin_bar', -1 );

/**
 * Réactive la barre admin à chaque connexion wp-admin.
 *
 * @param string  $user_login Login.
 * @param WP_User $user       Utilisateur.
 */
function anrhpub_on_wp_admin_login( $user_login, $user ) {
	if ( ! $user instanceof WP_User ) {
		return;
	}

	if ( user_can( $user, 'edit_posts' ) || user_can( $user, 'manage_options' ) ) {
		update_user_option( $user->ID, 'show_admin_bar_front', 'true', true );
	}

	if ( user_can( $user, 'manage_options' ) && function_exists( 'anrhpub_clear_client_session_cookie' ) ) {
		anrhpub_clear_client_session_cookie();
	}
}
add_action( 'wp_login', 'anrhpub_on_wp_admin_login', 10, 2 );

/**
 * Z-index : la barre wp-admin doit rester au-dessus du loader et du header.
 */
function anrhpub_enqueue_admin_bar_fix() {
	if ( ! is_admin_bar_showing() ) {
		return;
	}

	wp_register_style( 'anrhpub-admin-bar-fix', false, array(), ANRHPUB_THEME_VERSION );
	wp_enqueue_style( 'anrhpub-admin-bar-fix' );
	wp_add_inline_style(
		'anrhpub-admin-bar-fix',
		'#wpadminbar{z-index:100001!important;position:fixed!important;}'
	);
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_admin_bar_fix', 100 );

/**
 * Classes body — admin connecté sans session client catalogue.
 *
 * @param string[] $classes Classes body.
 * @return string[]
 */
function anrhpub_admin_body_class( $classes ) {
	if ( anrhpub_is_wp_admin_user() && function_exists( 'anrhpub_is_client_logged_in' ) && ! anrhpub_is_client_logged_in() ) {
		$classes[] = 'anr-wp-admin';
	}

	if ( function_exists( 'anrhpub_is_admin_previewing_client' ) && anrhpub_is_admin_previewing_client() ) {
		$classes[] = 'anr-admin-preview-client';
	}

	return $classes;
}
add_filter( 'body_class', 'anrhpub_admin_body_class' );
