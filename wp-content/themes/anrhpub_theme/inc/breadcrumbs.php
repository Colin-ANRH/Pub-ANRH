<?php
/**
 * Fil d'Ariane (SEO + accessibilité).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Éléments du fil d'Ariane pour la requête courante.
 *
 * @return array<int, array{label: string, url: string}>
 */
function anrhpub_get_breadcrumb_items() {
	$items   = array();
	$items[] = array(
		'label' => __( 'Accueil', 'anrhpub_theme' ),
		'url'   => home_url( '/' ),
	);

	if ( is_front_page() ) {
		return $items;
	}

	if ( is_home() ) {
		$items[] = array(
			'label' => __( 'Actualités', 'anrhpub_theme' ),
			'url'   => '',
		);
		return $items;
	}

	if ( is_singular( 'anr_product' ) ) {
		$archive = get_post_type_archive_link( 'anr_product' );
		if ( $archive ) {
			$items[] = array(
				'label' => __( 'Nos produits', 'anrhpub_theme' ),
				'url'   => $archive,
			);
		}

		$terms = get_the_terms( get_queried_object_id(), 'anr_category' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$term = $terms[0];
			foreach ( array_reverse( get_ancestors( $term->term_id, 'anr_category' ) ) as $ancestor_id ) {
				$ancestor = get_term( $ancestor_id, 'anr_category' );
				if ( $ancestor && ! is_wp_error( $ancestor ) ) {
					$link = get_term_link( $ancestor );
					if ( ! is_wp_error( $link ) ) {
						$items[] = array(
							'label' => $ancestor->name,
							'url'   => $link,
						);
					}
				}
			}
			$term_link = get_term_link( $term );
			if ( ! is_wp_error( $term_link ) ) {
				$items[] = array(
					'label' => $term->name,
					'url'   => $term_link,
				);
			}
		}

		$items[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
		return $items;
	}

	if ( is_post_type_archive( 'anr_product' ) ) {
		$items[] = array(
			'label' => __( 'Nos produits', 'anrhpub_theme' ),
			'url'   => anrhpub_catalogue_is_search_active() ? anrhpub_catalogue_url() : '',
		);
		if ( anrhpub_catalogue_is_search_active() ) {
			$items[] = array(
				'label' => sprintf(
					/* translators: %s: search keywords */
					__( 'Recherche : %s', 'anrhpub_theme' ),
					anrhpub_get_catalogue_search_term()
				),
				'url'   => '',
			);
		}
		return $items;
	}

	if ( is_tax( 'anr_category' ) ) {
		$archive = get_post_type_archive_link( 'anr_product' );
		if ( $archive ) {
			$items[] = array(
				'label' => __( 'Nos produits', 'anrhpub_theme' ),
				'url'   => $archive,
			);
		}

		if ( anrhpub_catalogue_is_search_active() ) {
			$items[] = array(
				'label' => sprintf(
					/* translators: %s: search keywords */
					__( 'Recherche : %s', 'anrhpub_theme' ),
					anrhpub_get_catalogue_search_term()
				),
				'url'   => '',
			);
			return $items;
		}

		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			foreach ( array_reverse( get_ancestors( $term->term_id, 'anr_category' ) ) as $ancestor_id ) {
				$ancestor = get_term( $ancestor_id, 'anr_category' );
				if ( $ancestor && ! is_wp_error( $ancestor ) ) {
					$link = get_term_link( $ancestor );
					if ( ! is_wp_error( $link ) ) {
						$items[] = array(
							'label' => $ancestor->name,
							'url'   => $link,
						);
					}
				}
			}
			$items[] = array(
				'label' => $term->name,
				'url'   => '',
			);
		}
		return $items;
	}

	if ( is_page( 'connexion' ) ) {
		$items[] = array(
			'label' => __( 'Connexion', 'anrhpub_theme' ),
			'url'   => '',
		);
		return $items;
	}

	if ( is_page( 'inscription' ) ) {
		$items[] = array(
			'label' => __( 'Inscription', 'anrhpub_theme' ),
			'url'   => '',
		);
		return $items;
	}

	if ( is_page( 'mon-compte' ) ) {
		$items[] = array(
			'label' => __( 'Mon compte', 'anrhpub_theme' ),
			'url'   => '',
		);
		return $items;
	}

	if ( is_page() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post && $post->post_parent ) {
			$ancestors = array_reverse( get_post_ancestors( $post ) );
			foreach ( $ancestors as $ancestor_id ) {
				$items[] = array(
					'label' => get_the_title( $ancestor_id ),
					'url'   => get_permalink( $ancestor_id ),
				);
			}
		}
		$items[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
		return $items;
	}

	if ( is_search() ) {
		$items[] = array(
			'label' => sprintf(
				/* translators: %s: search query */
				__( 'Recherche : %s', 'anrhpub_theme' ),
				get_search_query()
			),
			'url'   => '',
		);
		return $items;
	}

	if ( is_404() ) {
		$items[] = array(
			'label' => __( 'Page introuvable', 'anrhpub_theme' ),
			'url'   => '',
		);
		return $items;
	}

	$items[] = array(
		'label' => wp_get_document_title(),
		'url'   => '',
	);

	return $items;
}

/**
 * Affiche le fil d'Ariane.
 */
function anrhpub_render_breadcrumbs() {
	$items = anrhpub_get_breadcrumb_items();
	if ( count( $items ) < 1 ) {
		return;
	}

	get_template_part( 'template-parts/breadcrumbs', null, array( 'items' => $items ) );
}
