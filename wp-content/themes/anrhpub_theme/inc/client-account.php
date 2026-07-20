<?php
/**
 * Comptes clients — connexion, profil, favoris produits.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_CLIENT_ROLE', 'anr_client' );
define( 'ANRHPUB_FAVORITES_META', 'anrhpub_favorites' );
define( 'ANRHPUB_COMPANY_META', 'anrhpub_company' );
define( 'ANRHPUB_BRAND_LOGO_META', 'anrhpub_brand_logo_id' );
define( 'ANRHPUB_ACCOUNT_PAGES_VERSION', 1 );

/**
 * Enregistre le rôle client.
 */
function anrhpub_register_client_role() {
	if ( get_role( ANRHPUB_CLIENT_ROLE ) ) {
		return;
	}

	add_role(
		ANRHPUB_CLIENT_ROLE,
		__( 'Client ANRH', 'anrhpub_theme' ),
		array(
			'read' => true,
		)
	);
}

/**
 * Meta utilisateur « société » (profil client).
 */
function anrhpub_register_company_user_meta() {
	register_meta(
		'user',
		ANRHPUB_COMPANY_META,
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function ( $allowed, $meta_key, $user_id ) {
				unset( $allowed, $meta_key );
				return (int) get_current_user_id() === (int) $user_id;
			},
			'show_in_rest'      => false,
		)
	);
}
add_action( 'init', 'anrhpub_register_company_user_meta', 11 );

/**
 * Lit la société du client.
 *
 * @param int $user_id ID utilisateur (0 = courant).
 * @return string
 */
function anrhpub_get_client_company( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();

	if ( $user_id <= 0 ) {
		return '';
	}

	return (string) get_user_meta( $user_id, ANRHPUB_COMPANY_META, true );
}

/**
 * Enregistre la société du client connecté.
 *
 * @param int    $user_id ID utilisateur.
 * @param string $company Nom société.
 * @return bool
 */
function anrhpub_save_client_company( $user_id, $company ) {
	$user_id = (int) $user_id;

	if ( $user_id <= 0 || ! anrhpub_is_client_user( $user_id ) ) {
		return false;
	}

	if ( (int) anrhpub_get_client_user_id() !== $user_id ) {
		return false;
	}

	$company = sanitize_text_field( (string) $company );

	update_user_meta( $user_id, ANRHPUB_COMPANY_META, $company );

	return anrhpub_get_client_company( $user_id ) === $company;
}

/**
 * Pages compte à créer.
 *
 * @return array<string, array{title: string, content: string}>
 */
function anrhpub_account_pages_config() {
	return array(
		'connexion'    => array(
			'title'   => __( 'Connexion', 'anrhpub_theme' ),
			'content' => '',
		),
		'inscription'  => array(
			'title'   => __( 'Inscription', 'anrhpub_theme' ),
			'content' => '',
		),
		'mon-compte'   => array(
			'title'   => __( 'Mon compte', 'anrhpub_theme' ),
			'content' => '',
		),
	);
}

/**
 * Crée les pages compte si besoin.
 */
function anrhpub_ensure_account_pages() {
	foreach ( anrhpub_account_pages_config() as $slug => $page ) {
		if ( get_page_by_path( $slug ) ) {
			continue;
		}

		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => $page['content'],
				'post_status'  => 'publish',
			)
		);
	}

	update_option( 'anrhpub_account_pages_version', ANRHPUB_ACCOUNT_PAGES_VERSION );
}

/**
 * URL connexion.
 *
 * @param string $redirect_to Redirect after login.
 * @return string
 */
function anrhpub_login_url( $redirect_to = '' ) {
	$url = home_url( '/connexion/' );
	if ( $redirect_to ) {
		$url = add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $url );
	}
	return $url;
}

/**
 * URL inscription.
 *
 * @return string
 */
function anrhpub_register_url() {
	return home_url( '/inscription/' );
}

/**
 * URL profil client.
 *
 * @return string
 */
function anrhpub_account_url() {
	return home_url( '/mon-compte/' );
}

/**
 * Libellé du bouton compte dans le header.
 *
 * @return string
 */
function anrhpub_get_account_nav_toggle_label() {
	if ( function_exists( 'anrhpub_is_admin_previewing_client' ) && anrhpub_is_admin_previewing_client() ) {
		return __( 'Mon compte (test)', 'anrhpub_theme' );
	}

	$user_id = function_exists( 'anrhpub_get_client_user_id' ) ? anrhpub_get_client_user_id() : 0;
	$user    = $user_id ? get_userdata( $user_id ) : null;

	if ( $user && $user->display_name ) {
		$name = $user->display_name;
		if ( function_exists( 'mb_strlen' ) && mb_strlen( $name ) > 20 ) {
			$name = mb_substr( $name, 0, 18 ) . '…';
		} elseif ( strlen( $name ) > 20 ) {
			$name = substr( $name, 0, 18 ) . '…';
		}
		return $name;
	}

	return __( 'Mon compte', 'anrhpub_theme' );
}

/**
 * Liens du menu compte (header).
 *
 * @return array<int, array<string, mixed>>
 */
function anrhpub_get_account_nav_items() {
	$base  = anrhpub_account_url();
	$items = array(
		array(
			'label' => __( 'Tableau de bord', 'anrhpub_theme' ),
			'url'   => $base,
		),
		array( 'type' => 'divider' ),
		array(
			'label' => __( 'Profil & société', 'anrhpub_theme' ),
			'url'   => $base . '#panel-profile',
		),
		array(
			'label' => __( 'Mot de passe', 'anrhpub_theme' ),
			'url'   => $base . '#panel-password',
		),
		array(
			'label' => __( 'Mes devis', 'anrhpub_theme' ),
			'url'   => $base . '#panel-quotes',
		),
		array(
			'label' => __( 'Commandes', 'anrhpub_theme' ),
			'url'   => $base . '#panel-orders',
		),
		array(
			'label' => __( 'Avoirs', 'anrhpub_theme' ),
			'url'   => $base . '#panel-credits',
		),
		array(
			'label' => __( 'Adresses de livraison', 'anrhpub_theme' ),
			'url'   => $base . '#panel-addresses',
		),
		array(
			'label' => __( 'Favoris', 'anrhpub_theme' ),
			'url'   => $base . '#panel-favorites',
		),
		array( 'type' => 'divider' ),
	);

	if ( function_exists( 'anrhpub_compare_url' ) ) {
		$items[] = array(
			'label'      => __( 'Comparateur', 'anrhpub_theme' ),
			'url'        => anrhpub_compare_url(),
			'badge'      => 'compare',
			'badge_attr' => 'data-compare-badge',
		);
	}

	$items[] = array(
		'label'     => __( 'Mon panier devis', 'anrhpub_theme' ),
		'url'       => function_exists( 'anrhpub_quote_cart_url' ) ? anrhpub_quote_cart_url() : home_url( '/' ),
		'highlight' => true,
	);
	$items[] = array(
		'label' => __( 'Catalogue produits', 'anrhpub_theme' ),
		'url'   => function_exists( 'anrhpub_catalogue_url' ) ? anrhpub_catalogue_url() : home_url( '/' ),
	);
	$items[] = array( 'type' => 'divider' );
	$items[] = array(
		'type'  => 'logout',
		'label' => ( function_exists( 'anrhpub_is_admin_previewing_client' ) && anrhpub_is_admin_previewing_client() )
			? __( 'Quitter le mode client', 'anrhpub_theme' )
			: __( 'Déconnexion', 'anrhpub_theme' ),
	);

	/**
	 * Filtre les entrées du menu compte header.
	 *
	 * @param array<int, array<string, mixed>> $items Liens.
	 */
	return apply_filters( 'anrhpub_account_nav_items', $items );
}

/**
 * Affiche le menu déroulant compte dans le header.
 */
function anrhpub_render_header_account_menu() {
	if ( ! function_exists( 'anrhpub_is_client_logged_in' ) || ! anrhpub_is_client_logged_in() ) {
		return;
	}

	get_template_part( 'template-parts/header', 'account-menu' );
}

/**
 * Utilisateur connecté avec le rôle client catalogue.
 *
 * @param int $user_id ID utilisateur (0 = session courante).
 * @return bool
 */
function anrhpub_is_client_user( $user_id = 0 ) {
	$user_id = (int) $user_id;

	if ( $user_id > 0 ) {
		return anrhpub_user_has_client_role( $user_id );
	}

	return anrhpub_is_client_logged_in();
}

/**
 * URL du logo marque client.
 *
 * @param int $user_id User ID.
 * @return string
 */
function anrhpub_get_client_brand_logo_url( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();
	$att_id  = (int) get_user_meta( $user_id, ANRHPUB_BRAND_LOGO_META, true );

	if ( $att_id <= 0 ) {
		return '';
	}

	return (string) wp_get_attachment_image_url( $att_id, 'medium' );
}

/**
 * Enregistre le logo marque (upload).
 *
 * @param int $user_id User ID.
 * @return bool|WP_Error
 */
function anrhpub_save_client_brand_logo( $user_id ) {
	if ( empty( $_FILES['brand_logo']['name'] ) ) {
		return true;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$file = $_FILES['brand_logo'];

	if ( ! empty( $file['error'] ) && UPLOAD_ERR_NO_FILE !== (int) $file['error'] ) {
		return new WP_Error( 'upload', __( 'Erreur lors du téléversement du logo.', 'anrhpub_theme' ) );
	}

	$allowed = array( 'image/jpeg', 'image/png', 'image/webp', 'image/gif' );

	if ( ! in_array( $file['type'], $allowed, true ) ) {
		return new WP_Error( 'type', __( 'Format accepté : JPG, PNG, WebP ou GIF.', 'anrhpub_theme' ) );
	}

	$attachment_id = media_handle_upload( 'brand_logo', 0 );

	if ( is_wp_error( $attachment_id ) ) {
		return $attachment_id;
	}

	$old_id = (int) get_user_meta( $user_id, ANRHPUB_BRAND_LOGO_META, true );

	if ( $old_id && $old_id !== $attachment_id ) {
		wp_delete_attachment( $old_id, true );
	}

	update_user_meta( $user_id, ANRHPUB_BRAND_LOGO_META, (int) $attachment_id );

	return true;
}

/**
 * URL déconnexion.
 *
 * @return string
 */
function anrhpub_logout_url() {
	return wp_logout_url( anrhpub_login_url() );
}

/**
 * Page compte (slug).
 *
 * @return bool
 */
function anrhpub_is_account_page() {
	return is_page( array( 'connexion', 'inscription', 'mon-compte' ) );
}

/**
 * IDs favoris de l'utilisateur.
 *
 * @param int $user_id User ID (0 = courant).
 * @return int[]
 */
function anrhpub_get_user_favorites( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : anrhpub_get_client_user_id();
	if ( ! $user_id ) {
		return array();
	}

	$favorites = get_user_meta( $user_id, ANRHPUB_FAVORITES_META, true );

	if ( ! is_array( $favorites ) ) {
		return array();
	}

	return array_values(
		array_unique(
			array_filter( array_map( 'intval', $favorites ) )
		)
	);
}

/**
 * Produit en favori ?
 *
 * @param int $post_id Product ID.
 * @param int $user_id User ID.
 * @return bool
 */
function anrhpub_is_product_favorite( $post_id = 0, $user_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	return in_array( (int) $post_id, anrhpub_get_user_favorites( $user_id ), true );
}

/**
 * Affiche le bouton favori.
 *
 * @param int $post_id Product ID.
 */
function anrhpub_render_favorite_button( $post_id = 0, $variant = '' ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! $post_id || 'anr_product' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( '' === $variant ) {
		$variant = is_singular( 'anr_product' ) ? 'single' : 'card';
	}

	$is_fav    = anrhpub_is_client_logged_in() && anrhpub_is_product_favorite( $post_id );
	$logged_in = anrhpub_is_client_logged_in();

	get_template_part(
		'template-parts/product',
		'favorite',
		array(
			'post_id'   => $post_id,
			'is_fav'    => $is_fav,
			'logged_in' => $logged_in,
			'variant'   => $variant,
		)
	);
}

/**
 * Messages flash compte (transient session via query arg).
 *
 * @return array{type: string, message: string}|null
 */
function anrhpub_get_account_notice() {
	if ( empty( $_GET['account_notice'] ) ) {
		return null;
	}

	$notices = array(
		'login_required' => array(
			'type'    => 'info',
			'message' => __( 'Connectez-vous pour accéder à votre compte et vos favoris.', 'anrhpub_theme' ),
		),
		'favorite_login' => array(
			'type'    => 'info',
			'message' => __( 'Connectez-vous avec votre compte client pour enregistrer des favoris.', 'anrhpub_theme' ),
		),
		'not_client'     => array(
			'type'    => 'info',
			'message' => __( 'Cet espace est réservé aux comptes clients. Créez un compte ou connectez-vous avec vos identifiants client.', 'anrhpub_theme' ),
		),
		'login_ok'       => array(
			'type'    => 'success',
			'message' => __( 'Connexion réussie.', 'anrhpub_theme' ),
		),
		'logout_ok'      => array(
			'type'    => 'success',
			'message' => __( 'Vous êtes déconnecté.', 'anrhpub_theme' ),
		),
		'register_ok'    => array(
			'type'    => 'success',
			'message' => __( 'Compte créé. Bienvenue !', 'anrhpub_theme' ),
		),
		'register_pending' => array(
			'type'    => 'info',
			'message' => __( 'Inscription enregistrée. Votre compte sera activé après validation par notre équipe.', 'anrhpub_theme' ),
		),
		'profile_ok'     => array(
			'type'    => 'success',
			'message' => __( 'Profil mis à jour.', 'anrhpub_theme' ),
		),
		'password_ok'    => array(
			'type'    => 'success',
			'message' => __( 'Mot de passe modifié.', 'anrhpub_theme' ),
		),
		'address_ok'     => array(
			'type'    => 'success',
			'message' => __( 'Adresse enregistrée.', 'anrhpub_theme' ),
		),
		'address_deleted' => array(
			'type'    => 'success',
			'message' => __( 'Adresse supprimée.', 'anrhpub_theme' ),
		),
		'delivery_ok'    => array(
			'type'    => 'success',
			'message' => __( 'Adresse de livraison mise à jour.', 'anrhpub_theme' ),
		),
	);

	$key = sanitize_key( wp_unslash( (string) $_GET['account_notice'] ) );

	return isset( $notices[ $key ] ) ? $notices[ $key ] : null;
}

/**
 * Texte lisible pour les toasts (sans balises HTML WordPress).
 *
 * @param string $message Message brut.
 * @return string
 */
function anrhpub_plain_account_message( $message ) {
	$message = wp_strip_all_tags( (string) $message );
	$message = html_entity_decode( $message, ENT_QUOTES, 'UTF-8' );
	$message = trim( preg_replace( '/\s+/u', ' ', $message ) );

	return (string) preg_replace( '/^Erreur\s*:\s*/iu', '', $message );
}

/**
 * Message d'erreur connexion en français clair.
 *
 * @param WP_Error $error Erreur WordPress.
 * @return string
 */
function anrhpub_format_login_error( $error ) {
	if ( ! $error instanceof WP_Error ) {
		return anrhpub_plain_account_message( (string) $error );
	}

	$code = $error->get_error_code();

	if ( in_array( $code, array( 'invalid_username', 'invalid_email', 'invalidcombo' ), true ) ) {
		return __( 'Identifiant inconnu. Utilisez votre adresse e-mail ou créez un compte.', 'anrhpub_theme' );
	}

	if ( 'incorrect_password' === $code ) {
		return __( 'Mot de passe incorrect.', 'anrhpub_theme' );
	}

	return anrhpub_plain_account_message( $error->get_error_message() );
}

/**
 * Résout e-mail, identifiant ou nom affiché vers le login client.
 *
 * @param string $input Saisie utilisateur.
 * @return string Login WordPress ou saisie d'origine.
 */
function anrhpub_resolve_client_login( $input ) {
	$input = trim( (string) $input );

	if ( '' === $input ) {
		return '';
	}

	if ( is_email( $input ) ) {
		$user = get_user_by( 'email', $input );
		return ( $user && anrhpub_is_client_user( $user->ID ) ) ? $user->user_login : $input;
	}

	$user = get_user_by( 'login', $input );
	if ( $user && anrhpub_is_client_user( $user->ID ) ) {
		return $user->user_login;
	}

	$query = new WP_User_Query(
		array(
			'role'           => ANRHPUB_CLIENT_ROLE,
			'search'         => '*' . $input . '*',
			'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
			'number'         => 2,
			'fields'         => 'all',
		)
	);

	$users = $query->get_results();

	if ( 1 === count( $users ) ) {
		return $users[0]->user_login;
	}

	return $input;
}

/**
 * Enregistre une erreur formulaire (texte seul).
 *
 * @param string $message Message.
 */
function anrhpub_set_account_error( $message ) {
	$GLOBALS['anrhpub_account_error'] = anrhpub_plain_account_message( $message );
}

/**
 * Message toast à afficher (erreur formulaire ou notice URL).
 *
 * @return array{type: string, message: string}|null
 */
function anrhpub_get_client_flash_message() {
	if ( ! empty( $GLOBALS['anrhpub_account_error'] ) ) {
		return array(
			'type'    => 'error',
			'message' => anrhpub_plain_account_message( (string) $GLOBALS['anrhpub_account_error'] ),
		);
	}

	$notice = anrhpub_get_account_notice();

	if ( $notice ) {
		$notice['message'] = anrhpub_plain_account_message( $notice['message'] );
	}

	return $notice;
}

/**
 * Redirection avec notice.
 *
 * @param string $url    URL.
 * @param string $notice Notice key.
 */
function anrhpub_account_redirect( $url, $notice = '' ) {
	if ( $notice ) {
		$url = add_query_arg( 'account_notice', $notice, $url );
	}
	wp_safe_redirect( $url );
	exit;
}

/**
 * Traitement des formulaires compte (POST).
 */
function anrhpub_handle_account_forms() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		return;
	}

	if ( isset( $_POST['anrhpub_logout'] ) ) {
		check_admin_referer( 'anrhpub_logout' );
		anrhpub_client_logout();
		anrhpub_account_redirect( anrhpub_login_url(), 'logout_ok' );
	}

	if ( isset( $_POST['anrhpub_login'] ) ) {
		check_admin_referer( 'anrhpub_login' );

		$login = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
		$login = anrhpub_resolve_client_login( $login );

		$creds = array(
			'user_login'    => $login,
			'user_password' => isset( $_POST['pwd'] ) ? (string) wp_unslash( $_POST['pwd'] ) : '',
			'remember'      => ! empty( $_POST['rememberme'] ),
		);

		$password = $creds['user_password'];
		$result   = anrhpub_client_login( $login, $password, $creds['remember'] );

		if ( is_wp_error( $result ) ) {
			anrhpub_set_account_error( anrhpub_format_login_error( $result ) );
			return;
		}

		$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
		$redirect = $redirect ? $redirect : anrhpub_account_url();

		anrhpub_account_redirect( $redirect, 'login_ok' );
	}

	if ( isset( $_POST['anrhpub_register'] ) ) {
		check_admin_referer( 'anrhpub_register' );

		$email     = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$password  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
		$password2 = isset( $_POST['password_confirm'] ) ? (string) wp_unslash( $_POST['password_confirm'] ) : '';
		$first     = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last      = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$company   = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';

		if ( ! is_email( $email ) ) {
			anrhpub_set_account_error( __( 'Adresse e-mail invalide.', 'anrhpub_theme' ) );
			return;
		}
		if ( strlen( $password ) < 8 ) {
			anrhpub_set_account_error( __( 'Le mot de passe doit contenir au moins 8 caractères.', 'anrhpub_theme' ) );
			return;
		}
		if ( $password !== $password2 ) {
			anrhpub_set_account_error( __( 'Les mots de passe ne correspondent pas.', 'anrhpub_theme' ) );
			return;
		}
		if ( email_exists( $email ) ) {
			anrhpub_set_account_error( __( 'Un compte existe déjà avec cet e-mail.', 'anrhpub_theme' ) );
			return;
		}

		$username = sanitize_user( current( explode( '@', $email ) ), true );
		if ( username_exists( $username ) ) {
			$username = sanitize_user( $username . wp_rand( 100, 999 ), true );
		}

		$user_id = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			anrhpub_set_account_error( anrhpub_plain_account_message( $user_id->get_error_message() ) );
			return;
		}

		$user = new WP_User( $user_id );
		$user->set_role( ANRHPUB_CLIENT_ROLE );

		wp_update_user(
			array(
				'ID'         => $user_id,
				'first_name' => $first,
				'last_name'  => $last,
				'display_name' => trim( $first . ' ' . $last ) ? trim( $first . ' ' . $last ) : $email,
			)
		);

		update_user_meta( $user_id, ANRHPUB_COMPANY_META, sanitize_text_field( (string) $company ) );

		if ( function_exists( 'anrhpub_save_registration_pro_fields' ) ) {
			anrhpub_save_registration_pro_fields( $user_id );
		}

		if ( function_exists( 'anrhpub_get_account_status' ) && 'approved' === anrhpub_get_account_status( $user_id ) ) {
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id, true );
			if ( function_exists( 'anrhpub_set_client_session_cookie' ) ) {
				anrhpub_set_client_session_cookie( $user_id, true );
			}
			anrhpub_account_redirect( anrhpub_account_url(), 'register_ok' );
		}

		anrhpub_account_redirect( anrhpub_login_url(), 'register_pending' );
	}

	if ( isset( $_POST['anrhpub_update_profile'] ) ) {
		if ( ! anrhpub_is_client_logged_in() ) {
			anrhpub_set_account_error( __( 'Session expirée. Reconnectez-vous.', 'anrhpub_theme' ) );
			return;
		}

		check_admin_referer( 'anrhpub_profile' );

		$user_id = anrhpub_get_client_user_id();
		$first   = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
		$last    = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
		$company = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';

		$updated = wp_update_user(
			array(
				'ID'           => $user_id,
				'first_name'   => $first,
				'last_name'    => $last,
				'display_name' => trim( $first . ' ' . $last ) ? trim( $first . ' ' . $last ) : wp_get_current_user()->user_email,
			)
		);

		if ( is_wp_error( $updated ) ) {
			anrhpub_set_account_error( anrhpub_plain_account_message( $updated->get_error_message() ) );
			return;
		}

		if ( ! anrhpub_save_client_company( $user_id, $company ) ) {
			anrhpub_set_account_error( __( 'Impossible d’enregistrer la société. Réessayez.', 'anrhpub_theme' ) );
			return;
		}

		$logo_result = anrhpub_save_client_brand_logo( $user_id );

		if ( is_wp_error( $logo_result ) ) {
			anrhpub_set_account_error( $logo_result->get_error_message() );
			return;
		}

		if ( ! empty( $_POST['remove_brand_logo'] ) ) {
			$old_id = (int) get_user_meta( $user_id, ANRHPUB_BRAND_LOGO_META, true );
			if ( $old_id ) {
				wp_delete_attachment( $old_id, true );
			}
			delete_user_meta( $user_id, ANRHPUB_BRAND_LOGO_META );
		}

		anrhpub_account_redirect( anrhpub_account_url() . '#panel-profile', 'profile_ok' );
	}

	if ( isset( $_POST['anrhpub_change_password'] ) && anrhpub_is_client_logged_in() && ! anrhpub_is_admin_previewing_client() ) {
		check_admin_referer( 'anrhpub_password' );

		$user         = wp_get_current_user();
		$current_pass = isset( $_POST['current_password'] ) ? (string) wp_unslash( $_POST['current_password'] ) : '';
		$new_pass     = isset( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : '';
		$new_pass2    = isset( $_POST['new_password_confirm'] ) ? (string) wp_unslash( $_POST['new_password_confirm'] ) : '';

		if ( ! wp_check_password( $current_pass, $user->user_pass, $user->ID ) ) {
			anrhpub_set_account_error( __( 'Mot de passe actuel incorrect.', 'anrhpub_theme' ) );
			return;
		}
		if ( strlen( $new_pass ) < 8 ) {
			anrhpub_set_account_error( __( 'Le nouveau mot de passe doit contenir au moins 8 caractères.', 'anrhpub_theme' ) );
			return;
		}
		if ( $new_pass !== $new_pass2 ) {
			anrhpub_set_account_error( __( 'Les nouveaux mots de passe ne correspondent pas.', 'anrhpub_theme' ) );
			return;
		}

		wp_set_password( $new_pass, $user->ID );
		wp_set_auth_cookie( $user->ID, true );
		if ( function_exists( 'anrhpub_set_client_session_cookie' ) ) {
			anrhpub_set_client_session_cookie( $user->ID, true );
		}

		anrhpub_account_redirect( anrhpub_account_url() . '#panel-password', 'password_ok' );
	}
}
add_action( 'init', 'anrhpub_handle_account_forms', 20 );

/**
 * Garde d'accès aux pages compte.
 */
function anrhpub_account_access_guard() {
	if ( is_page( 'mon-compte' ) && ! anrhpub_is_client_logged_in() ) {
		anrhpub_account_redirect(
			anrhpub_login_url( anrhpub_account_url() ),
			'login_required'
		);
	}

	if ( is_page( 'mon-compte' ) && is_user_logged_in() && ! anrhpub_is_client_logged_in() && ! current_user_can( 'manage_options' ) ) {
		anrhpub_account_redirect( anrhpub_login_url(), 'not_client' );
	}

	if ( anrhpub_is_client_logged_in() && is_page( array( 'connexion', 'inscription' ) ) ) {
		wp_safe_redirect( anrhpub_account_url() );
		exit;
	}
}
add_action( 'template_redirect', 'anrhpub_account_access_guard', 8 );

/**
 * Bloque wp-admin pour les comptes clients purs (pas les administrateurs WP).
 */
function anrhpub_block_client_admin() {
	if ( ! is_user_logged_in() || wp_doing_ajax() ) {
		return;
	}

	if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_others_posts' ) ) {
		return;
	}

	$user = wp_get_current_user();
	if ( in_array( ANRHPUB_CLIENT_ROLE, (array) $user->roles, true ) ) {
		wp_safe_redirect( anrhpub_account_url() );
		exit;
	}
}
add_action( 'admin_init', 'anrhpub_block_client_admin' );

/**
 * Après connexion wp-login : admin → tableau de bord, client → Mon compte.
 *
 * @param string           $redirect_to           Redirect.
 * @param string           $requested_redirect_to Requested.
 * @param WP_User|WP_Error $user                  User.
 * @return string
 */
function anrhpub_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
	if ( ! $user instanceof WP_User ) {
		return $redirect_to;
	}

	if ( user_can( $user, 'manage_options' ) ) {
		if ( $requested_redirect_to && strpos( $requested_redirect_to, admin_url() ) === 0 ) {
			return $requested_redirect_to;
		}
		return admin_url();
	}

	if ( in_array( ANRHPUB_CLIENT_ROLE, (array) $user->roles, true ) ) {
		return anrhpub_account_url();
	}

	return $redirect_to;
}
add_filter( 'login_redirect', 'anrhpub_login_redirect', 10, 3 );

/**
 * AJAX — bascule favori produit.
 */
function anrhpub_ajax_toggle_favorite() {
	check_ajax_referer( 'anrhpub_favorites', 'nonce' );

	if ( ! anrhpub_is_client_logged_in() ) {
		wp_send_json_error(
			array(
				'message'   => __( 'Connectez-vous pour enregistrer des favoris.', 'anrhpub_theme' ),
				'login_url' => anrhpub_login_url(),
			),
			401
		);
	}

	$post_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

	if ( ! $post_id || 'anr_product' !== get_post_type( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Produit invalide.', 'anrhpub_theme' ) ), 400 );
	}

	$favorites = anrhpub_get_user_favorites();
	$index     = array_search( $post_id, $favorites, true );
	$active    = false;

	if ( false !== $index ) {
		unset( $favorites[ $index ] );
		$favorites = array_values( $favorites );
	} else {
		$favorites[] = $post_id;
		$active      = true;
	}

	update_user_meta( anrhpub_get_client_user_id(), ANRHPUB_FAVORITES_META, $favorites );

	wp_send_json_success(
		array(
			'active'  => $active,
			'count'   => count( $favorites ),
			'message' => $active
				? __( 'Produit ajouté à vos favoris.', 'anrhpub_theme' )
				: __( 'Produit retiré de vos favoris.', 'anrhpub_theme' ),
			'label'   => $active
				? __( 'Retirer des favoris', 'anrhpub_theme' )
				: __( 'Ajouter aux favoris', 'anrhpub_theme' ),
			'text'    => $active
				? __( 'Dans mes favoris', 'anrhpub_theme' )
				: __( 'Favoris', 'anrhpub_theme' ),
		)
	);
}
add_action( 'wp_ajax_anrhpub_toggle_favorite', 'anrhpub_ajax_toggle_favorite' );

/**
 * AJAX nopriv — favoris réservés aux clients connectés.
 */
function anrhpub_ajax_toggle_favorite_guest() {
	wp_send_json_error(
		array(
			'message'   => __( 'Connectez-vous pour enregistrer des favoris.', 'anrhpub_theme' ),
			'login_url' => anrhpub_login_url(),
		),
		401
	);
}
add_action( 'wp_ajax_nopriv_anrhpub_toggle_favorite', 'anrhpub_ajax_toggle_favorite_guest' );

/**
 * AJAX — liste favoris (HTML grille) pour rafraîchissement profil.
 */
function anrhpub_ajax_get_favorites_html() {
	check_ajax_referer( 'anrhpub_favorites', 'nonce' );

	if ( ! anrhpub_is_client_logged_in() ) {
		wp_send_json_error( null, 401 );
	}

	ob_start();
	get_template_part( 'template-parts/account', 'favorites' );
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_anrhpub_get_favorites_html', 'anrhpub_ajax_get_favorites_html' );

/**
 * Pages nécessitant account.css (formulaires compte + contact).
 *
 * @return bool
 */
function anrhpub_needs_account_css() {
	if ( is_page( array( 'mon-compte', 'connexion', 'inscription', 'contact', 'panier-devis' ) ) ) {
		return true;
	}

	return function_exists( 'anrhpub_is_account_page' ) && anrhpub_is_account_page();
}

/**
 * Scripts compte + favoris.
 */
function anrhpub_enqueue_account_assets() {
	$flash = anrhpub_get_client_flash_message();

	wp_localize_script(
		'anrhpub-main',
		'anrhpubAccount',
		array(
			'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
			'nonce'      => wp_create_nonce( 'anrhpub_favorites' ),
			'isLoggedIn' => anrhpub_is_client_logged_in(),
			'loginUrl'   => anrhpub_login_url(),
			'flash'      => $flash ? $flash : null,
			'toastMs'    => 4800,
			'i18n'       => array(
				'add'    => __( 'Ajouter aux favoris', 'anrhpub_theme' ),
				'remove' => __( 'Retirer des favoris', 'anrhpub_theme' ),
				'error'  => __( 'Action impossible. Réessayez.', 'anrhpub_theme' ),
				'close'  => __( 'Fermer', 'anrhpub_theme' ),
			),
		)
	);

	if ( ! anrhpub_needs_account_css() ) {
		return;
	}

	$account_deps = array( 'anrhpub-charte' );
	if ( wp_style_is( 'anrhpub-pages', 'enqueued' ) ) {
		$account_deps[] = 'anrhpub-pages';
	}

	wp_enqueue_style(
		'anrhpub-account',
		ANRHPUB_THEME_URI . '/assets/css/account.css',
		$account_deps,
		ANRHPUB_THEME_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_account_assets', 35 );

/**
 * Initialisation compte (rôle + pages).
 */
function anrhpub_init_client_account() {
	anrhpub_register_client_role();

	if ( (int) get_option( 'anrhpub_account_pages_version', 0 ) < ANRHPUB_ACCOUNT_PAGES_VERSION ) {
		anrhpub_ensure_account_pages();
	}
}
add_action( 'init', 'anrhpub_init_client_account', 12 );

/**
 * À l'activation du thème.
 */
function anrhpub_setup_client_account() {
	anrhpub_register_client_role();
	anrhpub_ensure_account_pages();
}
add_action( 'after_switch_theme', 'anrhpub_setup_client_account', 18 );
