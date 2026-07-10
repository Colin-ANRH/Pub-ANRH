<?php
/**
 * Réinitialise l'accès client : supprime les comptes non-admin,
 * libère l'e-mail principal et crée un compte client catalogue.
 *
 * Usage :
 *   php wp-content/themes/anrhpub_theme/bin/reset-client-access.php \
 *     --client-email=c.cayre@anrh.fr \
 *     --admin-email=admin-wp@anrh.fr
 *
 * Mots de passe : optionnels (--client-password, --admin-password).
 * S'ils sont omis, des mots de passe aléatoires sont générés et affichés une fois.
 *
 * @package anrhpub_theme
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( "CLI uniquement.\n" );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	$wp_load = dirname( __DIR__, 3 ) . '/wp-load.php';
}
require $wp_load;

if ( ! function_exists( 'anrhpub_register_client_role' ) ) {
	require get_template_directory() . '/inc/client-account.php';
}

/**
 * @param string $key     Option CLI (--key=value).
 * @param string $default Valeur par défaut.
 * @return string
 */
function anrhpub_cli_arg( $key, $default = '' ) {
	foreach ( array_slice( $_SERVER['argv'], 1 ) as $arg ) {
		$prefix = '--' . $key . '=';
		if ( 0 === strpos( $arg, $prefix ) ) {
			return substr( $arg, strlen( $prefix ) );
		}
	}
	return $default;
}

anrhpub_register_client_role();

$client_email    = anrhpub_cli_arg( 'client-email', getenv( 'ANRH_CLIENT_EMAIL' ) ?: 'c.cayre@anrh.fr' );
$admin_email     = anrhpub_cli_arg( 'admin-email', getenv( 'ANRH_ADMIN_EMAIL' ) ?: 'admin-wp@anrh.fr' );
$client_password = anrhpub_cli_arg( 'client-password', getenv( 'ANRH_CLIENT_PASSWORD' ) ?: '' );
$admin_password  = anrhpub_cli_arg( 'admin-password', getenv( 'ANRH_ADMIN_PASSWORD' ) ?: '' );

if ( '' === $client_password ) {
	$client_password = wp_generate_password( 16, true, true );
}
if ( '' === $admin_password ) {
	$admin_password = wp_generate_password( 16, true, true );
}

echo "=== Réinitialisation accès client ANRH ===\n\n";

$deleted = 0;

foreach ( get_users( array( 'fields' => 'all' ) ) as $user ) {
	if ( user_can( $user, 'manage_options' ) ) {
		continue;
	}

	if ( wp_delete_user( $user->ID ) ) {
		++$deleted;
		echo "Supprimé : {$user->user_login} (ID {$user->ID})\n";
	}
}

echo "\n{$deleted} compte(s) client(s) supprimé(s).\n\n";

$admin = get_user_by( 'id', 1 );

if ( $admin ) {
	wp_update_user(
		array(
			'ID'         => 1,
			'user_email' => $admin_email,
		)
	);
	wp_set_password( $admin_password, 1 );
	echo "Admin WordPress (ID 1) :\n";
	echo "  - E-mail admin : {$admin_email}\n";
	echo "  - Mot de passe temporaire : {$admin_password}\n";
	echo "  - Connexion : /wp-admin/\n\n";
}

$existing = get_user_by( 'email', $client_email );

if ( $existing ) {
	if ( in_array( 'anr_client', (array) $existing->roles, true ) ) {
		wp_set_password( $client_password, $existing->ID );
		echo "Compte client existant réinitialisé (ID {$existing->ID}).\n";
	} else {
		echo "ERREUR : l'e-mail {$client_email} est encore utilisé par un autre compte.\n";
		exit( 1 );
	}
} else {
	$username = sanitize_user( strstr( $client_email, '@', true ) ?: 'client', true );

	if ( username_exists( $username ) ) {
		$username = $username . '.' . wp_rand( 100, 999 );
	}

	$user_id = wp_create_user( $username, $client_password, $client_email );

	if ( is_wp_error( $user_id ) ) {
		echo 'ERREUR création client : ' . $user_id->get_error_message() . "\n";
		exit( 1 );
	}

	$user = new WP_User( $user_id );
	$user->set_role( 'anr_client' );

	echo "Compte client créé (ID {$user_id}).\n";
}

echo "\n--- Connexion site (catalogue) ---\n";
echo 'URL : ' . home_url( '/connexion/' ) . "\n";
echo "E-mail : {$client_email}\n";
echo "Mot de passe temporaire : {$client_password}\n";
echo "\nChangez ce mot de passe dans Mon compte après connexion.\n";
echo "\nNe commitez jamais ces mots de passe dans le dépôt Git.\n";
