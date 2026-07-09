<?php
if ( php_sapi_name() !== 'cli' ) {
	exit( "CLI uniquement.\n" );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	$wp_load = dirname( __DIR__, 3 ) . '/wp-load.php';
}
require $wp_load;

foreach ( get_users( array( 'fields' => 'all' ) ) as $user ) {
	echo sprintf(
		"%d | %s | %s | %s | %s\n",
		$user->ID,
		$user->user_login,
		$user->user_email,
		$user->display_name,
		implode( ',', (array) $user->roles )
	);
}
