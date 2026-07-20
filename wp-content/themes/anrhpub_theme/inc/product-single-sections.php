<?php
/**
 * Fiche produit — sections description, caractéristiques, fiche technique, réassurance.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Éléments de réassurance (contenus alignés sur la vitrine du site).
 *
 * @return array<int, array{title: string, text: string, url?: string, url_label?: string}>
 */
function anrhpub_get_product_reassurance_items() {
	$items = array(
		array(
			'title' => __( 'Devis sur mesure', 'anrhpub_theme' ),
			'text'  => __( 'Tarif établi selon votre quantité, les couleurs et la technique de marquage — catalogue sans paiement en ligne.', 'anrhpub_theme' ),
			'url'   => function_exists( 'anrhpub_quote_cart_url' ) ? anrhpub_quote_cart_url() : home_url( '/panier-devis/' ),
			'url_label' => __( 'Composer mon devis', 'anrhpub_theme' ),
		),
		array(
			'title' => __( 'Marquage personnalisé', 'anrhpub_theme' ),
			'text'  => __( 'Logo, texte ou visuel sur vos objets publicitaires — plusieurs techniques selon la matière et la forme du produit.', 'anrhpub_theme' ),
			'url'   => home_url( '/marquage/' ),
			'url_label' => __( 'Techniques de marquage', 'anrhpub_theme' ),
		),
		array(
			'title' => __( 'ANRH Peyruis', 'anrhpub_theme' ),
			'text'  => __( 'Entreprise adaptée du réseau ANRH : objets publicitaires personnalisés et insertion professionnelle à Peyruis.', 'anrhpub_theme' ),
			'url'   => home_url( '/societe/' ),
			'url_label' => __( 'Notre activité', 'anrhpub_theme' ),
		),
		array(
			'title' => __( 'Qualité ISO 9001', 'anrhpub_theme' ),
			'text'  => __( 'Démarche qualité du réseau ANRH pour un accompagnement professionnel fiable.', 'anrhpub_theme' ),
			'url'   => function_exists( 'anrhpub_anrh_history_url' ) ? anrhpub_anrh_history_url() : home_url( '/histoire-anrh/' ),
			'url_label' => __( 'Histoire de l’ANRH', 'anrhpub_theme' ),
		),
		array(
			'title' => __( 'Large catalogue', 'anrhpub_theme' ),
			'text'  => __( 'Plus de 450 références classées par univers — textiles, bureau, technologie, bagagerie et plus encore.', 'anrhpub_theme' ),
			'url'   => function_exists( 'anrhpub_catalogue_url' ) ? anrhpub_catalogue_url() : home_url( '/catalogue/' ),
			'url_label' => __( 'Parcourir le catalogue', 'anrhpub_theme' ),
		),
		array(
			'title' => __( 'Équipe à votre écoute', 'anrhpub_theme' ),
			'text'  => __( 'Une question sur ce produit, un échantillon ou un délai ? Notre équipe vous répond.', 'anrhpub_theme' ),
			'url'   => home_url( '/contact/' ),
			'url_label' => __( 'Nous contacter', 'anrhpub_theme' ),
		),
	);

	return (array) apply_filters( 'anrhpub_product_reassurance_items', $items );
}

/**
 * Affiche le bloc de réassurance.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_render_product_reassurance( $post_id = 0 ) {
	$items = anrhpub_get_product_reassurance_items();

	if ( empty( $items ) ) {
		return;
	}
	?>
	<section class="product-reassurance" aria-labelledby="product-reassurance-title">
		<div class="product-reassurance__divider" role="presentation" aria-hidden="true">
			<span class="product-reassurance__divider-line"></span>
			<span class="product-reassurance__divider-badge"><?php esc_html_e( 'ANRH Peyruis', 'anrhpub_theme' ); ?></span>
			<span class="product-reassurance__divider-line"></span>
		</div>
		<header class="product-reassurance__head">
			<p class="product-reassurance__kicker"><?php esc_html_e( 'Au-delà de ce produit', 'anrhpub_theme' ); ?></p>
			<h2 id="product-reassurance-title" class="product-reassurance__title"><?php esc_html_e( 'Nos engagements & services', 'anrhpub_theme' ); ?></h2>
			<p class="product-reassurance__lead">
				<?php esc_html_e( 'Ces informations concernent l’ANRH Peyruis et notre accompagnement — elles ne font pas partie de la description, des caractéristiques ni de la fiche technique de l’article ci-dessus.', 'anrhpub_theme' ); ?>
			</p>
		</header>
		<ul class="product-reassurance__grid" role="list">
			<?php foreach ( $items as $item ) : ?>
				<li class="product-reassurance__item">
					<h3 class="product-reassurance__item-title"><?php echo esc_html( $item['title'] ); ?></h3>
					<p class="product-reassurance__item-text"><?php echo esc_html( $item['text'] ); ?></p>
					<?php if ( ! empty( $item['url'] ) && ! empty( $item['url_label'] ) ) : ?>
						<a class="product-reassurance__item-link" href="<?php echo esc_url( $item['url'] ); ?>">
							<?php echo esc_html( $item['url_label'] ); ?>
							<span aria-hidden="true">→</span>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}

/**
 * Sections onglets + réassurance sous la fiche produit.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_render_product_single_sections( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	$excerpt  = get_the_excerpt( $post_id );
	$content  = get_post_field( 'post_content', $post_id );
	$details  = get_post_meta( $post_id, 'anr_details', true );

	if ( function_exists( 'anrhpub_normalize_utf8_text' ) ) {
		$content = anrhpub_normalize_utf8_text( (string) $content );
		$details = anrhpub_normalize_utf8_text( (string) $details );
	}
	$tech_rows = function_exists( 'anrhpub_get_product_technical_sheet_rows' )
		? anrhpub_get_product_technical_sheet_rows( $post_id )
		: array();

	$has_description = ( (string) $excerpt !== '' || (string) trim( $content ) !== '' );
	$has_specs       = (string) trim( $details ) !== '';
	$has_tech        = ! empty( $tech_rows );

	$tabs = array();

	if ( $has_description ) {
		$tabs['desc'] = __( 'Description', 'anrhpub_theme' );
	}
	if ( $has_specs ) {
		$tabs['specs'] = __( 'Caractéristiques', 'anrhpub_theme' );
	}
	if ( $has_tech ) {
		$tabs['tech'] = __( 'Fiche technique', 'anrhpub_theme' );
	}

	$first_tab = array_key_first( $tabs );

	if ( null === $first_tab ) {
		anrhpub_render_product_reassurance( $post_id );
		return;
	}
	?>
	<div class="product-single__sections">
		<div class="product-single__product-info">
			<header class="product-single__info-head">
				<h2 class="product-single__info-title"><?php esc_html_e( 'Informations sur ce produit', 'anrhpub_theme' ); ?></h2>
				<p class="product-single__info-lead"><?php esc_html_e( 'Description, caractéristiques et fiche technique de la référence.', 'anrhpub_theme' ); ?></p>
			</header>
			<div class="product-single__tabs-card">
				<div class="product-single__tabs" data-product-tabs>
					<div class="product-single__tablist" role="tablist" aria-label="<?php esc_attr_e( 'Informations produit', 'anrhpub_theme' ); ?>">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<?php $is_active = ( $key === $first_tab ); ?>
					<button
						type="button"
						class="product-single__tab<?php echo $is_active ? ' is-active' : ''; ?>"
						role="tab"
						id="<?php echo esc_attr( 'tab-' . $key ); ?>"
						aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr( 'panel-' . $key ); ?>"
						tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
						data-product-tab="<?php echo esc_attr( $key ); ?>"
					>
						<?php echo esc_html( $label ); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<?php if ( $has_description ) : ?>
				<section
					id="panel-desc"
					class="product-single__tab-panel<?php echo 'desc' === $first_tab ? ' is-active' : ''; ?>"
					role="tabpanel"
					aria-labelledby="tab-desc"
					data-product-panel="desc"
					<?php echo 'desc' === $first_tab ? '' : 'hidden'; ?>
				>
					<?php if ( $excerpt ) : ?>
						<div class="product-single__lead"><?php echo wp_kses_post( wpautop( $excerpt ) ); ?></div>
					<?php endif; ?>
					<?php if ( $content ) : ?>
						<div class="product-single__content entry-content">
							<?php echo apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<?php if ( $has_specs ) : ?>
				<section
					id="panel-specs"
					class="product-single__tab-panel<?php echo 'specs' === $first_tab ? ' is-active' : ''; ?>"
					role="tabpanel"
					aria-labelledby="tab-specs"
					data-product-panel="specs"
					<?php echo 'specs' === $first_tab ? '' : 'hidden'; ?>
				>
					<div class="product-single__specs entry-content">
						<?php echo wp_kses_post( wpautop( $details ) ); ?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $has_tech ) : ?>
				<section
					id="panel-tech"
					class="product-single__tab-panel<?php echo 'tech' === $first_tab ? ' is-active' : ''; ?>"
					role="tabpanel"
					aria-labelledby="tab-tech"
					data-product-panel="tech"
					<?php echo 'tech' === $first_tab ? '' : 'hidden'; ?>
				>
					<?php anrhpub_render_product_technical_sheet_table( $tech_rows, 'product-tech-sheet__list--tab' ); ?>
				</section>
			<?php endif; ?>
				</div>
			</div>
		</div>

		<?php anrhpub_render_product_reassurance( $post_id ); ?>
	</div>
	<?php
}
