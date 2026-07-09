<?php
/**
 * Admin — validation comptes pro.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Colonnes utilisateurs clients.
 *
 * @param array $columns Columns.
 * @return array
 */
function anrhpub_users_columns_pro( $columns ) {
	$columns['anr_account_status'] = __( 'Compte pro', 'anrhpub_theme' );
	$columns['anr_erp_code']       = __( 'Code ERP', 'anrhpub_theme' );

	return $columns;
}
add_filter( 'manage_users_columns', 'anrhpub_users_columns_pro' );

/**
 * Contenu colonnes.
 *
 * @param string $value       Value.
 * @param string $column_name Column.
 * @param int    $user_id     User ID.
 * @return string
 */
function anrhpub_users_column_pro_content( $value, $column_name, $user_id ) {
	$user = get_userdata( $user_id );

	if ( ! $user || ! in_array( ANRHPUB_CLIENT_ROLE, (array) $user->roles, true ) ) {
		return $value;
	}

	if ( 'anr_account_status' === $column_name ) {
		return esc_html( anrhpub_get_account_status_label( anrhpub_get_account_status( $user_id ) ) );
	}

	if ( 'anr_erp_code' === $column_name ) {
		return esc_html( (string) get_user_meta( $user_id, ANRHPUB_ERP_CODE_META, true ) );
	}

	return $value;
}
add_filter( 'manage_users_custom_column', 'anrhpub_users_column_pro_content', 10, 3 );

/**
 * Champs profil utilisateur (admin).
 *
 * @param WP_User $user User.
 */
function anrhpub_user_profile_pro_fields( $user ) {
	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}

	$status  = (string) get_user_meta( $user->ID, ANRHPUB_ACCOUNT_STATUS_META, true );
	$status  = $status ? $status : 'approved';
	$siret   = (string) get_user_meta( $user->ID, ANRHPUB_SIRET_META, true );
	$vat     = (string) get_user_meta( $user->ID, ANRHPUB_VAT_META, true );
	$erp     = (string) get_user_meta( $user->ID, ANRHPUB_ERP_CODE_META, true );
	$terms   = (string) get_user_meta( $user->ID, ANRHPUB_PAYMENT_TERMS_META, true );
	$disc    = (float) get_user_meta( $user->ID, ANRHPUB_DISCOUNT_META, true );
	?>
	<h2><?php esc_html_e( 'Compte professionnel ANRH', 'anrhpub_theme' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="anrhpub_account_status"><?php esc_html_e( 'Validation', 'anrhpub_theme' ); ?></label></th>
			<td>
				<select name="anrhpub_account_status" id="anrhpub_account_status">
					<?php foreach ( anrhpub_account_statuses() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="anrhpub_siret"><?php esc_html_e( 'SIRET', 'anrhpub_theme' ); ?></label></th>
			<td><input type="text" name="anrhpub_siret" id="anrhpub_siret" value="<?php echo esc_attr( $siret ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="anrhpub_vat"><?php esc_html_e( 'TVA intracom.', 'anrhpub_theme' ); ?></label></th>
			<td><input type="text" name="anrhpub_vat" id="anrhpub_vat" value="<?php echo esc_attr( $vat ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="anrhpub_erp_code"><?php esc_html_e( 'Code client ERP', 'anrhpub_theme' ); ?></label></th>
			<td><input type="text" name="anrhpub_erp_code" id="anrhpub_erp_code" value="<?php echo esc_attr( $erp ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="anrhpub_payment_terms"><?php esc_html_e( 'Conditions de paiement', 'anrhpub_theme' ); ?></label></th>
			<td><input type="text" name="anrhpub_payment_terms" id="anrhpub_payment_terms" value="<?php echo esc_attr( $terms ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Ex. 30 jours fin de mois', 'anrhpub_theme' ); ?>" /></td>
		</tr>
		<tr>
			<th><label for="anrhpub_discount"><?php esc_html_e( 'Remise globale (%)', 'anrhpub_theme' ); ?></label></th>
			<td><input type="number" name="anrhpub_discount" id="anrhpub_discount" value="<?php echo esc_attr( (string) $disc ); ?>" min="0" max="100" step="0.01" /></td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'anrhpub_user_profile_pro_fields' );
add_action( 'edit_user_profile', 'anrhpub_user_profile_pro_fields' );

/**
 * Sauvegarde profil pro.
 *
 * @param int $user_id User ID.
 */
function anrhpub_save_user_profile_pro_fields( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	$old_status = (string) get_user_meta( $user_id, ANRHPUB_ACCOUNT_STATUS_META, true );

	if ( isset( $_POST['anrhpub_account_status'] ) ) {
		$new_status = sanitize_key( wp_unslash( $_POST['anrhpub_account_status'] ) );
		if ( isset( anrhpub_account_statuses()[ $new_status ] ) ) {
			update_user_meta( $user_id, ANRHPUB_ACCOUNT_STATUS_META, $new_status );

			if ( $old_status !== $new_status && 'approved' === $new_status ) {
				$user = get_userdata( $user_id );
				if ( $user && is_email( $user->user_email ) ) {
					wp_mail(
						$user->user_email,
						'[' . get_bloginfo( 'name' ) . '] ' . __( 'Compte professionnel activé', 'anrhpub_theme' ),
						__( 'Votre compte est validé. Vous pouvez vous connecter et consulter les tarifs du catalogue.', 'anrhpub_theme' ) . "\n\n" . wp_login_url()
					);
				}
			}
		}
	}

	if ( isset( $_POST['anrhpub_siret'] ) ) {
		update_user_meta( $user_id, ANRHPUB_SIRET_META, sanitize_text_field( wp_unslash( $_POST['anrhpub_siret'] ) ) );
	}
	if ( isset( $_POST['anrhpub_vat'] ) ) {
		update_user_meta( $user_id, ANRHPUB_VAT_META, sanitize_text_field( wp_unslash( $_POST['anrhpub_vat'] ) ) );
	}
	if ( isset( $_POST['anrhpub_erp_code'] ) ) {
		update_user_meta( $user_id, ANRHPUB_ERP_CODE_META, sanitize_text_field( wp_unslash( $_POST['anrhpub_erp_code'] ) ) );
	}
	if ( isset( $_POST['anrhpub_payment_terms'] ) ) {
		update_user_meta( $user_id, ANRHPUB_PAYMENT_TERMS_META, sanitize_text_field( wp_unslash( $_POST['anrhpub_payment_terms'] ) ) );
	}
	if ( isset( $_POST['anrhpub_discount'] ) ) {
		update_user_meta( $user_id, ANRHPUB_DISCOUNT_META, (float) wp_unslash( $_POST['anrhpub_discount'] ) );
	}
}
add_action( 'personal_options_update', 'anrhpub_save_user_profile_pro_fields' );
add_action( 'edit_user_profile_update', 'anrhpub_save_user_profile_pro_fields' );
