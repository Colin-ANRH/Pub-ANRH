<?php
/**
 * Plugin Name: ANRH Staging Gate
 * Description: Accès restreint par identifiant / mot de passe pour l'environnement de staging. À supprimer à la mise en production.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Jamais sur l'environnement local de développement.
if ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE === 'local' ) {
	return;
}

// Uniquement sur l'environnement staging OVH.
if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) || WP_ENVIRONMENT_TYPE !== 'staging' ) {
	return;
}

if ( ! defined( 'ANRH_STAGING_GATE' ) || ! ANRH_STAGING_GATE ) {
	return;
}

if ( ! defined( 'ANRH_STAGING_USER' ) || ! defined( 'ANRH_STAGING_PASSWORD' ) || ANRH_STAGING_PASSWORD === '' ) {
	return;
}

/**
 * Bloque l'indexation (SEO) sur le staging.
 */
add_filter(
	'pre_option_blog_public',
	static function () {
		return '0';
	}
);

add_filter(
	'robots_txt',
	static function ( $output ) {
		return "User-agent: *\nDisallow: /\n";
	},
	999
);

add_action(
	'send_headers',
	static function () {
		if ( ! headers_sent() ) {
			header( 'X-Robots-Tag: noindex, nofollow, noarchive, nosnippet', true );
		}
	}
);

/**
 * @return bool
 */
function anrh_staging_gate_is_authenticated() {
	$cookie = isset( $_COOKIE['anrh_staging_auth'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['anrh_staging_auth'] ) ) : '';

	if ( $cookie === '' ) {
		return false;
	}

	$expected = anrh_staging_gate_auth_token();

	return hash_equals( $expected, $cookie );
}

/**
 * @return string
 */
function anrh_staging_gate_auth_token() {
	return hash_hmac( 'sha256', ANRH_STAGING_USER . '|' . ANRH_STAGING_PASSWORD, wp_salt( 'auth' ) );
}

/**
 * @return void
 */
function anrh_staging_gate_set_auth_cookie() {
	$secure   = is_ssl();
	$httponly = true;

	setcookie(
		'anrh_staging_auth',
		anrh_staging_gate_auth_token(),
		time() + ( 14 * DAY_IN_SECONDS ),
		COOKIEPATH ? COOKIEPATH : '/',
		COOKIE_DOMAIN,
		$secure,
		$httponly
	);

	$_COOKIE['anrh_staging_auth'] = anrh_staging_gate_auth_token();
}

/**
 * @return void
 */
function anrh_staging_gate_clear_auth_cookie() {
	setcookie(
		'anrh_staging_auth',
		'',
		time() - YEAR_IN_SECONDS,
		COOKIEPATH ? COOKIEPATH : '/',
		COOKIE_DOMAIN,
		is_ssl(),
		true
	);

	unset( $_COOKIE['anrh_staging_auth'] );
}

/**
 * @return void
 */
function anrh_staging_gate_handle_request() {
	if ( isset( $_GET['anrh_staging_logout'] ) ) {
		anrh_staging_gate_clear_auth_cookie();
		wp_safe_redirect( remove_query_arg( 'anrh_staging_logout' ) );
		exit;
	}

	if ( anrh_staging_gate_is_authenticated() ) {
		return;
	}

	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && isset( $_POST['anrh_staging_login'] ) ) {
		$user = isset( $_POST['anrh_staging_user'] ) ? sanitize_text_field( wp_unslash( $_POST['anrh_staging_user'] ) ) : '';
		$pass = isset( $_POST['anrh_staging_pass'] ) ? (string) wp_unslash( $_POST['anrh_staging_pass'] ) : '';

		if ( hash_equals( ANRH_STAGING_USER, $user ) && hash_equals( ANRH_STAGING_PASSWORD, $pass ) ) {
			anrh_staging_gate_set_auth_cookie();
			wp_safe_redirect( remove_query_arg( array( 'anrh_staging_logout' ) ) );
			exit;
		}

		anrh_staging_gate_render_login( 'Identifiant ou mot de passe incorrect.' );
		exit;
	}

	anrh_staging_gate_render_login();
	exit;
}

/**
 * @param string $error_message Message d'erreur optionnel.
 * @return void
 */
function anrh_staging_gate_render_login( $error_message = '' ) {
	status_header( 401 );
	nocache_headers();

	$action = esc_url( home_url( add_query_arg( array() ) ) );
	?>
	<!DOCTYPE html>
	<html lang="fr">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
		<title>Accès staging — ANRH Publications</title>
		<style>
			*, *::before, *::after { box-sizing: border-box; }
			body {
				margin: 0;
				min-height: 100vh;
				display: grid;
				place-items: center;
				padding: 1.5rem;
				font-family: "Segoe UI", system-ui, sans-serif;
				background: #f4f1ec;
				color: #2a2630;
			}
			.anrh-staging-modal {
				width: min(100%, 420px);
				background: #fffcf8;
				border: 1px solid #d4cdd9;
				border-radius: 16px;
				box-shadow: 0 24px 60px rgba(46, 36, 48, 0.12);
				padding: 2rem;
			}
			.anrh-staging-modal h1 {
				margin: 0 0 .35rem;
				font-size: 1.35rem;
				color: #814a79;
			}
			.anrh-staging-modal p {
				margin: 0 0 1.5rem;
				color: #5e5868;
				line-height: 1.5;
				font-size: .95rem;
			}
			.anrh-staging-badge {
				display: inline-block;
				margin-bottom: 1rem;
				padding: .25rem .65rem;
				border-radius: 999px;
				background: #efe4ed;
				color: #5f3560;
				font-size: .75rem;
				font-weight: 600;
				letter-spacing: .04em;
				text-transform: uppercase;
			}
			.anrh-staging-field {
				margin-bottom: 1rem;
			}
			.anrh-staging-field label {
				display: block;
				margin-bottom: .35rem;
				font-size: .875rem;
				font-weight: 600;
			}
			.anrh-staging-field input {
				width: 100%;
				padding: .75rem .9rem;
				border: 1px solid #d4cdd9;
				border-radius: 10px;
				font-size: 1rem;
				background: #fff;
			}
			.anrh-staging-field input:focus {
				outline: 2px solid #814a79;
				outline-offset: 1px;
				border-color: #814a79;
			}
			.anrh-staging-error {
				margin-bottom: 1rem;
				padding: .75rem .9rem;
				border-radius: 10px;
				background: #fdecec;
				color: #8a1f1f;
				font-size: .875rem;
			}
			.anrh-staging-submit {
				width: 100%;
				margin-top: .25rem;
				padding: .85rem 1rem;
				border: 0;
				border-radius: 10px;
				background: #814a79;
				color: #fff;
				font-size: 1rem;
				font-weight: 600;
				cursor: pointer;
			}
			.anrh-staging-submit:hover {
				background: #5f3560;
			}
		</style>
	</head>
	<body>
		<div class="anrh-staging-modal" role="dialog" aria-modal="true" aria-labelledby="anrh-staging-title">
			<span class="anrh-staging-badge">Staging</span>
			<h1 id="anrh-staging-title">Espace de prévisualisation</h1>
			<p>Ce site n'est pas encore public. Saisissez vos identifiants pour continuer.</p>
			<?php if ( $error_message !== '' ) : ?>
				<div class="anrh-staging-error"><?php echo esc_html( $error_message ); ?></div>
			<?php endif; ?>
			<form method="post" action="<?php echo $action; ?>">
				<div class="anrh-staging-field">
					<label for="anrh_staging_user">Identifiant</label>
					<input type="text" id="anrh_staging_user" name="anrh_staging_user" autocomplete="username" required>
				</div>
				<div class="anrh-staging-field">
					<label for="anrh_staging_pass">Mot de passe</label>
					<input type="password" id="anrh_staging_pass" name="anrh_staging_pass" autocomplete="current-password" required>
				</div>
				<button type="submit" class="anrh-staging-submit" name="anrh_staging_login" value="1">Accéder au site</button>
			</form>
		</div>
	</body>
	</html>
	<?php
}

add_action( 'template_redirect', 'anrh_staging_gate_handle_request', 0 );
add_action( 'admin_init', 'anrh_staging_gate_handle_request', 0 );
add_action( 'login_init', 'anrh_staging_gate_handle_request', 0 );

add_filter(
	'rest_authentication_errors',
	static function ( $result ) {
		if ( true === $result || is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! anrh_staging_gate_is_authenticated() ) {
			return new WP_Error(
				'anrh_staging_gate',
				'Accès restreint.',
				array( 'status' => 401 )
			);
		}

		return $result;
	}
);
