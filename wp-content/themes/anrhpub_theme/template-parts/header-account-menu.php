<?php
/**
 * Menu déroulant « Mon compte » — header.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$items        = anrhpub_get_account_nav_items();
$toggle_label = anrhpub_get_account_nav_toggle_label();
$is_preview   = function_exists( 'anrhpub_is_admin_previewing_client' ) && anrhpub_is_admin_previewing_client();
$client_id    = function_exists( 'anrhpub_get_client_user_id' ) ? anrhpub_get_client_user_id() : 0;
$client       = $client_id ? get_userdata( $client_id ) : null;
$company      = $client_id && function_exists( 'anrhpub_get_client_company' ) ? anrhpub_get_client_company( $client_id ) : '';
?>
<div class="account-menu" data-account-menu>
	<button
		type="button"
		class="account-menu__toggle site-header__account"
		aria-expanded="false"
		aria-haspopup="true"
		aria-controls="account-menu-panel"
		id="account-menu-toggle"
	>
		<span class="account-menu__label"><?php echo esc_html( $toggle_label ); ?></span>
		<span class="nav-dropdown-toggle__icon" aria-hidden="true"></span>
	</button>
	<div class="account-menu__panel" id="account-menu-panel" role="menu" aria-labelledby="account-menu-toggle" hidden>
		<?php if ( $client ) : ?>
			<div class="account-menu__head">
				<p class="account-menu__name"><?php echo esc_html( $client->display_name ); ?></p>
				<?php if ( $company ) : ?>
					<p class="account-menu__company"><?php echo esc_html( $company ); ?></p>
				<?php endif; ?>
				<?php if ( $is_preview ) : ?>
					<p class="account-menu__preview"><?php esc_html_e( 'Mode test client', 'anrhpub_theme' ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<ul class="account-menu__list">
			<?php foreach ( $items as $item ) : ?>
				<?php
				$type = isset( $item['type'] ) ? (string) $item['type'] : 'link';
				if ( 'divider' === $type ) :
					?>
					<li class="account-menu__divider" role="separator" aria-hidden="true"></li>
					<?php
					continue;
				endif;

				if ( 'logout' === $type ) :
					?>
					<li class="account-menu__item account-menu__item--logout" role="none">
						<form method="post" action="<?php echo esc_url( anrhpub_account_url() ); ?>" class="account-menu__logout-form">
							<?php wp_nonce_field( 'anrhpub_logout' ); ?>
							<button type="submit" name="anrhpub_logout" value="1" class="account-menu__link account-menu__link--logout" role="menuitem">
								<?php echo esc_html( $item['label'] ?? __( 'Déconnexion', 'anrhpub_theme' ) ); ?>
							</button>
						</form>
					</li>
					<?php
					continue;
				endif;

				$url   = isset( $item['url'] ) ? (string) $item['url'] : '';
				$label = isset( $item['label'] ) ? (string) $item['label'] : '';
				if ( ! $url || ! $label ) {
					continue;
				}

				$link_class = 'account-menu__link';
				if ( ! empty( $item['highlight'] ) ) {
					$link_class .= ' account-menu__link--highlight';
				}
				?>
				<li class="account-menu__item" role="none">
					<a class="<?php echo esc_attr( $link_class ); ?>" href="<?php echo esc_url( $url ); ?>" role="menuitem">
						<?php echo esc_html( $label ); ?>
						<?php if ( ! empty( $item['badge'] ) && 'compare' === $item['badge'] ) : ?>
							<span class="account-menu__badge site-header__compare-badge" data-compare-badge hidden>0</span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
