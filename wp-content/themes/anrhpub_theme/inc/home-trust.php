<?php
/**
 * Accueil — références « Ils nous font confiance » (EA Peyruis).
 *
 * @package anrhpub_theme
 * @see https://anrh.fr/decouvrir-notre-offre/nos-etablissements/entreprise-adaptee/anrh-peyruis/
 */

defined( 'ABSPATH' ) || exit;

/**
 * Récupère les logos gérés en wp-admin (CPT) selon la zone.
 *
 * @param 'clients'|'partners' $type Type de zone.
 * @return array<int, array{name: string, logo: string, url: string}>
 */
function anrhpub_get_trust_logos_from_cpt( $type ) {
	if ( ! in_array( $type, array( 'clients', 'partners' ), true ) ) {
		return array();
	}

	$query = new WP_Query(
		array(
			'post_type'      => ANRHPUB_TRUST_LOGO_POST_TYPE,
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'meta_key'       => ANRHPUB_TRUST_LOGO_META_ORDER,
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'   => ANRHPUB_TRUST_LOGO_META_TYPE,
					'value' => $type,
				),
			),
		)
	);

	if ( ! $query->have_posts() ) {
		return array();
	}

	$items = array();

	foreach ( $query->posts as $post ) {
		$id = (int) $post->ID;

		$name = (string) get_the_title( $id );
		$link = (string) get_post_meta( $id, ANRHPUB_TRUST_LOGO_META_LINK, true );

		$attachment_id = (int) get_post_thumbnail_id( $id );
		$logo_url      = $attachment_id ? (string) wp_get_attachment_image_url( $attachment_id, 'large' ) : '';

		if ( '' === $name || '' === $logo_url ) {
			continue;
		}

		$items[] = array(
			'name' => $name,
			'logo' => $logo_url,
			'url'  => $link,
		);
	}

	wp_reset_postdata();

	return $items;
}

/**
 * Clients mis en avant — « Ils nous font confiance ».
 *
 * @return array<int, array{name: string, logo: string, url: string}>
 */
function anrhpub_get_trust_clients() {
	$items = anrhpub_get_trust_logos_from_cpt( 'clients' );

	if ( ! empty( $items ) ) {
		return $items;
	}

	// Fallback historique (si pas de contenu wp-admin).
	$base = ANRHPUB_THEME_URI . '/assets/images/trust/';

	return array(
		array(
			'name' => 'CY-Clope',
			'logo' => $base . 'cy-clope.png',
			'url'  => 'https://www.cy-clope.com/',
		),
		array(
			'name' => 'Sodikart',
			'logo' => $base . 'sodikart.png',
			'url'  => 'https://www.sodikart.com/fr-fr/',
		),
		array(
			'name' => 'Armor Group',
			'logo' => $base . 'armor-group.png',
			'url'  => 'https://www.armor-group.com/',
		),
		array(
			'name' => 'Clinique Jules Verne',
			'logo' => $base . 'clinique-jules-verne.png',
			'url'  => 'https://www.cliniquejulesverne.fr/',
		),
		array(
			'name' => 'CNIEG',
			'logo' => $base . 'cnieg.png',
			'url'  => 'https://www.cnieg.fr/',
		),
		array(
			'name' => 'Newclip Technics',
			'logo' => $base . 'newclip.png',
			'url'  => 'https://newcliptechnics.fr/',
		),
	);
}

/**
 * Partenaires institutionnels — « Nos partenaires » (EA Peyruis).
 *
 * @return array<int, array{name: string, logo: string, url: string}>
 */
function anrhpub_get_trust_institutional_partners() {
	$items = anrhpub_get_trust_logos_from_cpt( 'partners' );

	if ( ! empty( $items ) ) {
		return $items;
	}

	// Fallback historique (si pas de contenu wp-admin).
	$base = ANRHPUB_THEME_URI . '/assets/images/trust/partners/';

	return array(
		array(
			'name' => 'DREETS',
			'logo' => $base . 'dreets.png',
			'url'  => 'https://dreets.gouv.fr/',
		),
		array(
			'name' => 'Agefiph',
			'logo' => $base . 'agefiph.png',
			'url'  => 'https://www.agefiph.fr/',
		),
		array(
			'name' => 'Cap Emploi',
			'logo' => $base . 'cap-emploi.png',
			'url'  => 'https://www.capemploi.info/',
		),
		array(
			'name' => 'France Travail',
			'logo' => $base . 'france-travail.png',
			'url'  => 'https://www.francetravail.fr/',
		),
		array(
			'name' => 'Hosmoz',
			'logo' => $base . 'hosmoz.png',
			'url'  => 'https://www.hosmoz.fr/',
		),
		array(
			'name' => 'Le Marché de l’inclusion',
			'logo' => $base . 'marche-inclusion.png',
			'url'  => 'https://lemarche.inclusion.gouv.fr/',
		),
		array(
			'name' => 'UNEA',
			'logo' => $base . 'unea.png',
			'url'  => 'https://www.unea.fr/',
		),
	);
}

/**
 * Liste de logos (clients ou partenaires).
 *
 * @param array<int, array{name: string, logo: string, url: string}> $items       Logos.
 * @param bool                                                       $marquee_dup  Dupliquer pour défilement.
 * @param bool                                                       $round        Visuels ronds.
 */
function anrhpub_render_trust_logo_list( $items, $marquee_dup = false, $round = false ) {
	if ( empty( $items ) ) {
		return;
	}

	$list_class = 'home-trust__list';
	if ( $round ) {
		$list_class .= ' home-trust__list--round';
	}
	?>
	<ul
		class="<?php echo esc_attr( $list_class ); ?>"
		<?php echo $marquee_dup ? 'aria-hidden="true"' : ''; ?>
	>
		<?php foreach ( $items as $item ) : ?>
			<?php
			$name = isset( $item['name'] ) ? (string) $item['name'] : '';
			$logo = isset( $item['logo'] ) ? (string) $item['logo'] : '';
			$url  = isset( $item['url'] ) ? (string) $item['url'] : '';

			if ( '' === $name || '' === $logo ) {
				continue;
			}

			$link_class = 'home-trust__link';
			if ( $round ) {
				$link_class .= ' home-trust__link--round';
			}
			?>
			<li class="home-trust__item">
				<?php if ( $url && ! $marquee_dup ) : ?>
					<a
						class="<?php echo esc_attr( $link_class ); ?>"
						href="<?php echo esc_url( $url ); ?>"
						target="_blank"
						rel="noopener noreferrer"
					>
						<img
							class="home-trust__logo"
							src="<?php echo esc_url( $logo ); ?>"
							alt="<?php echo esc_attr( $name ); ?>"
							width="<?php echo $round ? '88' : '160'; ?>"
							height="<?php echo $round ? '88' : '64'; ?>"
							loading="lazy"
							decoding="async"
						/>
					</a>
				<?php else : ?>
					<span class="<?php echo esc_attr( $link_class ); ?>">
						<img
							class="home-trust__logo"
							src="<?php echo esc_url( $logo ); ?>"
							alt=""
							width="<?php echo $round ? '88' : '160'; ?>"
							height="<?php echo $round ? '88' : '64'; ?>"
							loading="lazy"
							decoding="async"
						/>
					</span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Affiche « Ils nous font confiance » sur l’accueil.
 */
function anrhpub_render_home_trust_clients() {
	if ( ! is_front_page() ) {
		return;
	}

	$clients = anrhpub_get_trust_clients();

	if ( empty( $clients ) ) {
		return;
	}

	get_template_part(
		'template-parts/home',
		'trust-slider',
		array(
			'clients' => $clients,
		)
	);
}

/**
 * Affiche « Nos partenaires » sur l’accueil.
 */
function anrhpub_render_home_trust_partners() {
	if ( ! is_front_page() ) {
		return;
	}

	$partners = anrhpub_get_trust_institutional_partners();

	if ( empty( $partners ) ) {
		return;
	}

	get_template_part(
		'template-parts/home',
		'trust-partners',
		array(
			'partners' => $partners,
		)
	);
}

/**
 * @deprecated Utiliser anrhpub_render_home_trust_clients().
 */
function anrhpub_render_home_trust_slider() {
	anrhpub_render_home_trust_clients();
}
