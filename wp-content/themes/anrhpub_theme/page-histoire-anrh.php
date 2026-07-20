<?php
/**
 * Template — Histoire de l’ANRH.
 *
 * @package anrhpub_theme
 */

get_header();
?>

<main id="main-content" class="page-anrh-history page-atelier">
	<section class="atelier-hero" data-animate aria-labelledby="history-hero-title">
		<div class="container atelier-hero__inner">
			<p class="atelier-hero__brand">ANRH</p>
			<p class="atelier-hero__kicker"><?php esc_html_e( 'Association reconnue d’utilité publique', 'anrhpub_theme' ); ?></p>
			<h1 id="history-hero-title" class="atelier-hero__title">
				<?php esc_html_e( 'Histoire de l’ANRH', 'anrhpub_theme' ); ?>
			</h1>
			<p class="atelier-hero__lead">
				<?php esc_html_e( 'Depuis 1954, l’ANRH œuvre pour l’emploi durable des personnes en situation de handicap, en conciliant inclusion sociale et performance économique.', 'anrhpub_theme' ); ?>
			</p>
			<div class="atelier-hero__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( ANRHPUB_ANRH_URL ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Découvrir anrh.fr', 'anrhpub_theme' ); ?>
				</a>
				<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/societe/' ) ); ?>">
					<?php esc_html_e( 'ANRH Peyruis', 'anrhpub_theme' ); ?>
				</a>
			</div>
		</div>
	</section>

	<section class="atelier-strip" data-animate aria-label="<?php esc_attr_e( 'L’ANRH en chiffres', 'anrhpub_theme' ); ?>">
		<div class="container atelier-strip__grid">
			<div class="atelier-strip__item">
				<span class="atelier-strip__value">70</span>
				<span class="atelier-strip__label"><?php esc_html_e( 'ans d’engagement inclusif', 'anrhpub_theme' ); ?></span>
			</div>
			<div class="atelier-strip__item">
				<span class="atelier-strip__value">25</span>
				<span class="atelier-strip__label"><?php esc_html_e( 'établissements + siège', 'anrhpub_theme' ); ?></span>
			</div>
			<div class="atelier-strip__item">
				<span class="atelier-strip__value">2 000</span>
				<span class="atelier-strip__label"><?php esc_html_e( 'collaborateurs (80 % en situation de handicap)', 'anrhpub_theme' ); ?></span>
			</div>
			<div class="atelier-strip__item">
				<span class="atelier-strip__value">1954</span>
				<span class="atelier-strip__label"><?php esc_html_e( 'création de l’association', 'anrhpub_theme' ); ?></span>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/anrh', 'history-content' ); ?>
</main>

<?php
get_footer();
