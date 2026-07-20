<?php
/**
 * Admin — connexion Salesforce (espace client B2B).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Page réglages sous Catalogue.
 */
function anrhpub_sf_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=anr_product',
		__( 'Salesforce', 'anrhpub_theme' ),
		__( 'Salesforce', 'anrhpub_theme' ),
		'manage_options',
		'anrhpub-salesforce',
		'anrhpub_render_salesforce_settings_page'
	);
}
add_action( 'admin_menu', 'anrhpub_sf_admin_menu', 26 );

/**
 * Sauvegarde réglages + actions (test / vider file).
 */
function anrhpub_sf_admin_handle_actions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( empty( $_POST['anrhpub_sf_action'] ) ) {
		return;
	}

	check_admin_referer( 'anrhpub_salesforce_settings' );

	$action = sanitize_key( wp_unslash( $_POST['anrhpub_sf_action'] ) );
	$redirect_args = array(
		'page' => 'anrhpub-salesforce',
	);

	if ( 'save' === $action ) {
		$settings = array(
			'enabled' => ! empty( $_POST['anrhpub_sf_enabled'] ),
		);
		update_option( ANRHPUB_SF_SETTINGS_OPTION, $settings );
		$redirect_args['updated'] = '1';
	}

	if ( 'ping' === $action ) {
		if ( ! anrhpub_sf_is_configured() ) {
			$redirect_args['sf_error'] = 'not_configured';
		} else {
			$result = anrhpub_sf_ping();
			if ( is_wp_error( $result ) ) {
				$redirect_args['sf_error'] = 'ping_failed';
			} else {
				$redirect_args['sf_ok'] = '1';
			}
		}
	}

	if ( 'clear_queue' === $action ) {
		anrhpub_sf_save_queue( array() );
		$redirect_args['queue_cleared'] = '1';
	}

	if ( 'clear_token' === $action ) {
		anrhpub_sf_clear_token_cache();
		$redirect_args['token_cleared'] = '1';
	}

	wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'edit.php?post_type=anr_product' ) ) );
	exit;
}
add_action( 'admin_init', 'anrhpub_sf_admin_handle_actions' );

/**
 * Rendu page admin.
 */
function anrhpub_render_salesforce_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings   = anrhpub_sf_get_settings();
	$configured = anrhpub_sf_is_configured();
	$enabled    = anrhpub_sf_is_enabled();
	$queue_n    = anrhpub_sf_queue_count();
	$log        = anrhpub_sf_get_last_log();
	$login_url  = anrhpub_sf_login_url();
	$api_ver    = anrhpub_sf_api_version();

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Salesforce — espace client', 'anrhpub_theme' ); ?></h1>

		<?php if ( ! empty( $_GET['updated'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Réglages enregistrés.', 'anrhpub_theme' ); ?></p></div>
		<?php endif; ?>

		<?php if ( ! empty( $_GET['sf_ok'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Connexion Salesforce réussie (GET /sobjects).', 'anrhpub_theme' ); ?></p></div>
		<?php endif; ?>

		<?php if ( ! empty( $_GET['sf_error'] ) ) : ?>
			<div class="notice notice-error is-dismissible"><p>
				<?php
				if ( 'not_configured' === $_GET['sf_error'] ) {
					esc_html_e( 'Secrets manquants dans wp-config (CLIENT_ID, CLIENT_SECRET, REFRESH_TOKEN).', 'anrhpub_theme' );
				} else {
					$msg = $log && ! empty( $log['message'] ) ? $log['message'] : __( 'Échec du test de connexion.', 'anrhpub_theme' );
					echo esc_html( $msg );
				}
				?>
			</p></div>
		<?php endif; ?>

		<?php if ( ! empty( $_GET['queue_cleared'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'File d’événements vidée.', 'anrhpub_theme' ); ?></p></div>
		<?php endif; ?>

		<?php if ( ! empty( $_GET['token_cleared'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Cache token invalidé.', 'anrhpub_theme' ); ?></p></div>
		<?php endif; ?>

		<table class="widefat striped" style="max-width:720px;margin:1em 0;">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Credentials (wp-config)', 'anrhpub_theme' ); ?></th>
					<td>
						<?php if ( $configured ) : ?>
							<span style="color:#008a20;"><?php esc_html_e( 'Configurés', 'anrhpub_theme' ); ?></span>
						<?php else : ?>
							<span style="color:#b32d2e;"><?php esc_html_e( 'Manquants', 'anrhpub_theme' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Sync active', 'anrhpub_theme' ); ?></th>
					<td><?php echo $enabled ? esc_html__( 'Oui', 'anrhpub_theme' ) : esc_html__( 'Non', 'anrhpub_theme' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Login URL', 'anrhpub_theme' ); ?></th>
					<td><code><?php echo esc_html( $login_url ); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'API version', 'anrhpub_theme' ); ?></th>
					<td><code><?php echo esc_html( $api_ver ); ?></code></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'File d’événements', 'anrhpub_theme' ); ?></th>
					<td><?php echo esc_html( (string) $queue_n ); ?></td>
				</tr>
			</tbody>
		</table>

		<form method="post" action="">
			<?php wp_nonce_field( 'anrhpub_salesforce_settings' ); ?>
			<input type="hidden" name="anrhpub_sf_action" value="save" />

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Activer la sync', 'anrhpub_theme' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="anrhpub_sf_enabled" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?> <?php disabled( ! $configured ); ?> />
							<?php esc_html_e( 'Mettre en file les devis / commandes vers Salesforce', 'anrhpub_theme' ); ?>
						</label>
						<?php if ( ! $configured ) : ?>
							<p class="description">
								<?php
								esc_html_e(
									'Définir dans wp-config.php : ANRHPUB_SF_CLIENT_ID, ANRHPUB_SF_CLIENT_SECRET, ANRHPUB_SF_REFRESH_TOKEN (et optionnellement ANRHPUB_SF_LOGIN_URL, ANRHPUB_SF_API_VERSION).',
									'anrhpub_theme'
								);
								?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Enregistrer', 'anrhpub_theme' ) ); ?>
		</form>

		<hr />

		<h2><?php esc_html_e( 'Actions', 'anrhpub_theme' ); ?></h2>
		<p>
			<form method="post" action="" style="display:inline-block;margin-right:8px;">
				<?php wp_nonce_field( 'anrhpub_salesforce_settings' ); ?>
				<input type="hidden" name="anrhpub_sf_action" value="ping" />
				<?php submit_button( __( 'Tester la connexion', 'anrhpub_theme' ), 'secondary', 'submit', false ); ?>
			</form>
			<form method="post" action="" style="display:inline-block;margin-right:8px;">
				<?php wp_nonce_field( 'anrhpub_salesforce_settings' ); ?>
				<input type="hidden" name="anrhpub_sf_action" value="clear_token" />
				<?php submit_button( __( 'Invalider le token', 'anrhpub_theme' ), 'secondary', 'submit', false ); ?>
			</form>
			<form method="post" action="" style="display:inline-block;" onsubmit="return confirm('<?php echo esc_js( __( 'Vider toute la file ?', 'anrhpub_theme' ) ); ?>');">
				<?php wp_nonce_field( 'anrhpub_salesforce_settings' ); ?>
				<input type="hidden" name="anrhpub_sf_action" value="clear_queue" />
				<?php submit_button( __( 'Vider la file', 'anrhpub_theme' ), 'delete', 'submit', false ); ?>
			</form>
		</p>

		<?php if ( $log ) : ?>
			<h2><?php esc_html_e( 'Dernier log', 'anrhpub_theme' ); ?></h2>
			<p>
				<strong><?php echo esc_html( strtoupper( (string) ( $log['level'] ?? '' ) ) ); ?></strong>
				—
				<?php echo esc_html( (string) ( $log['message'] ?? '' ) ); ?>
				<?php if ( ! empty( $log['time'] ) ) : ?>
					<br /><span class="description"><?php echo esc_html( wp_date( 'd/m/Y H:i:s', (int) $log['time'] ) ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<p class="description">
			<?php esc_html_e( 'Les événements sont mis en file (pending_mapping). Le mapping métier (Opportunity, Case, etc.) se branche ensuite via anrhpub_sf_request().', 'anrhpub_theme' ); ?>
		</p>
	</div>
	<?php
}
