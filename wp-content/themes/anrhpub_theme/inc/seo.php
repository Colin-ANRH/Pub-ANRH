<?php
/**
 * SEO global — meta, Open Graph, schema, sitemap.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_SEO_TITLE_META', '_anrhpub_seo_title' );
define( 'ANRHPUB_SEO_DESC_META', '_anrhpub_seo_description' );
define( 'ANRHPUB_SEO_NOINDEX_META', '_anrhpub_seo_noindex' );

/**
 * Contexte SEO courant.
 *
 * @return array{title: string, description: string, url: string, image: string, type: string, noindex: bool}
 */
function anrhpub_seo_get_context() {
	$site_name = get_bloginfo( 'name' );
	$default   = sprintf(
		/* translators: %s: site name */
		__( '%s — objets publicitaires personnalisés, marquage et catalogue professionnel à Peyruis.', 'anrhpub_theme' ),
		$site_name
	);

	$ctx = array(
		'title'       => $site_name,
		'description' => $default,
		'url'         => home_url( add_query_arg( array() ) ),
		'image'       => function_exists( 'anrhpub_get_brand_image_url' ) ? anrhpub_get_brand_image_url() : '',
		'type'        => 'website',
		'noindex'     => false,
	);

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$custom_title = get_post_meta( $post->ID, ANRHPUB_SEO_TITLE_META, true );
			$custom_desc  = get_post_meta( $post->ID, ANRHPUB_SEO_DESC_META, true );
			$ctx['title'] = $custom_title ? $custom_title : get_the_title( $post );
			$ctx['url']   = get_permalink( $post );

			if ( $custom_desc ) {
				$ctx['description'] = $custom_desc;
			} elseif ( has_excerpt( $post ) ) {
				$ctx['description'] = wp_strip_all_tags( get_the_excerpt( $post ) );
			} else {
				$ctx['description'] = wp_trim_words( wp_strip_all_tags( $post->post_content ), 28, '…' );
			}

			if ( has_post_thumbnail( $post ) ) {
				$ctx['image'] = get_the_post_thumbnail_url( $post, 'large' );
			}

			$ctx['type'] = 'anr_product' === $post->post_type ? 'product' : 'article';
			$ctx['noindex'] = (bool) get_post_meta( $post->ID, ANRHPUB_SEO_NOINDEX_META, true );
		}
	} elseif ( is_tax( 'anr_category' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$ctx['title'] = $term->name . ' | ' . __( 'Catalogue', 'anrhpub_theme' );
			$ctx['description'] = $term->description ? wp_strip_all_tags( $term->description ) : sprintf( __( 'Produits publicitaires — %s.', 'anrhpub_theme' ), $term->name );
			$ctx['url'] = get_term_link( $term );
		}
	} elseif ( is_post_type_archive( 'anr_product' ) ) {
		$ctx['title'] = __( 'Catalogue produits publicitaires', 'anrhpub_theme' ) . ' | ' . $site_name;
		$ctx['description'] = __( 'Références classées par catégorie — devis personnalisé avec marquage.', 'anrhpub_theme' );
		$ctx['url'] = anrhpub_catalogue_url();
	} elseif ( is_front_page() ) {
		$tagline = get_bloginfo( 'description' );
		$ctx['title'] = $site_name . ( $tagline ? ' — ' . $tagline : '' );
	}

	if ( function_exists( 'anrhpub_catalogue_is_search_active' ) && anrhpub_catalogue_is_search_active() ) {
		$ctx['noindex'] = true;
	}

	foreach ( array( 'connexion', 'inscription', 'mon-compte', 'panier-devis' ) as $private_slug ) {
		if ( is_page( $private_slug ) ) {
			$ctx['noindex'] = true;
		}
	}

	$ctx['description'] = anrhpub_seo_trim( $ctx['description'], 160 );
	$ctx['title']       = anrhpub_seo_trim( $ctx['title'], 70 );

	return $ctx;
}

/**
 * @param string $text Text.
 * @param int    $max  Max chars.
 * @return string
 */
function anrhpub_seo_trim( $text, $max ) {
	$text = trim( wp_strip_all_tags( $text ) );

	if ( mb_strlen( $text ) <= $max ) {
		return $text;
	}

	return mb_substr( $text, 0, $max - 1 ) . '…';
}

/**
 * Filtre titre document.
 *
 * @param array $parts Parts.
 * @return array
 */
function anrhpub_seo_document_title_parts( $parts ) {
	$ctx = anrhpub_seo_get_context();
	$parts['title'] = $ctx['title'];

	return $parts;
}
add_filter( 'document_title_parts', 'anrhpub_seo_document_title_parts', 20 );

/**
 * Meta + OG + canonical.
 */
function anrhpub_seo_wp_head() {
	if ( is_admin() ) {
		return;
	}

	$ctx = anrhpub_seo_get_context();

	if ( $ctx['description'] ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $ctx['description'] ) );
	}

	$canonical = $ctx['url'];
	if ( function_exists( 'anrhpub_get_current_catalogue_url' ) && anrhpub_is_catalogue_context() ) {
		$canonical = anrhpub_get_current_catalogue_url();
	}

	printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canonical ) );

	if ( $ctx['noindex'] ) {
		echo '<meta name="robots" content="noindex,follow" />' . "\n";
	}

	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $ctx['title'] ) );
	printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $ctx['description'] ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $canonical ) );
	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( 'product' === $ctx['type'] ? 'product' : 'website' ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:locale" content="fr_FR" />' . "\n" );

	if ( $ctx['image'] ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $ctx['image'] ) );
	}

	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $ctx['title'] ) );
	printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $ctx['description'] ) );
}
add_action( 'wp_head', 'anrhpub_seo_wp_head', 3 );

/**
 * JSON-LD Organization + WebSite / Product.
 */
function anrhpub_seo_json_ld() {
	$graphs = array(
		array(
			'@type' => 'Organization',
			'@id'   => home_url( '/#organization' ),
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
			'logo'  => function_exists( 'anrhpub_get_brand_image_url' ) ? anrhpub_get_brand_image_url() : '',
			'email' => anrhpub_get_contact_email(),
			'telephone' => '+33492612713',
			'address' => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => 'Av. Pierre Gassendi',
				'addressLocality' => 'Peyruis',
				'postalCode'      => '04310',
				'addressCountry'  => 'FR',
			),
		),
	);

	if ( is_front_page() ) {
		$graphs[] = array(
			'@type'       => 'WebSite',
			'@id'         => home_url( '/#website' ),
			'url'         => home_url( '/' ),
			'name'        => get_bloginfo( 'name' ),
			'publisher'   => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => anrhpub_catalogue_url() . '?catalogue_q={search_term_string}',
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	if ( is_singular( 'anr_product' ) ) {
		$post_id = get_the_ID();
		$graphs[] = array(
			'@type'       => 'Product',
			'name'        => get_the_title(),
			'description' => anrhpub_seo_get_context()['description'],
			'sku'         => (string) get_post_meta( $post_id, 'anr_reference', true ) ?: (string) $post_id,
			'image'       => has_post_thumbnail() ? get_the_post_thumbnail_url( $post_id, 'large' ) : '',
			'offers'      => array(
				'@type'         => 'Offer',
				'priceCurrency' => 'EUR',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink(),
			),
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graphs,
		),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	) . '</script>' . "\n";
}
add_action( 'wp_head', 'anrhpub_seo_json_ld', 20 );

/**
 * Sitemap : exclure pages privées.
 *
 * @param WP_Query $query Query.
 */
function anrhpub_seo_sitemap_posts( $query ) {
	if ( ! is_object( $query ) ) {
		return $query;
	}

	$exclude = array();
	foreach ( array( 'connexion', 'inscription', 'mon-compte', 'panier-devis' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$exclude[] = $page->ID;
		}
	}

	if ( $exclude ) {
		$query->set( 'post__not_in', array_merge( (array) $query->get( 'post__not_in' ), $exclude ) );
	}

	return $query;
}
add_filter( 'wp_sitemaps_posts_query_args', 'anrhpub_seo_sitemap_posts' );

/**
 * Séparateur titre.
 *
 * @return string
 */
function anrhpub_seo_title_separator() {
	return '|';
}
add_filter( 'document_title_separator', 'anrhpub_seo_title_separator' );
