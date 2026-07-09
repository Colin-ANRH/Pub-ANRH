<?php
/**
 * SEO — metabox éditeur.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta box SEO.
 */
function anrhpub_seo_meta_box() {
	$types = array( 'page', 'post', 'anr_product' );

	foreach ( $types as $type ) {
		add_meta_box(
			'anrhpub_seo',
			__( 'Référencement (SEO)', 'anrhpub_theme' ),
			'anrhpub_seo_meta_box_render',
			$type,
			'normal',
			'low'
		);
	}
}
add_action( 'add_meta_boxes', 'anrhpub_seo_meta_box' );

/**
 * @param WP_Post $post Post.
 */
function anrhpub_seo_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_seo', 'anrhpub_seo_nonce' );
	$title   = get_post_meta( $post->ID, ANRHPUB_SEO_TITLE_META, true );
	$desc    = get_post_meta( $post->ID, ANRHPUB_SEO_DESC_META, true );
	$noindex = (bool) get_post_meta( $post->ID, ANRHPUB_SEO_NOINDEX_META, true );
	?>
	<p>
		<label for="anrhpub_seo_title"><strong><?php esc_html_e( 'Titre SEO', 'anrhpub_theme' ); ?></strong></label>
		<input type="text" class="widefat" name="anrhpub_seo_title" id="anrhpub_seo_title" value="<?php echo esc_attr( (string) $title ); ?>" maxlength="70" />
	</p>
	<p>
		<label for="anrhpub_seo_description"><strong><?php esc_html_e( 'Meta description', 'anrhpub_theme' ); ?></strong></label>
		<textarea class="widefat" name="anrhpub_seo_description" id="anrhpub_seo_description" rows="3" maxlength="160"><?php echo esc_textarea( (string) $desc ); ?></textarea>
	</p>
	<p>
		<label><input type="checkbox" name="anrhpub_seo_noindex" value="1" <?php checked( $noindex ); ?> />
		<?php esc_html_e( 'Ne pas indexer (noindex)', 'anrhpub_theme' ); ?></label>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function anrhpub_save_seo_meta_box( $post_id ) {
	if ( ! isset( $_POST['anrhpub_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_seo_nonce'] ) ), 'anrhpub_save_seo' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, ANRHPUB_SEO_TITLE_META, isset( $_POST['anrhpub_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_seo_title'] ) ) : '' );
	update_post_meta( $post_id, ANRHPUB_SEO_DESC_META, isset( $_POST['anrhpub_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['anrhpub_seo_description'] ) ) : '' );
	update_post_meta( $post_id, ANRHPUB_SEO_NOINDEX_META, ! empty( $_POST['anrhpub_seo_noindex'] ) ? '1' : '' );
}
add_action( 'save_post', 'anrhpub_save_seo_meta_box' );
