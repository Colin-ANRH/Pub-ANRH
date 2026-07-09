<?php
/**
 * Favicon (logo_fav) et image de partage sur tout le site.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL du favicon — pictogramme ANRH (logo_fav.webp).
 *
 * @return string
 */
function anrhpub_get_favicon_url() {
	if ( function_exists( 'anrhpub_theme_image_uri' ) ) {
		$uri = anrhpub_theme_image_uri( 'assets/images/logo_fav' );

		if ( $uri && false !== strpos( $uri, 'logo_fav' ) ) {
			return $uri;
		}
	}

	return ANRHPUB_THEME_URI . '/assets/images/logo_fav.webp';
}

/**
 * URL image marque pour Open Graph / Twitter (même pictogramme que le favicon).
 *
 * @return string
 */
function anrhpub_get_brand_image_url() {
	return anrhpub_get_favicon_url();
}

/**
 * Type MIME de l’icône selon l’extension.
 *
 * @param string $url URL du fichier.
 * @return string
 */
function anrhpub_brand_image_mime( $url ) {
	$ext = strtolower( pathinfo( (string) wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );

	switch ( $ext ) {
		case 'png':
			return 'image/png';
		case 'jpg':
		case 'jpeg':
			return 'image/jpeg';
		case 'svg':
			return 'image/svg+xml';
		case 'ico':
			return 'image/x-icon';
		case 'webp':
		default:
			return 'image/webp';
	}
}

/**
 * Balises favicon + Open Graph / Twitter (image).
 */
function anrhpub_output_site_branding_meta() {
	if ( is_admin() ) {
		return;
	}

	$favicon_url = anrhpub_get_favicon_url();
	$share_url   = anrhpub_get_brand_image_url();
	$fav_mime    = anrhpub_brand_image_mime( $favicon_url );
	$share_mime  = anrhpub_brand_image_mime( $share_url );

	printf(
		'<link rel="icon" href="%1$s" type="%2$s" sizes="32x32">' . "\n",
		esc_url( $favicon_url ),
		esc_attr( $fav_mime )
	);
	printf(
		'<link rel="icon" href="%1$s" type="%2$s" sizes="192x192">' . "\n",
		esc_url( $favicon_url ),
		esc_attr( $fav_mime )
	);
	printf(
		'<link rel="shortcut icon" href="%s">' . "\n",
		esc_url( $favicon_url )
	);
	printf(
		'<link rel="apple-touch-icon" href="%s">' . "\n",
		esc_url( $favicon_url )
	);

}

/**
 * Remplace l’icône du site WordPress par logo_fav.
 */
function anrhpub_setup_site_branding() {
	remove_action( 'wp_head', 'wp_site_icon', 99 );
	add_action( 'wp_head', 'anrhpub_output_site_branding_meta', 99 );
}
add_action( 'init', 'anrhpub_setup_site_branding' );

/**
 * Filtre l’URL de l’icône WP (login, admin, API).
 *
 * @param string $url     URL actuelle.
 * @param int    $size    Taille demandée.
 * @param int    $blog_id ID du site.
 * @return string
 */
function anrhpub_filter_site_icon_url( $url, $size = 512, $blog_id = 0 ) {
	unset( $url, $size, $blog_id );

	return anrhpub_get_favicon_url();
}
add_filter( 'get_site_icon_url', 'anrhpub_filter_site_icon_url', 10, 3 );
