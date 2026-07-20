<?php
/**
 * Product category tree (structure anr-pub.fr).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_CATEGORIES_VERSION', 3 );

/**
 * Full category hierarchy.
 *
 * @return array<string, array{name: string, children: array<string, string>}>
 */
function anrhpub_get_category_tree() {
	return array(
		'ecriture'                  => array(
			'name'     => 'Écriture',
			'children' => array(
				'stylos'    => 'Stylos',
				'parure'    => 'Parure',
				'coloriage' => 'Coloriage',
			),
		),
		'bureau'                    => array(
			'name'     => 'Bureau',
			'children' => array(
				'calculatrices'              => 'Calculatrices',
				'pot-a-crayons'              => 'Pot à crayons',
				'notes-et-regles'            => 'Notes et règles',
				'lampes'                     => 'Lampes',
				'horloges-et-stations-meteo' => 'Horloges et stations météo',
				'diffuseurs'                 => 'Diffuseurs',
			),
		),
		'technologie'               => array(
			'name'     => 'Technologie',
			'children' => array(
				'cles-usb'                 => 'Clés USB',
				'batteries'                => 'Batteries',
				'accessoires-telephone'    => 'Accessoires téléphone',
				'accessoires-informatique' => 'Accessoires informatique',
				'audio-et-video'           => 'Audio et vidéo',
				'enceintes'                => 'Enceintes',
				'montre-connectees'        => 'Montre connectées',
			),
		),
		'accessoires'               => array(
			'name'     => 'Accessoires',
			'children' => array(
				'porte-cles'          => 'Porte-clés',
				'porte-cles-fonction' => 'Porte-clés fonction',
				'maroquinerie'        => 'Maroquinerie',
				'badges-et-lanyard'   => 'Badges et lanyard',
				'porte-sacs'          => 'Porte-sacs',
			),
		),
		'securite'                  => array(
			'name'     => 'Sécurité',
			'children' => array(
				'lampes-torches'           => 'Lampes torches',
				'disques-de-stationnement' => 'Disques de stationnement',
				'gratte-neige'             => 'Gratte-neige',
				'trousses-de-securite'     => 'Trousses de sécurité',
				'securite-de-voiture'      => 'Sécurité de voiture',
				'securite-velo-pietons'    => 'Sécurité vélo/piétons',
			),
		),
		'bagagerie'                 => array(
			'name'     => 'Bagagerie',
			'children' => array(
				'conferenciers-et-sacs-congres' => 'Conférenciers et sacs congrès',
				'sacs-a-dos'                    => 'Sacs à dos',
				'sacs-de-plage-et-de-course'    => 'Sacs de plage et de course',
				'sacs-de-sport-et-de-voyage'    => 'Sacs de sport et de voyage',
				'sacs-isotherme'                => 'Sacs isotherme',
				'glacieres'                     => 'Glacières',
				'parapluies'                    => 'Parapluies',
			),
		),
		'plein-air-et-loisirs'      => array(
			'name'     => 'Plein air et loisirs',
			'children' => array(
				'thermos-et-mugs-isothermes'    => 'Thermos et mugs isothermes',
				'gourdes'                       => 'Gourdes',
				'mugs'                          => 'Mugs',
				'boites-repas'                  => 'Boîtes repas',
				'kits-barbecue-et-cuisine'      => 'Kits barbecue et cuisine',
				'kits-sommeliers'               => 'Kits sommeliers',
				'jardin'                        => 'Jardin',
				'bricolage'                     => 'Bricolage',
				'plaids-et-boites-de-rangement' => 'Plaids et boîtes de rangement',
				'sets-de-voyage'                => 'Sets de voyage',
				'radios-reveils-et-diffuseurs'  => 'Radios-réveils et diffuseurs',
				'accessoires-de-plage'          => 'Accessoires de plage',
				'sets-de-manucure-et-cirage'    => 'Sets de manucure et cirage',
				'jeux'                          => 'Jeux',
				'accessoires-divers'            => 'Accessoires Divers',
				'accessoires-randonnee'         => 'Accessoires randonnée',
			),
		),
		'textile'                   => array(
			'name'     => 'Textile',
			'children' => array(
				'tee-shirts'                    => 'Tee-shirts',
				'vestes-et-gilets'              => 'Vestes et Gilets',
				'casquettes-et-bonnets'         => 'Casquettes et Bonnets',
				'vetements-de-securite'         => 'Vêtements de sécurité',
				'vetements-de-travail'          => 'Vêtements de travail',
				'polos'                         => 'Polos',
				'securite-velo-pietons-textile' => 'Sécurité vélo/piétons',
			),
		),
		'les-nouveautes-objets-pubs' => array(
			'name'     => 'Les nouveautés objets pubs',
			'children' => array(),
		),
		'produits-eco-responsables' => array(
			'name'     => 'Produits Éco responsables',
			'children' => array(),
		),
		'produits-antibacteriens'   => array(
			'name'     => 'Produits antibactériens',
			'children' => array(),
		),
	);
}

/**
 * Ensure a term exists and return its ID.
 *
 * @param string $name   Label.
 * @param string $slug   Slug.
 * @param int    $parent Parent term ID.
 * @return int|null
 */
function anrhpub_ensure_category_term( $name, $slug, $parent = 0 ) {
	$existing = term_exists( $slug, 'anr_category' );
	if ( $existing ) {
		$term_id = (int) $existing['term_id'];
		wp_update_term(
			$term_id,
			'anr_category',
			array(
				'name'   => $name,
				'parent' => (int) $parent,
				'slug'   => $slug,
			)
		);
		return $term_id;
	}

	$result = wp_insert_term(
		$name,
		'anr_category',
		array(
			'slug'   => $slug,
			'parent' => (int) $parent,
		)
	);

	if ( is_wp_error( $result ) ) {
		return null;
	}

	return (int) $result['term_id'];
}

/**
 * Remove categories that are not in the official tree.
 *
 * @param string[] $valid_slugs Allowed slugs.
 */
function anrhpub_prune_obsolete_categories( $valid_slugs ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'anr_category',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) ) {
		return;
	}

	foreach ( $terms as $term ) {
		if ( ! in_array( $term->slug, $valid_slugs, true ) ) {
			wp_delete_term( $term->term_id, 'anr_category' );
		}
	}
}

/**
 * Install or sync the full category tree.
 *
 * @return array<string, int> Slug => term_id (parents and children).
 */
function anrhpub_install_categories() {
	$tree        = anrhpub_get_category_tree();
	$term_ids    = array();
	$valid_slugs = array();

	foreach ( $tree as $parent_slug => $parent_data ) {
		$valid_slugs[] = $parent_slug;
		$parent_id     = anrhpub_ensure_category_term( $parent_data['name'], $parent_slug, 0 );
		if ( $parent_id ) {
			$term_ids[ $parent_slug ] = $parent_id;
		}

		foreach ( $parent_data['children'] as $child_slug => $child_name ) {
			$valid_slugs[] = $child_slug;
			$child_id      = anrhpub_ensure_category_term( $child_name, $child_slug, $parent_id ? $parent_id : 0 );
			if ( $child_id ) {
				$term_ids[ $child_slug ] = $child_id;
			}
		}
	}

	anrhpub_prune_obsolete_categories( $valid_slugs );

	return $term_ids;
}

/**
 * Demo product reference → leaf category slug.
 *
 * @return array<string, string>
 */
function anrhpub_demo_product_category_map() {
	return array(
		'ST14'      => 'stylos',
		'DIF2'      => 'diffuseurs',
		'SETOUTIL6' => 'bricolage',
		'COF5'      => 'parure',
		'SACDO7'    => 'sacs-a-dos',
		'CONGRES1'  => 'conferenciers-et-sacs-congres',
		'BOUTISOB1' => 'gourdes',
		'ST10'      => 'stylos',
		'HUB4'      => 'accessoires-informatique',
		'EN11'      => 'enceintes',
		'MONTRE3'   => 'montre-connectees',
		'PCL13'     => 'porte-cles-fonction',
		'PCA1'      => 'maroquinerie',
		'SOFT3'     => 'vestes-et-gilets',
		'TEESPORT'  => 'tee-shirts',
		'TEE19V'    => 'tee-shirts',
	);
}

/**
 * Reassign demo products to leaf categories.
 */
function anrhpub_reassign_demo_product_categories() {
	$map      = anrhpub_demo_product_category_map();
	$term_ids = array();

	foreach ( $map as $ref => $slug ) {
		if ( ! isset( $term_ids[ $slug ] ) ) {
			$term = get_term_by( 'slug', $slug, 'anr_category' );
			if ( $term && ! is_wp_error( $term ) ) {
				$term_ids[ $slug ] = (int) $term->term_id;
			}
		}
	}

	foreach ( $map as $ref => $slug ) {
		if ( empty( $term_ids[ $slug ] ) ) {
			continue;
		}

		$posts = get_posts(
			array(
				'post_type'      => 'anr_product',
				'meta_key'       => 'anr_reference',
				'meta_value'     => $ref,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $posts ) ) {
			wp_set_object_terms( (int) $posts[0], (int) $term_ids[ $slug ], 'anr_category' );
		}
	}
}

/**
 * Upgrade categories when the tree definition changes.
 */
function anrhpub_maybe_upgrade_categories() {
	if ( (int) get_option( 'anrhpub_categories_version', 0 ) >= ANRHPUB_CATEGORIES_VERSION ) {
		return;
	}

	anrhpub_install_categories();
	anrhpub_reassign_demo_product_categories();
	update_option( 'anrhpub_categories_version', ANRHPUB_CATEGORIES_VERSION );
}
add_action( 'init', 'anrhpub_maybe_upgrade_categories', 20 );
add_action( 'after_switch_theme', 'anrhpub_maybe_upgrade_categories', 15 );

/**
 * Nombre de produits publiés dans le catalogue.
 *
 * @return int
 */
function anrhpub_get_catalogue_product_count() {
	static $count = null;

	if ( null !== $count ) {
		return $count;
	}

	$counts = wp_count_posts( 'anr_product' );
	$count  = ( $counts && isset( $counts->publish ) ) ? (int) $counts->publish : 0;

	return $count;
}

/**
 * Nombre de catégories parentes du catalogue.
 *
 * @param bool $hide_empty Exclure les catégories sans produit.
 * @return int
 */
function anrhpub_get_parent_category_count( $hide_empty = false ) {
	return count( anrhpub_get_parent_categories( $hide_empty ) );
}

/**
 * Parent categories for menus and filters.
 *
 * @param bool $hide_empty Hide empty terms.
 * @return WP_Term[]
 */
function anrhpub_get_parent_categories( $hide_empty = false ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'anr_category',
			'parent'     => 0,
			'hide_empty' => $hide_empty,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	return ( is_wp_error( $terms ) || empty( $terms ) ) ? array() : $terms;
}

/**
 * Child categories of a parent.
 *
 * @param int  $parent_id  Parent term ID.
 * @param bool $hide_empty Hide empty terms.
 * @return WP_Term[]
 */
function anrhpub_get_child_categories( $parent_id, $hide_empty = false ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'anr_category',
			'parent'     => (int) $parent_id,
			'hide_empty' => $hide_empty,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	return ( is_wp_error( $terms ) || empty( $terms ) ) ? array() : $terms;
}

/**
 * Render hierarchical category filter list (accordéon).
 *
 * @param WP_Term|null $current_term Active term.
 */
function anrhpub_render_catalogue_filters( $current_term = null ) {
	$parents          = anrhpub_get_parent_categories( false );
	$is_tax           = $current_term instanceof WP_Term;
	$active_id        = $is_tax ? (int) $current_term->term_id : 0;
	$active_parent_id = 0;

	if ( $is_tax ) {
		if ( $current_term->parent ) {
			$active_parent_id = (int) $current_term->parent;
		} else {
			$active_parent_id = (int) $current_term->term_id;
		}
	}

	foreach ( $parents as $parent ) {
		$children     = anrhpub_get_child_categories( $parent->term_id, false );
		$has_children = ! empty( $children );
		$is_parent_active = $is_tax && $active_id === (int) $parent->term_id;
		$is_child_active  = $is_tax && $active_parent_id === (int) $parent->term_id;
		$is_expanded      = $is_parent_active || $is_child_active;
		$panel_id         = 'catalogue-filter-' . (int) $parent->term_id;
		?>
		<li class="catalogue-filters__group<?php echo $is_expanded ? ' is-expanded' : ''; ?><?php echo ! $has_children ? ' catalogue-filters__group--solo' : ''; ?>">
			<div class="catalogue-filters__head">
				<?php if ( $has_children ) : ?>
					<button
						type="button"
						class="catalogue-filters__toggle"
						aria-expanded="<?php echo $is_expanded ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr( $panel_id ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'Afficher les sous-catégories : %s', 'anrhpub_theme' ), $parent->name ) ); ?>"
					>
						<span class="catalogue-filters__chevron" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
				<a
					class="catalogue-filters__parent<?php echo $is_parent_active ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( get_term_link( $parent ) ); ?>"
				>
					<span class="catalogue-filters__label"><?php echo esc_html( $parent->name ); ?></span>
					<?php if ( (int) $parent->count > 0 ) : ?>
						<span class="catalogue-filters__count"><?php echo esc_html( (string) $parent->count ); ?></span>
					<?php endif; ?>
				</a>
			</div>
			<?php if ( $has_children ) : ?>
				<ul id="<?php echo esc_attr( $panel_id ); ?>" class="catalogue-filters__children">
					<?php foreach ( $children as $child ) : ?>
						<li>
							<a
								class="catalogue-filters__child<?php echo ( $is_tax && $active_id === (int) $child->term_id ) ? ' is-active' : ''; ?>"
								href="<?php echo esc_url( get_term_link( $child ) ); ?>"
							>
								<span><?php echo esc_html( $child->name ); ?></span>
								<?php if ( (int) $child->count > 0 ) : ?>
									<span class="catalogue-filters__count"><?php echo esc_html( (string) $child->count ); ?></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</li>
		<?php
	}
}
