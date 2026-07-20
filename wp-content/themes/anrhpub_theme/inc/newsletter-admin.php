<?php
/**
 * Admin — newsletter (abonnés + réglages bloc accueil).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Page réglages sous Catalogue.
 */
function anrhpub_newsletter_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=anr_product',
		__( 'Réglages newsletter', 'anrhpub_theme' ),
		__( 'Newsletter (accueil)', 'anrhpub_theme' ),
		'manage_options',
		'anrhpub-newsletter-settings',
		'anrhpub_render_newsletter_settings_page'
	);
}
add_action( 'admin_menu', 'anrhpub_newsletter_admin_menu', 25 );

/**
 * Sauvegarde réglages.
 */
function anrhpub_save_newsletter_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( empty( $_POST['anrhpub_newsletter_settings_submit'] ) ) {
		return;
	}

	check_admin_referer( 'anrhpub_newsletter_settings' );

	$defaults = anrhpub_get_newsletter_default_settings();
	$input    = isset( $_POST['anrhpub_newsletter'] ) && is_array( $_POST['anrhpub_newsletter'] )
		? wp_unslash( $_POST['anrhpub_newsletter'] )
		: array();

	$settings = array(
		'enabled'         => ! empty( $input['enabled'] ),
		'kicker'          => isset( $input['kicker'] ) ? sanitize_text_field( $input['kicker'] ) : $defaults['kicker'],
		'title'           => isset( $input['title'] ) ? sanitize_text_field( $input['title'] ) : $defaults['title'],
		'title_em'        => isset( $input['title_em'] ) ? sanitize_text_field( $input['title_em'] ) : $defaults['title_em'],
		'text'            => isset( $input['text'] ) ? sanitize_textarea_field( $input['text'] ) : $defaults['text'],
		'button_label'    => isset( $input['button_label'] ) ? sanitize_text_field( $input['button_label'] ) : $defaults['button_label'],
		'consent_text'    => isset( $input['consent_text'] ) ? sanitize_textarea_field( $input['consent_text'] ) : $defaults['consent_text'],
		'success_message' => isset( $input['success_message'] ) ? sanitize_text_field( $input['success_message'] ) : $defaults['success_message'],
		'notify_email'    => isset( $input['notify_email'] ) ? sanitize_email( $input['notify_email'] ) : '',
	);

	update_option( ANRHPUB_NEWSLETTER_SETTINGS_OPTION, $settings );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'anrhpub-newsletter-settings',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'anrhpub_save_newsletter_settings' );

/**
 * Rendu page réglages.
 */
function anrhpub_render_newsletter_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = anrhpub_get_newsletter_settings();
	$list_url = admin_url( 'edit.php?post_type=' . ANRHPUB_NEWSLETTER_CPT );
	$updated  = isset( $_GET['updated'] ) && '1' === $_GET['updated'];
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Newsletter — bloc accueil', 'anrhpub_theme' ); ?></h1>

		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Réglages enregistrés.', 'anrhpub_theme' ); ?></p></div>
		<?php endif; ?>

		<p>
			<?php
			printf(
				/* translators: %s: subscribers list URL */
				wp_kses_post( __( 'Gérer les <a href="%s">abonnés inscrits</a> depuis la liste Newsletter.', 'anrhpub_theme' ) ),
				esc_url( $list_url )
			);
			?>
		</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'anrhpub_newsletter_settings' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Bloc actif', 'anrhpub_theme' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="anrhpub_newsletter[enabled]" value="1" <?php checked( $settings['enabled'] ); ?> />
							<?php esc_html_e( 'Afficher le formulaire sur la page d’accueil', 'anrhpub_theme' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="nl-kicker"><?php esc_html_e( 'Sur-titre', 'anrhpub_theme' ); ?></label></th>
					<td><input type="text" class="large-text" id="nl-kicker" name="anrhpub_newsletter[kicker]" value="<?php echo esc_attr( (string) $settings['kicker'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="nl-title"><?php esc_html_e( 'Titre (partie 1)', 'anrhpub_theme' ); ?></label></th>
					<td><input type="text" class="large-text" id="nl-title" name="anrhpub_newsletter[title]" value="<?php echo esc_attr( (string) $settings['title'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="nl-title-em"><?php esc_html_e( 'Titre (accent italique)', 'anrhpub_theme' ); ?></label></th>
					<td><input type="text" class="large-text" id="nl-title-em" name="anrhpub_newsletter[title_em]" value="<?php echo esc_attr( (string) $settings['title_em'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="nl-text"><?php esc_html_e( 'Texte', 'anrhpub_theme' ); ?></label></th>
					<td><textarea class="large-text" rows="4" id="nl-text" name="anrhpub_newsletter[text]"><?php echo esc_textarea( (string) $settings['text'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="nl-button"><?php esc_html_e( 'Libellé bouton', 'anrhpub_theme' ); ?></label></th>
					<td><input type="text" class="regular-text" id="nl-button" name="anrhpub_newsletter[button_label]" value="<?php echo esc_attr( (string) $settings['button_label'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="nl-consent"><?php esc_html_e( 'Texte consentement', 'anrhpub_theme' ); ?></label></th>
					<td><textarea class="large-text" rows="3" id="nl-consent" name="anrhpub_newsletter[consent_text]"><?php echo esc_textarea( (string) $settings['consent_text'] ); ?></textarea></td>
				</tr>
				<tr>
					<th scope="row"><label for="nl-success"><?php esc_html_e( 'Message de succès', 'anrhpub_theme' ); ?></label></th>
					<td><input type="text" class="large-text" id="nl-success" name="anrhpub_newsletter[success_message]" value="<?php echo esc_attr( (string) $settings['success_message'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="nl-notify"><?php esc_html_e( 'E-mail de notification', 'anrhpub_theme' ); ?></label></th>
					<td>
						<input type="email" class="regular-text" id="nl-notify" name="anrhpub_newsletter[notify_email]" value="<?php echo esc_attr( (string) $settings['notify_email'] ); ?>" placeholder="<?php echo esc_attr( anrhpub_get_contact_email() ); ?>" />
						<p class="description"><?php esc_html_e( 'Optionnel : recevoir un e-mail à chaque nouvelle inscription. Laissez vide pour utiliser l’e-mail contact du site.', 'anrhpub_theme' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Enregistrer', 'anrhpub_theme' ), 'primary', 'anrhpub_newsletter_settings_submit' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Masquer « Ajouter » sur la liste abonnés.
 */
function anrhpub_newsletter_hide_add_new() {
	global $submenu;

	if ( isset( $submenu['edit.php?post_type=anr_product'] ) ) {
		foreach ( $submenu['edit.php?post_type=anr_product'] as $key => $item ) {
			if ( isset( $item[2] ) && 'post-new.php?post_type=' . ANRHPUB_NEWSLETTER_CPT === $item[2] ) {
				unset( $submenu['edit.php?post_type=anr_product'][ $key ] );
			}
		}
	}
}
add_action( 'admin_menu', 'anrhpub_newsletter_hide_add_new', 999 );

/**
 * Colonnes liste abonnés.
 *
 * @param array<string, string> $columns Colonnes.
 * @return array<string, string>
 */
function anrhpub_newsletter_columns( $columns ) {
	return array(
		'cb'            => $columns['cb'] ?? '<input type="checkbox" />',
		'title'         => __( 'E-mail', 'anrhpub_theme' ),
		'nl_status'     => __( 'Statut', 'anrhpub_theme' ),
		'nl_subscribed' => __( 'Inscription', 'anrhpub_theme' ),
		'date'          => __( 'Modifié', 'anrhpub_theme' ),
	);
}
add_filter( 'manage_' . ANRHPUB_NEWSLETTER_CPT . '_posts_columns', 'anrhpub_newsletter_columns' );

/**
 * Contenu colonnes.
 *
 * @param string $column  Colonne.
 * @param int    $post_id Post ID.
 */
function anrhpub_newsletter_column_content( $column, $post_id ) {
	if ( 'nl_status' === $column ) {
		$status = anrhpub_get_newsletter_status( $post_id );
		echo 'active' === $status
			? '<span style="color:#1a7f37;font-weight:600;">' . esc_html__( 'Actif', 'anrhpub_theme' ) . '</span>'
			: '<span style="color:#996800;">' . esc_html__( 'Désinscrit', 'anrhpub_theme' ) . '</span>';
		return;
	}

	if ( 'nl_subscribed' === $column ) {
		$at = get_post_meta( $post_id, 'anr_subscribed_at', true );
		echo $at ? esc_html( wp_date( 'd/m/Y H:i', strtotime( $at ) ) ) : '—';
	}
}
add_action( 'manage_' . ANRHPUB_NEWSLETTER_CPT . '_posts_custom_column', 'anrhpub_newsletter_column_content', 10, 2 );

/**
 * Meta box statut abonné.
 */
function anrhpub_newsletter_meta_box() {
	add_meta_box(
		'anrhpub_newsletter_details',
		__( 'Abonnement', 'anrhpub_theme' ),
		'anrhpub_newsletter_meta_box_render',
		ANRHPUB_NEWSLETTER_CPT,
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_newsletter_meta_box' );

/**
 * Rendu meta box.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_newsletter_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_newsletter_sub', 'anrhpub_newsletter_nonce' );

	$status = anrhpub_get_newsletter_status( $post->ID );
	$at     = get_post_meta( $post->ID, 'anr_subscribed_at', true );
	?>
	<p>
		<label for="anr_newsletter_status"><strong><?php esc_html_e( 'Statut', 'anrhpub_theme' ); ?></strong></label><br />
		<select name="anr_newsletter_status" id="anr_newsletter_status">
			<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Actif', 'anrhpub_theme' ); ?></option>
			<option value="unsubscribed" <?php selected( $status, 'unsubscribed' ); ?>><?php esc_html_e( 'Désinscrit', 'anrhpub_theme' ); ?></option>
		</select>
	</p>
	<?php if ( $at ) : ?>
		<p class="description"><?php printf( esc_html__( 'Inscrit le %s', 'anrhpub_theme' ), esc_html( wp_date( 'd/m/Y à H:i', strtotime( $at ) ) ) ); ?></p>
	<?php endif; ?>
	<p class="description"><?php esc_html_e( 'L’e-mail est le titre de la fiche. Les inscriptions viennent du formulaire accueil.', 'anrhpub_theme' ); ?></p>
	<?php
}

/**
 * Sauvegarde meta abonné.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_save_newsletter_subscriber_admin( $post_id ) {
	if ( ! isset( $_POST['anrhpub_newsletter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_newsletter_nonce'] ) ), 'anrhpub_save_newsletter_sub' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$status = isset( $_POST['anr_newsletter_status'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_newsletter_status'] ) ) : 'active';

	update_post_meta( $post_id, 'anr_newsletter_status', 'unsubscribed' === $status ? 'unsubscribed' : 'active' );
}
add_action( 'save_post_' . ANRHPUB_NEWSLETTER_CPT, 'anrhpub_save_newsletter_subscriber_admin' );

/**
 * Lien export CSV sur la liste.
 *
 * @param string $which top|bottom.
 */
function anrhpub_newsletter_export_link( $which ) {
	if ( 'top' !== $which ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || ANRHPUB_NEWSLETTER_CPT !== $screen->post_type ) {
		return;
	}

	$url = wp_nonce_url(
		admin_url( 'admin-post.php?action=anrhpub_export_newsletter' ),
		'anrhpub_export_newsletter'
	);
	?>
	<div class="alignleft actions">
		<a class="button" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Exporter CSV', 'anrhpub_theme' ); ?></a>
	</div>
	<?php
}
add_action( 'manage_posts_extra_tablenav', 'anrhpub_newsletter_export_link' );

/**
 * Export CSV abonnés actifs.
 */
function anrhpub_export_newsletter_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'anrhpub_theme' ) );
	}

	check_admin_referer( 'anrhpub_export_newsletter' );

	$posts = get_posts(
		array(
			'post_type'      => ANRHPUB_NEWSLETTER_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	$filename = 'newsletter-anrh-peyruis-' . gmdate( 'Y-m-d' ) . '.csv';

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'email', 'statut', 'inscription', 'modifié' ), ';' );

	foreach ( $posts as $post ) {
		$at = get_post_meta( $post->ID, 'anr_subscribed_at', true );
		fputcsv(
			$out,
			array(
				$post->post_title,
				anrhpub_get_newsletter_status( $post->ID ),
				$at ? wp_date( 'Y-m-d H:i', strtotime( $at ) ) : '',
				get_the_modified_date( 'Y-m-d H:i', $post ),
			),
			';'
		);
	}

	fclose( $out );
	exit;
}
add_action( 'admin_post_anrhpub_export_newsletter', 'anrhpub_export_newsletter_csv' );
