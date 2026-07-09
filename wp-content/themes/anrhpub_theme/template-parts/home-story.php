<?php
/**
 * Accueil — argumentaire produits personnalisés & marquage.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$marquage_url = home_url( '/marquage/' );
$contact_url  = home_url( '/contact/' );
?>
<section class="home-story" data-animate aria-labelledby="home-story-why-title">
	<div class="container">
		<div class="home-story__grid">
			<article class="home-story__panel home-story__panel--why">
				<p class="home-story__kicker"><?php esc_html_e( 'Personnalisation', 'anrhpub_theme' ); ?></p>
				<h2 id="home-story-why-title" class="home-story__title">
					<?php esc_html_e( 'Des produits personnalisés pour vous démarquer !', 'anrhpub_theme' ); ?>
				</h2>
				<ul class="home-story__list">
					<li>
						<?php esc_html_e( 'Pour fidéliser vos clients et qu’ils se souviennent de votre entreprise.', 'anrhpub_theme' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Pour marquer le coup auprès de vos prospects, employés ou évènements d’entreprise.', 'anrhpub_theme' ); ?>
					</li>
					<li>
						<?php esc_html_e( 'Pour les CSE qui veulent accompagner leurs chèques vacances ou faire un cadeau original pour les fêtes de fin d’année…', 'anrhpub_theme' ); ?>
					</li>
				</ul>
				<p class="home-story__text home-story__text--lead">
					<?php esc_html_e( 'L’ANRH Peyruis vous propose une large gamme de produits à personnaliser sur mesure.', 'anrhpub_theme' ); ?>
				</p>
				<p class="home-story__text">
					<?php esc_html_e( 'Tous nos produits peuvent recevoir un marquage (logo, prénom, texte, image…) qui véhiculera l’image de votre entreprise partout.', 'anrhpub_theme' ); ?>
				</p>
			</article>

			<article class="home-story__panel home-story__panel--marking" aria-labelledby="home-story-marking-title">
				<p class="home-story__kicker"><?php esc_html_e( 'Marquage', 'anrhpub_theme' ); ?></p>
				<h2 id="home-story-marking-title" class="home-story__title">
					<?php esc_html_e( 'À chaque produit son marquage !', 'anrhpub_theme' ); ?>
				</h2>
				<p class="home-story__text">
					<?php esc_html_e( 'Vous avez choisi votre produit, il ne reste plus qu’à choisir le marquage idéal.', 'anrhpub_theme' ); ?>
				</p>
				<p class="home-story__text">
					<?php esc_html_e( 'En monochrome ou en quadri ? Discret ou au contraire bien visible ? Plusieurs choix sont possibles en fonction de la matière et de la forme de l’objet publicitaire.', 'anrhpub_theme' ); ?>
				</p>
				<p class="home-story__text">
					<?php esc_html_e( 'Vous pouvez consulter nos différentes techniques de marquage en cliquant sur le lien suivant :', 'anrhpub_theme' ); ?>
				</p>
				<p class="home-story__actions">
					<a class="btn btn--outline home-story__btn" href="<?php echo esc_url( $marquage_url ); ?>">
						<?php esc_html_e( 'ANR Publicité — techniques de marquage', 'anrhpub_theme' ); ?>
					</a>
				</p>
				<p class="home-story__text home-story__text--closing">
					<?php esc_html_e( 'Notre équipe est à votre disposition pour toutes demandes d’informations supplémentaires.', 'anrhpub_theme' ); ?>
					<a class="home-story__contact-link" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Nous contacter', 'anrhpub_theme' ); ?></a>
				</p>
			</article>
		</div>
	</div>
</section>
