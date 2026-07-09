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
	exit( "no user\n" );
}

wp_set_current_user( $user->ID );

$result = wp_update_user(
	array(
		'ID'           => $user->ID,
		'first_name'   => 'Colin',
		'last_name'    => 'Cayre',
		'display_name' => 'Colin Cayre',
	)
);

echo 'wp_update_user: ';
echo is_wp_error( $result ) ? $result->get_error_message() : 'ok ID ' . $result;
echo "\n";

$meta = update_user_meta( $user->ID, 'anrhpub_company', 'ANRH Peyruis' );
echo 'company meta: ' . var_export( $meta, true ) . "\n";
echo 'read: ' . get_user_meta( $user->ID, 'anrhpub_company', true ) . "\n";
