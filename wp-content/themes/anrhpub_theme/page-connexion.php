<?php
/**
 * Template — Connexion client.
 *
 * @package anrhpub_theme
 */

get_header();

$redirect_to = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : anrhpub_account_url();
?>
<main id="main-content" class="account-page account-page--login">
	<?php
	anrhpub_page_hero(
		array(
			'kicker' => __( 'Espace client', 'anrhpub_theme' ),
			'title'  => __( 'Connexion', 'anrhpub_theme' ),
			'lead'   => __( 'Accédez à votre profil et à vos produits favoris du catalogue.', 'anrhpub_theme' ),
		)
	);
	?>

	<section class="section account-section" data-animate>
		<div class="container account-section__inner">
			<div class="account-card">
				<form class="account-form" method="post" action="<?php echo esc_url( anrhpub_login_url() ); ?>">
					<?php wp_nonce_field( 'anrhpub_login' ); ?>
					<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />

					<p class="account-form__field">
						<label for="log"><?php esc_html_e( 'E-mail (recommandé)', 'anrhpub_theme' ); ?></label>
						<input type="text" name="log" id="log" required autocomplete="username" />
					</p>
					<p class="account-form__field">
						<label for="pwd"><?php esc_html_e( 'Mot de passe', 'anrhpub_theme' ); ?></label>
						<input type="password" name="pwd" id="pwd" required autocomplete="current-password" />
					</p>
					<p class="account-form__field account-form__field--row">
						<label class="account-form__checkbox">
							<input type="checkbox" name="rememberme" value="1" />
							<?php esc_html_e( 'Se souvenir de moi', 'anrhpub_theme' ); ?>
						</label>
					</p>
					<p class="account-form__actions">
						<button type="submit" name="anrhpub_login" value="1" class="btn btn--primary btn--block">
							<?php esc_html_e( 'Se connecter', 'anrhpub_theme' ); ?>
						</button>
					</p>
				</form>
				<p class="account-card__footer">
					<?php esc_html_e( 'Pas encore de compte ?', 'anrhpub_theme' ); ?>
					<a href="<?php echo esc_url( anrhpub_register_url() ); ?>"><?php esc_html_e( 'Créer un compte', 'anrhpub_theme' ); ?></a>
				</p>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
