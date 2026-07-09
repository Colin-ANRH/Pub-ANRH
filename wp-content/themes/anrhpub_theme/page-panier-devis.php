<?php
/**
 * Template — Panier devis (sans paiement).
 *
 * @package anrhpub_theme
 */

get_header();
?>
<main id="main-content" class="quote-cart-page">
	<?php
	anrhpub_page_hero(
		array(
			'kicker' => __( 'Catalogue', 'anrhpub_theme' ),
			'title'  => __( 'Panier devis', 'anrhpub_theme' ),
			'lead'   => __( 'Votre sélection de produits et quantités — contactez-nous pour recevoir un devis personnalisé avec marquage.', 'anrhpub_theme' ),
		)
	);
	?>

	<section class="section quote-cart-section" data-animate>
		<div class="container">
			<div class="quote-cart-layout">
				<div class="quote-cart-main" id="quote-cart-app" data-quote-cart-app>
					<?php anrhpub_render_quote_cart_table( array() ); ?>
				</div>

				<aside class="quote-cart-aside" aria-label="<?php esc_attr_e( 'Demander un devis', 'anrhpub_theme' ); ?>">
					<div class="quote-cart-aside__card">
						<h2><?php esc_html_e( 'Étape suivante', 'anrhpub_theme' ); ?></h2>
						<p><?php esc_html_e( 'Aucun paiement en ligne. Notre équipe établit un devis selon vos quantités, couleurs et marquage (logo, texte).', 'anrhpub_theme' ); ?></p>
						<a class="btn btn--primary btn--block" href="<?php echo esc_url( anrhpub_quote_cart_contact_url() ); ?>" data-quote-contact-link>
							<?php esc_html_e( 'Envoyer ma demande de devis', 'anrhpub_theme' ); ?>
						</a>
						<a class="btn btn--outline btn--block" href="tel:+33492612713">
							<?php esc_html_e( 'Appeler le 04 92 61 27 13', 'anrhpub_theme' ); ?>
						</a>
						<a class="btn btn--ghost btn--block" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
							<?php esc_html_e( 'Continuer mes achats', 'anrhpub_theme' ); ?>
						</a>
						<div class="quote-cart-tools">
							<?php if ( function_exists( 'anrhpub_get_cart_export_url' ) ) : ?>
								<a class="btn btn--outline btn--sm btn--block" href="<?php echo esc_url( anrhpub_get_cart_export_url() ); ?>" data-quote-export>
									<?php esc_html_e( 'Exporter Excel', 'anrhpub_theme' ); ?>
								</a>
							<?php endif; ?>
							<button type="button" class="btn btn--outline btn--sm btn--block" data-quote-save-draft <?php echo anrhpub_is_client_logged_in() ? '' : 'disabled'; ?>>
								<?php esc_html_e( 'Enregistrer brouillon', 'anrhpub_theme' ); ?>
							</button>
							<button type="button" class="btn btn--outline btn--sm btn--block" data-shared-list-save <?php echo anrhpub_is_client_logged_in() ? '' : 'disabled'; ?>>
								<?php esc_html_e( 'Sauver liste', 'anrhpub_theme' ); ?>
							</button>
						</div>
					</div>
					<div class="quote-cart-aside__recap" hidden data-quote-recap-box>
						<h3><?php esc_html_e( 'Récapitulatif', 'anrhpub_theme' ); ?></h3>
						<pre class="quote-cart-aside__recap-text" data-quote-recap-text></pre>
						<button type="button" class="btn btn--outline btn--block" data-quote-copy-recap>
							<?php esc_html_e( 'Copier le récapitulatif', 'anrhpub_theme' ); ?>
						</button>
					</div>
				</aside>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
