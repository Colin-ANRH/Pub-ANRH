<?php
/**
 * Accueil — « Ils nous font confiance ».
 *
 * @package anrhpub_theme
 * @var array $args { clients: array }
 */

defined( 'ABSPATH' ) || exit;

$clients = isset( $args['clients'] ) && is_array( $args['clients'] ) ? $args['clients'] : array();

if ( empty( $clients ) ) {
	return;
}

$anrh_peyruis_url = 'https://anrh.fr/decouvrir-notre-offre/nos-etablissements/entreprise-adaptee/anrh-peyruis/';

$settings = function_exists( 'anrhpub_get_trust_clients_settings' ) ? anrhpub_get_trust_clients_settings() : array();
$kicker = isset( $settings['kicker'] ) ? (string) $settings['kicker'] : __( 'Références', 'anrhpub_theme' );
$title  = isset( $settings['title'] ) ? (string) $settings['title'] : __( 'Ils nous font confiance', 'anrhpub_theme' );
$prefix = isset( $settings['lead_prefix'] ) ? (string) $settings['lead_prefix'] : '';
$link_label = isset( $settings['lead_link_label'] ) ? (string) $settings['lead_link_label'] : '';
$suffix = isset( $settings['lead_suffix'] ) ? (string) $settings['lead_suffix'] : '';
?>
<section
	class="home-trust section home-trust--epure home-trust--clients"
	data-animate
	aria-labelledby="home-trust-title"
>
	<div class="home-trust__block home-trust__block--clients">
		<div class="container home-trust__header home-trust__header--center">
			<div class="section-header">
				<p class="home-trust__kicker"><?php echo esc_html( $kicker ); ?></p>
				<h2 id="home-trust-title" class="home-trust__title">
					<?php echo esc_html( $title ); ?>
				</h2>
				<p class="section-header__lead home-trust__lead">
					<?php echo esc_html( $prefix ); ?>
					<a
						href="<?php echo esc_url( $anrh_peyruis_url ); ?>"
						target="_blank"
						rel="noopener noreferrer"
					><?php echo esc_html( $link_label ); ?></a>
					<?php echo esc_html( $suffix ); ?>
				</p>
			</div>
		</div>

		<div class="container home-trust__marquee-shell">
			<div
				class="home-trust__viewport home-trust__viewport--clients"
				data-trust-marquee
				role="region"
				aria-label="<?php esc_attr_e( 'Logos de nos clients', 'anrhpub_theme' ); ?>"
				tabindex="0"
			>
				<div class="home-trust__track">
					<?php
					anrhpub_render_trust_logo_list( $clients );
					anrhpub_render_trust_logo_list( $clients, true );
					?>
				</div>
			</div>
		</div>
	</div>
</section>
