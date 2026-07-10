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

function wp_get_object_terms( $object_id, $taxonomies, $args = array() ) {
	unset( $object_id, $taxonomies, $args );
	return array();
}

function is_wp_error( $thing ) {
	return false;
}

function get_term( $term, $taxonomy = '', $output = OBJECT, $filter = 'raw' ) {
	unset( $term, $taxonomy, $output, $filter );
	return null;
}

function get_terms( $args = array() ) {
	unset( $args );
	return array();
}

function get_the_ID() {
	return 0;
}

function home_url( $path = '' ) {
	return 'https://example.test' . $path;
}

function anrhpub_get_client_user_id() {
	return (int) $GLOBALS['anrhpub_test_current_user'];
}

function anrhpub_user_has_client_role( $user_id = 0 ) {
	unset( $user_id );
	return true;
}

function anrhpub_reset_test_state(): void {
	$GLOBALS['anrhpub_test_user_meta']    = array();
	$GLOBALS['anrhpub_test_current_user'] = 0;
	$GLOBALS['anrhpub_test_caps']         = array();
	$GLOBALS['anrhpub_test_posts']        = array();
	$GLOBALS['anrhpub_test_post_meta']    = array();
}
