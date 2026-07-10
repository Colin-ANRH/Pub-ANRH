<?php
/**
 * Génère wp-config.php pour OVH à partir des variables d'environnement (GitHub Actions).
 */

$password = getenv( 'OVH_DB_PASSWORD' );
if ( $password === false || $password === '' ) {
	fwrite( STDERR, "OVH_DB_PASSWORD manquant.\n" );
	exit( 1 );
}

$staging_user = getenv( 'STAGING_GATE_USER' );
if ( $staging_user === false || $staging_user === '' ) {
	$staging_user = 'anrh';
}

$staging_password = getenv( 'STAGING_GATE_PASSWORD' );
if ( $staging_password === false || $staging_password === '' ) {
	fwrite( STDERR, "STAGING_GATE_PASSWORD manquant — deploy staging annule.\n" );
	exit( 1 );
}

$gate_enabled = 'true';

$template = file_get_contents( __DIR__ . '/wp-config.template.php' );
if ( $template === false ) {
	fwrite( STDERR, "Template wp-config.template.php introuvable.\n" );
	exit( 1 );
}

$config = str_replace(
	array(
		'VOTRE_MDP_OVH',
		'VOTRE_STAGING_GATE_ENABLED',
		'VOTRE_STAGING_USER',
		'VOTRE_STAGING_PASSWORD',
	),
	array(
		$password,
		$gate_enabled,
		$staging_user,
		$staging_password,
	),
	$template
);

$target = dirname( __DIR__ ) . '/wp-config.php';

if ( file_put_contents( $target, $config ) === false ) {
	fwrite( STDERR, "Impossible d'écrire wp-config.php.\n" );
	exit( 1 );
}

echo "wp-config.php généré.\n";
