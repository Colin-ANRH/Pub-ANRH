<?php
/**
 * Images WebP — conversion automatique et utilitaires.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_WEBP_QUALITY_OPTION', 'anrhpub_webp_quality' );
define( 'ANRHPUB_WEBP_DELETE_ORIGINAL_OPTION', 'anrhpub_webp_delete_original' );

/**
 * Qualité WebP (0–100).
 *
 * @return int
 */
function anrhpub_get_webp_quality() {
	$quality = (int) get_option( ANRHPUB_WEBP_QUALITY_OPTION, 82 );

	return max( 50, min( 95, $quality ) );
}

/**
 * Supprimer les fichiers source après conversion ?
 *
 * @return bool
 */
function anrhpub_webp_should_delete_original() {
	return (bool) get_option( ANRHPUB_WEBP_DELETE_ORIGINAL_OPTION, true );
}

/**
 * WebP supporté par le serveur ?
 *
 * @return bool
 */
function anrhpub_webp_is_available() {
	if ( function_exists( 'imagewebp' ) && ( function_exists( 'imagecreatefromjpeg' ) || function_exists( 'imagecreatefrompng' ) ) ) {
		return true;
	}

	if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
		$formats = Imagick::queryFormats( 'WEBP' );
		return in_array( 'WEBP', $formats, true );
	}

	return false;
}

/**
 * URI image du thème (.webp si présent).
 *
 * @param string $relative Chemin relatif sans extension (ex. assets/images/logo-anr).
 * @param string $fallback_ext Extension de repli (jpg).
 * @return string
 */
function anrhpub_theme_image_uri( $relative, $fallback_ext = 'jpg' ) {
	$relative = ltrim( str_replace( '\\', '/', $relative ), '/' );
	$base     = pathinfo( $relative, PATHINFO_DIRNAME );
	$name     = pathinfo( $relative, PATHINFO_FILENAME );
	$dir      = $base && '.' !== $base ? $base . '/' : '';

	$webp_path = ANRHPUB_THEME_DIR . '/' . $dir . $name . '.webp';
	if ( file_exists( $webp_path ) ) {
		return ANRHPUB_THEME_URI . '/' . $dir . $name . '.webp';
	}

	$fallback_path = ANRHPUB_THEME_DIR . '/' . $dir . $name . '.' . $fallback_ext;
	if ( file_exists( $fallback_path ) ) {
		return ANRHPUB_THEME_URI . '/' . $dir . $name . '.' . $fallback_ext;
	}

	return ANRHPUB_THEME_URI . '/' . $dir . $name . '.webp';
}

/**
 * Fichier convertible en WebP ?
 *
 * @param string $path Chemin absolu.
 * @return bool
 */
function anrhpub_is_convertible_image_path( $path ) {
	if ( ! $path || ! file_exists( $path ) ) {
		return false;
	}

	$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

	if ( 'webp' === $ext ) {
		return false;
	}

	return in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif' ), true );
}

/**
 * Convertit un fichier raster en WebP.
 *
 * @param string $source   Chemin source.
 * @param string $dest     Chemin destination (optionnel).
 * @param int    $quality  Qualité.
 * @return string|WP_Error Chemin WebP.
 */
function anrhpub_convert_image_file_to_webp( $source, $dest = '', $quality = 0 ) {
	if ( ! anrhpub_webp_is_available() ) {
		return new WP_Error( 'webp_unavailable', __( 'La conversion WebP n’est pas disponible sur ce serveur (GD ou Imagick requis).', 'anrhpub_theme' ) );
	}

	if ( ! file_exists( $source ) ) {
		return new WP_Error( 'missing_file', __( 'Fichier introuvable.', 'anrhpub_theme' ) );
	}

	if ( ! anrhpub_is_convertible_image_path( $source ) ) {
		return $source;
	}

	$quality = $quality > 0 ? $quality : anrhpub_get_webp_quality();
	$dest    = $dest ? $dest : preg_replace( '/\.[^.]+$/i', '.webp', $source );

	if ( function_exists( 'imagewebp' ) ) {
		$result = anrhpub_convert_image_file_to_webp_gd( $source, $dest, $quality );
	} else {
		$result = anrhpub_convert_image_file_to_webp_imagick( $source, $dest, $quality );
	}

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	return $dest;
}

/**
 * Conversion GD.
 *
 * @param string $source  Source.
 * @param string $dest    Destination.
 * @param int    $quality Qualité.
 * @return true|WP_Error
 */
function anrhpub_convert_image_file_to_webp_gd( $source, $dest, $quality ) {
	$info = wp_getimagesize( $source );

	if ( false === $info ) {
		return new WP_Error( 'invalid_image', __( 'Image non lisible.', 'anrhpub_theme' ) );
	}

	$mime = $info['mime'] ?? '';
	$img  = null;

	switch ( $mime ) {
		case 'image/jpeg':
			$img = imagecreatefromjpeg( $source );
			break;
		case 'image/png':
			$img = imagecreatefrompng( $source );
			if ( $img ) {
				imagepalettetotruecolor( $img );
				imagealphablending( $img, true );
				imagesavealpha( $img, true );
			}
			break;
		case 'image/gif':
			$img = imagecreatefromgif( $source );
			break;
		default:
			return new WP_Error( 'unsupported_format', __( 'Format non pris en charge.', 'anrhpub_theme' ) );
	}

	if ( ! $img ) {
		return new WP_Error( 'gd_open_failed', __( 'Impossible d’ouvrir l’image.', 'anrhpub_theme' ) );
	}

	$dir = dirname( $dest );
	if ( ! wp_mkdir_p( $dir ) ) {
		imagedestroy( $img );
		return new WP_Error( 'mkdir_failed', __( 'Impossible de créer le dossier de destination.', 'anrhpub_theme' ) );
	}

	$ok = imagewebp( $img, $dest, $quality );
	imagedestroy( $img );

	if ( ! $ok || ! file_exists( $dest ) ) {
		return new WP_Error( 'webp_write_failed', __( 'Échec de l’écriture WebP.', 'anrhpub_theme' ) );
	}

	return true;
}

/**
 * Conversion Imagick.
 *
 * @param string $source  Source.
 * @param string $dest    Destination.
 * @param int    $quality Qualité.
 * @return true|WP_Error
 */
function anrhpub_convert_image_file_to_webp_imagick( $source, $dest, $quality ) {
	try {
		$image = new Imagick( $source );
		$image->setImageFormat( 'webp' );
		$image->setImageCompressionQuality( $quality );
		$image->writeImage( $dest );
		$image->clear();
		$image->destroy();
	} catch ( Exception $e ) {
		return new WP_Error( 'imagick_failed', $e->getMessage() );
	}

	if ( ! file_exists( $dest ) ) {
		return new WP_Error( 'webp_write_failed', __( 'Échec de l’écriture WebP.', 'anrhpub_theme' ) );
	}

	return true;
}

/**
 * Remplace un fichier par sa version WebP et met à jour le chemin.
 *
 * @param string $path Chemin absolu.
 * @return string|WP_Error Nouveau chemin.
 */
function anrhpub_replace_path_with_webp( $path ) {
	$webp_path = preg_replace( '/\.[^.]+$/i', '.webp', $path );
	$result    = anrhpub_convert_image_file_to_webp( $path, $webp_path );

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	if ( $webp_path !== $path && anrhpub_webp_should_delete_original() && file_exists( $path ) ) {
		wp_delete_file( $path );
	}

	return $webp_path;
}

/**
 * Convertit une pièce jointe (original + tailles).
 *
 * @param int   $attachment_id ID attachment.
 * @param array $metadata      Métadonnées existantes.
 * @return array|WP_Error
 */
function anrhpub_convert_attachment_to_webp( $attachment_id, $metadata = null ) {
	static $busy = array();

	if ( isset( $busy[ $attachment_id ] ) ) {
		return is_array( $metadata ) ? $metadata : array();
	}

	$busy[ $attachment_id ] = true;

	if ( ! wp_attachment_is_image( $attachment_id ) ) {
		unset( $busy[ $attachment_id ] );
		return is_array( $metadata ) ? $metadata : array();
	}

	if ( null === $metadata ) {
		$metadata = wp_get_attachment_metadata( $attachment_id );
	}

	if ( ! is_array( $metadata ) ) {
		$metadata = array();
	}

	$main = get_attached_file( $attachment_id );

	if ( ! $main || ! file_exists( $main ) ) {
		unset( $busy[ $attachment_id ] );
		return $metadata;
	}

	$upload_dir = wp_upload_dir();
	$base_dir   = trailingslashit( $upload_dir['basedir'] );

	if ( ! empty( $metadata['file'] ) ) {
		$main_dir  = trailingslashit( dirname( $metadata['file'] ) );
		$main_file = $base_dir . $metadata['file'];
	} else {
		$main_dir  = trailingslashit( str_replace( $base_dir, '', dirname( $main ) ) );
		$main_file = $main;
		$metadata['file'] = $main_dir . basename( $main );
	}

	if ( anrhpub_is_convertible_image_path( $main_file ) ) {
		$new_main = anrhpub_replace_path_with_webp( $main_file );

		if ( ! is_wp_error( $new_main ) ) {
			$metadata['file'] = $main_dir . basename( $new_main );
			update_attached_file( $attachment_id, $new_main );
		}
	}

	if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
		foreach ( $metadata['sizes'] as $size => $data ) {
			if ( empty( $data['file'] ) ) {
				continue;
			}

			$size_path = $base_dir . $main_dir . $data['file'];

			if ( ! anrhpub_is_convertible_image_path( $size_path ) ) {
				if ( '.webp' === substr( strtolower( $data['file'] ), -5 ) ) {
					$metadata['sizes'][ $size ]['mime-type'] = 'image/webp';
				}
				continue;
			}

			$new_size = anrhpub_replace_path_with_webp( $size_path );

			if ( is_wp_error( $new_size ) ) {
				continue;
			}

			$metadata['sizes'][ $size ]['file']      = basename( $new_size );
			$metadata['sizes'][ $size ]['mime-type'] = 'image/webp';
		}
	}

	wp_update_post(
		array(
			'ID'             => $attachment_id,
			'post_mime_type' => 'image/webp',
		)
	);

	wp_update_attachment_metadata( $attachment_id, $metadata );

	unset( $busy[ $attachment_id ] );

	return $metadata;
}

/**
 * Nouvelles tailles générées en WebP (WordPress 5.8+).
 *
 * @param array<string, string|null> $formats Formats.
 * @return array<string, string|null>
 */
function anrhpub_webp_editor_output_format( $formats ) {
	if ( ! anrhpub_webp_is_available() ) {
		return $formats;
	}

	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	$formats['image/gif']  = 'image/webp';

	return $formats;
}
add_filter( 'image_editor_output_format', 'anrhpub_webp_editor_output_format' );

/**
 * Conversion après génération des métadonnées.
 *
 * @param array $metadata      Métadonnées.
 * @param int   $attachment_id ID.
 * @return array
 */
function anrhpub_webp_generate_attachment_metadata( $metadata, $attachment_id ) {
	if ( ! anrhpub_webp_is_available() || ! is_array( $metadata ) ) {
		return $metadata;
	}

	$converted = anrhpub_convert_attachment_to_webp( $attachment_id, $metadata );

	return is_wp_error( $converted ) ? $metadata : $converted;
}
add_filter( 'wp_generate_attachment_metadata', 'anrhpub_webp_generate_attachment_metadata', 99, 2 );

/**
 * MIME WebP autorisé.
 *
 * @param array<string, string> $mimes MIME.
 * @return array<string, string>
 */
function anrhpub_webp_upload_mimes( $mimes ) {
	$mimes['webp'] = 'image/webp';
	return $mimes;
}
add_filter( 'upload_mimes', 'anrhpub_webp_upload_mimes' );

/**
 * Compte les attachments encore non WebP.
 *
 * @return int
 */
function anrhpub_count_non_webp_attachments() {
	$query = new WP_Query(
		array(
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'post_mime_type'         => array( 'image/jpeg', 'image/png', 'image/gif' ),
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	return (int) $query->found_posts;
}

/**
 * Lot de conversion médiathèque.
 *
 * @param int $offset Offset.
 * @param int $limit  Limite.
 * @return array{processed: int, converted: int, errors: int, remaining: int, messages: array<int, string>}
 */
function anrhpub_webp_batch_convert_attachments( $offset = 0, $limit = 15 ) {
	$limit = max( 1, min( 30, (int) $limit ) );

	$query = new WP_Query(
		array(
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'post_mime_type'         => array( 'image/jpeg', 'image/png', 'image/gif' ),
			'posts_per_page'         => $limit,
			'offset'                 => max( 0, (int) $offset ),
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$processed = 0;
	$converted = 0;
	$errors    = 0;
	$messages  = array();

	foreach ( $query->posts as $attachment_id ) {
		++$processed;
		$file = get_attached_file( (int) $attachment_id );

		if ( $file && anrhpub_is_convertible_image_path( $file ) ) {
			anrhpub_convert_attachment_to_webp( (int) $attachment_id );
			$after  = get_attached_file( (int) $attachment_id );

			if ( $after && '.webp' === substr( strtolower( $after ), -5 ) ) {
				++$converted;
			} else {
				++$errors;
				$messages[] = sprintf( '#%d : %s', $attachment_id, __( 'Conversion non appliquée', 'anrhpub_theme' ) );
			}
		} else {
			anrhpub_convert_attachment_to_webp( (int) $attachment_id );
			++$converted;
		}
	}

	return array(
		'processed' => $processed,
		'converted' => $converted,
		'errors'    => $errors,
		'remaining' => max( 0, (int) $query->found_posts - $offset - $processed ),
		'messages'  => $messages,
	);
}

/**
 * Chemins d’images statiques du thème.
 *
 * @return array<int, string>
 */
function anrhpub_get_theme_static_image_paths() {
	$paths = array();
	$roots = array(
		ANRHPUB_THEME_DIR . '/assets/images',
		ANRHPUB_THEME_DIR . '/assets/img',
	);
	$extensions = array( 'jpg', 'jpeg', 'png', 'gif' );

	foreach ( $roots as $root ) {
		if ( ! is_dir( $root ) ) {
			continue;
		}

		try {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS )
			);
		} catch ( Exception $e ) {
			continue;
		}

		foreach ( $iterator as $file ) {
			if ( ! $file->isFile() ) {
				continue;
			}

			$ext = strtolower( $file->getExtension() );
			if ( in_array( $ext, $extensions, true ) ) {
				$paths[] = $file->getPathname();
			}
		}
	}

	return array_values( array_unique( array_filter( $paths, 'anrhpub_is_convertible_image_path' ) ) );
}

/**
 * Convertit les images statiques du thème.
 *
 * @return array{converted: int, errors: int, messages: array<int, string>}
 */
function anrhpub_convert_theme_static_images_to_webp() {
	$converted = 0;
	$errors    = 0;
	$messages  = array();

	foreach ( anrhpub_get_theme_static_image_paths() as $path ) {
		$result = anrhpub_replace_path_with_webp( $path );

		if ( is_wp_error( $result ) ) {
			++$errors;
			$messages[] = basename( $path ) . ' : ' . $result->get_error_message();
		} else {
			++$converted;
		}
	}

	return array(
		'converted' => $converted,
		'errors'    => $errors,
		'messages'  => $messages,
	);
}
