<?php
/**
 * Admin — gestion WebP (réglages + conversion lot).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Menu admin.
 */
function anrhpub_webp_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=anr_product',
		__( 'Images WebP', 'anrhpub_theme' ),
		__( 'Images WebP', 'anrhpub_theme' ),
		'manage_options',
		'anrhpub-webp-images',
		'anrhpub_render_webp_admin_page'
	);
}
add_action( 'admin_menu', 'anrhpub_webp_admin_menu', 26 );

/**
 * Sauvegarde réglages.
 */
function anrhpub_webp_save_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( empty( $_POST['anrhpub_webp_settings_submit'] ) ) {
		return;
	}

	check_admin_referer( 'anrhpub_webp_settings' );

	$quality = isset( $_POST['anrhpub_webp_quality'] ) ? (int) $_POST['anrhpub_webp_quality'] : 82;
	$quality = max( 50, min( 95, $quality ) );

	update_option( ANRHPUB_WEBP_QUALITY_OPTION, $quality );
	update_option( ANRHPUB_WEBP_DELETE_ORIGINAL_OPTION, ! empty( $_POST['anrhpub_webp_delete_original'] ) ? 1 : 0 );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'anrhpub-webp-images',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'anrhpub_webp_save_settings' );

/**
 * Scripts admin.
 *
 * @param string $hook Hook.
 */
function anrhpub_webp_admin_assets( $hook ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['page'] ) || 'anrhpub-webp-images' !== $_GET['page'] ) {
		return;
	}

	wp_enqueue_script(
		'anrhpub-webp-admin',
		ANRHPUB_THEME_URI . '/assets/js/webp-admin.js',
		array(),
		ANRHPUB_THEME_VERSION,
		true
	);

	wp_localize_script(
		'anrhpub-webp-admin',
		'anrhpubWebpAdmin',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'anrhpub_webp_admin' ),
			'i18n'    => array(
				'running'  => __( 'Conversion en cours…', 'anrhpub_theme' ),
				'done'     => __( 'Conversion terminée.', 'anrhpub_theme' ),
				'error'    => __( 'Erreur lors de la conversion.', 'anrhpub_theme' ),
				'progress' => __( '%1$d traitées — %2$d restantes', 'anrhpub_theme' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'anrhpub_webp_admin_assets' );

/**
 * AJAX — lot médiathèque.
 */
function anrhpub_ajax_webp_batch() {
	check_ajax_referer( 'anrhpub_webp_admin', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Accès refusé.', 'anrhpub_theme' ) ), 403 );
	}

	if ( ! anrhpub_webp_is_available() ) {
		wp_send_json_error( array( 'message' => __( 'WebP non disponible sur ce serveur.', 'anrhpub_theme' ) ), 400 );
	}

	$offset = isset( $_POST['offset'] ) ? (int) $_POST['offset'] : 0;
	$result = anrhpub_webp_batch_convert_attachments( $offset, 12 );

	wp_send_json_success(
		array(
			'processed' => $result['processed'],
			'converted' => $result['converted'],
			'errors'    => $result['errors'],
			'remaining' => anrhpub_count_non_webp_attachments(),
			'messages'  => $result['messages'],
			'next_offset' => $offset + $result['processed'],
			'done'      => 0 === $result['processed'] || 0 === anrhpub_count_non_webp_attachments(),
		)
	);
}
add_action( 'wp_ajax_anrhpub_webp_batch', 'anrhpub_ajax_webp_batch' );

/**
 * AJAX — assets thème.
 */
function anrhpub_ajax_webp_theme_assets() {
	check_ajax_referer( 'anrhpub_webp_admin', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Accès refusé.', 'anrhpub_theme' ) ), 403 );
	}

	if ( ! anrhpub_webp_is_available() ) {
		wp_send_json_error( array( 'message' => __( 'WebP non disponible sur ce serveur.', 'anrhpub_theme' ) ), 400 );
	}

	$result = anrhpub_convert_theme_static_images_to_webp();

	wp_send_json_success( $result );
}
add_action( 'wp_ajax_anrhpub_webp_theme_assets', 'anrhpub_ajax_webp_theme_assets' );

/**
 * Page admin.
 */
function anrhpub_render_webp_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$available   = anrhpub_webp_is_available();
	$remaining   = anrhpub_count_non_webp_attachments();
	$theme_files = anrhpub_get_theme_static_image_paths();
	$quality     = anrhpub_get_webp_quality();
	$delete_orig = anrhpub_webp_should_delete_original();
	$updated     = isset( $_GET['updated'] ) && '1' === $_GET['updated'];
	?>
	<div class="wrap anrhpub-webp-admin">
		<h1><?php esc_html_e( 'Images WebP', 'anrhpub_theme' ); ?></h1>

		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Réglages enregistrés.', 'anrhpub_theme' ); ?></p></div>
		<?php endif; ?>

		<?php if ( ! $available ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'La conversion WebP nécessite PHP GD (imagewebp) ou l’extension Imagick. Contactez votre hébergeur.', 'anrhpub_theme' ); ?></p></div>
		<?php else : ?>
			<div class="notice notice-info"><p><?php esc_html_e( 'Les nouveaux uploads (JPEG, PNG, GIF) sont automatiquement convertis en WebP.', 'anrhpub_theme' ); ?></p></div>
		<?php endif; ?>

		<div class="card" style="max-width:720px;padding:1rem 1.25rem;margin-top:1rem;">
			<h2 style="margin-top:0;"><?php esc_html_e( 'État', 'anrhpub_theme' ); ?></h2>
			<ul style="list-style:disc;padding-left:1.25rem;">
				<li>
					<?php
					echo esc_html(
						$available
							? __( 'Serveur : conversion WebP disponible', 'anrhpub_theme' )
							: __( 'Serveur : conversion WebP indisponible', 'anrhpub_theme' )
					);
					?>
				</li>
				<li>
					<?php
					printf(
						/* translators: %d: attachment count */
						esc_html__( 'Médiathèque : %d image(s) encore en JPEG/PNG/GIF', 'anrhpub_theme' ),
						(int) $remaining
					);
					?>
				</li>
				<li>
					<?php
					printf(
						/* translators: %d: file count */
						esc_html__( 'Thème : %d fichier(s) statique(s) à convertir', 'anrhpub_theme' ),
						count( $theme_files )
					);
					?>
				</li>
			</ul>
		</div>

		<form method="post" action="" style="max-width:720px;margin-top:1.25rem;">
			<?php wp_nonce_field( 'anrhpub_webp_settings' ); ?>
			<h2><?php esc_html_e( 'Réglages', 'anrhpub_theme' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="anrhpub-webp-quality"><?php esc_html_e( 'Qualité WebP', 'anrhpub_theme' ); ?></label></th>
					<td>
						<input type="number" min="50" max="95" id="anrhpub-webp-quality" name="anrhpub_webp_quality" value="<?php echo esc_attr( (string) $quality ); ?>" />
						<p class="description"><?php esc_html_e( '50 (léger) à 95 (meilleure qualité). Recommandé : 82.', 'anrhpub_theme' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Fichiers source', 'anrhpub_theme' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="anrhpub_webp_delete_original" value="1" <?php checked( $delete_orig ); ?> />
							<?php esc_html_e( 'Supprimer les JPEG/PNG/GIF après conversion', 'anrhpub_theme' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Enregistrer les réglages', 'anrhpub_theme' ), 'secondary', 'anrhpub_webp_settings_submit' ); ?>
		</form>

		<div class="card" style="max-width:720px;padding:1rem 1.25rem;margin-top:1.25rem;">
			<h2 style="margin-top:0;"><?php esc_html_e( 'Convertir les images existantes', 'anrhpub_theme' ); ?></h2>
			<p><?php esc_html_e( 'Lance la conversion de toutes les images de la médiathèque vers WebP (par lots).', 'anrhpub_theme' ); ?></p>
			<p>
				<button type="button" class="button button-primary" id="anrhpub-webp-batch" <?php disabled( ! $available || $remaining < 1 ); ?>>
					<?php esc_html_e( 'Convertir la médiathèque', 'anrhpub_theme' ); ?>
				</button>
				<button type="button" class="button" id="anrhpub-webp-theme" <?php disabled( ! $available || count( $theme_files ) < 1 ); ?>>
					<?php esc_html_e( 'Convertir les images du thème', 'anrhpub_theme' ); ?>
				</button>
			</p>
			<p id="anrhpub-webp-log" class="description" style="min-height:1.5em;margin-top:0.75rem;" aria-live="polite"></p>
		</div>
	</div>
	<?php
}

/**
 * Notice admin si WebP indisponible.
 */
function anrhpub_webp_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) || anrhpub_webp_is_available() ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || false === strpos( (string) $screen->id, 'anr_product' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p>';
	echo esc_html__( 'ANRH Peyruis : la conversion WebP est désactivée (GD/Imagick manquant).', 'anrhpub_theme' );
	echo ' <a href="' . esc_url( admin_url( 'admin.php?page=anrhpub-webp-images' ) ) . '">' . esc_html__( 'En savoir plus', 'anrhpub_theme' ) . '</a>';
	echo '</p></div>';
}
add_action( 'admin_notices', 'anrhpub_webp_admin_notice' );
