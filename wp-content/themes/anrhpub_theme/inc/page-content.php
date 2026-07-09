<?php
/**
 * Contenus éditoriaux des pages vitrine.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_CONTENT_VERSION', 3 );

/**
 * Contenu HTML — page Notre activité (/societe/).
 *
 * @return string
 */
function anrhpub_societe_page_content() {
	ob_start();
	?>
	<section class="societe-section" data-animate>
		<h2><?php esc_html_e( 'Qui sommes-nous ?', 'anrhpub_theme' ); ?></h2>
		<p><?php esc_html_e( 'ANRH Peyruis (AssociatioN pour l’insertion et la Réinsertion professionnelle et humaine des Handicapés) propose une activité commerciale dédiée aux objets publicitaires personnalisés.', 'anrhpub_theme' ); ?></p>
		<p><?php esc_html_e( 'Le but premier de l’ANRH est l’insertion professionnelle et sociale des personnes adultes handicapées physiques et/ou psychiques (Article 1er des statuts).', 'anrhpub_theme' ); ?></p>
		<p>
			<?php esc_html_e( 'Pour davantage d’informations, consultez notre site internet :', 'anrhpub_theme' ); ?>
			<a href="https://www.anrh.fr" target="_blank" rel="noopener noreferrer">www.anrh.fr</a>
		</p>
	</section>

	<section class="societe-section" data-animate>
		<h2><?php esc_html_e( 'Notre activité', 'anrhpub_theme' ); ?></h2>
		<p><?php esc_html_e( 'Notre activité est dédiée aux professionnels, entreprises, collectivités et même aux associations.', 'anrhpub_theme' ); ?></p>
		<p><?php esc_html_e( 'En travaillant avec l’EA de Peyruis, vous agissez de façon responsable en devenant un acteur dans l’insertion professionnelle des travailleurs handicapés.', 'anrhpub_theme' ); ?></p>
	</section>

	<section class="societe-section" data-animate>
		<h2><?php esc_html_e( 'Notre offre', 'anrhpub_theme' ); ?></h2>
		<p><?php esc_html_e( 'Nous vous proposons plus de 450 produits, répartis dans 10 catégories (dont certains sont composés de matières recyclées et/ou recyclables).', 'anrhpub_theme' ); ?></p>
		<p><?php esc_html_e( 'Vous trouverez des produits allant du plus classique (stylos, blocs-notes, tasses, t-shirt…) au plus original et hi-tech (diffuseur, lampe, réveil, accessoires de cuisine, chargeur solaire…).', 'anrhpub_theme' ); ?></p>
		<p><?php esc_html_e( 'Notre entreprise adaptée vous propose une personnalisation à votre image afin de répondre à toutes vos demandes de communication en B to B afin de promouvoir votre entreprise.', 'anrhpub_theme' ); ?></p>
		<p><?php esc_html_e( 'Que ce soit pour un évènement, un cadeau CSE ou cadeau client, vous pourrez le personnaliser avec votre logo, coordonnées ou autre… afin de vous démarquer et surtout… vous faire remarquer !', 'anrhpub_theme' ); ?></p>
	</section>

	<aside class="societe-highlight" data-animate>
		<p><?php esc_html_e( 'Fort d’une certification ISO 9001 groupe version 2015, vous êtes assuré d’acheter des produits de qualité, tout en participant à l’économie solidaire.', 'anrhpub_theme' ); ?></p>
	</aside>
	<?php
	return ob_get_clean();
}

/**
 * Contenu HTML — page Marquage (intro légère ; détail dans le template).
 *
 * @return string
 */
function anrhpub_marquage_page_content() {
	return '';
}

/**
 * Met à jour le contenu des pages vitrine existantes.
 */
function anrhpub_maybe_update_page_content() {
	if ( (int) get_option( 'anrhpub_content_version', 0 ) >= ANRHPUB_CONTENT_VERSION ) {
		return;
	}

	$updates = array(
		'societe'  => anrhpub_societe_page_content(),
		'marquage' => anrhpub_marquage_page_content(),
	);

	foreach ( $updates as $slug => $content ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			wp_update_post(
				array(
					'ID'           => $page->ID,
					'post_content' => $content,
				)
			);
		}
	}

	update_option( 'anrhpub_content_version', ANRHPUB_CONTENT_VERSION );
}
add_action( 'init', 'anrhpub_maybe_update_page_content', 20 );
