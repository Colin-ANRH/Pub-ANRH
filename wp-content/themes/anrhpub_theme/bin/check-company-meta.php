<?php
if ( php_sapi_name() !== 'cli' ) {
	exit( "CLI uniquement.\n" );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	$wp_load = dirname( __DIR__, 3 ) . '/wp-load.php';
}
require $wp_load;

$user = get_user_by( 'email', 'c.cayre@anrh.fr' );
if ( ! $user ) {
	echo "Utilisateur client introuvable.\n";
	exit( 1 );
}

echo "ID: {$user->ID}\n";
echo "Rôles: " . implode( ',', $user->roles ) . "\n";
echo "anrhpub_company: [" . get_user_meta( $user->ID, 'anrhpub_company', true ) . "]\n";
echo "edit_user cap (self): " . ( current_user_can( 'edit_user', $user->ID ) ? 'yes' : 'no' ) . "\n";

wp_set_current_user( $user->ID );
echo "edit_user cap when logged as self: " . ( current_user_can( 'edit_user', $user->ID ) ? 'yes' : 'no' ) . "\n";

$ok = update_user_meta( $user->ID, 'anrhpub_company', 'TEST-SOCIETE-' . time() );
echo "update_user_meta result: " . var_export( $ok, true ) . "\n";
echo "after update: [" . get_user_meta( $user->ID, 'anrhpub_company', true ) . "]\n";
