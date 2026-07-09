<?php
/**
 * RGPD — confidentialité, cookies, newsletter.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_PRIVACY_PAGE_SLUG', 'politique-confidentialite' );
define( 'ANRHPUB_COOKIE_CONSENT_OPTION', 'anrhpub_cookie_consent_v1' );

/**
 * Crée la page confidentialité.
 */
function anrhpub_ensure_privacy_page() {
	if ( get_page_by_path( ANRHPUB_PRIVACY_PAGE_SLUG ) ) {
		return;
	}

	wp_insert_post(
		array(
			'post_title'   => __( 'Politique de confidentialité', 'anrhpub_theme' ),
			'post_name'    => ANRHPUB_PRIVACY_PAGE_SLUG,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => anrhpub_default_privacy_content(),
		)
	);
}

/**
 * Contenu par défaut politique confidentialité.
 *
 * @return string
 */
function anrhpub_default_privacy_content() {
	return '<h2>' . esc_html__( 'Responsable du traitement', 'anrhpub_theme' ) . '</h2>
<p>ANRH Entreprise Adaptée Peyruis — contact : contact-peyruis@anrh.fr</p>
<h2>' . esc_html__( 'Données collectées', 'anrhpub_theme' ) . '</h2>
<p>' . esc_html__( 'Formulaires contact/devis, compte client, newsletter, cookies techniques et de mesure (si acceptés).', 'anrhpub_theme' ) . '</p>
<h2>' . esc_html__( 'Vos droits', 'anrhpub_theme' ) . '</h2>
<p>' . esc_html__( 'Accès, rectification, effacement, opposition — contactez-nous à l’adresse ci-dessus.', 'anrhpub_theme' ) . '</p>';
}

/**
 * URL politique confidentialité.
 *
 * @return string
 */
function anrhpub_privacy_url() {
	$page = get_page_by_path( ANRHPUB_PRIVACY_PAGE_SLUG );

	return $page ? get_permalink( $page ) : home_url( '/' . ANRHPUB_PRIVACY_PAGE_SLUG . '/' );
}

/**
 * Bandeau cookies.
 */
function anrhpub_render_cookie_banner() {
	if ( is_admin() || isset( $_COOKIE[ ANRHPUB_COOKIE_CONSENT_OPTION ] ) ) {
		return;
	}
	?>
	<div class="anr-cookie-banner" id="anr-cookie-banner" role="dialog" aria-live="polite" aria-label="<?php esc_attr_e( 'Cookies', 'anrhpub_theme' ); ?>">
		<div class="container anr-cookie-banner__inner">
			<p><?php esc_html_e( 'Nous utilisons des cookies pour le fonctionnement du site et, si vous acceptez, la mesure d’audience.', 'anrhpub_theme' ); ?>
				<a href="<?php echo esc_url( anrhpub_privacy_url() ); ?>"><?php esc_html_e( 'Politique de confidentialité', 'anrhpub_theme' ); ?></a>
			</p>
			<div class="anr-cookie-banner__actions">
				<button type="button" class="btn btn--outline btn--sm" data-cookie-reject><?php esc_html_e( 'Refuser', 'anrhpub_theme' ); ?></button>
				<button type="button" class="btn btn--primary btn--sm" data-cookie-accept><?php esc_html_e( 'Accepter', 'anrhpub_theme' ); ?></button>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'anrhpub_render_cookie_banner', 5 );

/**
 * Consentement newsletter renforcé (meta abonné).
 *
 * @param int $post_id Subscriber post ID.
 */
function anrhpub_newsletter_store_consent_meta( $post_id ) {
	update_post_meta( $post_id, 'anr_newsletter_consent_at', current_time( 'mysql' ) );
	update_post_meta( $post_id, 'anr_newsletter_consent_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
}
