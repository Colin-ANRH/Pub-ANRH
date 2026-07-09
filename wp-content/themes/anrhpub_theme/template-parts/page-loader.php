<?php
/**
 * Overlay de chargement — affiché à chaque chargement de page.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$logo_url = anrhpub_theme_image_uri( 'assets/images/logo-anr' );

if ( has_custom_logo() ) {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$custom  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
	if ( $custom ) {
		$logo_url = $custom;
	}
}
?>
<div id="page-loader" class="page-loader" role="status" aria-live="polite" aria-busy="true" aria-label="<?php esc_attr_e( 'Chargement de la page', 'anrhpub_theme' ); ?>">
	<div class="page-loader__backdrop" aria-hidden="true"></div>
	<div class="page-loader__panel">
		<img
			class="page-loader__logo"
			src="<?php echo esc_url( $logo_url ); ?>"
			alt=""
			width="160"
			height="auto"
			decoding="async"
		/>
		<div class="page-loader__track" aria-hidden="true">
			<span class="page-loader__bar"></span>
		</div>
		<p class="page-loader__label"><?php esc_html_e( 'Chargement', 'anrhpub_theme' ); ?></p>
	</div>
</div>
