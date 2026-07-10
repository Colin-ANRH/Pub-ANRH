<?php
/**
 * Sécurité — limitation connexion, journal, 2FA (si extension).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_LOGIN_ATTEMPTS_OPTION', 'anrhpub_login_attempts' );
define( 'ANRHPUB_AUDIT_LOG_OPTION', 'anrhpub_audit_log' );
define( 'ANRHPUB_MAX_LOGIN_ATTEMPTS', 5 );
define( 'ANRHPUB_LOCKOUT_MINUTES', 15 );

function anrhpub_get_request_ip() {
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
}

/**
 * Limite le débit par IP (formulaires publics).
 *
 * @param string $bucket  Identifiant action.
 * @param int    $limit   Nombre max de requêtes.
 * @param int    $window  Fenêtre en secondes.
 * @return bool True si limite atteinte.
 */
function anrhpub_rate_limit_exceeded( $bucket, $limit = 5, $window = 900 ) {
	$key   = 'anr_rl_' . md5( $bucket . '|' . anrhpub_get_request_ip() );
	$count = (int) get_transient( $key );

	if ( $count >= $limit ) {
		return true;
	}

	set_transient( $key, $count + 1, $window );

	return false;
}

/**
 * Limite le débit par e-mail (newsletter, etc.).
 *
 * @param string $bucket Identifiant action.
 * @param string $email  E-mail.
 * @param int    $limit  Max.
 * @param int    $window Fenêtre.
 * @return bool
 */
function anrhpub_rate_limit_exceeded_for_email( $bucket, $email, $limit = 3, $window = 3600 ) {
	$email = sanitize_email( $email );

	if ( ! is_email( $email ) ) {
		return true;
	}

	$key   = 'anr_rl_' . md5( $bucket . '|' . strtolower( $email ) );
	$count = (int) get_transient( $key );

	if ( $count >= $limit ) {
		return true;
	}

	set_transient( $key, $count + 1, $window );

	return false;
}

/**
 * Génère un captcha arithmétique simple.
 *
 * @param string $context Contexte (contact, newsletter…).
 * @return array{token: string, label: string}
 */
function anrhpub_create_form_captcha( $context = 'contact' ) {
	$a      = wp_rand( 2, 9 );
	$b      = wp_rand( 2, 9 );
	$token  = wp_generate_password( 12, false, false );
	$answer = $a + $b;

	set_transient(
		'anr_captcha_' . sanitize_key( $context ) . '_' . $token,
		array(
			'answer' => $answer,
			'ip'     => anrhpub_get_request_ip(),
		),
		20 * MINUTE_IN_SECONDS
	);

	return array(
		'token' => $token,
		'label' => sprintf(
			/* translators: 1: number, 2: number */
			__( 'Combien font %1$d + %2$d ?', 'anrhpub_theme' ),
			$a,
			$b
		),
	);
}

/**
 * Vérifie la réponse captcha.
 *
 * @param string $context Contexte.
 * @param string $token   Token.
 * @param mixed  $answer  Réponse utilisateur.
 * @return bool
 */
function anrhpub_verify_form_captcha( $context, $token, $answer ) {
	$context = sanitize_key( $context );
	$token   = sanitize_text_field( (string) $token );
	$key     = 'anr_captcha_' . $context . '_' . $token;
	$stored  = get_transient( $key );

	delete_transient( $key );

	if ( ! is_array( $stored ) ) {
		return false;
	}

	if ( (string) ( $stored['ip'] ?? '' ) !== anrhpub_get_request_ip() ) {
		return false;
	}

	return (int) $answer === (int) ( $stored['answer'] ?? -1 );
}

/**
 * Journal d’audit.
 *
 * @param string $action  Action.
 * @param string $detail  Detail.
 * @param int    $user_id User ID.
 */
function anrhpub_audit_log( $action, $detail = '', $user_id = 0 ) {
	$log = get_option( ANRHPUB_AUDIT_LOG_OPTION, array() );

	if ( ! is_array( $log ) ) {
		$log = array();
	}

	array_unshift(
		$log,
		array(
			'time'    => current_time( 'mysql' ),
			'action'  => sanitize_key( $action ),
			'detail'  => sanitize_text_field( $detail ),
			'user_id' => $user_id ? (int) $user_id : get_current_user_id(),
			'ip'      => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
		)
	);

	$log = array_slice( $log, 0, 500 );
	update_option( ANRHPUB_AUDIT_LOG_OPTION, $log, false );
}

/**
 * Clé tentative login.
 *
 * @param string $username Username.
 * @return string
 */
function anrhpub_login_attempt_key( $username ) {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

	return 'anr_' . md5( strtolower( $username ) . '|' . $ip );
}

/**
 * Bloque après trop de tentatives.
 *
 * @param WP_User|WP_Error|null $user     User.
 * @param string                $username Login.
 * @param string                $password Password.
 * @return WP_User|WP_Error|null
 */
function anrhpub_limit_login_attempts( $user, $username, $password ) {
	unset( $password );

	if ( empty( $username ) ) {
		return $user;
	}

	$key      = anrhpub_login_attempt_key( $username );
	$attempts = get_transient( $key );

	if ( is_array( $attempts ) && (int) ( $attempts['count'] ?? 0 ) >= ANRHPUB_MAX_LOGIN_ATTEMPTS ) {
		return new WP_Error(
			'too_many_attempts',
			sprintf(
				/* translators: %d: minutes */
				__( 'Trop de tentatives. Réessayez dans %d minutes.', 'anrhpub_theme' ),
				ANRHPUB_LOCKOUT_MINUTES
			)
		);
	}

	return $user;
}
add_filter( 'authenticate', 'anrhpub_limit_login_attempts', 25, 3 );

/**
 * Compte échec / succès login.
 *
 * @param string  $user_login Login.
 * @param WP_User $user       User.
 */
function anrhpub_on_login_success( $user_login, $user ) {
	$key = anrhpub_login_attempt_key( $user_login );
	delete_transient( $key );
	anrhpub_audit_log( 'login_ok', $user_login, $user->ID );
}
add_action( 'wp_login', 'anrhpub_on_login_success', 10, 2 );

/**
 * @param string $user_login Login.
 */
function anrhpub_on_login_failed( $user_login ) {
	$key      = anrhpub_login_attempt_key( $user_login );
	$attempts = get_transient( $key );

	if ( ! is_array( $attempts ) ) {
		$attempts = array( 'count' => 0 );
	}

	++$attempts['count'];
	set_transient( $key, $attempts, ANRHPUB_LOCKOUT_MINUTES * MINUTE_IN_SECONDS );
	anrhpub_audit_log( 'login_fail', $user_login, 0 );
}
add_action( 'wp_login_failed', 'anrhpub_on_login_failed' );

/**
 * Menu admin journal.
 */
function anrhpub_audit_log_menu() {
	add_submenu_page(
		'edit.php?post_type=anr_product',
		__( 'Journal sécurité', 'anrhpub_theme' ),
		__( 'Journal sécurité', 'anrhpub_theme' ),
		'manage_options',
		'anrhpub-audit-log',
		'anrhpub_audit_log_page'
	);
}
add_action( 'admin_menu', 'anrhpub_audit_log_menu' );

/**
 * Page journal.
 */
function anrhpub_audit_log_page() {
	$log = get_option( ANRHPUB_AUDIT_LOG_OPTION, array() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Journal sécurité ANRH', 'anrhpub_theme' ); ?></h1>
		<p><?php esc_html_e( 'Connexions, échecs et événements sensibles (500 derniers).', 'anrhpub_theme' ); ?></p>
		<p><?php esc_html_e( 'Pour la double authentification admin : installez une extension 2FA (WordPress 5.2+) ou activez Application Passwords + politique interne.', 'anrhpub_theme' ); ?></p>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'Date', 'anrhpub_theme' ); ?></th><th><?php esc_html_e( 'Action', 'anrhpub_theme' ); ?></th><th><?php esc_html_e( 'Détail', 'anrhpub_theme' ); ?></th><th>IP</th></tr></thead>
			<tbody>
			<?php foreach ( (array) $log as $row ) : ?>
				<tr>
					<td><?php echo esc_html( $row['time'] ?? '' ); ?></td>
					<td><?php echo esc_html( $row['action'] ?? '' ); ?></td>
					<td><?php echo esc_html( $row['detail'] ?? '' ); ?></td>
					<td><?php echo esc_html( $row['ip'] ?? '' ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
