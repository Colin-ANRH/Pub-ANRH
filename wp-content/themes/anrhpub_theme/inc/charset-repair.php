<?php
/**
 * Réparation encodage UTF-8 (mojibake) — contenus importés / export SQL Windows.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_CHARSET_REPAIR_VERSION', 2 );

/**
 * Chaîne de référence (texte démo produit).
 *
 * @return string
 */
function anrhpub_demo_product_disclaimer() {
	return __( "Produit de démonstration — contenu à remplacer lors de l'intégration du catalogue réel.", 'anrhpub_theme' );
}

/**
 * Détecte un texte probablement corrompu (double encodage UTF-8, etc.).
 *
 * @param string $text Texte.
 * @return bool
 */
function anrhpub_string_looks_mojibake( $text ) {
	if ( ! is_string( $text ) || $text === '' ) {
		return false;
	}

	return (bool) preg_match( '/Ã.|â[\x80-\xBF]|ÔÇ.|├.|ï¿½/u', $text );
}

/**
 * Choisit la meilleure variante UTF-8 parmi des candidats.
 *
 * @param string[] $candidates Candidats.
 * @return string
 */
function anrhpub_pick_best_utf8_candidate( array $candidates ) {
	$best       = '';
	$best_score = PHP_INT_MIN;

	foreach ( array_unique( array_filter( $candidates, 'is_string' ) ) as $candidate ) {
		if ( $candidate === '' || ! mb_check_encoding( $candidate, 'UTF-8' ) ) {
			continue;
		}

		$score = 0;

		if ( ! anrhpub_string_looks_mojibake( $candidate ) ) {
			$score += 20;
		}

		$score += preg_match_all( '/[éèêëàâùûôîïçÉÈÀÂÙÔÎÏÇ]/u', $candidate, $m );
		$score -= 3 * substr_count( $candidate, 'Ã' );
		$score -= 2 * substr_count( $candidate, 'â' );
		$score -= 5 * substr_count( $candidate, '├' );
		$score -= 5 * substr_count( $candidate, "\u{FFFD}" );

		if ( $score > $best_score ) {
			$best_score = $score;
			$best       = $candidate;
		}
	}

	return $best;
}

/**
 * Tente de réparer une chaîne UTF-8 corrompue.
 *
 * @param string $text Texte source.
 * @return string
 */
function anrhpub_fix_mojibake_string( $text ) {
	if ( ! is_string( $text ) || $text === '' ) {
		return $text;
	}

	if ( ! anrhpub_string_looks_mojibake( $text ) ) {
		return $text;
	}

	$candidates = array( $text );

	$iso_fix = @mb_convert_encoding( $text, 'UTF-8', 'ISO-8859-1' );
	if ( is_string( $iso_fix ) && $iso_fix !== '' ) {
		$candidates[] = $iso_fix;
	}

	if ( function_exists( 'iconv' ) ) {
		$bytes = @iconv( 'UTF-8', 'ISO-8859-1//IGNORE', $text );
		if ( is_string( $bytes ) && $bytes !== '' ) {
			$utf8 = @iconv( 'ISO-8859-1', 'UTF-8//IGNORE', $bytes );
			if ( is_string( $utf8 ) && $utf8 !== '' ) {
				$candidates[] = $utf8;
			}
		}

		$win = @iconv( 'UTF-8', 'Windows-1252//IGNORE', $text );
		if ( is_string( $win ) && $win !== '' ) {
			$utf8_win = @iconv( 'Windows-1252', 'UTF-8//IGNORE', $win );
			if ( is_string( $utf8_win ) && $utf8_win !== '' ) {
				$candidates[] = $utf8_win;
			}
		}
	}

	$fixed = anrhpub_pick_best_utf8_candidate( $candidates );

	return $fixed !== '' ? $fixed : $text;
}

/**
 * Répare un champ texte si nécessaire.
 *
 * @param string $text Texte.
 * @return string
 */
function anrhpub_normalize_utf8_text( $text ) {
	if ( ! is_string( $text ) || $text === '' ) {
		return $text;
	}

	$text = str_replace( array( "\u{2018}", "\u{2019}", "\u{201C}", "\u{201D}" ), array( "'", "'", '"', '"' ), $text );

	return anrhpub_fix_mojibake_string( $text );
}

/**
 * Réparation globale base de contenus (une fois par version).
 *
 * @return array{posts: int, meta: int, terms: int, options: int}
 */
function anrhpub_run_charset_repair() {
	global $wpdb;

	$stats = array(
		'posts'   => 0,
		'meta'    => 0,
		'terms'   => 0,
		'options' => 0,
	);

	$post_types = array( 'anr_product', 'page', 'post', 'anr_quote' );
	$posts      = get_posts(
		array(
			'post_type'              => $post_types,
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	foreach ( $posts as $post_id ) {
		$post    = get_post( $post_id );
		$updates = array( 'ID' => $post_id );
		$changed = false;

		foreach ( array( 'post_title', 'post_content', 'post_excerpt' ) as $field ) {
			$original = (string) $post->{$field};
			$fixed    = anrhpub_normalize_utf8_text( $original );

			if ( $fixed !== $original ) {
				$updates[ $field ] = $fixed;
				$changed           = true;
			}
		}

		if ( $changed ) {
			wp_update_post( $updates, true );
			++$stats['posts'];
		}
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$meta_rows = $wpdb->get_results(
		"SELECT meta_id, meta_value FROM {$wpdb->postmeta}
		WHERE meta_value LIKE '%Ã%' OR meta_value LIKE '%â%' OR meta_value LIKE '%ÔÇ%' OR meta_value LIKE '%├%' OR meta_value LIKE '%ï¿½%'",
		ARRAY_A
	);

	if ( $meta_rows ) {
		foreach ( $meta_rows as $row ) {
			$original = (string) $row['meta_value'];
			if ( is_serialized( $original ) ) {
				continue;
			}
			$fixed = anrhpub_normalize_utf8_text( $original );

			if ( $fixed !== $original ) {
				update_metadata_by_mid( 'post', (int) $row['meta_id'], $fixed );
				++$stats['meta'];
			}
		}
	}

	$terms = get_terms(
		array(
			'taxonomy'   => array( 'anr_category', 'anr_color', 'anr_material', 'anr_product_badge', 'category' ),
			'hide_empty' => false,
		)
	);

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$name_fixed = anrhpub_normalize_utf8_text( $term->name );
			$desc_fixed = anrhpub_normalize_utf8_text( $term->description );

			if ( $name_fixed !== $term->name || $desc_fixed !== $term->description ) {
				wp_update_term(
					$term->term_id,
					$term->taxonomy,
					array(
						'name'        => $name_fixed,
						'description' => $desc_fixed,
					)
				);
				++$stats['terms'];
			}
		}
	}

	$option_keys = array( 'blogname', 'blogdescription' );
	foreach ( $option_keys as $key ) {
		$original = (string) get_option( $key, '' );
		$fixed    = anrhpub_normalize_utf8_text( $original );

		if ( $fixed !== $original ) {
			update_option( $key, $fixed );
			++$stats['options'];
		}
	}

	if ( get_option( 'blog_charset' ) !== 'UTF-8' ) {
		update_option( 'blog_charset', 'UTF-8' );
	}

	return $stats;
}

/**
 * Réparation automatique après déploiement / import SQL.
 */
function anrhpub_maybe_run_charset_repair() {
	if ( (int) get_option( 'anrhpub_charset_repair_version', 0 ) >= ANRHPUB_CHARSET_REPAIR_VERSION ) {
		return;
	}

	if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
		return;
	}

	// Admin uniquement — évite timeout / erreur fatale sur le front public.
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$stats = anrhpub_run_charset_repair();
	update_option( 'anrhpub_charset_repair_version', ANRHPUB_CHARSET_REPAIR_VERSION, false );
	update_option( 'anrhpub_charset_repair_last_stats', $stats, false );
}
add_action( 'admin_init', 'anrhpub_maybe_run_charset_repair', 5 );

/**
 * Connexion MySQL utf8mb4 explicite.
 */
function anrhpub_ensure_db_charset() {
	global $wpdb;

	if ( ! isset( $wpdb->dbh ) || ! $wpdb->dbh ) {
		return;
	}

	if ( method_exists( $wpdb, 'set_charset' ) ) {
		$wpdb->set_charset( $wpdb->dbh, 'utf8mb4', 'utf8mb4_unicode_ci' );
	}
}
add_action( 'init', 'anrhpub_ensure_db_charset', 0 );

/**
 * Notice admin après réparation.
 */
function anrhpub_charset_repair_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$stats = get_option( 'anrhpub_charset_repair_last_stats' );
	if ( ! is_array( $stats ) ) {
		return;
	}

	$total = (int) $stats['posts'] + (int) $stats['meta'] + (int) $stats['terms'] + (int) $stats['options'];
	if ( $total <= 0 ) {
		return;
	}

	if ( get_user_meta( get_current_user_id(), 'anrhpub_dismiss_charset_repair_notice', true ) ) {
		return;
	}

	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: 1: posts, 2: meta, 3: terms, 4: options */
				__( 'Encodage UTF-8 : %1$d contenus, %2$d métadonnées, %3$d termes et %4$d options corrigés.', 'anrhpub_theme' ),
				(int) $stats['posts'],
				(int) $stats['meta'],
				(int) $stats['terms'],
				(int) $stats['options']
			)
		)
	);
}
add_action( 'admin_notices', 'anrhpub_charset_repair_admin_notice' );
