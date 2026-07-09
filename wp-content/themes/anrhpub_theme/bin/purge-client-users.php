<?php
/**
 * Supprime tous les comptes sauf les administrateurs.
 *
 * Usage : php wp-content/themes/anrhpub_theme/bin/purge-client-users.php
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

$deleted = 0;
$kept    = 0;

foreach ( get_users( array( 'fields' => 'all' ) ) as $user ) {
	if ( user_can( $user, 'manage_options' ) ) {
		++$kept;
		echo "Conservé (admin) : {$user->user_login} (ID {$user->ID})\n";
		continue;
	}

	if ( wp_delete_user( $user->ID ) ) {
		++$deleted;
		echo "Supprimé : {$user->user_login} (ID {$user->ID})\n";
	}
}

echo "\nTerminé — {$deleted} compte(s) supprimé(s), {$kept} administrateur(s) conservé(s).\n";
