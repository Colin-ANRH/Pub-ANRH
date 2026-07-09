<?php
/**
 * Génère wp-config.php pour OVH à partir des variables d'environnement (GitHub Actions).
 */

$password = getenv( 'OVH_DB_PASSWORD' );
if ( $password === false || $password === '' ) {
	fwrite( STDERR, "OVH_DB_PASSWORD manquant.\n" );
	exit( 1 );
}

$template = file_get_contents( __DIR__ . '/wp-config.template.php' );
if ( $template === false ) {
	fwrite( STDERR, "Template wp-config.template.php introuvable.\n" );
	exit( 1 );
}

$config = str_replace( 'VOTRE_MDP_OVH', $password, $template );
$target = dirname( __DIR__ ) . '/wp-config.php';

if ( file_put_contents( $target, $config ) === false ) {
	fwrite( STDERR, "Impossible d'écrire wp-config.php.\n" );
	exit( 1 );
}

echo "wp-config.php généré.\n";
