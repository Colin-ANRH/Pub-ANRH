<?php
/**
 * Réinitialise l'accès client : supprime les comptes non-admin,
 * libère l'e-mail principal et crée un compte client catalogue.
 *
 * Usage : c:\xampp\php\php.exe wp-content/themes/anrhpub_theme/bin/reset-client-access.php
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

anrhpub_register_client_role();

$client_email    = 'c.cayre@anrh.fr';
$client_password = 'Anrh-Client-2026!';
$admin_email     = 'admin-wp@anrh.fr';

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
	wp_set_password( 'Anrh-Admin-2026!', 1 );
	echo "Admin WordPress (ID 1) :\n";
	echo "  - E-mail admin : {$admin_email}\n";
	echo "  - Mot de passe temporaire : Anrh-Admin-2026!\n";
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
	$username = 'colin.cayre';

	if ( username_exists( $username ) ) {
		$username = 'colin.cayre.' . wp_rand( 100, 999 );
	}

	$user_id = wp_create_user( $username, $client_password, $client_email );

	if ( is_wp_error( $user_id ) ) {
		echo 'ERREUR création client : ' . $user_id->get_error_message() . "\n";
		exit( 1 );
	}

	$user = new WP_User( $user_id );
	$user->set_role( 'anr_client' );

	wp_update_user(
		array(
			'ID'           => $user_id,
			'display_name' => 'Colin Cayre',
			'first_name'   => 'Colin',
			'last_name'    => 'Cayre',
		)
	);

	echo "Compte client créé (ID {$user_id}).\n";
}

echo "\n--- Connexion site (catalogue) ---\n";
echo "URL : " . home_url( '/connexion/' ) . "\n";
echo "E-mail : {$client_email}\n";
echo "Mot de passe temporaire : {$client_password}\n";
echo "\nChangez ce mot de passe dans Mon compte après connexion.\n";
