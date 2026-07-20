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
 * Environnement staging (pub.anrh.fr) ?
 *
 * @return bool
 */
function anrhpub_is_staging_environment() {
	return function_exists( 'wp_get_environment_type' ) && 'staging' === wp_get_environment_type();
}

/**
 * La page courante doit être noindex ?
 *
 * @param array $ctx Contexte SEO.
 * @return bool
 */
function anrhpub_seo_should_noindex( array $ctx = array() ) {
	if ( anrhpub_is_staging_environment() ) {
		return true;
	}

	if ( ! empty( $ctx['noindex'] ) ) {
		return true;
	}

	if ( function_exists( 'anrhpub_catalogue_has_active_facets' ) && anrhpub_catalogue_has_active_facets() ) {
		return true;
	}

	return false;
}

/**
 * Contexte SEO courant.
 *
 * @return array{title: string, description: string, url: string, image: string, type: string, noindex: bool, post_id: int}
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
		'post_id'     => 0,
	);

	$paged = max( 1, (int) get_query_var( 'paged' ) );

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$custom_title = get_post_meta( $post->ID, ANRHPUB_SEO_TITLE_META, true );
			$custom_desc  = get_post_meta( $post->ID, ANRHPUB_SEO_DESC_META, true );
			$ctx['title'] = $custom_title ? $custom_title : get_the_title( $post );
			$ctx['url']   = get_permalink( $post );
			$ctx['post_id'] = (int) $post->ID;

			if ( $custom_desc ) {
				$ctx['description'] = $custom_desc;
			} elseif ( has_excerpt( $post ) ) {
				$ctx['description'] = wp_strip_all_tags( get_the_excerpt( $post ) );
			} else {
				$desc = wp_strip_all_tags( $post->post_content );
				if ( function_exists( 'anrhpub_normalize_utf8_text' ) ) {
					$desc = anrhpub_normalize_utf8_text( $desc );
				}
				$ctx['description'] = wp_trim_words( $desc, 28, '…' );
			}

			if ( has_post_thumbnail( $post ) ) {
				$ctx['image'] = get_the_post_thumbnail_url( $post, 'large' );
			}

			$ctx['type']    = 'anr_product' === $post->post_type ? 'product' : 'article';
			$ctx['noindex'] = (bool) get_post_meta( $post->ID, ANRHPUB_SEO_NOINDEX_META, true );
		}
	} elseif ( is_tax( 'anr_category' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$ctx['title'] = $term->name . ' | ' . __( 'Catalogue', 'anrhpub_theme' );
			$ctx['description'] = $term->description ? wp_strip_all_tags( $term->description ) : sprintf( __( 'Produits publicitaires personnalisables — %s. Devis et marquage ANRH Peyruis.', 'anrhpub_theme' ), $term->name );
			$link = get_term_link( $term );
			$ctx['url'] = ! is_wp_error( $link ) ? (string) $link : anrhpub_catalogue_url();
		}
	} elseif ( is_post_type_archive( 'anr_product' ) ) {
		$ctx['title'] = __( 'Catalogue produits publicitaires', 'anrhpub_theme' ) . ' | ' . $site_name;
		$ctx['description'] = __( 'Références classées par catégorie — devis personnalisé avec marquage.', 'anrhpub_theme' );
		$ctx['url'] = anrhpub_catalogue_url();
	} elseif ( is_front_page() ) {
		$tagline = get_bloginfo( 'description' );
		$ctx['title'] = $site_name . ( $tagline ? ' — ' . $tagline : '' );
	}

	if ( $paged > 1 && ! is_singular() ) {
		$ctx['title'] .= sprintf(
			/* translators: %d: page number */
			__( ' — page %d', 'anrhpub_theme' ),
			$paged
		);
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
	$ctx['title']         = anrhpub_seo_trim( $ctx['title'], 70 );

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
 * Texte alt image produit.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function anrhpub_get_product_image_alt( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$title   = get_the_title( $post_id );
	$ref     = (string) get_post_meta( $post_id, 'anr_reference', true );

	if ( $ref && $title ) {
		return sprintf(
			/* translators: 1: product title, 2: reference */
			__( '%1$s — réf. %2$s', 'anrhpub_theme' ),
			$title,
			$ref
		);
	}

	return $title ? $title : __( 'Produit publicitaire', 'anrhpub_theme' );
}

/**
 * URL canonique SEO.
 *
 * @param array $ctx Contexte.
 * @return string
 */
function anrhpub_seo_get_canonical_url( array $ctx ) {
	if ( function_exists( 'anrhpub_get_current_catalogue_url' ) && anrhpub_is_catalogue_context() ) {
		return anrhpub_get_current_catalogue_url();
	}

	return $ctx['url'];
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
	if ( is_admin() || anrhpub_is_staging_environment() ) {
		if ( anrhpub_is_staging_environment() ) {
			echo '<meta name="robots" content="noindex,nofollow,noarchive,nosnippet" />' . "\n";
			echo '<meta name="googlebot" content="noindex,nofollow,noarchive,nosnippet" />' . "\n";
		}
		return;
	}

	$ctx       = anrhpub_seo_get_context();
	$noindex   = anrhpub_seo_should_noindex( $ctx );
	$canonical = anrhpub_seo_get_canonical_url( $ctx );

	if ( $ctx['description'] ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $ctx['description'] ) );
	}

	printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canonical ) );

	if ( $noindex ) {
		echo '<meta name="robots" content="noindex,follow" />' . "\n";
	}

	if ( $noindex ) {
		return;
	}

	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $ctx['title'] ) );
	printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $ctx['description'] ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $canonical ) );
	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( 'product' === $ctx['type'] ? 'product' : 'website' ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:locale" content="fr_FR" />' . "\n" );

	if ( $ctx['image'] ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $ctx['image'] ) );
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $ctx['image'] ) );
	}

	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $ctx['title'] ) );
	printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $ctx['description'] ) );
}
add_action( 'wp_head', 'anrhpub_seo_wp_head', 3 );

/**
 * Produits pour schema ItemList catalogue.
 *
 * @param int $limit Max items.
 * @return array<int, array{name: string, url: string}>
 */
function anrhpub_seo_get_catalogue_list_items( $limit = 12 ) {
	$args = array(
		'post_type'              => 'anr_product',
		'post_status'            => 'publish',
		'posts_per_page'         => max( 1, min( 24, (int) $limit ) ),
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
		'orderby'                => 'title',
		'order'                  => 'ASC',
	);

	if ( is_tax( 'anr_category' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$args['tax_query'] = array(
				array(
					'taxonomy'         => 'anr_category',
					'field'            => 'term_id',
					'terms'            => (int) $term->term_id,
					'include_children' => true,
				),
			);
		}
	}

	$query = new WP_Query( $args );
	$items = array();

	foreach ( $query->posts as $post ) {
		$items[] = array(
			'name' => get_the_title( $post ),
			'url'  => get_permalink( $post ),
		);
	}

	return $items;
}

/**
 * JSON-LD Organization + WebSite / Product / CollectionPage.
 */
function anrhpub_seo_json_ld() {
	if ( is_admin() || anrhpub_is_staging_environment() ) {
		return;
	}

	$ctx    = anrhpub_seo_get_context();
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
		array(
			'@type'       => 'LocalBusiness',
			'@id'         => home_url( '/#localbusiness' ),
			'name'        => get_bloginfo( 'name' ),
			'url'         => home_url( '/' ),
			'image'       => function_exists( 'anrhpub_get_brand_image_url' ) ? anrhpub_get_brand_image_url() : '',
			'telephone'   => '+33492612713',
			'priceRange'  => '€€',
			'address'     => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => 'Av. Pierre Gassendi',
				'addressLocality' => 'Peyruis',
				'postalCode'      => '04310',
				'addressCountry'  => 'FR',
			),
			'parentOrganization' => array( '@id' => home_url( '/#organization' ) ),
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

	if ( is_post_type_archive( 'anr_product' ) || is_tax( 'anr_category' ) ) {
		$list_items = array();
		foreach ( anrhpub_seo_get_catalogue_list_items() as $index => $item ) {
			$list_items[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'url'      => $item['url'],
				'name'     => $item['name'],
			);
		}

		$graphs[] = array(
			'@type'       => 'CollectionPage',
			'name'        => $ctx['title'],
			'description' => $ctx['description'],
			'url'         => anrhpub_seo_get_canonical_url( $ctx ),
			'isPartOf'    => array( '@id' => home_url( '/#website' ) ),
		);

		if ( $list_items ) {
			$graphs[] = array(
				'@type'           => 'ItemList',
				'itemListElement' => $list_items,
			);
		}
	}

	if ( is_singular( 'anr_product' ) ) {
		$post_id = get_the_ID();
		$images  = array();

		if ( has_post_thumbnail( $post_id ) ) {
			$thumb = get_the_post_thumbnail_url( $post_id, 'large' );
			if ( $thumb ) {
				$images[] = $thumb;
			}
		}

		$terms = get_the_terms( $post_id, 'anr_category' );
		$category_name = '';
		if ( $terms && ! is_wp_error( $terms ) ) {
			$category_name = $terms[0]->name;
		}

		$product = array(
			'@type'       => 'Product',
			'name'        => get_the_title(),
			'description' => $ctx['description'],
			'sku'         => (string) get_post_meta( $post_id, 'anr_reference', true ) ?: (string) $post_id,
			'mpn'         => (string) get_post_meta( $post_id, 'anr_reference', true ),
			'brand'       => array(
				'@type' => 'Brand',
				'name'  => get_bloginfo( 'name' ),
			),
			'url'         => get_permalink(),
			'offers'      => array(
				'@type'         => 'Offer',
				'priceCurrency' => 'EUR',
				'availability'  => 'https://schema.org/InStock',
				'url'           => get_permalink(),
			),
		);

		if ( $images ) {
			$product['image'] = $images;
		}

		if ( $category_name ) {
			$product['category'] = $category_name;
		}

		$graphs[] = $product;
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
 * Sitemap : exclure pages privées et contenus noindex.
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

	$meta_query = (array) $query->get( 'meta_query' );
	$meta_query[] = array(
		'relation' => 'OR',
		array(
			'key'     => ANRHPUB_SEO_NOINDEX_META,
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => ANRHPUB_SEO_NOINDEX_META,
			'value'   => '1',
			'compare' => '!=',
		),
	);
	$query->set( 'meta_query', $meta_query );

	return $query;
}
add_filter( 'wp_sitemaps_posts_query_args', 'anrhpub_seo_sitemap_posts' );

/**
 * Désactive les sitemaps en staging (filet de sécurité thème).
 *
 * @param bool $enabled Enabled.
 * @return bool
 */
function anrhpub_seo_disable_sitemaps_on_staging( $enabled ) {
	if ( anrhpub_is_staging_environment() ) {
		return false;
	}

	return $enabled;
}
add_filter( 'wp_sitemaps_enabled', 'anrhpub_seo_disable_sitemaps_on_staging', 999 );

/**
 * robots.txt production.
 *
 * @param string $output Output.
 * @param bool   $public Public.
 * @return string
 */
function anrhpub_seo_robots_txt( $output, $public ) {
	if ( anrhpub_is_staging_environment() || ! $public ) {
		return $output;
	}

	$rules  = "User-agent: *\n";
	$rules .= "Disallow: /wp-admin/\n";
	$rules .= "Allow: /wp-admin/admin-ajax.php\n";
	$rules .= "Disallow: /wp-includes/\n";
	$rules .= "Disallow: /panier-devis/\n";
	$rules .= "Disallow: /mon-compte/\n";
	$rules .= "Disallow: /connexion/\n";
	$rules .= "Disallow: /inscription/\n";
	$rules .= "\nSitemap: " . home_url( '/wp-sitemap.xml' ) . "\n";

	return $rules;
}
add_filter( 'robots_txt', 'anrhpub_seo_robots_txt', 10, 2 );

/**
 * wp_robots : filet staging côté thème.
 *
 * @param array $robots Robots.
 * @return array
 */
function anrhpub_seo_wp_robots_staging( $robots ) {
	if ( anrhpub_is_staging_environment() ) {
		$robots['noindex']   = true;
		$robots['nofollow']  = true;
		$robots['noarchive'] = true;
		$robots['nosnippet'] = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'anrhpub_seo_wp_robots_staging', 999 );

/**
 * Séparateur titre.
 *
 * @return string
 */
function anrhpub_seo_title_separator() {
	return '|';
}
add_filter( 'document_title_separator', 'anrhpub_seo_title_separator' );
