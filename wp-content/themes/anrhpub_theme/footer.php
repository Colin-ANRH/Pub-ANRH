<?php
/**
 * Footer épuré.
 *
 * @package anrhpub_theme
 */
?>
<footer class="site-footer">
	<div class="container site-footer__inner">
		<p class="site-footer__copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?> — Peyruis</p>
		<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Liens pied de page', 'anrhpub_theme' ); ?>">
			<a href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>"><?php esc_html_e( 'Catalogue', 'anrhpub_theme' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/societe/' ) ); ?>"><?php esc_html_e( 'Notre activité', 'anrhpub_theme' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/marquage/' ) ); ?>"><?php esc_html_e( 'Marquage', 'anrhpub_theme' ); ?></a>
			<a href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>"><?php esc_html_e( 'Panier', 'anrhpub_theme' ); ?></a>
			<a href="<?php echo esc_url( anrhpub_terms_url() ); ?>"><?php esc_html_e( 'Conditions d’utilisation', 'anrhpub_theme' ); ?></a>
			<?php if ( function_exists( 'anrhpub_privacy_url' ) ) : ?>
				<a href="<?php echo esc_url( anrhpub_privacy_url() ); ?>"><?php esc_html_e( 'Confidentialité', 'anrhpub_theme' ); ?></a>
			<?php endif; ?>
		</nav>
	</div>
</footer>

<div id="anrhpub-toast-root" class="anrhpub-toast-root" aria-live="polite" aria-relevant="additions" aria-atomic="true"></div>

<?php wp_footer(); ?>
</body>
</html>
