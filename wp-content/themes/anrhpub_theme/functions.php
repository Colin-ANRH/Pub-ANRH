<?php
/**
 * ANRH Pub Theme functions.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_THEME_VERSION', '2.41.0' );
define( 'ANRHPUB_THEME_DIR', get_template_directory() );
define( 'ANRHPUB_THEME_URI', get_template_directory_uri() );

require_once ANRHPUB_THEME_DIR . '/inc/post-types.php';
require_once ANRHPUB_THEME_DIR . '/inc/categories.php';
require_once ANRHPUB_THEME_DIR . '/inc/template-tags.php';
require_once ANRHPUB_THEME_DIR . '/inc/nav-walker.php';
require_once ANRHPUB_THEME_DIR . '/inc/page-content.php';
require_once ANRHPUB_THEME_DIR . '/inc/marquage.php';
require_once ANRHPUB_THEME_DIR . '/inc/breadcrumbs.php';
require_once ANRHPUB_THEME_DIR . '/inc/catalogue.php';
require_once ANRHPUB_THEME_DIR . '/inc/catalogue-search.php';
require_once ANRHPUB_THEME_DIR . '/inc/demo-data.php';
require_once ANRHPUB_THEME_DIR . '/inc/product-images.php';
require_once ANRHPUB_THEME_DIR . '/inc/product-gallery.php';
require_once ANRHPUB_THEME_DIR . '/inc/client-session.php';
require_once ANRHPUB_THEME_DIR . '/inc/client-account.php';
require_once ANRHPUB_THEME_DIR . '/inc/contact-form.php';
require_once ANRHPUB_THEME_DIR . '/inc/client-addresses.php';
require_once ANRHPUB_THEME_DIR . '/inc/client-orders.php';
require_once ANRHPUB_THEME_DIR . '/inc/product-tech-sheet.php';
require_once ANRHPUB_THEME_DIR . '/inc/product-single-sections.php';
require_once ANRHPUB_THEME_DIR . '/inc/product-colors.php';
require_once ANRHPUB_THEME_DIR . '/inc/quote-cart.php';
require_once ANRHPUB_THEME_DIR . '/inc/home-slider.php';
require_once ANRHPUB_THEME_DIR . '/inc/trust-logos.php';
require_once ANRHPUB_THEME_DIR . '/inc/home-trust.php';
require_once ANRHPUB_THEME_DIR . '/inc/home-spotlight.php';
require_once ANRHPUB_THEME_DIR . '/inc/nav-nouveautes.php';
require_once ANRHPUB_THEME_DIR . '/inc/anrh-history.php';
require_once ANRHPUB_THEME_DIR . '/inc/newsletter.php';
require_once ANRHPUB_THEME_DIR . '/inc/newsletter-admin.php';
require_once ANRHPUB_THEME_DIR . '/inc/webp-images.php';
require_once ANRHPUB_THEME_DIR . '/inc/webp-images-admin.php';
require_once ANRHPUB_THEME_DIR . '/inc/terms-of-use.php';
require_once ANRHPUB_THEME_DIR . '/inc/wp-admin-front.php';
require_once ANRHPUB_THEME_DIR . '/inc/site-branding.php';
require_once ANRHPUB_THEME_DIR . '/inc/seo.php';
require_once ANRHPUB_THEME_DIR . '/inc/seo-admin.php';
require_once ANRHPUB_THEME_DIR . '/inc/gdpr.php';
require_once ANRHPUB_THEME_DIR . '/inc/charset-repair.php';
require_once ANRHPUB_THEME_DIR . '/inc/b2b/bootstrap.php';

// Admin texts for accueil « confiance / partenaires ».
require_once ANRHPUB_THEME_DIR . '/inc/home-trust-admin.php';

/**
 * Theme setup.
 */
function anrhpub_theme_setup() {
	load_theme_textdomain( 'anrhpub_theme', ANRHPUB_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 280,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );

	add_image_size( 'anr_product_card', 480, 480, true );

	register_nav_menus(
		array(
			'primary'   => __( 'Menu principal', 'anrhpub_theme' ),
			'footer'    => __( 'Menu pied de page', 'anrhpub_theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'anrhpub_theme_setup' );

/**
 * UTF-8 : évite les accents cassés (Apache, e-mails, chaînes PHP).
 */
function anrhpub_utf8_bootstrap() {
	if ( function_exists( 'mb_internal_encoding' ) ) {
		mb_internal_encoding( 'UTF-8' );
	}
}
add_action( 'after_setup_theme', 'anrhpub_utf8_bootstrap', 0 );

/**
 * En-tête HTML UTF-8 explicite sur le front.
 */
function anrhpub_utf8_headers() {
	if ( is_admin() || headers_sent() ) {
		return;
	}

	header( 'Content-Type: text/html; charset=UTF-8' );
}
add_action( 'send_headers', 'anrhpub_utf8_headers', 0 );

add_filter(
	'wp_mail_charset',
	static function () {
		return 'UTF-8';
	}
);

/**
 * Pages nécessitant pages.css (mise en page contenu / catalogue).
 *
 * @return bool
 */
function anrhpub_needs_pages_css() {
	return is_front_page()
		|| is_singular( 'anr_product' )
		|| is_post_type_archive( 'anr_product' )
		|| is_tax( 'anr_category' )
		|| is_page();
}

/**
 * Pages nécessitant b2b.css (compte client, panier devis, fiche produit).
 *
 * @return bool
 */
function anrhpub_needs_b2b_css() {
	if ( is_singular( 'anr_product' ) ) {
		return true;
	}

	if ( is_page( array( 'mon-compte', 'connexion', 'inscription', 'panier-devis' ) ) ) {
		return true;
	}

	return function_exists( 'anrhpub_is_account_page' ) && anrhpub_is_account_page();
}

/**
 * Enqueue styles and scripts.
 */
function anrhpub_enqueue_assets() {
	wp_enqueue_style(
		'anrhpub-fonts-face',
		ANRHPUB_THEME_URI . '/assets/fonts/fonts.css',
		array(),
		ANRHPUB_THEME_VERSION
	);

	wp_enqueue_style(
		'anrhpub-main',
		ANRHPUB_THEME_URI . '/assets/css/main.css',
		array( 'anrhpub-fonts-face' ),
		ANRHPUB_THEME_VERSION
	);

	wp_enqueue_style(
		'anrhpub-charte',
		ANRHPUB_THEME_URI . '/assets/css/charte.css',
		array( 'anrhpub-main' ),
		ANRHPUB_THEME_VERSION
	);

	wp_enqueue_style(
		'anrhpub-epure',
		ANRHPUB_THEME_URI . '/assets/css/epure.css',
		array( 'anrhpub-charte' ),
		ANRHPUB_THEME_VERSION
	);

	wp_enqueue_style(
		'anrhpub-animations',
		ANRHPUB_THEME_URI . '/assets/css/animations.css',
		array( 'anrhpub-epure' ),
		ANRHPUB_THEME_VERSION
	);

	wp_enqueue_style(
		'anrhpub-mega-menu',
		ANRHPUB_THEME_URI . '/assets/css/mega-menu.css',
		array( 'anrhpub-charte' ),
		ANRHPUB_THEME_VERSION
	);

	wp_enqueue_style(
		'anrhpub-nav-responsive',
		ANRHPUB_THEME_URI . '/assets/css/nav-responsive.css',
		array( 'anrhpub-mega-menu', 'anrhpub-epure' ),
		ANRHPUB_THEME_VERSION
	);

	if ( is_front_page() ) {
		wp_enqueue_style(
			'anrhpub-vitrine',
			ANRHPUB_THEME_URI . '/assets/css/vitrine.css',
			array( 'anrhpub-animations' ),
			ANRHPUB_THEME_VERSION
		);
	}

	$pages_deps = array( 'anrhpub-animations' );
	if ( is_front_page() ) {
		$pages_deps[] = 'anrhpub-vitrine';
	}

	if ( anrhpub_needs_pages_css() ) {
		wp_enqueue_style(
			'anrhpub-pages',
			ANRHPUB_THEME_URI . '/assets/css/pages.css',
			$pages_deps,
			ANRHPUB_THEME_VERSION
		);
	}

	if ( anrhpub_needs_b2b_css() ) {
		$b2b_deps = array( 'anrhpub-animations' );
		if ( wp_style_is( 'anrhpub-pages', 'enqueued' ) ) {
			$b2b_deps[] = 'anrhpub-pages';
		}
		wp_enqueue_style(
			'anrhpub-b2b',
			ANRHPUB_THEME_URI . '/assets/css/b2b.css',
			$b2b_deps,
			ANRHPUB_THEME_VERSION
		);
	}

	wp_enqueue_script(
		'anrhpub-main',
		ANRHPUB_THEME_URI . '/assets/js/main.js',
		array(),
		ANRHPUB_THEME_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_assets' );

/**
 * Default custom logo from bundled asset.
 */
function anrhpub_custom_logo_setup() {
	if ( get_theme_mod( 'custom_logo' ) ) {
		return;
	}
	add_filter(
		'get_custom_logo',
		function ( $html ) {
			if ( $html ) {
				return $html;
			}
			$logo = anrhpub_theme_image_uri( 'assets/images/logo-anr' );
			$home = esc_url( home_url( '/' ) );
			$alt  = esc_attr( get_bloginfo( 'name' ) );
			return '<a href="' . $home . '" class="custom-logo-link" rel="home">'
				. '<img src="' . esc_url( $logo ) . '" class="custom-logo" alt="' . $alt . '" width="220" height="auto" loading="eager" /></a>';
		}
	);
}
add_action( 'wp', 'anrhpub_custom_logo_setup' );

/**
 * Widget areas.
 */
function anrhpub_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Pied de page — colonne 1', 'anrhpub_theme' ),
			'id'            => 'footer-1',
			'before_widget' => '<div class="footer-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="footer-widget__title">',
			'after_title'   => '</h4>',
		)
	);
}
add_action( 'widgets_init', 'anrhpub_widgets_init' );

/**
 * Body classes.
 *
 * @param array $classes Body classes.
 */
function anrhpub_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'anr-front';
	}
	if ( is_singular( 'anr_product' ) ) {
		$classes[] = 'anr-single-product';
	}

	if ( anrhpub_is_catalogue_context() ) {
		$classes[] = 'anr-catalogue';
	}

	if ( is_user_logged_in() ) {
		$classes[] = 'anr-logged-in';
	}

	if ( anrhpub_is_account_page() ) {
		$classes[] = 'anr-account-page';
	}

	return $classes;
}
add_filter( 'body_class', 'anrhpub_body_classes' );

/**
 * Excerpt length for product cards.
 */
function anrhpub_excerpt_length( $length ) {
	if ( is_post_type_archive( 'anr_product' ) || is_front_page() ) {
		return 22;
	}
	return $length;
}
add_filter( 'excerpt_length', 'anrhpub_excerpt_length' );

/**
 * Flush rewrite rules on theme activation.
 */
function anrhpub_flush_rewrites() {
	anrhpub_register_product_cpt();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'anrhpub_flush_rewrites', 20 );
