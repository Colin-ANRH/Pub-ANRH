<?php
/**
 * Session client front — sans déconnecter l’administrateur WordPress.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_CLIENT_SESSION_COOKIE', 'anrhpub_client_session' );
define( 'ANRHPUB_CLIENT_SESSION_DAYS', 14 );
define( 'ANRHPUB_CLIENT_SESSION_REMEMBER_DAYS', 30 );

/**
 * Chemin cookie (sous-répertoire /ANRPUB/, etc.).
 *
 * @return string
 */
function anrhpub_get_cookie_path() {
	if ( defined( 'COOKIEPATH' ) && COOKIEPATH ) {
		return COOKIEPATH;
	}

	$path = wp_parse_url( home_url( '/' ), PHP_URL_PATH );

	return $path ? trailingslashit( $path ) : '/';
}

/**
 * Domaine cookie.
 *
 * @return string
 */
function anrhpub_get_cookie_domain() {
	return defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
}

/**
 * Durée session client (jours).
 *
 * @param bool $remember Se souvenir.
 * @return int
 */
function anrhpub_client_session_days( $remember = false ) {
	return $remember ? ANRHPUB_CLIENT_SESSION_REMEMBER_DAYS : ANRHPUB_CLIENT_SESSION_DAYS;
}

/**
 * Utilisateur a le rôle client ?
 *
 * @param int $user_id User ID.
 * @return bool
 */
function anrhpub_user_has_client_role( $user_id ) {
	$user_id = (int) $user_id;

	if ( $user_id <= 0 ) {
		return false;
	}

	$user = get_userdata( $user_id );

	return $user instanceof WP_User && in_array( ANRHPUB_CLIENT_ROLE, (array) $user->roles, true );
}

/**
 * Signature cookie session.
 *
 * @param int $user_id User ID.
 * @param int $expires Timestamp.
 * @return string
 */
function anrhpub_client_session_sign( $user_id, $expires ) {
	return hash_hmac( 'sha256', $user_id . '|' . $expires, wp_salt( 'auth' ) );
}

/**
 * Pose le cookie session client.
 *
 * @param int  $user_id  User ID.
 * @param bool $remember Prolonger la durée (« Se souvenir »).
 */
function anrhpub_set_client_session_cookie( $user_id, $remember = false ) {
	$user_id = (int) $user_id;

	if ( ! anrhpub_user_has_client_role( $user_id ) ) {
		return;
	}

	$expires = time() + ( DAY_IN_SECONDS * anrhpub_client_session_days( $remember ) );
	$token   = $user_id . '|' . $expires . '|' . anrhpub_client_session_sign( $user_id, $expires );

	setcookie(
		ANRHPUB_CLIENT_SESSION_COOKIE,
		$token,
		array(
			'expires'  => $expires,
			'path'     => anrhpub_get_cookie_path(),
			'domain'   => anrhpub_get_cookie_domain(),
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	$_COOKIE[ ANRHPUB_CLIENT_SESSION_COOKIE ] = $token;
}

/**
 * Supprime le cookie session client.
 */
function anrhpub_clear_client_session_cookie() {
	setcookie(
		ANRHPUB_CLIENT_SESSION_COOKIE,
		'',
		array(
			'expires'  => time() - YEAR_IN_SECONDS,
			'path'     => anrhpub_get_cookie_path(),
			'domain'   => anrhpub_get_cookie_domain(),
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	unset( $_COOKIE[ ANRHPUB_CLIENT_SESSION_COOKIE ] );
}

/**
 * ID client depuis le cookie session.
 *
 * @return int
 */
function anrhpub_get_client_session_user_id() {
	if ( empty( $_COOKIE[ ANRHPUB_CLIENT_SESSION_COOKIE ] ) ) {
		return 0;
	}

	$parts = explode( '|', (string) wp_unslash( $_COOKIE[ ANRHPUB_CLIENT_SESSION_COOKIE ] ), 3 );

	if ( 3 !== count( $parts ) ) {
		return 0;
	}

	$user_id = (int) $parts[0];
	$expires = (int) $parts[1];
	$sign    = (string) $parts[2];

	if ( $user_id <= 0 || $expires < time() ) {
		return 0;
	}

	if ( ! hash_equals( anrhpub_client_session_sign( $user_id, $expires ), $sign ) ) {
		return 0;
	}

	if ( ! anrhpub_user_has_client_role( $user_id ) ) {
		return 0;
	}

	return $user_id;
}

/**
 * ID du client « actif » sur le site (session dédiée ou connexion client WP).
 *
 * Les administrateurs WordPress ne sont jamais considérés comme clients,
 * sauf en mode test explicite via le formulaire de connexion client (cookie session).
 *
 * @return int
 */
function anrhpub_get_client_user_id() {
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return anrhpub_get_client_session_user_id();
	}

	$session_id = anrhpub_get_client_session_user_id();

	if ( $session_id > 0 ) {
		return $session_id;
	}

	if ( is_user_logged_in() ) {
		$wp_id = (int) get_current_user_id();

		if ( $wp_id > 0 && anrhpub_user_has_client_role( $wp_id ) && ! user_can( $wp_id, 'manage_options' ) ) {
			return $wp_id;
		}
	}

	return 0;
}

/**
 * Client connecté sur le front ?
 *
 * @return bool
 */
function anrhpub_is_client_logged_in() {
	return anrhpub_get_client_user_id() > 0;
}

/**
 * Admin WP connecté + prévisualisation compte client ?
 *
 * @return bool
 */
function anrhpub_is_admin_previewing_client() {
	return is_user_logged_in()
		&& current_user_can( 'manage_options' )
		&& anrhpub_get_client_session_user_id() > 0;
}

/**
 * Connexion client (préserve la session admin si présente).
 *
 * @param string $login    Login ou e-mail.
 * @param string $password Mot de passe.
 * @param bool   $remember Se souvenir.
 * @return int|WP_Error User ID ou erreur.
 */
function anrhpub_client_login( $login, $password, $remember = false ) {
	$login = anrhpub_resolve_client_login( $login );
	$user  = wp_authenticate( $login, $password );

	if ( is_wp_error( $user ) ) {
		return $user;
	}

	if ( ! anrhpub_user_has_client_role( $user->ID ) ) {
		return new WP_Error(
			'not_client',
			__( 'Identifiants administrateur : utilisez /wp-admin/. Ici, seuls les comptes clients sont acceptés.', 'anrhpub_theme' )
		);
	}

	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		anrhpub_set_client_session_cookie( $user->ID, $remember );
		return (int) $user->ID;
	}

	$result = wp_signon(
		array(
			'user_login'    => $user->user_login,
			'user_password' => $password,
			'remember'      => $remember,
		),
		is_ssl()
	);

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	anrhpub_set_client_session_cookie( (int) $result->ID, $remember );

	return (int) $result->ID;
}

/**
 * Maintient le cookie session si WordPress a déjà authentifié un client.
 */
function anrhpub_sync_client_session_cookie() {
	if ( headers_sent() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( current_user_can( 'manage_options' ) ) {
		return;
	}

	$user_id = (int) get_current_user_id();

	if ( $user_id <= 0 || ! anrhpub_user_has_client_role( $user_id ) ) {
		return;
	}

	$session_id = anrhpub_get_client_session_user_id();

	if ( $session_id === $user_id ) {
		return;
	}

	if ( $session_id > 0 && $session_id !== $user_id ) {
		return;
	}

	anrhpub_set_client_session_cookie( $user_id, true );
}
add_action( 'init', 'anrhpub_sync_client_session_cookie', 5 );

/**
 * Déconnexion client (ne déconnecte pas l’admin WP).
 */
function anrhpub_client_logout() {
	anrhpub_clear_client_session_cookie();

	if ( is_user_logged_in() && anrhpub_user_has_client_role( get_current_user_id() ) && ! current_user_can( 'manage_options' ) ) {
		wp_logout();
	}
}

/**
 * Bannière mode test (admin + client).
 */
function anrhpub_render_admin_client_preview_bar() {
	if ( ! anrhpub_is_admin_previewing_client() ) {
		return;
	}

	$client = get_userdata( anrhpub_get_client_session_user_id() );

	if ( ! $client ) {
		return;
	}
	?>
	<div class="anrhpub-client-preview-bar" role="status">
		<p>
			<?php
			printf(
				/* translators: %s: client display name */
				esc_html__( 'Mode test client : %s — votre session administrateur WordPress reste active.', 'anrhpub_theme' ),
				esc_html( $client->display_name )
			);
			?>
		</p>
		<form method="post" action="<?php echo esc_url( anrhpub_account_url() ); ?>">
			<?php wp_nonce_field( 'anrhpub_logout' ); ?>
			<button type="submit" name="anrhpub_logout" value="1" class="anrhpub-client-preview-bar__btn">
				<?php esc_html_e( 'Quitter le mode client', 'anrhpub_theme' ); ?>
			</button>
		</form>
	</div>
	<?php
}
add_action( 'wp_body_open', 'anrhpub_render_admin_client_preview_bar', 5 );
