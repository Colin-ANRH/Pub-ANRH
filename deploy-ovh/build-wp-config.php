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

// Salesforce — optionnels ; chaînes vides = connexion désactivée.
$sf_client_id     = getenv( 'SF_CLIENT_ID' );
$sf_client_secret = getenv( 'SF_CLIENT_SECRET' );
$sf_refresh_token = getenv( 'SF_REFRESH_TOKEN' );
$sf_login_url     = getenv( 'SF_LOGIN_URL' );
$sf_api_version   = getenv( 'SF_API_VERSION' );

if ( $sf_client_id === false ) {
	$sf_client_id = '';
}
if ( $sf_client_secret === false ) {
	$sf_client_secret = '';
}
if ( $sf_refresh_token === false ) {
	$sf_refresh_token = '';
}
if ( $sf_login_url === false || $sf_login_url === '' ) {
	$sf_login_url = 'https://login.salesforce.com';
}
if ( $sf_api_version === false || $sf_api_version === '' ) {
	$sf_api_version = 'v59.0';
}

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
		'VOTRE_SF_CLIENT_ID',
		'VOTRE_SF_CLIENT_SECRET',
		'VOTRE_SF_REFRESH_TOKEN',
		'VOTRE_SF_LOGIN_URL',
		'VOTRE_SF_API_VERSION',
	),
	array(
		$password,
		$gate_enabled,
		$staging_user,
		$staging_password,
		$sf_client_id,
		$sf_client_secret,
		$sf_refresh_token,
		$sf_login_url,
		$sf_api_version,
	),
	$template
);

$target = dirname( __DIR__ ) . '/wp-config.php';

if ( file_put_contents( $target, $config ) === false ) {
	fwrite( STDERR, "Impossible d'écrire wp-config.php.\n" );
	exit( 1 );
}

echo "wp-config.php généré.\n";
