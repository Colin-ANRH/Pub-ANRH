<?php
/**
 * Images de démonstration pour les produits fictifs.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_IMAGES_VERSION', 3 );

/**
 * URLs d’aperçu (Unsplash) par référence produit.
 *
 * @return array<string, string>
 */
function anrhpub_demo_product_image_urls() {
	return array(
		'ST14'      => 'https://loremflickr.com/800/800/pen?lock=14',
		'DIF2'      => 'https://loremflickr.com/800/800/perfume,diffuser?lock=2',
		'SETOUTIL6' => 'https://loremflickr.com/800/800/tools?lock=6',
		'COF5'      => 'https://loremflickr.com/800/800/mug,coffee?lock=5',
		'SACDO7'    => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=800&h=800&fit=crop&q=80',
		'CONGRES1'  => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&h=800&fit=crop&q=80',
		'BOUTISOB1' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=800&h=800&fit=crop&q=80',
		'ST10'      => 'https://loremflickr.com/800/800/bottle,thermos?lock=10',
		'HUB4'      => 'https://loremflickr.com/800/800/usb,hub?lock=4',
		'EN11'      => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800&h=800&fit=crop&q=80',
		'MONTRE3'   => 'https://loremflickr.com/800/800/watch?lock=3',
		'PCL13'     => 'https://loremflickr.com/800/800/laptop?lock=13',
		'PCA1'      => 'https://loremflickr.com/800/800/notebook,pen?lock=1',
		'SOFT3'     => 'https://images.unsplash.com/photo-1544022613-e87ca75a784a?w=800&h=800&fit=crop&q=80',
		'TEESPORT'  => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&h=800&fit=crop&q=80',
		'TEE19V'    => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=800&h=800&fit=crop&q=80',
		'NOUV01'    => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=800&h=800&fit=crop&q=80',
		'NOUV02'    => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?w=800&h=800&fit=crop&q=80',
		'NOUV03'    => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=800&h=800&fit=crop&q=80',
		'NOUV04'    => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=800&h=800&fit=crop&q=80',
	);
}

/**
 * Télécharge et attache l’image mise en avant d’un produit.
 *
 * @param int    $post_id Post ID.
 * @param string $url     Image URL.
 * @param string $title   Attachment title.
 * @return bool
 */
function anrhpub_attach_product_image( $post_id, $url, $title, $force = false ) {
	if ( ! $force && has_post_thumbnail( $post_id ) ) {
		return true;
	}

	if ( $force && has_post_thumbnail( $post_id ) ) {
		$old = get_post_thumbnail_id( $post_id );
		if ( $old ) {
			wp_delete_attachment( $old, true );
		}
	}

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment_id = media_sideload_image( $url, $post_id, $title, 'id' );

	if ( is_wp_error( $attachment_id ) ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 30,
				'sslverify' => false,
				'headers'   => array(
					'User-Agent' => 'Mozilla/5.0 (compatible; ANRHPUB/1.0)',
				),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return false;
		}

		$upload = wp_upload_bits( sanitize_file_name( $title ) . '.jpg', null, $body );
		if ( ! empty( $upload['error'] ) ) {
			return false;
		}

		$filetype = wp_check_filetype( $upload['file'], null );
		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'] ?: 'image/jpeg',
				'post_title'     => $title,
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file'],
			$post_id
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			return false;
		}

		wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
	}

	set_post_thumbnail( $post_id, (int) $attachment_id );
	return true;
}

/**
 * Attache les images à tous les produits de démo.
 */
function anrhpub_seed_product_images() {
	$urls = anrhpub_demo_product_image_urls();

	foreach ( $urls as $ref => $url ) {
		$posts = get_posts(
			array(
				'post_type'      => 'anr_product',
				'meta_key'       => 'anr_reference',
				'meta_value'     => $ref,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( empty( $posts ) ) {
			continue;
		}

		anrhpub_attach_product_image( (int) $posts[0], $url, 'ANRH demo ' . $ref, true );
	}
}

/**
 * Migration images (une fois par version).
 */
function anrhpub_maybe_seed_product_images() {
	if ( (int) get_option( 'anrhpub_images_version', 0 ) >= ANRHPUB_IMAGES_VERSION ) {
		return;
	}

	anrhpub_seed_product_images();
	update_option( 'anrhpub_images_version', ANRHPUB_IMAGES_VERSION );
}
add_action( 'init', 'anrhpub_maybe_seed_product_images', 25 );
