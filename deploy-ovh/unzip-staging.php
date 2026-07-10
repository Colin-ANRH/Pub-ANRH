<?php
/**
 * Décompresse deploy.zip sur le serveur (usage unique, staging).
 * Supprimer ce fichier après déploiement.
 */

$token_expected = 'CHANGE_ME';

if ( ! isset( $_GET['token'] ) || ! hash_equals( $token_expected, (string) $_GET['token'] ) ) {
	http_response_code( 403 );
	exit( 'Forbidden' );
}

$zip_path = __DIR__ . '/deploy.zip';

if ( ! file_exists( $zip_path ) ) {
	http_response_code( 404 );
	exit( 'deploy.zip introuvable' );
}

if ( ! class_exists( 'ZipArchive' ) ) {
	http_response_code( 500 );
	exit( 'ZipArchive indisponible sur le serveur' );
}

$zip = new ZipArchive();
if ( $zip->open( $zip_path ) !== true ) {
	http_response_code( 500 );
	exit( 'Impossible d ouvrir deploy.zip' );
}

$zip->extractTo( __DIR__ );
$zip->close();

@unlink( $zip_path );
@unlink( __FILE__ );

echo 'Deploiement termine.';
