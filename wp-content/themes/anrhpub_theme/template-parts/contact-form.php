<?php
/**
 * Formulaire contact / devis.
 *
 * @package anrhpub_theme
 * @var array  $args['defaults']
 * @var string $args['error']
 * @var bool   $args['sent']
 */

defined( 'ABSPATH' ) || exit;

$defaults = isset( $args['defaults'] ) && is_array( $args['defaults'] ) ? $args['defaults'] : array();
$error    = isset( $args['error'] ) ? (string) $args['error'] : '';
$sent     = ! empty( $args['sent'] );
$is_devis = ! empty( $defaults['is_devis'] );
?>
<section class="contact-form-section" aria-labelledby="contact-form-title">
	<h2 id="contact-form-title" class="contact-form-section__title">
		<?php echo $is_devis ? esc_html__( 'Envoyer ma demande de devis', 'anrhpub_theme' ) : esc_html__( 'Nous écrire', 'anrhpub_theme' ); ?>
	</h2>

	<?php if ( $sent ) : ?>
		<div class="contact-form-alert contact-form-alert--success" role="status">
			<?php esc_html_e( 'Votre message a bien été envoyé. Notre équipe vous répondra rapidement.', 'anrhpub_theme' ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $error ) : ?>
		<div class="contact-form-alert contact-form-alert--error" role="alert">
			<?php echo esc_html( $error ); ?>
		</div>
	<?php endif; ?>

	<form class="contact-form account-form" method="post" action="<?php echo esc_url( home_url( '/contact/' ) ); ?>" data-contact-form>
		<?php wp_nonce_field( 'anrhpub_contact_form' ); ?>
		<input type="hidden" name="anrhpub_contact_submit" value="1" />
		<input type="hidden" name="contact_is_devis" value="<?php echo $is_devis ? '1' : '0'; ?>" />
		<p class="account-form__field account-form__field--hp" aria-hidden="true">
			<label for="anrhpub_website">Site web</label>
			<input type="text" name="anrhpub_website" id="anrhpub_website" tabindex="-1" autocomplete="off" />
		</p>

		<div class="account-form__grid account-form__grid--contact">
			<p class="account-form__field">
				<label for="contact_name"><?php esc_html_e( 'Nom complet', 'anrhpub_theme' ); ?> <span class="required">*</span></label>
				<input type="text" name="contact_name" id="contact_name" value="<?php echo esc_attr( $defaults['name'] ?? '' ); ?>" required autocomplete="name" />
			</p>
			<p class="account-form__field">
				<label for="contact_email"><?php esc_html_e( 'E-mail', 'anrhpub_theme' ); ?> <span class="required">*</span></label>
				<input type="email" name="contact_email" id="contact_email" value="<?php echo esc_attr( $defaults['email'] ?? '' ); ?>" required autocomplete="email" />
			</p>
		</div>

		<div class="account-form__grid account-form__grid--contact">
			<p class="account-form__field account-form__field--phone">
				<label for="contact_phone"><?php esc_html_e( 'Téléphone', 'anrhpub_theme' ); ?></label>
				<input type="tel" name="contact_phone" id="contact_phone" value="<?php echo esc_attr( $defaults['phone'] ?? '' ); ?>" autocomplete="tel" inputmode="tel" placeholder="06 12 34 56 78" />
			</p>
			<p class="account-form__field">
				<label for="contact_company"><?php esc_html_e( 'Société', 'anrhpub_theme' ); ?></label>
				<input type="text" name="contact_company" id="contact_company" value="<?php echo esc_attr( $defaults['company'] ?? '' ); ?>" autocomplete="organization" />
			</p>
		</div>

		<p class="account-form__field">
			<label for="contact_subject"><?php esc_html_e( 'Objet', 'anrhpub_theme' ); ?></label>
			<input type="text" name="contact_subject" id="contact_subject" value="<?php echo esc_attr( $defaults['subject'] ?? '' ); ?>" />
		</p>

		<?php if ( $is_devis ) : ?>
			<div class="contact-devis-cart" data-contact-cart-preview hidden>
				<p class="contact-devis-cart__label"><?php esc_html_e( 'Produits dans votre panier devis', 'anrhpub_theme' ); ?></p>
				<ul class="contact-devis-cart__list" data-contact-cart-list></ul>
				<p class="contact-devis-cart__empty" data-contact-cart-empty hidden>
					<?php esc_html_e( 'Votre panier est vide.', 'anrhpub_theme' ); ?>
					<a href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>"><?php esc_html_e( 'Parcourir le catalogue', 'anrhpub_theme' ); ?></a>
				</p>
			</div>
			<div class="contact-devis-delivery" data-contact-delivery-box>
				<p class="contact-devis-delivery__label"><?php esc_html_e( 'Adresse de livraison pour ce devis', 'anrhpub_theme' ); ?></p>
				<?php if ( ! empty( $defaults['delivery_summary'] ) ) : ?>
					<pre class="contact-devis-delivery__text" data-contact-delivery-preview><?php echo esc_html( $defaults['delivery_summary'] ); ?></pre>
					<input type="hidden" name="contact_delivery_summary" value="<?php echo esc_attr( $defaults['delivery_summary'] ); ?>" data-contact-delivery-hidden />
				<?php else : ?>
					<p class="contact-devis-delivery__empty" data-contact-delivery-empty>
						<?php esc_html_e( 'Aucune adresse de livraison enregistrée.', 'anrhpub_theme' ); ?>
						<a href="<?php echo esc_url( anrhpub_account_url() ); ?>#panel-addresses"><?php esc_html_e( 'Ajouter une adresse dans Mon compte', 'anrhpub_theme' ); ?></a>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $defaults['brand_logo_url'] ) ) : ?>
				<div class="contact-devis-logo" data-contact-logo-box>
					<p class="contact-devis-logo__label"><?php esc_html_e( 'Logo pour le marquage', 'anrhpub_theme' ); ?></p>
					<img
						class="contact-devis-logo__img"
						src="<?php echo esc_url( $defaults['brand_logo_url'] ); ?>"
						alt="<?php echo esc_attr( $defaults['brand_logo_alt'] ?? '' ); ?>"
						width="160"
						height="160"
						data-contact-logo-preview
					/>
					<input type="hidden" name="contact_brand_logo_url" value="<?php echo esc_attr( $defaults['brand_logo_url'] ); ?>" data-contact-logo-hidden />
					<p class="contact-devis-logo__hint"><?php esc_html_e( 'Ce logo sera joint à votre demande (visible par notre équipe pour le devis marquage).', 'anrhpub_theme' ); ?></p>
				</div>
			<?php else : ?>
				<div class="contact-devis-logo contact-devis-logo--empty" data-contact-logo-box hidden>
					<p class="contact-devis-logo__label"><?php esc_html_e( 'Logo pour le marquage', 'anrhpub_theme' ); ?></p>
					<img class="contact-devis-logo__img" src="" alt="" width="160" height="160" data-contact-logo-preview hidden />
					<input type="hidden" name="contact_brand_logo_url" value="" data-contact-logo-hidden />
					<p class="contact-devis-logo__hint" data-contact-logo-empty>
						<?php esc_html_e( 'Aucun logo enregistré.', 'anrhpub_theme' ); ?>
						<a href="<?php echo esc_url( anrhpub_account_url() ); ?>#panel-profile"><?php esc_html_e( 'Ajouter un logo dans Mon compte', 'anrhpub_theme' ); ?></a>
					</p>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<p class="account-form__field">
			<label for="contact_message">
				<?php echo $is_devis ? esc_html__( 'Votre demande de devis', 'anrhpub_theme' ) : esc_html__( 'Message', 'anrhpub_theme' ); ?>
				<span class="required">*</span>
			</label>
			<?php if ( $is_devis ) : ?>
				<span class="account-form__hint"><?php esc_html_e( 'Le message se met à jour automatiquement quand vous modifiez votre panier. Vous pouvez l’ajuster avant envoi.', 'anrhpub_theme' ); ?></span>
			<?php endif; ?>
			<textarea name="contact_message" id="contact_message" rows="12" required data-contact-message><?php echo esc_textarea( $defaults['message'] ?? '' ); ?></textarea>
		</p>

		<?php if ( ! empty( $defaults['captcha_token'] ) && ! empty( $defaults['captcha_label'] ) ) : ?>
			<p class="account-form__field">
				<label for="contact_captcha_answer"><?php echo esc_html( $defaults['captcha_label'] ); ?> <span class="required">*</span></label>
				<input type="number" name="contact_captcha_answer" id="contact_captcha_answer" inputmode="numeric" required autocomplete="off" />
				<input type="hidden" name="contact_captcha_token" value="<?php echo esc_attr( $defaults['captcha_token'] ); ?>" />
			</p>
		<?php endif; ?>

		<p class="account-form__actions">
			<button type="submit" class="btn btn--primary btn--lg">
				<?php echo $is_devis ? esc_html__( 'Envoyer la demande de devis', 'anrhpub_theme' ) : esc_html__( 'Envoyer le message', 'anrhpub_theme' ); ?>
			</button>
		</p>
	</form>
</section>
