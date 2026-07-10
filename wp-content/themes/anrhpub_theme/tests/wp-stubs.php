<?php
/**
 * Stubs WordPress pour les tests unitaires du thème.
 *
 * @package anrhpub_theme
 */

declare(strict_types=1);

function add_action( ...$args ) {
	unset( $args );
}

function add_filter( ...$args ) {
	unset( $args );
}

function remove_action( ...$args ) {
	unset( $args );
}

function do_action( ...$args ) {
	unset( $args );
}

function apply_filters( $tag, $value, ...$args ) {
	unset( $tag, $args );
	return $value;
}

function register_post_type( ...$args ) {
	unset( $args );
	return true;
}

function register_post_meta( ...$args ) {
	unset( $args );
	return true;
}

function register_meta( ...$args ) {
	unset( $args );
	return true;
}

function register_taxonomy( ...$args ) {
	unset( $args );
	return true;
}

function register_term_meta( ...$args ) {
	unset( $args );
	return true;
}

function sanitize_text_field( $value ) {
	return is_string( $value ) ? trim( $value ) : '';
}

function __( $text, $domain = 'default' ) {
	unset( $domain );
	return $text;
}

function absint( $value ) {
	return abs( (int) $value );
}

function get_user_meta( $user_id, $key = '', $single = false ) {
	unset( $single );
	$store = $GLOBALS['anrhpub_test_user_meta'][ (int) $user_id ] ?? array();
	return $store[ $key ] ?? '';
}

function user_can( $user_id, $capability ) {
	unset( $user_id );
	return ! empty( $GLOBALS['anrhpub_test_caps'][ $capability ] );
}

function current_user_can( $capability, ...$args ) {
	unset( $args );
	return ! empty( $GLOBALS['anrhpub_test_caps'][ $capability ] );
}

function get_post_type( $post_id ) {
	$post = $GLOBALS['anrhpub_test_posts'][ (int) $post_id ] ?? null;
	return $post['type'] ?? 'post';
}

function get_post_status( $post_id ) {
	$post = $GLOBALS['anrhpub_test_posts'][ (int) $post_id ] ?? null;
	return $post['status'] ?? 'draft';
}

function get_post( $post_id ) {
	$post = $GLOBALS['anrhpub_test_posts'][ (int) $post_id ] ?? null;
	if ( ! $post ) {
		return null;
	}
	return (object) $post;
}

function get_post_meta( $post_id, $key = '', $single = false ) {
	unset( $single );
	$store = $GLOBALS['anrhpub_test_post_meta'][ (int) $post_id ] ?? array();
	return $store[ $key ] ?? '';
}

function update_post_meta( $post_id, $key, $value ) {
	$GLOBALS['anrhpub_test_post_meta'][ (int) $post_id ][ $key ] = $value;
	return true;
}

function anrhpub_get_client_user_id() {
	return (int) $GLOBALS['anrhpub_test_current_user'];
}

function anrhpub_user_has_client_role( $user_id = 0 ) {
	unset( $user_id );
	return true;
}

function anrhpub_product_has_colors( $post_id = 0 ) {
	unset( $post_id );
	return false;
}

function anrhpub_validate_product_color( $product_id, $color_id ) {
	unset( $product_id, $color_id );
	return 0;
}

function anrhpub_clamp_quote_qty_for_color( $product_id, $color_id, $qty ) {
	unset( $color_id );
	return anrhpub_clamp_quote_qty( $product_id, $qty );
}

function anrhpub_reset_test_state(): void {
	$GLOBALS['anrhpub_test_user_meta']    = array();
	$GLOBALS['anrhpub_test_current_user'] = 0;
	$GLOBALS['anrhpub_test_caps']         = array();
	$GLOBALS['anrhpub_test_posts']        = array();
	$GLOBALS['anrhpub_test_post_meta']    = array();
}
