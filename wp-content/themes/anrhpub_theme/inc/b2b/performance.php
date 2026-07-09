<?php
/**
 * Performance & production — en-têtes cache, checklist admin.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache navigateur assets statiques (thème).
 */
function anrhpub_send_static_cache_headers() {
	if ( is_admin() ) {
		return;
	}

	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';

	if ( preg_match( '/\.(css|js|webp|woff2?|jpg|png|svg)(\?|$)/i', $uri ) ) {
		header( 'Cache-Control: public, max-age=31536000, immutable' );
	}
}
add_action( 'send_headers', 'anrhpub_send_static_cache_headers' );

/**
 * Notice checklist production.
 */
function anrhpub_production_checklist_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'edit-anr_product' !== $screen->id ) {
		return;
	}

	$https = is_ssl();
	?>
	<div class="notice notice-info">
		<p><strong><?php esc_html_e( 'Checklist production ANRH', 'anrhpub_theme' ); ?></strong></p>
		<ul style="list-style:disc;margin-left:1.25rem;">
			<li><?php echo $https ? '✓' : '✗'; ?> <?php esc_html_e( 'HTTPS actif sur le domaine public', 'anrhpub_theme' ); ?></li>
			<li><?php esc_html_e( 'Configurer un plugin de cache (WP Rocket, W3 Total Cache…) + CDN si trafic national', 'anrhpub_theme' ); ?></li>
			<li><?php esc_html_e( 'Sauvegardes automatiques (UpdraftPlus, hébergeur) + environnement staging', 'anrhpub_theme' ); ?></li>
			<li><?php esc_html_e( 'Vérifier les e-mails SMTP (devis, commandes, validation comptes)', 'anrhpub_theme' ); ?></li>
		</ul>
	</div>
	<?php
}
add_action( 'admin_notices', 'anrhpub_production_checklist_notice' );
