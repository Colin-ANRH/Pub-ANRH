<?php
/**
 * Admin — textes d’accueil « Ils nous font confiance » / « Nos partenaires ».
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_TRUST_CLIENTS_SETTINGS_OPTION', 'anrhpub_trust_clients_settings' );
define( 'ANRHPUB_TRUST_PARTNERS_SETTINGS_OPTION', 'anrhpub_trust_partners_settings' );

/**
 * Page admin (sous Catalogue).
 */
function anrhpub_trust_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=anr_product',
		__( 'Références (accueil)', 'anrhpub_theme' ),
		__( 'Références (accueil)', 'anrhpub_theme' ),
		'manage_options',
		'anrhpub-trust-settings',
		'anrhpub_render_trust_admin_page'
	);
}
add_action( 'admin_menu', 'anrhpub_trust_admin_menu', 30 );

/**
 * Défauts — clients.
 *
 * @return array<string, string>
 */
function anrhpub_default_trust_clients_settings() {
	return array(
		'kicker'         => 'Références',
		'title'          => 'Ils nous font confiance',
		'lead_prefix'    => 'Entreprises et collectivités accompagnées par l’',
		'lead_link_label'=> 'Entreprise Adaptée ANRH de Peyruis',
		'lead_suffix'    => ' — parmi plus de 272 clients.',
	);
}

/**
 * Défauts — partenaires.
 *
 * @return array<string, string>
 */
function anrhpub_default_trust_partners_settings() {
	return array(
		'kicker'         => 'Écosystème',
		'title'          => 'Nos partenaires',
		'lead_prefix'    => 'Institutions et réseaux qui accompagnent l’',
		'lead_link_label'=> 'Entreprise Adaptée ANRH de Peyruis',
		'lead_suffix'    => ' dans l’insertion professionnelle et l’emploi inclusif.',
	);
}

/**
 * Récupération settings clients (defaults mergés).
 *
 * @return array<string, string>
 */
function anrhpub_get_trust_clients_settings() {
	$defaults = anrhpub_default_trust_clients_settings();
	$stored   = get_option( ANRHPUB_TRUST_CLIENTS_SETTINGS_OPTION, array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	return array_merge( $defaults, $stored );
}

/**
 * Récupération settings partenaires (defaults mergés).
 *
 * @return array<string, string>
 */
function anrhpub_get_trust_partners_settings() {
	$defaults = anrhpub_default_trust_partners_settings();
	$stored   = get_option( ANRHPUB_TRUST_PARTNERS_SETTINGS_OPTION, array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	return array_merge( $defaults, $stored );
}

/**
 * Page admin — rendu.
 */
function anrhpub_render_trust_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$clients  = anrhpub_get_trust_clients_settings();
	$partners = anrhpub_get_trust_partners_settings();

	if ( isset( $_POST['anrhpub_trust_settings_nonce'] ) && wp_verify_nonce( (string) wp_unslash( $_POST['anrhpub_trust_settings_nonce'] ), 'anrhpub_trust_settings_save' ) ) {
		$clients_new = array(
			'kicker'          => isset( $_POST['anrhpub_trust_clients_kicker'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_trust_clients_kicker'] ) ) : $clients['kicker'],
			'title'           => isset( $_POST['anrhpub_trust_clients_title'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_trust_clients_title'] ) ) : $clients['title'],
			'lead_prefix'     => isset( $_POST['anrhpub_trust_clients_lead_prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_trust_clients_lead_prefix'] ) ) : $clients['lead_prefix'],
			'lead_link_label' => isset( $_POST['anrhpub_trust_clients_lead_link_label'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_trust_clients_lead_link_label'] ) ) : $clients['lead_link_label'],
			'lead_suffix'     => isset( $_POST['anrhpub_trust_clients_lead_suffix'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_trust_clients_lead_suffix'] ) ) : $clients['lead_suffix'],
		);

		$partners_new = array(
			'kicker'          => isset( $_POST['anrhpub_trust_partners_kicker'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_trust_partners_kicker'] ) ) : $partners['kicker'],
			'title'           => isset( $_POST['anrhpub_trust_partners_title'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_trust_partners_title'] ) ) : $partners['title'],
			'lead_prefix'     => isset( $_POST['anrhpub_trust_partners_lead_prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_trust_partners_lead_prefix'] ) ) : $partners['lead_prefix'],
			'lead_link_label' => isset( $_POST['anrhpub_trust_partners_lead_link_label'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_trust_partners_lead_link_label'] ) ) : $partners['lead_link_label'],
			'lead_suffix'     => isset( $_POST['anrhpub_trust_partners_lead_suffix'] ) ? sanitize_text_field( wp_unslash( $_POST['anrhpub_trust_partners_lead_suffix'] ) ) : $partners['lead_suffix'],
		);

		update_option( ANRHPUB_TRUST_CLIENTS_SETTINGS_OPTION, $clients_new, false );
		update_option( ANRHPUB_TRUST_PARTNERS_SETTINGS_OPTION, $partners_new, false );

		$clients  = $clients_new;
		$partners = $partners_new;

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Réglages enregistrés.', 'anrhpub_theme' ) . '</p></div>';
	}

	$peyruis_url = 'https://anrh.fr/decouvrir-notre-offre/nos-etablissements/entreprise-adaptee/anrh-peyruis/';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Références — textes accueil', 'anrhpub_theme' ); ?></h1>
		<p><?php esc_html_e( 'Les logos se gèrent via le menu « Références (logos) ». Ici, vous modifiez uniquement les textes.', 'anrhpub_theme' ); ?></p>

		<form method="post" action="">
			<?php wp_nonce_field( 'anrhpub_trust_settings_save', 'anrhpub_trust_settings_nonce' ); ?>

			<h2><?php esc_html_e( 'Ils nous font confiance (clients)', 'anrhpub_theme' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="anrhpub_trust_clients_kicker"><?php esc_html_e( 'Kicker', 'anrhpub_theme' ); ?></label></th>
					<td><input class="regular-text" type="text" id="anrhpub_trust_clients_kicker" name="anrhpub_trust_clients_kicker" value="<?php echo esc_attr( (string) $clients['kicker'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="anrhpub_trust_clients_title"><?php esc_html_e( 'Titre', 'anrhpub_theme' ); ?></label></th>
					<td><input class="regular-text" type="text" id="anrhpub_trust_clients_title" name="anrhpub_trust_clients_title" value="<?php echo esc_attr( (string) $clients['title'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="anrhpub_trust_clients_lead_prefix"><?php esc_html_e( 'Texte avant le lien', 'anrhpub_theme' ); ?></label></th>
					<td><input class="regular-text" type="text" id="anrhpub_trust_clients_lead_prefix" name="anrhpub_trust_clients_lead_prefix" value="<?php echo esc_attr( (string) $clients['lead_prefix'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="anrhpub_trust_clients_lead_link_label"><?php esc_html_e( 'Libellé du lien', 'anrhpub_theme' ); ?></label></th>
					<td><input class="regular-text" type="text" id="anrhpub_trust_clients_lead_link_label" name="anrhpub_trust_clients_lead_link_label" value="<?php echo esc_attr( (string) $clients['lead_link_label'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="anrhpub_trust_clients_lead_suffix"><?php esc_html_e( 'Texte après le lien', 'anrhpub_theme' ); ?></label></th>
					<td><input class="regular-text" type="text" id="anrhpub_trust_clients_lead_suffix" name="anrhpub_trust_clients_lead_suffix" value="<?php echo esc_attr( (string) $clients['lead_suffix'] ); ?>"></td>
				</tr>
			</table>

			<h2><?php esc_html_e( 'Nos partenaires (partenaires)', 'anrhpub_theme' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="anrhpub_trust_partners_kicker"><?php esc_html_e( 'Kicker', 'anrhpub_theme' ); ?></label></th>
					<td><input class="regular-text" type="text" id="anrhpub_trust_partners_kicker" name="anrhpub_trust_partners_kicker" value="<?php echo esc_attr( (string) $partners['kicker'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="anrhpub_trust_partners_title"><?php esc_html_e( 'Titre', 'anrhpub_theme' ); ?></label></th>
					<td><input class="regular-text" type="text" id="anrhpub_trust_partners_title" name="anrhpub_trust_partners_title" value="<?php echo esc_attr( (string) $partners['title'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="anrhpub_trust_partners_lead_prefix"><?php esc_html_e( 'Texte avant le lien', 'anrhpub_theme' ); ?></label></th>
					<td><input class="regular-text" type="text" id="anrhpub_trust_partners_lead_prefix" name="anrhpub_trust_partners_lead_prefix" value="<?php echo esc_attr( (string) $partners['lead_prefix'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="anrhpub_trust_partners_lead_link_label"><?php esc_html_e( 'Libellé du lien', 'anrhpub_theme' ); ?></label></th>
					<td><input class="regular-text" type="text" id="anrhpub_trust_partners_lead_link_label" name="anrhpub_trust_partners_lead_link_label" value="<?php echo esc_attr( (string) $partners['lead_link_label'] ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="anrhpub_trust_partners_lead_suffix"><?php esc_html_e( 'Texte après le lien', 'anrhpub_theme' ); ?></label></th>
					<td><input class="regular-text" type="text" id="anrhpub_trust_partners_lead_suffix" name="anrhpub_trust_partners_lead_suffix" value="<?php echo esc_attr( (string) $partners['lead_suffix'] ); ?>"></td>
				</tr>
			</table>

			<p>
				<input class="button button-primary" type="submit" value="<?php echo esc_attr__( 'Enregistrer', 'anrhpub_theme' ); ?>">
				<span class="description" style="margin-left:10px;">
					<?php printf( esc_html__( 'Lien fixe : %s', 'anrhpub_theme' ), esc_html( $peyruis_url ) ); ?>
				</span>
			</p>
		</form>
	</div>
	<?php
}

