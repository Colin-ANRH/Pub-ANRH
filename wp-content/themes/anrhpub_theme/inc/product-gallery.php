<?php
/**
 * Galerie multi-images des produits catalogue.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_PRODUCT_GALLERY_META', 'anr_product_gallery' );
define( 'ANRHPUB_PRODUCT_GALLERY_MAX', 20 );

/**
 * IDs des images de galerie (hors image mise en avant).
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
function anrhpub_get_product_gallery_ids( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	if ( $post_id <= 0 ) {
		return array();
	}

	$raw = get_post_meta( $post_id, ANRHPUB_PRODUCT_GALLERY_META, true );
	$ids = array();

	if ( is_string( $raw ) && '' !== $raw ) {
		$decoded = json_decode( $raw, true );
		$raw     = is_array( $decoded ) ? $decoded : explode( ',', $raw );
	}

	if ( is_array( $raw ) ) {
		foreach ( $raw as $id ) {
			$id = absint( $id );
			if ( $id > 0 && wp_attachment_is_image( $id ) ) {
				$ids[] = $id;
			}
		}
	}

	$thumb = (int) get_post_thumbnail_id( $post_id );
	$ids   = array_values( array_unique( $ids ) );

	if ( $thumb > 0 ) {
		$ids = array_values( array_diff( $ids, array( $thumb ) ) );
	}

	return array_slice( $ids, 0, ANRHPUB_PRODUCT_GALLERY_MAX );
}

/**
 * Toutes les images produit : mise en avant puis galerie.
 *
 * @param int $post_id Post ID.
 * @return int[]
 */
function anrhpub_get_product_image_ids( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$ids     = array();

	$thumb = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb > 0 ) {
		$ids[] = $thumb;
	}

	foreach ( anrhpub_get_product_gallery_ids( $post_id ) as $id ) {
		if ( ! in_array( $id, $ids, true ) ) {
			$ids[] = $id;
		}
	}

	return $ids;
}

/**
 * URL d’une image produit.
 *
 * @param int    $post_id Post ID.
 * @param string $size    Taille WordPress.
 * @return string
 */
function anrhpub_get_product_image_url( $post_id = 0, $size = 'large' ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$ids     = anrhpub_get_product_image_ids( $post_id );

	if ( empty( $ids ) ) {
		return '';
	}

	$url = wp_get_attachment_image_url( $ids[0], $size );

	return $url ? $url : '';
}

/**
 * Meta box — galerie.
 */
function anrhpub_product_gallery_meta_box() {
	add_meta_box(
		'anrhpub_product_gallery',
		__( 'Galerie photos', 'anrhpub_theme' ),
		'anrhpub_product_gallery_meta_box_render',
		'anr_product',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_product_gallery_meta_box' );

/**
 * Rendu meta box galerie.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_product_gallery_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_product_gallery', 'anrhpub_product_gallery_nonce' );

	$ids = array_merge(
		array_filter( array( (int) get_post_thumbnail_id( $post->ID ) ) ),
		anrhpub_get_product_gallery_ids( $post->ID )
	);
	$ids = array_values( array_unique( $ids ) );
	?>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'Image mise en avant = photo principale. Ajoutez d’autres vues ci-dessous.', 'anrhpub_theme' ); ?>
	</p>
	<input type="hidden" id="anr_product_gallery_ids" name="anr_product_gallery_ids" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>" />
	<ul id="anrhpub-gallery-preview" class="anrhpub-gallery-preview">
		<?php foreach ( $ids as $att_id ) : ?>
			<?php
			$src = wp_get_attachment_image_url( $att_id, 'thumbnail' );
			if ( ! $src ) {
				continue;
			}
			$is_thumb = (int) get_post_thumbnail_id( $post->ID ) === (int) $att_id;
			?>
			<li class="anrhpub-gallery-preview__item" data-id="<?php echo esc_attr( (string) $att_id ); ?>">
				<img src="<?php echo esc_url( $src ); ?>" alt="" width="60" height="60" />
				<?php if ( $is_thumb ) : ?>
					<span class="anrhpub-gallery-preview__badge"><?php esc_html_e( 'Principale', 'anrhpub_theme' ); ?></span>
				<?php endif; ?>
				<button type="button" class="anrhpub-gallery-preview__remove" aria-label="<?php esc_attr_e( 'Retirer', 'anrhpub_theme' ); ?>">&times;</button>
			</li>
		<?php endforeach; ?>
	</ul>
	<p>
		<button type="button" class="button" id="anrhpub-gallery-add">
			<?php esc_html_e( 'Ajouter / modifier les images', 'anrhpub_theme' ); ?>
		</button>
	</p>
	<?php
}

/**
 * Scripts admin galerie produit.
 *
 * @param string $hook Hook.
 */
function anrhpub_product_gallery_admin_assets( $hook ) {
	global $post_type;

	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'anr_product' !== $post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'anrhpub-product-gallery-admin',
		ANRHPUB_THEME_URI . '/assets/js/product-gallery-admin.js',
		array( 'jquery' ),
		ANRHPUB_THEME_VERSION,
		true
	);
	wp_localize_script(
		'anrhpub-product-gallery-admin',
		'anrhpubGalleryAdmin',
		array(
			'i18n' => array(
				'title'  => __( 'Images du produit', 'anrhpub_theme' ),
				'select' => __( 'Utiliser ces images', 'anrhpub_theme' ),
				'main'   => __( 'Principale', 'anrhpub_theme' ),
			),
		)
	);
	wp_add_inline_style(
		'wp-admin',
		'.anrhpub-gallery-preview{display:flex;flex-wrap:wrap;gap:8px;margin:10px 0 0;padding:0;list-style:none}
		.anrhpub-gallery-preview__item{position:relative;width:60px;height:60px;border:1px solid #c3c4c7;background:#fff}
		.anrhpub-gallery-preview__item img{width:100%;height:100%;object-fit:cover;display:block}
		.anrhpub-gallery-preview__badge{position:absolute;left:0;right:0;bottom:0;font-size:9px;line-height:1.2;text-align:center;background:rgba(0,0,0,.65);color:#fff;padding:2px}
		.anrhpub-gallery-preview__remove{position:absolute;top:-6px;right:-6px;width:18px;height:18px;padding:0;border-radius:50%;border:0;background:#b32d2e;color:#fff;cursor:pointer;line-height:1;font-size:14px}'
	);
}
add_action( 'admin_enqueue_scripts', 'anrhpub_product_gallery_admin_assets' );

/**
 * Sauvegarde galerie.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_save_product_gallery_meta( $post_id ) {
	if ( ! isset( $_POST['anrhpub_product_gallery_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_product_gallery_nonce'] ) ), 'anrhpub_save_product_gallery' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'anr_product' !== get_post_type( $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['anr_product_gallery_ids'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['anr_product_gallery_ids'] ) ) : '';
	$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );
	$ids = array_values( array_unique( $ids ) );
	$ids = array_slice( $ids, 0, ANRHPUB_PRODUCT_GALLERY_MAX + 1 );

	$valid = array();
	foreach ( $ids as $id ) {
		if ( wp_attachment_is_image( $id ) ) {
			$valid[] = $id;
		}
	}

	if ( ! empty( $valid ) ) {
		set_post_thumbnail( $post_id, $valid[0] );
		$gallery = array_slice( array_values( array_diff( $valid, array( (int) $valid[0] ) ) ), 0, ANRHPUB_PRODUCT_GALLERY_MAX );
	} else {
		delete_post_thumbnail( $post_id );
		$gallery = array();
	}

	update_post_meta( $post_id, ANRHPUB_PRODUCT_GALLERY_META, wp_json_encode( $gallery ) );
}
add_action( 'save_post_anr_product', 'anrhpub_save_product_gallery_meta' );

/**
 * Affiche la galerie sur la fiche produit.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_render_product_gallery( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$ids     = anrhpub_get_product_image_ids( $post_id );

	if ( empty( $ids ) ) {
		?>
		<div class="product-single__gallery product-single__gallery--empty">
			<div class="product-single__placeholder">
				<?php anrhpub_product_thumbnail( $post_id ); ?>
			</div>
		</div>
		<?php
		return;
	}

	$main_id  = $ids[0];
	$main_src = wp_get_attachment_image_url( $main_id, 'large' );
	$main_alt = get_post_meta( $main_id, '_wp_attachment_image_alt', true );
	$title    = get_the_title( $post_id );
	?>
	<div class="product-single__gallery product-gallery" data-product-gallery>
		<div class="product-gallery__stage">
			<?php
			echo wp_get_attachment_image(
				$main_id,
				'large',
				false,
				array(
					'class'         => 'product-single__img product-gallery__main',
					'data-gallery-main' => 'true',
					'alt'           => $main_alt ? $main_alt : $title,
					'loading'       => 'eager',
					'decoding'      => 'async',
				)
			);
			?>
		</div>
		<?php if ( count( $ids ) > 1 ) : ?>
			<div class="product-gallery__thumbs-wrap">
				<ul class="product-gallery__thumbs" role="tablist" aria-label="<?php esc_attr_e( 'Vues du produit', 'anrhpub_theme' ); ?>">
					<?php foreach ( $ids as $index => $att_id ) : ?>
						<?php
						$thumb_src = wp_get_attachment_image_url( $att_id, 'thumbnail' );
						if ( ! $thumb_src ) {
							continue;
						}
						$full_src = wp_get_attachment_image_url( $att_id, 'large' );
						$is_active = 0 === $index;
						?>
						<li role="presentation">
							<button
								type="button"
								class="product-gallery__thumb<?php echo $is_active ? ' is-active' : ''; ?>"
								role="tab"
								aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
								aria-controls="product-gallery-main"
								data-gallery-thumb
								data-image-id="<?php echo esc_attr( (string) $att_id ); ?>"
								data-src="<?php echo esc_url( $full_src ); ?>"
							>
								<img src="<?php echo esc_url( $thumb_src ); ?>" alt="" width="72" height="72" loading="lazy" decoding="async" />
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
