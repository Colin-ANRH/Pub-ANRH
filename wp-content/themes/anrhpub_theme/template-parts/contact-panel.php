<?php
/**
 * Bloc contact — coordonnées + carte Google Maps.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$place_name   = 'ANRH Entreprise Adaptée Peyruis';
$address_line = 'Av. Pierre Gassendi, 04310 Peyruis, France';
$maps_query   = rawurlencode( $place_name . ', ' . $address_line );
$maps_embed   = 'https://maps.google.com/maps?q=' . $maps_query . '&hl=fr&z=16&ie=UTF8&iwloc=&output=embed';
$maps_link    = 'https://www.google.com/maps/search/?api=1&query=' . $maps_query;
?>
<section class="contact-layout" aria-label="<?php esc_attr_e( 'Nous contacter', 'anrhpub_theme' ); ?>">
	<div class="contact-panel">
		<ul class="contact-panel__list">
			<li>
				<span class="contact-panel__label"><?php esc_html_e( 'Téléphone', 'anrhpub_theme' ); ?></span>
				<a href="tel:+33492612713">04 92 61 27 13</a>
			</li>
			<li>
				<span class="contact-panel__label"><?php esc_html_e( 'E-mail', 'anrhpub_theme' ); ?></span>
				<a href="mailto:contact-peyruis@anrh.fr">contact-peyruis@anrh.fr</a>
			</li>
			<li>
				<span class="contact-panel__label"><?php esc_html_e( 'Adresse', 'anrhpub_theme' ); ?></span>
				<address class="contact-panel__address">
					<strong><?php echo esc_html( $place_name ); ?></strong><br>
					<?php echo esc_html( $address_line ); ?>
				</address>
			</li>
		</ul>
		<div class="contact-panel__actions">
			<a class="btn btn--outline" href="<?php echo esc_url( $maps_link ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Ouvrir dans Google Maps', 'anrhpub_theme' ); ?>
			</a>
			<a class="btn btn--primary" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
				<?php esc_html_e( 'Voir le catalogue', 'anrhpub_theme' ); ?>
			</a>
		</div>
	</div>
	<div class="contact-map">
		<iframe
			class="contact-map__iframe"
			title="<?php echo esc_attr( sprintf( __( 'Carte — %s', 'anrhpub_theme' ), $place_name ) ); ?>"
			src="<?php echo esc_url( $maps_embed ); ?>"
			width="600"
			height="450"
			style="border:0;"
			allowfullscreen=""
			loading="lazy"
			referrerpolicy="no-referrer-when-downgrade"
		></iframe>
	</div>
</section>
