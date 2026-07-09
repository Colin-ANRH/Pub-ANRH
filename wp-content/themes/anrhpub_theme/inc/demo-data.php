<?php
/**
 * Placeholder catalogue data (seeded once on theme activation).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seed demo products and pages.
 */
function anrhpub_seed_demo_data() {
	$term_ids = anrhpub_install_categories();

	$products = array(
		array(
			'title'    => 'STYLO ST14',
			'ref'      => 'ST14',
			'excerpt'  => 'Stylo bille rétractable. Corps transparent fait de bouteilles d’eau recyclées. Encre noire.',
			'cat'      => 'stylos',
			'featured' => '1',
			'badge'    => '',
		),
		array(
			'title'    => 'DIF2 - Diffuseur humidificateur',
			'ref'      => 'DIF2',
			'excerpt'  => 'Humidificateur et diffuseur aromatique. Lumière LED multicolore. Câble USB inclus.',
			'cat'      => 'diffuseurs',
			'featured' => '1',
			'badge'    => '',
		),
		array(
			'title'    => 'SETOUTIL6 - Kit outils',
			'ref'      => 'SETOUTIL6',
			'excerpt'  => 'Kit de réparation 24 pièces dans un étui en bambou avec fermeture à boucle.',
			'cat'      => 'bricolage',
			'featured' => '1',
			'badge'    => '',
		),
		array(
			'title'    => 'PARURE COF5',
			'ref'      => 'COF5',
			'excerpt'  => 'Trousse non-tissé : taille-crayon, gomme, règle, stylo carton, crayon et bloc-notes.',
			'cat'      => 'parure',
			'featured' => '1',
			'badge'    => 'promo',
		),
		array(
			'title'    => 'SACDO7 - Sac à dos Trails',
			'ref'      => 'SACDO7',
			'excerpt'  => 'Sac 100 % recyclé hydrofuge GRS. Poches zippées et bandes réfléchissantes.',
			'cat'      => 'sacs-a-dos',
			'featured' => '1',
			'badge'    => '',
		),
		array(
			'title'    => 'CONGRES1 - Housse tablette RPET',
			'ref'      => 'CONGRES1',
			'excerpt'  => 'Housse feutre RPET rembourrée, jusqu’à 15 pouces, poche avant pour accessoires.',
			'cat'      => 'conferenciers-et-sacs-congres',
			'featured' => '1',
			'badge'    => 'nouveau',
		),
		array(
			'title'    => 'BOUTISOB1 - Bouteille isotherme',
			'ref'      => 'BOUTISOB1',
			'excerpt'  => 'Acier inox recyclé RCS 500 ml. Chaud 12 h, frais 24 h.',
			'cat'      => 'gourdes',
			'featured' => '1',
			'badge'    => '',
		),
		array(
			'title'    => 'STYLO ST10',
			'ref'      => 'ST10',
			'excerpt'  => 'Stylo bille en carton recyclé et plastique Berk. Encre noire.',
			'cat'      => 'stylos',
			'featured' => '1',
			'badge'    => '',
		),
		array(
			'title'    => 'HUB4 - Hub USB aluminium recyclé',
			'ref'      => 'HUB4',
			'excerpt'  => 'Hub double USB/Type C, 4 ports dont 1 Type C 2.0 et 3 USB.',
			'cat'      => 'accessoires-informatique',
			'featured' => '',
			'badge'    => 'nouveau',
		),
		array(
			'title'    => 'EN11 - Enceinte Bluetooth bois',
			'ref'      => 'EN11',
			'excerpt'  => 'Mini enceinte chêne 3W, Bluetooth 4.0, autonomie 3–4 h.',
			'cat'      => 'enceintes',
			'featured' => '',
			'badge'    => 'nouveau',
		),
		array(
			'title'    => 'MONTRE3 - Montre connectée',
			'ref'      => 'MONTRE3',
			'excerpt'  => 'Étanche, bracelet TPU recyclé RCS, écran OLED tactile 1,47".',
			'cat'      => 'montre-connectees',
			'featured' => '',
			'badge'    => 'nouveau',
		),
		array(
			'title'    => 'PCL13 - Porte-clés règle pliable',
			'ref'      => 'PCL13',
			'excerpt'  => 'Mètre 50 cm ABS, 10 tranches pliables de 5 cm.',
			'cat'      => 'porte-cles-fonction',
			'featured' => '',
			'badge'    => 'nouveau',
		),
		array(
			'title'    => 'PCA1 - Porte-cartes RPET',
			'ref'      => 'PCA1',
			'excerpt'  => 'Polyester RPET 300D, 3 compartiments indépendants.',
			'cat'      => 'maroquinerie',
			'featured' => '',
			'badge'    => 'nouveau',
		),
		array(
			'title'    => 'SOFT3 - Veste softshell',
			'ref'      => 'SOFT3',
			'excerpt'  => '3 couches imperméables 8000 mm, bandes réfléchissantes, classe 2.',
			'cat'      => 'vestes-et-gilets',
			'featured' => '',
			'badge'    => 'nouveau',
		),
		array(
			'title'    => 'TEESPORT - Tee-shirt sport',
			'ref'      => 'TEESPORT',
			'excerpt'  => '100 % polyester respirant dry fit. Coupe homme et femme.',
			'cat'      => 'tee-shirts',
			'featured' => '',
			'badge'    => 'nouveau',
		),
		array(
			'title'    => 'TEE19V - Tee-shirt col V',
			'ref'      => 'TEE19V',
			'excerpt'  => 'Coton Ringspun Jersey 190, col V, grammage lourd.',
			'cat'      => 'tee-shirts',
			'featured' => '',
			'badge'    => 'nouveau',
		),
	);

	if ( ! get_option( 'anrhpub_demo_seeded' ) ) {
		foreach ( $products as $product ) {
			$exists = get_posts(
				array(
					'post_type'      => 'anr_product',
					'meta_key'       => 'anr_reference',
					'meta_value'     => $product['ref'],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $exists ) ) {
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_type'    => 'anr_product',
					'post_title'   => $product['title'],
					'post_excerpt' => $product['excerpt'],
					'post_content' => '<p>' . esc_html( $product['excerpt'] ) . '</p><p><em>' . esc_html__( 'Produit de démonstration — contenu à remplacer lors de l’intégration du catalogue réel.', 'anrhpub_theme' ) . '</em></p>',
					'post_status'  => 'publish',
				),
				true
			);

			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			update_post_meta( $post_id, 'anr_reference', $product['ref'] );
			update_post_meta( $post_id, 'anr_hub1_reference', $product['ref'] );
			update_post_meta( $post_id, 'anr_marking_max_size', '50 x 7 mm' );
			update_post_meta( $post_id, 'anr_dimensions', 'L. 7,5 x l. 6,4 x h. 1,5 cm' );
			update_post_meta( $post_id, 'anr_price_label', __( 'Sur devis', 'anrhpub_theme' ) );
			update_post_meta(
				$post_id,
				'anr_details',
				sprintf(
					/* translators: %s: product reference */
					__( "Référence : %s\n• Personnalisation par marquage (logo, texte)\n• Délais et tarifs sur devis selon quantité\n• Contactez ANRH Peyruis pour un échantillon", 'anrhpub_theme' ),
					$product['ref']
				)
			);

			if ( $product['badge'] ) {
				update_post_meta( $post_id, 'anr_badge', $product['badge'] );
			}
			if ( $product['featured'] ) {
				update_post_meta( $post_id, 'anr_featured', '1' );
			}

			if ( isset( $term_ids[ $product['cat'] ] ) ) {
				wp_set_object_terms( $post_id, (int) $term_ids[ $product['cat'] ], 'anr_category' );
			}

			$color_terms = get_terms(
				array(
					'taxonomy'   => 'anr_color',
					'hide_empty' => false,
					'fields'     => 'ids',
				)
			);

			if ( ! is_wp_error( $color_terms ) && ! empty( $color_terms ) ) {
				$color_rows = array();

				foreach ( $color_terms as $color_id ) {
					$color_rows[] = array(
						'color_id' => (int) $color_id,
						'stock'    => 40 + ( (int) $post_id % 60 ),
					);
				}

				anrhpub_save_product_color_stock_rows( $post_id, $color_rows );
			}
		}

		$pages = array(
			'marquage' => array(
				'title'   => 'Techniques de marquage',
				'content' => anrhpub_marquage_page_content(),
			),
			'societe'  => array(
				'title'   => 'Notre activité',
				'content' => anrhpub_societe_page_content(),
			),
			'contact'      => array(
				'title'   => 'Contact & devis',
				'content' => '<p>Pour toute demande d’information ou de devis personnalisé, utilisez les coordonnées ci-dessous.</p>',
			),
			'histoire-anrh' => array(
				'title'   => 'Histoire de l’ANRH',
				'content' => '',
			),
		);

		foreach ( $pages as $slug => $page ) {
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				continue;
			}
			wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_title'   => $page['title'],
					'post_name'    => $slug,
					'post_content' => $page['content'],
					'post_status'  => 'publish',
				)
			);
		}

		update_option( 'anrhpub_demo_seeded', 1 );
	} else {
		anrhpub_reassign_demo_product_categories();
	}

	if ( function_exists( 'anrhpub_ensure_account_pages' ) ) {
		anrhpub_ensure_account_pages();
	}
}

/**
 * Run seed on theme switch.
 */
function anrhpub_after_switch_theme() {
	anrhpub_seed_demo_data();

	$front = get_page_by_path( 'accueil' );
	if ( ! $front ) {
		$front_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => __( 'Accueil', 'anrhpub_theme' ),
				'post_name'    => 'accueil',
				'post_status'  => 'publish',
				'post_content' => '',
			)
		);
		if ( ! is_wp_error( $front_id ) ) {
			update_option( 'page_on_front', $front_id );
			update_option( 'show_on_front', 'page' );
		}
	}
}
add_action( 'after_switch_theme', 'anrhpub_after_switch_theme' );

/**
 * Admin notice after seed.
 */
function anrhpub_admin_demo_notice() {
	if ( ! get_option( 'anrhpub_demo_seeded' ) ) {
		return;
	}
	if ( get_user_meta( get_current_user_id(), 'anrhpub_dismiss_demo_notice', true ) ) {
		return;
	}
	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<?php
			echo esc_html__(
				'ANRH Theme : le catalogue de démonstration (produits fictifs) a été installé. Activez le thème ou réactivez-le pour regénérer si besoin.',
				'anrhpub_theme'
			);
			?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'anrhpub_admin_demo_notice' );

/**
 * Produits de test « nouveauté » (complément au seed initial).
 *
 * @return array<int, array<string, string>>
 */
function anrhpub_get_nouveaute_demo_products() {
	return array(
		array(
			'title'   => 'GOURDE1 - Gourde isotherme 350 ml',
			'ref'     => 'NOUV01',
			'excerpt' => 'Gourde compacte double paroi, bouchon étanche, finition mate. Idéale salon et équipes terrain.',
			'cat'     => 'les-nouveautes-objets-pubs',
			'badge'   => 'nouveau',
		),
		array(
			'title'   => 'TOTE1 - Tote bag coton bio',
			'ref'     => 'NOUV02',
			'excerpt' => 'Sac shopping 140 g/m², anses longues, grande surface de marquage sérigraphie.',
			'cat'     => 'les-nouveautes-objets-pubs',
			'badge'   => 'nouveau',
		),
		array(
			'title'   => 'NOTE1 - Carnet A5 couverture liège',
			'ref'     => 'NOUV03',
			'excerpt' => 'Carnet 80 pages lignées, couverture liège naturel, élastique de fermeture assorti.',
			'cat'     => 'les-nouveautes-objets-pubs',
			'badge'   => 'nouveau',
		),
		array(
			'title'   => 'LAMP1 - Lampe bureau LED USB',
			'ref'     => 'NOUV04',
			'excerpt' => 'Lampe orientable 3 intensités, socle antidérapant, alimentation USB-C.',
			'cat'     => 'les-nouveautes-objets-pubs',
			'badge'   => 'nouveau',
		),
	);
}

/**
 * Crée un produit catalogue de démonstration.
 *
 * @param array<string, string> $product  Données produit.
 * @param array<string, int>    $term_ids IDs catégories par slug.
 * @return int Post ID ou 0.
 */
function anrhpub_insert_demo_product( $product, $term_ids ) {
	$exists = get_posts(
		array(
			'post_type'      => 'anr_product',
			'meta_key'       => 'anr_reference',
			'meta_value'     => $product['ref'],
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $exists ) ) {
		return (int) $exists[0];
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'anr_product',
			'post_title'   => $product['title'],
			'post_excerpt' => $product['excerpt'],
			'post_content' => '<p>' . esc_html( $product['excerpt'] ) . '</p><p><em>' . esc_html__( 'Produit de démonstration — contenu à remplacer lors de l’intégration du catalogue réel.', 'anrhpub_theme' ) . '</em></p>',
			'post_status'  => 'publish',
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return 0;
	}

	update_post_meta( $post_id, 'anr_reference', $product['ref'] );
	update_post_meta( $post_id, 'anr_hub1_reference', $product['ref'] );
	update_post_meta( $post_id, 'anr_marking_max_size', '50 x 7 mm' );
	update_post_meta( $post_id, 'anr_dimensions', 'L. 7,5 x l. 6,4 x h. 1,5 cm' );
	update_post_meta( $post_id, 'anr_price_label', __( 'Sur devis', 'anrhpub_theme' ) );
	update_post_meta(
		$post_id,
		'anr_details',
		sprintf(
			/* translators: %s: product reference */
			__( "Référence : %s\n• Personnalisation par marquage (logo, texte)\n• Délais et tarifs sur devis selon quantité\n• Contactez ANRH Peyruis pour un échantillon", 'anrhpub_theme' ),
			$product['ref']
		)
	);

	if ( ! empty( $product['badge'] ) ) {
		update_post_meta( $post_id, 'anr_badge', $product['badge'] );
	}
	if ( ! empty( $product['featured'] ) ) {
		update_post_meta( $post_id, 'anr_featured', '1' );
	}

	if ( ! empty( $product['cat'] ) && isset( $term_ids[ $product['cat'] ] ) ) {
		wp_set_object_terms( $post_id, (int) $term_ids[ $product['cat'] ], 'anr_category' );
	}

	if ( function_exists( 'anrhpub_save_product_color_stock_rows' ) ) {
		$color_terms = get_terms(
			array(
				'taxonomy'   => 'anr_color',
				'hide_empty' => false,
				'fields'     => 'ids',
			)
		);

		if ( ! is_wp_error( $color_terms ) && ! empty( $color_terms ) ) {
			$color_rows = array();
			foreach ( $color_terms as $color_id ) {
				$color_rows[] = array(
					'color_id' => (int) $color_id,
					'stock'    => 40 + ( (int) $post_id % 60 ),
				);
			}
			anrhpub_save_product_color_stock_rows( $post_id, $color_rows );
		}
	}

	if ( function_exists( 'anrhpub_demo_product_image_urls' ) && function_exists( 'anrhpub_attach_product_image' ) ) {
		$urls = anrhpub_demo_product_image_urls();
		if ( isset( $urls[ $product['ref'] ] ) ) {
			anrhpub_attach_product_image( $post_id, $urls[ $product['ref'] ], 'ANRH demo ' . $product['ref'] );
		}
	}

	return (int) $post_id;
}

/**
 * Installe les produits nouveautés de test (une fois).
 */
function anrhpub_ensure_nouveaute_demo_products() {
	$term_ids = array();
	if ( function_exists( 'anrhpub_install_categories' ) ) {
		$term_ids = anrhpub_install_categories();
	}

	if ( ! get_option( 'anrhpub_nouveautes_demo_v1' ) ) {
		$created = 0;
		foreach ( anrhpub_get_nouveaute_demo_products() as $product ) {
			if ( anrhpub_insert_demo_product( $product, $term_ids ) ) {
				++$created;
			}
		}
		if ( $created > 0 ) {
			update_option( 'anrhpub_nouveautes_demo_v1', 1 );
		}
	}

	if ( get_option( 'anrhpub_nouveautes_demo_v2' ) ) {
		return;
	}

	$nouv_slug = function_exists( 'anrhpub_nouveautes_category_slug' )
		? anrhpub_nouveautes_category_slug()
		: 'les-nouveautes-objets-pubs';

	if ( empty( $term_ids[ $nouv_slug ] ) ) {
		update_option( 'anrhpub_nouveautes_demo_v2', 1 );
		return;
	}

	$nouv_term_id = (int) $term_ids[ $nouv_slug ];

	foreach ( anrhpub_get_nouveaute_demo_products() as $product ) {
		$posts = get_posts(
			array(
				'post_type'      => 'anr_product',
				'meta_key'       => 'anr_reference',
				'meta_value'     => $product['ref'],
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( ! empty( $posts ) ) {
			wp_set_object_terms( (int) $posts[0], array( $nouv_term_id ), 'anr_category' );
		}
	}

	update_option( 'anrhpub_nouveautes_demo_v2', 1 );
}
add_action( 'init', 'anrhpub_ensure_nouveaute_demo_products', 22 );
