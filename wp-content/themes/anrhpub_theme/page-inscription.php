<?php
/**
 * Template — Inscription client.
 *
 * @package anrhpub_theme
 */

get_header();


?>
<main id="main-content" class="account-page account-page--register">
	<?php
	anrhpub_page_hero(
		array(
			'kicker' => __( 'Espace client', 'anrhpub_theme' ),
			'title'  => __( 'Inscription', 'anrhpub_theme' ),
			'lead'   => __( 'Créez votre compte pour sauvegarder vos produits favoris du catalogue ANRH.', 'anrhpub_theme' ),
		)
	);
	?>

	<section class="section account-section" data-animate>
		<div class="container account-section__inner">
			<div class="account-card">
				<form class="account-form" method="post" action="<?php echo esc_url( anrhpub_register_url() ); ?>">
					<?php wp_nonce_field( 'anrhpub_register' ); ?>

					<div class="account-form__grid">
						<p class="account-form__field">
							<label for="first_name"><?php esc_html_e( 'Prénom', 'anrhpub_theme' ); ?></label>
							<input type="text" name="first_name" id="first_name" autocomplete="given-name" />
						</p>
						<p class="account-form__field">
							<label for="last_name"><?php esc_html_e( 'Nom', 'anrhpub_theme' ); ?></label>
							<input type="text" name="last_name" id="last_name" autocomplete="family-name" />
						</p>
					</div>
					<p class="account-form__field">
						<label for="company"><?php esc_html_e( 'Raison sociale', 'anrhpub_theme' ); ?> <span class="required">*</span></label>
						<input type="text" name="company" id="company" required autocomplete="organization" />
					</p>
					<p class="account-form__field">
						<label for="siret"><?php esc_html_e( 'SIRET', 'anrhpub_theme' ); ?> <span class="required">*</span></label>
						<input type="text" name="siret" id="siret" required pattern="[0-9\s]{9,17}" autocomplete="off" />
					</p>
					<p class="account-form__field">
						<label for="vat_number"><?php esc_html_e( 'N° TVA intracommunautaire', 'anrhpub_theme' ); ?></label>
						<input type="text" name="vat_number" id="vat_number" autocomplete="off" />
					</p>
					<p class="account-form__field">
						<label for="erp_code"><?php esc_html_e( 'Code client ERP (si connu)', 'anrhpub_theme' ); ?></label>
						<input type="text" name="erp_code" id="erp_code" autocomplete="off" />
					</p>
					<p class="account-form__hint"><?php esc_html_e( 'Votre compte sera validé manuellement avant l’accès aux tarifs catalogue.', 'anrhpub_theme' ); ?></p>
					<p class="account-form__field">
						<label for="email"><?php esc_html_e( 'E-mail', 'anrhpub_theme' ); ?></label>
						<input type="email" name="email" id="email" required autocomplete="email" />
					</p>
					<p class="account-form__field">
						<label for="password"><?php esc_html_e( 'Mot de passe', 'anrhpub_theme' ); ?></label>
						<input type="password" name="password" id="password" required minlength="8" autocomplete="new-password" />
						<span class="account-form__hint"><?php esc_html_e( '8 caractères minimum.', 'anrhpub_theme' ); ?></span>
					</p>
					<p class="account-form__field">
						<label for="password_confirm"><?php esc_html_e( 'Confirmer le mot de passe', 'anrhpub_theme' ); ?></label>
						<input type="password" name="password_confirm" id="password_confirm" required minlength="8" autocomplete="new-password" />
					</p>
					<p class="account-form__actions">
						<button type="submit" name="anrhpub_register" value="1" class="btn btn--primary btn--block">
							<?php esc_html_e( 'Créer mon compte', 'anrhpub_theme' ); ?>
						</button>
					</p>
				</form>
				<p class="account-card__footer">
					<?php esc_html_e( 'Déjà inscrit ?', 'anrhpub_theme' ); ?>
					<a href="<?php echo esc_url( anrhpub_login_url() ); ?>"><?php esc_html_e( 'Se connecter', 'anrhpub_theme' ); ?></a>
				</p>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
