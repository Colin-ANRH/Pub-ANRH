<?php
/**
 * 404 template.
 *
 * @package anrhpub_theme
 */

get_header();
?>

<main id="main-content" class="section">
	<div class="container" style="text-align:center;padding:4rem 0;">
		<h1>404</h1>
		<p><?php esc_html_e( 'Page introuvable.', 'anrhpub_theme' ); ?></p>
		<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Retour à l’accueil', 'anrhpub_theme' ); ?></a>
	</div>
</main>

<?php
get_footer();
