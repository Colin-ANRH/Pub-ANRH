<?php
/**
 * Header — deux barres sticky (outils en haut, onglets en bas).
 *
 * @package anrhpub_theme
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style id="page-loader-critical">
		html.is-page-loading{overflow:hidden}
		.page-loader{position:fixed;inset:0;z-index:99990;display:flex;align-items:center;justify-content:center}
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php get_template_part( 'template-parts/page', 'loader' ); ?>
<script>document.documentElement.classList.add('is-page-loading');</script>

<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Aller au contenu', 'anrhpub_theme' ); ?></a>

<header class="site-header" id="site-header">
	<div class="container site-header__shell">
		<div class="site-header__bar site-header__bar--tools">
			<div class="site-header__brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="custom-logo-link" rel="home">
						<img src="<?php echo esc_url( anrhpub_theme_image_uri( 'assets/images/logo-anr' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="custom-logo" width="200" height="auto" />
					</a>
				<?php endif; ?>
			</div>

			<div class="site-header__tools">
				<?php
				if ( function_exists( 'anrhpub_render_header_product_search' ) ) {
					anrhpub_render_header_product_search();
				}
				?>
				<div class="site-header__actions">
					<?php if ( function_exists( 'anrhpub_is_client_logged_in' ) && anrhpub_is_client_logged_in() ) : ?>
						<?php anrhpub_render_header_account_menu(); ?>
					<?php elseif ( function_exists( 'anrhpub_is_wp_admin_user' ) && anrhpub_is_wp_admin_user() ) : ?>
						<a class="site-header__account site-header__account--ghost" href="<?php echo esc_url( anrhpub_login_url() ); ?>">
							<?php esc_html_e( 'Espace client', 'anrhpub_theme' ); ?>
						</a>
					<?php else : ?>
						<a class="site-header__account" href="<?php echo esc_url( anrhpub_login_url() ); ?>">
							<?php esc_html_e( 'Connexion', 'anrhpub_theme' ); ?>
						</a>
					<?php endif; ?>
					<a class="site-header__cta btn btn--primary" href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>" data-quote-cart-link>
						<?php esc_html_e( 'Mon panier', 'anrhpub_theme' ); ?>
						<span class="site-header__cart-count" data-quote-cart-count hidden>0</span>
					</a>
					<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" id="nav-toggle">
						<span class="nav-toggle__bar"></span>
						<span class="nav-toggle__bar"></span>
						<span class="nav-toggle__bar"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'anrhpub_theme' ); ?></span>
					</button>
				</div>
			</div>
		</div>

		<div class="site-header__bar site-header__bar--nav">
			<nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'anrhpub_theme' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'site-nav__list',
						'fallback_cb'    => 'anrhpub_fallback_menu',
						'depth'          => 3,
						'walker'         => new Anrhpub_Nav_Walker(),
					)
				);
				?>
			</nav>
		</div>
	</div>
	<div class="nav-backdrop" id="nav-backdrop" hidden aria-hidden="true"></div>
</header>

<?php anrhpub_render_breadcrumbs(); ?>
