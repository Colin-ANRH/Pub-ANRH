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
 * Valeur du cookie de consentement.
 *
 * @return string essential|all|0|1|''
 */
function anrhpub_get_cookie_consent_value() {
	if ( ! isset( $_COOKIE[ ANRHPUB_COOKIE_CONSENT_OPTION ] ) ) {
		return '';
	}

	return sanitize_key( wp_unslash( (string) $_COOKIE[ ANRHPUB_COOKIE_CONSENT_OPTION ] ) );
}

/**
 * Consentement cookies analytics / mesure d’audience ?
 *
 * @return bool
 */
function anrhpub_has_analytics_consent() {
	$value = anrhpub_get_cookie_consent_value();

	return in_array( $value, array( 'all', '1' ), true );
}

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
 * Expose l’état consentement au JS (avant autres scripts).
 */
function anrhpub_enqueue_cookie_consent_bootstrap() {
	if ( is_admin() || ( function_exists( 'anrhpub_is_staging_environment' ) && anrhpub_is_staging_environment() ) ) {
		return;
	}

	wp_register_script( 'anrhpub-consent-bootstrap', false, array(), ANRHPUB_THEME_VERSION, false );
	wp_enqueue_script( 'anrhpub-consent-bootstrap' );
	wp_add_inline_script(
		'anrhpub-consent-bootstrap',
		'window.anrhpubConsent=' . wp_json_encode(
			array(
				'analytics' => anrhpub_has_analytics_consent(),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_cookie_consent_bootstrap', 1 );

/**
 * Bloque les scripts analytics tant que le consentement n’est pas donné.
 *
 * @param string $tag    Tag script.
 * @param string $handle Handle.
 * @param string $src    URL.
 * @return string
 */
function anrhpub_block_scripts_without_consent( $tag, $handle, $src ) {
	if ( anrhpub_has_analytics_consent() ) {
		return $tag;
	}

	$blocked = apply_filters(
		'anrhpub_consent_required_script_handles',
		array( 'google-analytics', 'gtag', 'facebook-pixel', 'hotjar' )
	);

	if ( in_array( $handle, $blocked, true ) ) {
		return str_replace( '<script ', '<script type="text/plain" data-anr-consent="analytics" ', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'anrhpub_block_scripts_without_consent', 10, 3 );

/**
 * Bandeau cookies.
 */
function anrhpub_render_cookie_banner() {
	if ( is_admin() || ( function_exists( 'anrhpub_is_staging_environment' ) && anrhpub_is_staging_environment() ) || anrhpub_get_cookie_consent_value() !== '' ) {
		return;
	}
	?>
	<div class="anr-cookie-banner" id="anr-cookie-banner" role="dialog" aria-live="polite" aria-label="<?php esc_attr_e( 'Cookies', 'anrhpub_theme' ); ?>" hidden>
		<div class="container anr-cookie-banner__inner">
			<p><?php esc_html_e( 'Nous utilisons des cookies essentiels au fonctionnement du site. Les cookies de mesure d’audience ne sont déposés qu’avec votre accord.', 'anrhpub_theme' ); ?>
				<a href="<?php echo esc_url( anrhpub_privacy_url() ); ?>"><?php esc_html_e( 'Politique de confidentialité', 'anrhpub_theme' ); ?></a>
			</p>
			<div class="anr-cookie-banner__actions">
				<button type="button" class="btn btn--outline btn--sm" data-cookie-reject><?php esc_html_e( 'Refuser les cookies analytics', 'anrhpub_theme' ); ?></button>
				<button type="button" class="btn btn--primary btn--sm" data-cookie-accept><?php esc_html_e( 'Tout accepter', 'anrhpub_theme' ); ?></button>
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
	update_post_meta( $post_id, 'anr_newsletter_consent_ip', anrhpub_get_request_ip() );
}
