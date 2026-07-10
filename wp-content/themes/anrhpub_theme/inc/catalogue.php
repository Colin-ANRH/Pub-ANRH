<?php
/**
 * Helpers catalogue vitrine (pas de vente en ligne).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL fiche produit — zone ajout panier (ancre).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function anrhpub_get_product_add_url( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$url     = get_permalink( $post_id );

	if ( ! $url ) {
		return anrhpub_catalogue_url();
	}

	return $url . '#product-quote';
}

/**
 * URL d’ajout au panier (alias — plus de lien contact/devis direct).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function anrhpub_get_product_devis_url( $post_id = 0 ) {
	return anrhpub_get_product_add_url( $post_id );
}

/**
 * Lien archive catalogue.
 *
 * @return string
 */
function anrhpub_catalogue_url() {
	$link = get_post_type_archive_link( 'anr_product' );
	return $link ? $link : home_url( '/catalogue/' );
}

/**
 * Slug de la catégorie catalogue « nouveautés ».
 *
 * @return string
 */
function anrhpub_nouveautes_category_slug() {
	return 'les-nouveautes-objets-pubs';
}

/**
 * URL de la vue catalogue — section nouveautés.
 *
 * @return string
 */
function anrhpub_nouveautes_catalogue_url() {
	$term = get_term_by( 'slug', anrhpub_nouveautes_category_slug(), 'anr_category' );

	if ( $term && ! is_wp_error( $term ) ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return (string) $link;
		}
	}

	return anrhpub_catalogue_url();
}

/**
 * Affichage catalogue sur la catégorie nouveautés.
 *
 * @return bool
 */
function anrhpub_is_nouveautes_catalogue_view() {
	if ( ! is_tax( 'anr_category' ) ) {
		return false;
	}

	$term = get_queried_object();

	return $term instanceof WP_Term && anrhpub_nouveautes_category_slug() === $term->slug;
}

/**
 * Pages du parcours catalogue / vitrine.
 *
 * @return bool
 */
function anrhpub_is_catalogue_context() {
	return is_post_type_archive( 'anr_product' )
		|| is_tax( 'anr_category' )
		|| is_singular( 'anr_product' );
}

/**
 * Requête AJAX partielle du catalogue.
 *
 * @return bool
 */
function anrhpub_catalogue_is_partial_request() {
	return isset( $_GET['catalogue_partial'] ) && '1' === (string) $_GET['catalogue_partial'];
}

/**
 * Données du hero catalogue pour la requête courante.
 *
 * @return array{kicker: string, title: string, lead: string}
 */
function anrhpub_get_catalogue_hero_args() {
	if ( anrhpub_catalogue_is_search_active() ) {
		$term = anrhpub_get_catalogue_search_term();

		return array(
			'kicker' => __( 'Recherche catalogue', 'anrhpub_theme' ),
			'title'  => sprintf(
				/* translators: %s: search keywords */
				__( 'Résultats pour « %s »', 'anrhpub_theme' ),
				$term
			),
			'lead'   => __( 'Produits, références et catégories correspondant à votre recherche — tarifs sur devis.', 'anrhpub_theme' ),
		);
	}

	$current_term = get_queried_object();
	$is_tax       = is_tax( 'anr_category' );

	if ( $is_tax && $current_term instanceof WP_Term ) {
		return array(
			'kicker' => __( 'Catégorie catalogue', 'anrhpub_theme' ),
			'title'  => $current_term->name,
			'lead'   => $current_term->description
				? $current_term->description
				: __( 'Produits publicitaires personnalisables de cette gamme — tarifs sur devis.', 'anrhpub_theme' ),
		);
	}

	return array(
		'kicker' => __( 'Catalogue', 'anrhpub_theme' ),
		'title'  => __( 'Nos produits publicitaires', 'anrhpub_theme' ),
		'lead'   => __( 'Références classées par catégorie. Consultez les fiches produit et contactez-nous pour un devis personnalisé avec marquage.', 'anrhpub_theme' ),
	);
}

/**
 * URL canonique de la vue catalogue courante (sans paramètre AJAX).
 *
 * @return string
 */
function anrhpub_get_current_catalogue_url() {
	if ( anrhpub_catalogue_is_search_active() ) {
		return anrhpub_catalogue_search_url(
			anrhpub_get_catalogue_search_term(),
			max( 1, (int) get_query_var( 'paged' ) )
		);
	}

	$paged = max( 1, (int) get_query_var( 'paged' ) );

	if ( is_tax( 'anr_category' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				$url = (string) $link;
				if ( $paged > 1 ) {
					$url = trailingslashit( $url ) . 'page/' . $paged . '/';
				}
				return $url;
			}
		}
	}

	$url = anrhpub_catalogue_url();
	if ( $paged > 1 ) {
		$url = trailingslashit( $url ) . 'page/' . $paged . '/';
	}

	return $url;
}

/**
 * Réponse JSON : résultats, filtres et hero.
 */
function anrhpub_serve_catalogue_partial() {
	if ( ! anrhpub_catalogue_is_partial_request() ) {
		return;
	}

	if ( ! is_post_type_archive( 'anr_product' ) && ! is_tax( 'anr_category' ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Page catalogue introuvable.', 'anrhpub_theme' ) ),
			404
		);
	}

	ob_start();
	get_template_part( 'template-parts/catalogue', 'results' );
	$results_html = ob_get_clean();

	ob_start();
	get_template_part( 'template-parts/catalogue', 'filters-list' );
	$filters_html = ob_get_clean();

	ob_start();
	anrhpub_render_breadcrumbs();
	$breadcrumbs_html = ob_get_clean();

	wp_send_json(
		array(
			'results'     => $results_html,
			'filters'     => $filters_html,
			'breadcrumbs' => $breadcrumbs_html,
			'hero'        => anrhpub_get_catalogue_hero_args(),
			'title'       => wp_get_document_title(),
			'url'         => anrhpub_get_current_catalogue_url(),
		)
	);
}
add_action( 'template_redirect', 'anrhpub_serve_catalogue_partial', 5 );

