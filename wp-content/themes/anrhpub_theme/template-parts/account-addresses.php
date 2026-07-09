<?php
/**
 * Adresses — espace client.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$user_id       = anrhpub_get_client_user_id();
$addresses     = anrhpub_get_client_addresses( $user_id );
$delivery_id   = anrhpub_get_delivery_address_id( $user_id );
$edit_id       = isset( $_GET['edit_address'] ) ? sanitize_key( wp_unslash( $_GET['edit_address'] ) ) : '';
$edit_address  = $edit_id ? anrhpub_get_client_address_by_id( $edit_id, $user_id ) : null;
$show_form     = isset( $_GET['add_address'] ) || $edit_address;
$account_base  = anrhpub_account_url();
?>
<div class="account-addresses-layout">
	<div class="account-addresses-list">
		<?php if ( empty( $addresses ) && ! $show_form ) : ?>
			<p class="account-empty"><?php esc_html_e( 'Aucune adresse enregistrée. Ajoutez une adresse pour vos livraisons.', 'anrhpub_theme' ); ?></p>
		<?php endif; ?>

		<?php if ( ! $show_form ) : ?>
			<p class="account-addresses-add">
				<a class="btn btn--outline" href="<?php echo esc_url( add_query_arg( 'add_address', '1', $account_base ) ); ?>#panel-addresses">
					<?php esc_html_e( 'Ajouter une adresse', 'anrhpub_theme' ); ?>
				</a>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $addresses ) ) : ?>
				<form method="post" action="<?php echo esc_url( anrhpub_account_url() ); ?>" class="account-delivery-form">
					<?php wp_nonce_field( 'anrhpub_delivery_address' ); ?>
					<input type="hidden" name="anrhpub_set_delivery_address" value="1" />
					<p class="account-delivery-form__label"><?php esc_html_e( 'Adresse de livraison par défaut', 'anrhpub_theme' ); ?></p>
					<ul class="account-address-cards">
						<?php foreach ( $addresses as $address ) : ?>
							<li class="account-address-card<?php echo $delivery_id === $address['id'] ? ' is-delivery' : ''; ?>">
								<label class="account-address-card__select">
									<input
										type="radio"
										name="delivery_address_id"
										value="<?php echo esc_attr( $address['id'] ); ?>"
										<?php checked( $delivery_id, $address['id'] ); ?>
									/>
									<span class="account-address-card__body">
										<?php if ( $address['label'] ) : ?>
											<strong><?php echo esc_html( $address['label'] ); ?></strong>
										<?php endif; ?>
										<span class="account-address-card__text"><?php echo esc_html( anrhpub_format_address_text( $address ) ); ?></span>
										<?php if ( $delivery_id === $address['id'] ) : ?>
											<span class="account-address-card__badge"><?php esc_html_e( 'Livraison', 'anrhpub_theme' ); ?></span>
										<?php endif; ?>
									</span>
								</label>
								<div class="account-address-card__actions">
									<a class="account-address-card__edit" href="<?php echo esc_url( add_query_arg( 'edit_address', $address['id'], anrhpub_account_url() ) ); ?>#panel-addresses">
										<?php esc_html_e( 'Modifier', 'anrhpub_theme' ); ?>
									</a>
									<form method="post" action="<?php echo esc_url( anrhpub_account_url() ); ?>" class="account-address-card__delete" onsubmit="return confirm('<?php echo esc_js( __( 'Supprimer cette adresse ?', 'anrhpub_theme' ) ); ?>');">
										<?php wp_nonce_field( 'anrhpub_delete_address' ); ?>
										<input type="hidden" name="address_id" value="<?php echo esc_attr( $address['id'] ); ?>" />
										<button type="submit" name="anrhpub_delete_address" value="1" class="account-link-btn"><?php esc_html_e( 'Supprimer', 'anrhpub_theme' ); ?></button>
									</form>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
					<p class="account-form__actions">
						<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Enregistrer l’adresse de livraison', 'anrhpub_theme' ); ?></button>
					</p>
				</form>
		<?php endif; ?>
	</div>

	<?php if ( $show_form ) : ?>
		<div class="account-address-form-wrap">
			<h3 class="account-address-form__title">
				<?php echo $edit_address ? esc_html__( 'Modifier l’adresse', 'anrhpub_theme' ) : esc_html__( 'Nouvelle adresse', 'anrhpub_theme' ); ?>
			</h3>
			<form class="account-form" method="post" action="<?php echo esc_url( anrhpub_account_url() ); ?>">
				<?php wp_nonce_field( 'anrhpub_address' ); ?>
				<input type="hidden" name="anrhpub_save_address" value="1" />
				<?php if ( $edit_address ) : ?>
					<input type="hidden" name="address_id" value="<?php echo esc_attr( $edit_address['id'] ); ?>" />
				<?php endif; ?>
				<p class="account-form__field">
					<label for="address_label"><?php esc_html_e( 'Libellé (ex. Siège, Entrepôt)', 'anrhpub_theme' ); ?></label>
					<input type="text" name="address_label" id="address_label" value="<?php echo esc_attr( $edit_address ? $edit_address['label'] : '' ); ?>" />
				</p>
				<div class="account-form__grid">
					<p class="account-form__field">
						<label for="address_first_name"><?php esc_html_e( 'Prénom', 'anrhpub_theme' ); ?></label>
						<input type="text" name="address_first_name" id="address_first_name" value="<?php echo esc_attr( $edit_address ? $edit_address['first_name'] : '' ); ?>" autocomplete="given-name" />
					</p>
					<p class="account-form__field">
						<label for="address_last_name"><?php esc_html_e( 'Nom', 'anrhpub_theme' ); ?></label>
						<input type="text" name="address_last_name" id="address_last_name" value="<?php echo esc_attr( $edit_address ? $edit_address['last_name'] : '' ); ?>" autocomplete="family-name" />
					</p>
				</div>
				<p class="account-form__field">
					<label for="address_company"><?php esc_html_e( 'Société', 'anrhpub_theme' ); ?></label>
					<input type="text" name="address_company" id="address_company" value="<?php echo esc_attr( $edit_address ? $edit_address['company'] : '' ); ?>" autocomplete="organization" />
				</p>
				<p class="account-form__field">
					<label for="address_1"><?php esc_html_e( 'Adresse', 'anrhpub_theme' ); ?> <span class="required">*</span></label>
					<input type="text" name="address_1" id="address_1" value="<?php echo esc_attr( $edit_address ? $edit_address['address_1'] : '' ); ?>" required autocomplete="street-address" />
				</p>
				<p class="account-form__field">
					<label for="address_2"><?php esc_html_e( 'Complément d’adresse', 'anrhpub_theme' ); ?></label>
					<input type="text" name="address_2" id="address_2" value="<?php echo esc_attr( $edit_address ? $edit_address['address_2'] : '' ); ?>" />
				</p>
				<div class="account-form__grid">
					<p class="account-form__field">
						<label for="address_postcode"><?php esc_html_e( 'Code postal', 'anrhpub_theme' ); ?></label>
						<input type="text" name="address_postcode" id="address_postcode" value="<?php echo esc_attr( $edit_address ? $edit_address['postcode'] : '' ); ?>" autocomplete="postal-code" />
					</p>
					<p class="account-form__field">
						<label for="address_city"><?php esc_html_e( 'Ville', 'anrhpub_theme' ); ?> <span class="required">*</span></label>
						<input type="text" name="address_city" id="address_city" value="<?php echo esc_attr( $edit_address ? $edit_address['city'] : '' ); ?>" required autocomplete="address-level2" />
					</p>
				</div>
				<p class="account-form__field">
					<label for="address_country"><?php esc_html_e( 'Pays', 'anrhpub_theme' ); ?></label>
					<input type="text" name="address_country" id="address_country" value="<?php echo esc_attr( $edit_address ? $edit_address['country'] : 'France' ); ?>" autocomplete="country-name" />
				</p>
				<p class="account-form__field account-form__field--phone">
					<label for="address_phone"><?php esc_html_e( 'Téléphone', 'anrhpub_theme' ); ?></label>
					<input type="tel" name="address_phone" id="address_phone" value="<?php echo esc_attr( $edit_address ? $edit_address['phone'] : '' ); ?>" autocomplete="tel" inputmode="tel" placeholder="06 12 34 56 78" />
				</p>
				<p class="account-form__field account-form__field--checkbox">
					<label>
						<input type="checkbox" name="set_as_delivery" value="1" <?php checked( ! $edit_address || $delivery_id === ( $edit_address['id'] ?? '' ) ); ?> />
						<?php esc_html_e( 'Utiliser comme adresse de livraison', 'anrhpub_theme' ); ?>
					</label>
				</p>
				<p class="account-form__actions">
					<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Enregistrer l’adresse', 'anrhpub_theme' ); ?></button>
					<a class="btn btn--outline" href="<?php echo esc_url( $account_base ); ?>#panel-addresses"><?php esc_html_e( 'Annuler', 'anrhpub_theme' ); ?></a>
				</p>
			</form>
		</div>
	<?php endif; ?>
</div>
