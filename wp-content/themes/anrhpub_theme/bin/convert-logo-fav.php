<?php
/**
 * One-off : PNG → logo_fav.webp (carré, fond transparent).
 *
 * @package anrhpub_theme
 */

$src = $argv[1] ?? '';
$dst = dirname( __DIR__ ) . '/assets/images/logo_fav.webp';

if ( ! $src || ! file_exists( $src ) ) {
	fwrite( STDERR, "Usage: php convert-logo-fav.php <source.png>\n" );
	exit( 1 );
}

if ( ! function_exists( 'imagewebp' ) ) {
	fwrite( STDERR, "GD WebP unavailable.\n" );
	exit( 1 );
}

$img = imagecreatefrompng( $src );
if ( ! $img ) {
	fwrite( STDERR, "Cannot load PNG.\n" );
	exit( 1 );
}

imagepalettetotruecolor( $img );
imagealphablending( $img, true );
imagesavealpha( $img, true );

$w  = imagesx( $img );
$h  = imagesy( $img );
$sq = max( $w, $h );

$canvas = imagecreatetruecolor( $sq, $sq );
imagealphablending( $canvas, false );
imagesavealpha( $canvas, true );
$trans = imagecolorallocatealpha( $canvas, 0, 0, 0, 127 );
imagefilledrectangle( $canvas, 0, 0, $sq, $sq, $trans );
imagealphablending( $canvas, true );
imagecopy( $canvas, $img, (int) ( ( $sq - $w ) / 2 ), (int) ( ( $sq - $h ) / 2 ), 0, 0, $w, $h );
imagedestroy( $img );

if ( ! imagewebp( $canvas, $dst, 88 ) ) {
	fwrite( STDERR, "WebP save failed.\n" );
	exit( 1 );
}

imagedestroy( $canvas );

echo 'Created: ' . $dst . ' (' . filesize( $dst ) . " bytes, {$sq}x{$sq})\n";
