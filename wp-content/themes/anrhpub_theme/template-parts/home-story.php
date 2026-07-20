<?php
/**
 * Accueil — personnalisation & marquage.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$marquage_url = home_url( '/marquage/' );
$contact_url  = home_url( '/contact/' );
?>
<section class="home-story home-story--open" data-animate aria-labelledby="home-story-why-title">
	<div class="container home-story__layout">
		<article class="home-story__chapter">
			<p class="home-story__kicker"><?php esc_html_e( 'Personnalisation', 'anrhpub_theme' ); ?></p>
			<h2 id="home-story-why-title" class="home-story__title">
				<?php esc_html_e( 'Des produits personnalisés pour vous démarquer', 'anrhpub_theme' ); ?>
			</h2>
			<ul class="home-story__list">
				<li><?php esc_html_e( 'Fidéliser vos clients et ancrer l’image de votre entreprise.', 'anrhpub_theme' ); ?></li>
				<li><?php esc_html_e( 'Marquer le coup auprès de vos prospects, équipes ou évènements.', 'anrhpub_theme' ); ?></li>
				<li><?php esc_html_e( 'Accompagner les CSE : chèques vacances, cadeaux de fin d’année…', 'anrhpub_theme' ); ?></li>
			</ul>
			<p class="home-story__text">
				<?php esc_html_e( 'L’ANRH Peyruis propose une large gamme à personnaliser sur mesure — logo, prénom, texte ou image.', 'anrhpub_theme' ); ?>
			</p>
		</article>

		<article class="home-story__chapter" aria-labelledby="home-story-marking-title">
			<p class="home-story__kicker"><?php esc_html_e( 'Marquage', 'anrhpub_theme' ); ?></p>
			<h2 id="home-story-marking-title" class="home-story__title">
				<?php esc_html_e( 'À chaque produit son marquage', 'anrhpub_theme' ); ?>
			</h2>
			<p class="home-story__text">
				<?php esc_html_e( 'Monochrome ou quadri, discret ou bien visible : le choix dépend de la matière et de la forme de l’objet.', 'anrhpub_theme' ); ?>
			</p>
			<p class="home-story__actions">
				<a class="home-story__text-link" href="<?php echo esc_url( $marquage_url ); ?>">
					<?php esc_html_e( 'Voir les techniques de marquage', 'anrhpub_theme' ); ?>
					<span aria-hidden="true">→</span>
				</a>
			</p>
			<p class="home-story__text home-story__text--closing">
				<?php esc_html_e( 'Une question ?', 'anrhpub_theme' ); ?>
				<a class="home-story__contact-link" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Nous contacter', 'anrhpub_theme' ); ?></a>
			</p>
		</article>
	</div>
</section>
