<?php
/**
 * Newsletter — bas de page d’accueil.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'anrhpub_is_newsletter_enabled' ) || ! anrhpub_is_newsletter_enabled() ) {
	return;
}

$settings = anrhpub_get_newsletter_settings();
?>
<section class="home-newsletter" data-animate aria-labelledby="home-newsletter-title">
	<div class="container">
		<div class="home-newsletter__inner">
			<div class="home-newsletter__copy">
				<?php if ( ! empty( $settings['kicker'] ) ) : ?>
					<p class="home-newsletter__kicker"><?php echo esc_html( (string) $settings['kicker'] ); ?></p>
				<?php endif; ?>
				<h2 id="home-newsletter-title" class="home-newsletter__title">
					<?php echo esc_html( (string) $settings['title'] ); ?>
					<?php if ( ! empty( $settings['title_em'] ) ) : ?>
						<em><?php echo esc_html( (string) $settings['title_em'] ); ?></em>
					<?php endif; ?>
				</h2>
				<?php if ( ! empty( $settings['text'] ) ) : ?>
					<p class="home-newsletter__text"><?php echo esc_html( (string) $settings['text'] ); ?></p>
				<?php endif; ?>
			</div>

			<form class="home-newsletter__form" data-newsletter-form method="post" novalidate>
				<div class="home-newsletter__field">
					<label class="screen-reader-text" for="newsletter-email"><?php esc_html_e( 'Adresse e-mail', 'anrhpub_theme' ); ?></label>
					<input
						type="email"
						id="newsletter-email"
						name="newsletter_email"
						class="home-newsletter__input"
						placeholder="<?php esc_attr_e( 'votre@email.pro', 'anrhpub_theme' ); ?>"
						required
						autocomplete="email"
					/>
					<button type="submit" class="btn btn--primary home-newsletter__submit" data-newsletter-submit>
						<?php echo esc_html( (string) $settings['button_label'] ); ?>
					</button>
				</div>

				<label class="home-newsletter__consent">
					<input type="checkbox" name="newsletter_consent" value="1" required />
					<span><?php echo esc_html( (string) $settings['consent_text'] ); ?></span>
				</label>

				<input type="text" name="anrhpub_website" class="home-newsletter__honeypot" tabindex="-1" autocomplete="off" aria-hidden="true" />

				<p class="home-newsletter__feedback" data-newsletter-feedback hidden role="status"></p>
			</form>
		</div>
	</div>
</section>
