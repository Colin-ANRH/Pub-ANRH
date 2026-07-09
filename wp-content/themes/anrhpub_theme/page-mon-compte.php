<?php
/**
 * Template — Profil client + favoris (onglets).
 *
 * @package anrhpub_theme
 */

get_header();

$client_id      = anrhpub_get_client_user_id();
$user           = get_userdata( $client_id );
$company        = anrhpub_get_client_company( $client_id );
$brand_logo_url = anrhpub_get_client_brand_logo_url( $client_id );
$fav_count      = count( anrhpub_get_user_favorites( $client_id ) );
$order_count    = count( anrhpub_get_client_orders( $client_id ) );
$quote_count    = function_exists( 'anrhpub_get_client_quotes' ) ? count( anrhpub_get_client_quotes( $client_id ) ) : 0;
$credit_balance = anrhpub_get_client_credit_balance( $client_id );
$address_count  = count( anrhpub_get_client_addresses( $client_id ) );
?>
<main id="main-content" class="account-page account-page--profile">
	<?php
	anrhpub_page_hero(
		array(
			'kicker' => __( 'Espace client', 'anrhpub_theme' ),
			'title'  => __( 'Mon compte', 'anrhpub_theme' ),
			'lead'   => __( 'Gérez votre profil, vos commandes, devis et favoris en un seul endroit.', 'anrhpub_theme' ),
		)
	);
	?>

	<section class="section account-section" data-animate>
		<div class="container account-shell" data-account-tabs>
			<header class="account-summary">
				<div class="account-summary__main">
					<?php if ( $brand_logo_url ) : ?>
						<div class="account-summary__logo">
							<img src="<?php echo esc_url( $brand_logo_url ); ?>" alt="" width="56" height="56" loading="lazy" />
						</div>
					<?php endif; ?>
					<div class="account-summary__text">
						<p class="account-summary__greeting">
							<?php
							printf(
								/* translators: %s: display name */
								esc_html__( 'Bonjour, %s', 'anrhpub_theme' ),
								esc_html( $user ? $user->display_name : '' )
							);
							?>
						</p>
						<?php if ( $company ) : ?>
							<p class="account-summary__company"><?php echo esc_html( $company ); ?></p>
						<?php endif; ?>
						<?php if ( $user && $user->user_email ) : ?>
							<p class="account-summary__email"><?php echo esc_html( $user->user_email ); ?></p>
						<?php endif; ?>
					</div>
				</div>
				<div class="account-summary__stats" role="group" aria-label="<?php esc_attr_e( 'Accès rapide', 'anrhpub_theme' ); ?>">
					<button type="button" class="account-summary__stat" data-account-tab-jump="quotes">
						<span class="account-summary__stat-value"><?php echo (int) $quote_count; ?></span>
						<span class="account-summary__stat-label"><?php esc_html_e( 'Devis', 'anrhpub_theme' ); ?></span>
					</button>
					<button type="button" class="account-summary__stat" data-account-tab-jump="orders">
						<span class="account-summary__stat-value"><?php echo (int) $order_count; ?></span>
						<span class="account-summary__stat-label"><?php esc_html_e( 'Commandes', 'anrhpub_theme' ); ?></span>
					</button>
					<button type="button" class="account-summary__stat" data-account-tab-jump="favorites">
						<span class="account-summary__stat-value" data-favorites-count><?php echo (int) $fav_count; ?></span>
						<span class="account-summary__stat-label"><?php esc_html_e( 'Favoris', 'anrhpub_theme' ); ?></span>
					</button>
					<?php if ( $credit_balance > 0 ) : ?>
						<button type="button" class="account-summary__stat account-summary__stat--credit" data-account-tab-jump="credits">
							<span class="account-summary__stat-value"><?php echo esc_html( number_format_i18n( $credit_balance, 0 ) ); ?> €</span>
							<span class="account-summary__stat-label"><?php esc_html_e( 'Avoirs', 'anrhpub_theme' ); ?></span>
						</button>
					<?php endif; ?>
				</div>
				<div class="account-summary__quick">
					<a class="account-tabs__tool-btn account-tabs__tool-btn--primary" href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>" data-quote-cart-link>
						<?php esc_html_e( 'Mon panier', 'anrhpub_theme' ); ?>
					</a>
					<a class="account-tabs__tool-btn account-tabs__tool-btn--outline" href="<?php echo esc_url( function_exists( 'anrhpub_catalogue_url' ) ? anrhpub_catalogue_url() : home_url( '/' ) ); ?>">
						<?php esc_html_e( 'Catalogue', 'anrhpub_theme' ); ?>
					</a>
				</div>
			</header>

			<div class="account-layout">
				<nav class="account-tabs__nav" aria-label="<?php esc_attr_e( 'Sections du compte', 'anrhpub_theme' ); ?>">
					<div class="account-tabs__scroll">
						<div class="account-tabs__list" role="tablist">
					<button
						type="button"
						class="account-tabs__tab is-active"
						role="tab"
						id="tab-profile"
						aria-selected="true"
						aria-controls="panel-profile"
						data-account-tab="profile"
					>
						<?php esc_html_e( 'Profil', 'anrhpub_theme' ); ?>
					</button>
					<button
						type="button"
						class="account-tabs__tab"
						role="tab"
						id="tab-password"
						aria-selected="false"
						aria-controls="panel-password"
						tabindex="-1"
						data-account-tab="password"
					>
						<?php esc_html_e( 'Mot de passe', 'anrhpub_theme' ); ?>
					</button>
					<button
						type="button"
						class="account-tabs__tab"
						role="tab"
						id="tab-quotes"
						aria-selected="false"
						aria-controls="panel-quotes"
						tabindex="-1"
						data-account-tab="quotes"
					>
						<?php esc_html_e( 'Devis', 'anrhpub_theme' ); ?>
						<?php if ( $quote_count > 0 ) : ?>
							<span class="account-tabs__badge"><?php echo (int) $quote_count; ?></span>
						<?php endif; ?>
					</button>
					<button
						type="button"
						class="account-tabs__tab"
						role="tab"
						id="tab-orders"
						aria-selected="false"
						aria-controls="panel-orders"
						tabindex="-1"
						data-account-tab="orders"
					>
						<?php esc_html_e( 'Commandes', 'anrhpub_theme' ); ?>
						<?php if ( $order_count > 0 ) : ?>
							<span class="account-tabs__badge"><?php echo (int) $order_count; ?></span>
						<?php endif; ?>
					</button>
					<button
						type="button"
						class="account-tabs__tab"
						role="tab"
						id="tab-credits"
						aria-selected="false"
						aria-controls="panel-credits"
						tabindex="-1"
						data-account-tab="credits"
					>
						<?php esc_html_e( 'Avoirs', 'anrhpub_theme' ); ?>
						<?php if ( $credit_balance > 0 ) : ?>
							<span class="account-tabs__badge account-tabs__badge--credit"><?php echo esc_html( number_format_i18n( $credit_balance, 0 ) ); ?> €</span>
						<?php endif; ?>
					</button>
					<button
						type="button"
						class="account-tabs__tab"
						role="tab"
						id="tab-addresses"
						aria-selected="false"
						aria-controls="panel-addresses"
						tabindex="-1"
						data-account-tab="addresses"
					>
						<?php esc_html_e( 'Adresses', 'anrhpub_theme' ); ?>
						<?php if ( $address_count > 0 ) : ?>
							<span class="account-tabs__badge"><?php echo (int) $address_count; ?></span>
						<?php endif; ?>
					</button>
					<button
						type="button"
						class="account-tabs__tab"
						role="tab"
						id="tab-favorites"
						aria-selected="false"
						aria-controls="panel-favorites"
						tabindex="-1"
						data-account-tab="favorites"
					>
						<?php esc_html_e( 'Favoris', 'anrhpub_theme' ); ?>
						<span class="account-tabs__badge" data-favorites-count><?php echo (int) $fav_count; ?></span>
					</button>
						</div>
					</div>
					<div class="account-tabs__tools">
						<?php if ( function_exists( 'anrhpub_compare_url' ) ) : ?>
							<a class="account-tabs__tool-btn account-tabs__tool-btn--outline" href="<?php echo esc_url( anrhpub_compare_url() ); ?>">
								<?php esc_html_e( 'Comparateur', 'anrhpub_theme' ); ?>
							</a>
						<?php endif; ?>
						<form method="post" action="<?php echo esc_url( anrhpub_account_url() ); ?>" class="account-tabs__logout-form">
							<?php wp_nonce_field( 'anrhpub_logout' ); ?>
							<button type="submit" name="anrhpub_logout" value="1" class="account-tabs__tool-btn account-tabs__tool-btn--ghost">
								<?php esc_html_e( 'Déconnexion', 'anrhpub_theme' ); ?>
							</button>
						</form>
					</div>
				</nav>

				<div class="account-tabs__panels">
				<section
					id="panel-profile"
					class="account-tab-panel is-active"
					role="tabpanel"
					aria-labelledby="tab-profile"
					data-account-panel="profile"
				>
					<form class="account-form account-form--profile" method="post" action="<?php echo esc_url( anrhpub_account_url() ); ?>" enctype="multipart/form-data">
						<?php wp_nonce_field( 'anrhpub_profile' ); ?>
						<input type="hidden" name="anrhpub_update_profile" value="1" />
						<div class="account-brand-logo">
							<p class="account-brand-logo__label"><?php esc_html_e( 'Logo de votre marque', 'anrhpub_theme' ); ?></p>
							<p class="account-form__hint"><?php esc_html_e( 'Utilisé pour vos demandes de devis et marquage personnalisé.', 'anrhpub_theme' ); ?></p>
							<?php if ( $brand_logo_url ) : ?>
								<div class="account-brand-logo__preview">
									<img src="<?php echo esc_url( $brand_logo_url ); ?>" alt="<?php esc_attr_e( 'Logo marque', 'anrhpub_theme' ); ?>" width="160" height="160" />
								</div>
								<p class="account-form__field account-form__field--checkbox">
									<label>
										<input type="checkbox" name="remove_brand_logo" value="1" />
										<?php esc_html_e( 'Supprimer le logo actuel', 'anrhpub_theme' ); ?>
									</label>
								</p>
							<?php endif; ?>
							<p class="account-form__field account-form__field--file">
								<label for="brand_logo"><?php esc_html_e( 'Téléverser un logo', 'anrhpub_theme' ); ?></label>
								<input type="file" name="brand_logo" id="brand_logo" accept="image/jpeg,image/png,image/webp,image/gif" />
							</p>
						</div>
						<div class="account-form__grid">
							<p class="account-form__field">
								<label for="profile_first_name"><?php esc_html_e( 'Prénom', 'anrhpub_theme' ); ?></label>
								<input type="text" name="first_name" id="profile_first_name" value="<?php echo esc_attr( $user ? $user->first_name : '' ); ?>" autocomplete="given-name" />
							</p>
							<p class="account-form__field">
								<label for="profile_last_name"><?php esc_html_e( 'Nom', 'anrhpub_theme' ); ?></label>
								<input type="text" name="last_name" id="profile_last_name" value="<?php echo esc_attr( $user ? $user->last_name : '' ); ?>" autocomplete="family-name" />
							</p>
						</div>
						<p class="account-form__field">
							<label for="profile_company"><?php esc_html_e( 'Société', 'anrhpub_theme' ); ?></label>
							<input type="text" name="company" id="profile_company" value="<?php echo esc_attr( $company ); ?>" autocomplete="organization" />
						</p>
						<p class="account-form__field">
							<label><?php esc_html_e( 'E-mail', 'anrhpub_theme' ); ?></label>
							<input type="email" value="<?php echo esc_attr( $user ? $user->user_email : '' ); ?>" disabled />
							<span class="account-form__hint"><?php esc_html_e( 'L’e-mail ne peut pas être modifié ici.', 'anrhpub_theme' ); ?></span>
						</p>
						<p class="account-form__actions">
							<button type="submit" name="anrhpub_update_profile" value="1" class="btn btn--primary">
								<?php esc_html_e( 'Enregistrer le profil', 'anrhpub_theme' ); ?>
							</button>
						</p>
					</form>
				</section>

				<section
					id="panel-password"
					class="account-tab-panel"
					role="tabpanel"
					aria-labelledby="tab-password"
					hidden
					data-account-panel="password"
				>
					<?php if ( anrhpub_is_admin_previewing_client() ) : ?>
						<p class="account-alert account-alert--info"><?php esc_html_e( 'En mode test client, modifiez le mot de passe depuis le compte réel ou déconnectez-vous du mode client.', 'anrhpub_theme' ); ?></p>
					<?php else : ?>
					<form class="account-form" method="post" action="<?php echo esc_url( anrhpub_account_url() ); ?>">
						<?php wp_nonce_field( 'anrhpub_password' ); ?>
						<p class="account-form__field">
							<label for="current_password"><?php esc_html_e( 'Mot de passe actuel', 'anrhpub_theme' ); ?></label>
							<input type="password" name="current_password" id="current_password" required autocomplete="current-password" />
						</p>
						<p class="account-form__field">
							<label for="new_password"><?php esc_html_e( 'Nouveau mot de passe', 'anrhpub_theme' ); ?></label>
							<input type="password" name="new_password" id="new_password" required minlength="8" autocomplete="new-password" />
						</p>
						<p class="account-form__field">
							<label for="new_password_confirm"><?php esc_html_e( 'Confirmer le nouveau mot de passe', 'anrhpub_theme' ); ?></label>
							<input type="password" name="new_password_confirm" id="new_password_confirm" required minlength="8" autocomplete="new-password" />
						</p>
						<p class="account-form__actions">
							<button type="submit" name="anrhpub_change_password" value="1" class="btn btn--primary">
								<?php esc_html_e( 'Mettre à jour le mot de passe', 'anrhpub_theme' ); ?>
							</button>
						</p>
					</form>
					<?php endif; ?>
				</section>

				<section
					id="panel-quotes"
					class="account-tab-panel"
					role="tabpanel"
					aria-labelledby="tab-quotes"
					hidden
					data-account-panel="quotes"
				>
					<p class="account-tab-panel__lead"><?php esc_html_e( 'Brouillons, demandes en cours et devis acceptés.', 'anrhpub_theme' ); ?></p>
					<?php get_template_part( 'template-parts/account', 'quotes' ); ?>
				</section>

				<section
					id="panel-orders"
					class="account-tab-panel"
					role="tabpanel"
					aria-labelledby="tab-orders"
					hidden
					data-account-panel="orders"
				>
					<p class="account-tab-panel__lead"><?php esc_html_e( 'Historique de vos commandes ANRH.', 'anrhpub_theme' ); ?></p>
					<?php get_template_part( 'template-parts/account', 'orders' ); ?>
				</section>

				<section
					id="panel-credits"
					class="account-tab-panel"
					role="tabpanel"
					aria-labelledby="tab-credits"
					hidden
					data-account-panel="credits"
				>
					<p class="account-tab-panel__lead"><?php esc_html_e( 'Avoirs émis suite à une annulation ou accord commercial.', 'anrhpub_theme' ); ?></p>
					<?php get_template_part( 'template-parts/account', 'credits' ); ?>
				</section>

				<section
					id="panel-addresses"
					class="account-tab-panel"
					role="tabpanel"
					aria-labelledby="tab-addresses"
					hidden
					data-account-panel="addresses"
				>
					<p class="account-tab-panel__lead"><?php esc_html_e( 'Gérez vos adresses et choisissez celle utilisée pour la livraison.', 'anrhpub_theme' ); ?></p>
					<?php get_template_part( 'template-parts/account', 'addresses' ); ?>
				</section>

				<section
					id="panel-favorites"
					class="account-tab-panel"
					role="tabpanel"
					aria-labelledby="tab-favorites"
					hidden
					data-account-panel="favorites"
				>
					<p class="account-tab-panel__lead"><?php esc_html_e( 'Produits que vous avez ajoutés à votre liste depuis le catalogue.', 'anrhpub_theme' ); ?></p>
					<div id="account-favorites-list" class="account-favorites-list">
						<?php get_template_part( 'template-parts/account', 'favorites' ); ?>
					</div>
				</section>
				</div>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
