<?php
/**
 * Comptes professionnels — validation, SIRET, TVA, code ERP.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_ACCOUNT_STATUS_META', 'anrhpub_account_status' );
define( 'ANRHPUB_SIRET_META', 'anrhpub_siret' );
define( 'ANRHPUB_VAT_META', 'anrhpub_vat_number' );
define( 'ANRHPUB_ERP_CODE_META', 'anrhpub_erp_code' );
define( 'ANRHPUB_PAYMENT_TERMS_META', 'anrhpub_payment_terms' );
define( 'ANRHPUB_DISCOUNT_META', 'anrhpub_discount_percent' );

/**
 * Statuts compte client.
 *
 * @return array<string, string>
 */
function anrhpub_account_statuses() {
	return array(
		'pending'  => __( 'En attente de validation', 'anrhpub_theme' ),
		'approved' => __( 'Validé', 'anrhpub_theme' ),
		'rejected' => __( 'Refusé', 'anrhpub_theme' ),
	);
}

/**
 * Compte client approuvé ?
 *
 * @param int $user_id User ID.
 * @return bool
 */
function anrhpub_client_is_approved( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	if ( $user_id <= 0 ) {
		return false;
	}

	if ( user_can( $user_id, 'manage_options' ) ) {
		return true;
	}

	$status = (string) get_user_meta( $user_id, ANRHPUB_ACCOUNT_STATUS_META, true );

	if ( '' === $status ) {
		return false;
	}

	return 'approved' === $status;
}

/**
 * Peut voir les tarifs catalogue ?
 *
 * @return bool
 */
function anrhpub_can_view_prices() {
	return anrhpub_client_is_approved();
}

/**
 * Statut compte.
 *
 * @param int $user_id User ID.
 * @return string
 */
function anrhpub_get_account_status( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();
	$status  = (string) get_user_meta( $user_id, ANRHPUB_ACCOUNT_STATUS_META, true );

	return $status ? $status : 'pending';
}

/**
 * Libellé statut.
 *
 * @param string $status Status slug.
 * @return string
 */
function anrhpub_get_account_status_label( $status ) {
	$all = anrhpub_account_statuses();

	return $all[ $status ] ?? $status;
}

/**
 * Enregistrement meta utilisateur pro.
 */
function anrhpub_register_pro_user_meta() {
	$fields = array(
		ANRHPUB_ACCOUNT_STATUS_META => 'string',
		ANRHPUB_SIRET_META          => 'string',
		ANRHPUB_VAT_META            => 'string',
		ANRHPUB_ERP_CODE_META       => 'string',
		ANRHPUB_PAYMENT_TERMS_META  => 'string',
		ANRHPUB_DISCOUNT_META       => 'number',
	);

	foreach ( $fields as $key => $type ) {
		register_meta(
			'user',
			$key,
			array(
				'type'              => $type,
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => false,
				'auth_callback'     => function () {
					return current_user_can( 'edit_users' );
				},
			)
		);
	}
}
add_action( 'init', 'anrhpub_register_pro_user_meta', 12 );

/**
 * Bloque connexion si compte non validé.
 *
 * @param WP_User|WP_Error|null $user     User.
 * @param string                $username Login.
 * @param string                $password Password.
 * @return WP_User|WP_Error|null
 */
function anrhpub_authenticate_client_approval( $user, $username, $password ) {
	unset( $username, $password );

	if ( ! $user instanceof WP_User ) {
		return $user;
	}

	if ( ! in_array( ANRHPUB_CLIENT_ROLE, (array) $user->roles, true ) ) {
		return $user;
	}

	$status = (string) get_user_meta( $user->ID, ANRHPUB_ACCOUNT_STATUS_META, true );

	if ( 'rejected' === $status ) {
		return new WP_Error(
			'account_rejected',
			__( 'Votre demande d’inscription a été refusée. Contactez ANRH Peyruis.', 'anrhpub_theme' )
		);
	}

	if ( 'pending' === $status ) {
		return new WP_Error(
			'account_pending',
			__( 'Votre compte est en attente de validation par notre équipe. Vous recevrez un e-mail dès qu’il sera activé.', 'anrhpub_theme' )
		);
	}

	return $user;
}
add_filter( 'authenticate', 'anrhpub_authenticate_client_approval', 30, 3 );

/**
 * Champs pro à l’inscription.
 *
 * @param int $user_id User ID.
 */
function anrhpub_save_registration_pro_fields( $user_id ) {
	$user_id = (int) $user_id;

	if ( $user_id <= 0 ) {
		return;
	}

	$siret = isset( $_POST['siret'] ) ? sanitize_text_field( wp_unslash( $_POST['siret'] ) ) : '';
	$vat   = isset( $_POST['vat_number'] ) ? sanitize_text_field( wp_unslash( $_POST['vat_number'] ) ) : '';
	$erp   = isset( $_POST['erp_code'] ) ? sanitize_text_field( wp_unslash( $_POST['erp_code'] ) ) : '';

	update_user_meta( $user_id, ANRHPUB_SIRET_META, $siret );
	update_user_meta( $user_id, ANRHPUB_VAT_META, $vat );
	update_user_meta( $user_id, ANRHPUB_ERP_CODE_META, $erp );
	update_user_meta( $user_id, ANRHPUB_ACCOUNT_STATUS_META, 'pending' );

	$admin_email = anrhpub_get_contact_email();
	$user        = get_userdata( $user_id );

	if ( $user && is_email( $admin_email ) ) {
		wp_mail(
			$admin_email,
			'[' . get_bloginfo( 'name' ) . '] ' . __( 'Nouvelle inscription client à valider', 'anrhpub_theme' ),
			sprintf(
				"%s\n\n%s: %s\n%s: %s\n\n%s",
				__( 'Un compte professionnel attend votre validation dans WordPress → Utilisateurs.', 'anrhpub_theme' ),
				__( 'Nom', 'anrhpub_theme' ),
				$user->display_name,
				__( 'E-mail', 'anrhpub_theme' ),
				$user->user_email,
				admin_url( 'users.php' )
			)
		);
	}

	if ( $user && is_email( $user->user_email ) ) {
		wp_mail(
			$user->user_email,
			'[' . get_bloginfo( 'name' ) . '] ' . __( 'Inscription reçue', 'anrhpub_theme' ),
			__( 'Merci pour votre inscription. Votre compte sera activé sous 48 h ouvrées après validation par notre équipe commerciale.', 'anrhpub_theme' )
		);
	}
}

/**
 * Validation inscription — champs obligatoires.
 */
function anrhpub_validate_registration_pro_fields() {
	if ( empty( $_POST['anrhpub_register'] ) ) {
		return;
	}

	$company = isset( $_POST['company'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['company'] ) ) ) : '';
	$siret   = isset( $_POST['siret'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['siret'] ) ) ) : '';

	if ( '' === $company ) {
		anrhpub_set_account_error( __( 'La raison sociale est obligatoire.', 'anrhpub_theme' ) );
		return;
	}

	if ( '' === $siret ) {
		anrhpub_set_account_error( __( 'Le numéro SIRET est obligatoire.', 'anrhpub_theme' ) );
	}
}
add_action( 'init', 'anrhpub_validate_registration_pro_fields', 19 );

/**
 * Bandeau compte en attente sur le front.
 */
function anrhpub_render_account_status_notice() {
	if ( ! is_user_logged_in() || anrhpub_client_is_approved() ) {
		return;
	}

	$user_id = get_current_user_id();

	if ( ! anrhpub_user_has_client_role( $user_id ) ) {
		return;
	}

	$status = anrhpub_get_account_status( $user_id );
	$class  = 'rejected' === $status ? 'is-error' : 'is-pending';

	echo '<div class="anr-account-status-bar anr-account-status-bar--' . esc_attr( $class ) . '" role="status">';
	echo '<div class="container">';
	echo esc_html( anrhpub_get_account_status_label( $status ) );
	echo ' — ';
	echo esc_html__( 'Les tarifs catalogue et certaines fonctions seront disponibles après validation.', 'anrhpub_theme' );
	echo '</div></div>';
}
add_action( 'wp_body_open', 'anrhpub_render_account_status_notice', 5 );

/**
 * Comptes clients existants sans statut : approuvés une fois (évite régression).
 */
function anrhpub_migrate_legacy_approved_clients() {
	if ( get_option( 'anrhpub_legacy_approved_migrated' ) ) {
		return;
	}

	if ( ! function_exists( 'anrhpub_user_has_client_role' ) ) {
		return;
	}

	$users = get_users(
		array(
			'role__in' => array( ANRHPUB_CLIENT_ROLE ),
			'fields'   => 'ID',
		)
	);

	foreach ( $users as $user_id ) {
		$status = (string) get_user_meta( (int) $user_id, ANRHPUB_ACCOUNT_STATUS_META, true );

		if ( '' === $status ) {
			update_user_meta( (int) $user_id, ANRHPUB_ACCOUNT_STATUS_META, 'approved' );
		}
	}

	update_option( 'anrhpub_legacy_approved_migrated', 1, false );
}
add_action( 'init', 'anrhpub_migrate_legacy_approved_clients', 20 );
