<?php
/**
 * Accueil — « Nos partenaires ».
 *
 * @package anrhpub_theme
 * @var array $args { partners: array }
 */

defined( 'ABSPATH' ) || exit;

$partners = isset( $args['partners'] ) && is_array( $args['partners'] ) ? $args['partners'] : array();

if ( empty( $partners ) ) {
	return;
}

$anrh_peyruis_url = 'https://anrh.fr/decouvrir-notre-offre/nos-etablissements/entreprise-adaptee/anrh-peyruis/';

$settings = function_exists( 'anrhpub_get_trust_partners_settings' ) ? anrhpub_get_trust_partners_settings() : array();
$kicker = isset( $settings['kicker'] ) ? (string) $settings['kicker'] : __( 'Écosystème', 'anrhpub_theme' );
$title  = isset( $settings['title'] ) ? (string) $settings['title'] : __( 'Nos partenaires', 'anrhpub_theme' );
$prefix = isset( $settings['lead_prefix'] ) ? (string) $settings['lead_prefix'] : '';
$link_label = isset( $settings['lead_link_label'] ) ? (string) $settings['lead_link_label'] : '';
$suffix = isset( $settings['lead_suffix'] ) ? (string) $settings['lead_suffix'] : '';
?>
<section
	class="home-trust section home-trust--epure home-trust--partners"
	data-animate
	aria-labelledby="home-trust-partners-title"
>
	<div class="container home-trust__header">
		<div class="section-header">
			<p class="home-trust__kicker"><?php echo esc_html( $kicker ); ?></p>
			<h2 id="home-trust-partners-title" class="home-trust__title">
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

	<div class="container home-trust__partners-wrap">
		<?php anrhpub_render_trust_logo_list( $partners, false, true ); ?>
	</div>
</section>
