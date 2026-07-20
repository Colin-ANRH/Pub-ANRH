<?php
/**
 * Connexion Salesforce — OAuth refresh token, API REST, file d’attente B2B.
 *
 * Secrets via wp-config : ANRHPUB_SF_CLIENT_ID, ANRHPUB_SF_CLIENT_SECRET,
 * ANRHPUB_SF_REFRESH_TOKEN, ANRHPUB_SF_LOGIN_URL, ANRHPUB_SF_API_VERSION.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_SF_SETTINGS_OPTION', 'anrhpub_salesforce_settings' );
define( 'ANRHPUB_SF_TOKEN_TRANSIENT', 'anrhpub_sf_oauth_token' );
define( 'ANRHPUB_SF_QUEUE_OPTION', 'anrhpub_sf_event_queue' );
define( 'ANRHPUB_SF_LOG_OPTION', 'anrhpub_sf_last_log' );
define( 'ANRHPUB_SF_ACCOUNT_META', 'anrhpub_sf_account_id' );
define( 'ANRHPUB_SF_CONTACT_META', 'anrhpub_sf_contact_id' );
define( 'ANRHPUB_SF_CRON_HOOK', 'anrhpub_sf_process_queue' );
define( 'ANRHPUB_SF_QUEUE_MAX', 200 );

/**
 * Réglages par défaut.
 *
 * @return array{enabled: bool}
 */
function anrhpub_sf_default_settings() {
	return array(
		'enabled' => false,
	);
}

/**
 * Réglages Salesforce (non secrets).
 *
 * @return array{enabled: bool}
 */
function anrhpub_sf_get_settings() {
	$stored = get_option( ANRHPUB_SF_SETTINGS_OPTION, array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	$settings            = array_merge( anrhpub_sf_default_settings(), $stored );
	$settings['enabled'] = ! empty( $settings['enabled'] );

	return $settings;
}

/**
 * Lit une constante SF (chaîne).
 *
 * @param string $name Constant name.
 * @param string $default Default.
 * @return string
 */
function anrhpub_sf_const( $name, $default = '' ) {
	return defined( $name ) ? (string) constant( $name ) : $default;
}

/**
 * Credentials OAuth présents ?
 *
 * @return bool
 */
function anrhpub_sf_is_configured() {
	$client_id     = anrhpub_sf_const( 'ANRHPUB_SF_CLIENT_ID' );
	$client_secret = anrhpub_sf_const( 'ANRHPUB_SF_CLIENT_SECRET' );
	$refresh       = anrhpub_sf_const( 'ANRHPUB_SF_REFRESH_TOKEN' );

	return '' !== $client_id && '' !== $client_secret && '' !== $refresh;
}

/**
 * Sync active (réglage + credentials) ?
 *
 * @return bool
 */
function anrhpub_sf_is_enabled() {
	$settings = anrhpub_sf_get_settings();

	return ! empty( $settings['enabled'] ) && anrhpub_sf_is_configured();
}

/**
 * URL login Salesforce.
 *
 * @return string
 */
function anrhpub_sf_login_url() {
	$url = anrhpub_sf_const( 'ANRHPUB_SF_LOGIN_URL', 'https://login.salesforce.com' );
	$url = untrailingslashit( trim( $url ) );

	return $url ? $url : 'https://login.salesforce.com';
}

/**
 * Version API REST.
 *
 * @return string
 */
function anrhpub_sf_api_version() {
	$version = anrhpub_sf_const( 'ANRHPUB_SF_API_VERSION', 'v59.0' );
	$version = trim( $version );

	if ( '' === $version ) {
		return 'v59.0';
	}

	if ( 0 !== strpos( $version, 'v' ) ) {
		$version = 'v' . $version;
	}

	return $version;
}

/**
 * Journalise un événement (dernier log admin).
 *
 * @param string               $level   info|success|error.
 * @param string               $message Message.
 * @param array<string, mixed> $context Context.
 */
function anrhpub_sf_log( $level, $message, $context = array() ) {
	$entry = array(
		'time'    => time(),
		'level'   => sanitize_key( $level ),
		'message' => sanitize_text_field( $message ),
		'context' => is_array( $context ) ? $context : array(),
	);

	update_option( ANRHPUB_SF_LOG_OPTION, $entry, false );
}

/**
 * Dernier log.
 *
 * @return array<string, mixed>|null
 */
function anrhpub_sf_get_last_log() {
	$log = get_option( ANRHPUB_SF_LOG_OPTION, null );

	return is_array( $log ) ? $log : null;
}

/**
 * Invalide le cache token.
 */
function anrhpub_sf_clear_token_cache() {
	delete_transient( ANRHPUB_SF_TOKEN_TRANSIENT );
}

/**
 * Obtient access_token + instance_url (cache transient).
 *
 * @param bool $force Forcer un refresh.
 * @return array{access_token: string, instance_url: string}|WP_Error
 */
function anrhpub_sf_get_token( $force = false ) {
	if ( ! anrhpub_sf_is_configured() ) {
		return new WP_Error( 'sf_not_configured', __( 'Salesforce non configuré (secrets wp-config manquants).', 'anrhpub_theme' ) );
	}

	if ( ! $force ) {
		$cached = get_transient( ANRHPUB_SF_TOKEN_TRANSIENT );
		if ( is_array( $cached ) && ! empty( $cached['access_token'] ) && ! empty( $cached['instance_url'] ) ) {
			return $cached;
		}
	}

	$url  = anrhpub_sf_login_url() . '/services/oauth2/token';
	$body = array(
		'grant_type'    => 'refresh_token',
		'client_id'     => anrhpub_sf_const( 'ANRHPUB_SF_CLIENT_ID' ),
		'client_secret' => anrhpub_sf_const( 'ANRHPUB_SF_CLIENT_SECRET' ),
		'refresh_token' => anrhpub_sf_const( 'ANRHPUB_SF_REFRESH_TOKEN' ),
	);

	$response = wp_remote_post(
		$url,
		array(
			'timeout' => 20,
			'body'    => $body,
		)
	);

	if ( is_wp_error( $response ) ) {
		anrhpub_sf_log( 'error', $response->get_error_message(), array( 'step' => 'token' ) );
		return $response;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$raw  = (string) wp_remote_retrieve_body( $response );
	$data = json_decode( $raw, true );

	if ( $code < 200 || $code >= 300 || ! is_array( $data ) || empty( $data['access_token'] ) || empty( $data['instance_url'] ) ) {
		$error_msg = is_array( $data ) && ! empty( $data['error_description'] )
			? (string) $data['error_description']
			: ( is_array( $data ) && ! empty( $data['error'] ) ? (string) $data['error'] : __( 'Échec OAuth Salesforce.', 'anrhpub_theme' ) );
		$err = new WP_Error( 'sf_oauth_failed', $error_msg, array( 'status' => $code ) );
		anrhpub_sf_log( 'error', $error_msg, array( 'step' => 'token', 'status' => $code ) );
		return $err;
	}

	$token = array(
		'access_token' => (string) $data['access_token'],
		'instance_url' => untrailingslashit( (string) $data['instance_url'] ),
	);

	// Access tokens SF ~2h ; cache 90 min.
	set_transient( ANRHPUB_SF_TOKEN_TRANSIENT, $token, 90 * MINUTE_IN_SECONDS );

	return $token;
}

/**
 * Appel REST Salesforce Data API.
 *
 * @param string               $method GET|POST|PATCH|DELETE.
 * @param string               $path   Ex. /sobjects ou /query?q=...
 * @param array<string, mixed> $body   JSON body (POST/PATCH).
 * @param bool                 $retried Internal retry flag.
 * @return array{status: int, body: mixed}|WP_Error
 */
function anrhpub_sf_request( $method, $path, $body = array(), $retried = false ) {
	$token = anrhpub_sf_get_token( false );

	if ( is_wp_error( $token ) ) {
		return $token;
	}

	$method = strtoupper( $method );
	$path   = '/' . ltrim( (string) $path, '/' );
	$url    = $token['instance_url'] . '/services/data/' . anrhpub_sf_api_version() . $path;

	$args = array(
		'method'  => $method,
		'timeout' => 25,
		'headers' => array(
			'Authorization' => 'Bearer ' . $token['access_token'],
			'Accept'        => 'application/json',
		),
	);

	if ( in_array( $method, array( 'POST', 'PATCH', 'PUT' ), true ) && ! empty( $body ) ) {
		$args['headers']['Content-Type'] = 'application/json';
		$args['body']                    = wp_json_encode( $body );
	}

	$response = wp_remote_request( $url, $args );

	if ( is_wp_error( $response ) ) {
		anrhpub_sf_log( 'error', $response->get_error_message(), array( 'step' => 'request', 'path' => $path ) );
		return $response;
	}

	$status  = (int) wp_remote_retrieve_response_code( $response );
	$raw     = (string) wp_remote_retrieve_body( $response );
	$decoded = '' !== $raw ? json_decode( $raw, true ) : null;

	if ( 401 === $status && ! $retried ) {
		anrhpub_sf_clear_token_cache();
		$fresh = anrhpub_sf_get_token( true );
		if ( is_wp_error( $fresh ) ) {
			return $fresh;
		}
		return anrhpub_sf_request( $method, $path, $body, true );
	}

	if ( $status < 200 || $status >= 300 ) {
		$msg = __( 'Erreur API Salesforce.', 'anrhpub_theme' );
		if ( is_array( $decoded ) ) {
			if ( isset( $decoded[0]['message'] ) ) {
				$msg = (string) $decoded[0]['message'];
			} elseif ( ! empty( $decoded['message'] ) ) {
				$msg = (string) $decoded['message'];
			}
		}
		anrhpub_sf_log( 'error', $msg, array( 'step' => 'request', 'path' => $path, 'status' => $status ) );
		return new WP_Error( 'sf_api_error', $msg, array( 'status' => $status, 'body' => $decoded ) );
	}

	return array(
		'status' => $status,
		'body'   => null !== $decoded ? $decoded : $raw,
	);
}

/**
 * Test connexion : GET /sobjects.
 *
 * @return true|WP_Error
 */
function anrhpub_sf_ping() {
	$result = anrhpub_sf_request( 'GET', '/sobjects' );

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	anrhpub_sf_log( 'success', __( 'Connexion Salesforce OK.', 'anrhpub_theme' ), array( 'step' => 'ping', 'status' => $result['status'] ) );

	return true;
}

/**
 * Charge la file d’événements.
 *
 * @return array<int, array<string, mixed>>
 */
function anrhpub_sf_get_queue() {
	$queue = get_option( ANRHPUB_SF_QUEUE_OPTION, array() );

	return is_array( $queue ) ? $queue : array();
}

/**
 * Persiste la file.
 *
 * @param array<int, array<string, mixed>> $queue Queue.
 */
function anrhpub_sf_save_queue( $queue ) {
	update_option( ANRHPUB_SF_QUEUE_OPTION, array_values( $queue ), false );
}

/**
 * Taille de la file.
 *
 * @return int
 */
function anrhpub_sf_queue_count() {
	return count( anrhpub_sf_get_queue() );
}

/**
 * Ajoute un événement à la file (si sync active).
 *
 * @param string               $type    Event type.
 * @param int                  $object_id WP object id.
 * @param array<string, mixed> $payload Stub payload.
 */
function anrhpub_sf_enqueue( $type, $object_id, $payload = array() ) {
	if ( ! anrhpub_sf_is_enabled() ) {
		return;
	}

	$queue = anrhpub_sf_get_queue();

	$queue[] = array(
		'id'         => uniqid( 'sf_', true ),
		'type'       => sanitize_key( $type ),
		'object_id'  => (int) $object_id,
		'payload'    => is_array( $payload ) ? $payload : array(),
		'status'     => 'pending_mapping',
		'attempts'   => 0,
		'created_at' => time(),
	);

	if ( count( $queue ) > ANRHPUB_SF_QUEUE_MAX ) {
		$queue = array_slice( $queue, -ANRHPUB_SF_QUEUE_MAX );
	}

	anrhpub_sf_save_queue( $queue );
}

/**
 * Construit un stub payload client.
 *
 * @param int $client_id User ID.
 * @return array<string, mixed>
 */
function anrhpub_sf_client_stub( $client_id ) {
	$client_id = (int) $client_id;
	$user      = $client_id > 0 ? get_userdata( $client_id ) : false;

	return array(
		'wp_user_id'     => $client_id,
		'email'          => $user ? $user->user_email : '',
		'display_name'   => $user ? $user->display_name : '',
		'siret'          => $client_id ? (string) get_user_meta( $client_id, ANRHPUB_SIRET_META, true ) : '',
		'erp_code'       => $client_id ? (string) get_user_meta( $client_id, ANRHPUB_ERP_CODE_META, true ) : '',
		'sf_account_id'  => $client_id ? (string) get_user_meta( $client_id, ANRHPUB_SF_ACCOUNT_META, true ) : '',
		'sf_contact_id'  => $client_id ? (string) get_user_meta( $client_id, ANRHPUB_SF_CONTACT_META, true ) : '',
	);
}

/**
 * Hook : nouveau devis.
 *
 * @param int $quote_id Quote ID.
 * @param int $client_id Client ID.
 */
function anrhpub_sf_on_quote_submitted( $quote_id, $client_id ) {
	$quote_id  = (int) $quote_id;
	$client_id = (int) $client_id;

	anrhpub_sf_enqueue(
		'quote_submitted',
		$quote_id,
		array(
			'client' => anrhpub_sf_client_stub( $client_id ),
			'number' => function_exists( 'anrhpub_get_quote_meta' )
				? (string) anrhpub_get_quote_meta( $quote_id, 'number', get_the_title( $quote_id ) )
				: get_the_title( $quote_id ),
			'status' => 'pending',
		)
	);
}
add_action( 'anrhpub_quote_submitted', 'anrhpub_sf_on_quote_submitted', 20, 2 );

/**
 * Hook : statut devis.
 *
 * @param int    $quote_id Quote ID.
 * @param string $new_status New status.
 * @param string $old_status Old status.
 */
function anrhpub_sf_on_quote_status_changed( $quote_id, $new_status, $old_status ) {
	$quote_id = (int) $quote_id;
	$client   = 0;

	if ( function_exists( 'anrhpub_get_quote_meta' ) ) {
		$client = (int) anrhpub_get_quote_meta( $quote_id, 'client_id', 0 );
	}

	anrhpub_sf_enqueue(
		'quote_status_changed',
		$quote_id,
		array(
			'client'     => anrhpub_sf_client_stub( $client ),
			'new_status' => sanitize_key( $new_status ),
			'old_status' => sanitize_key( $old_status ),
		)
	);
}
add_action( 'anrhpub_quote_status_changed', 'anrhpub_sf_on_quote_status_changed', 20, 3 );

/**
 * Hook : statut commande.
 *
 * @param int    $order_id Order ID.
 * @param string $new_status New.
 * @param string $old_status Old.
 */
function anrhpub_sf_on_order_status_changed( $order_id, $new_status, $old_status ) {
	$order_id  = (int) $order_id;
	$client_id = (int) get_post_meta( $order_id, 'anr_client_id', true );

	anrhpub_sf_enqueue(
		'order_status_changed',
		$order_id,
		array(
			'client'     => anrhpub_sf_client_stub( $client_id ),
			'new_status' => sanitize_key( $new_status ),
			'old_status' => sanitize_key( $old_status ),
		)
	);
}
add_action( 'anrhpub_order_status_changed', 'anrhpub_sf_on_order_status_changed', 20, 3 );

/**
 * Traite la file : marque pending_mapping (mapping métier à brancher plus tard).
 * Vérifie la connexion si des items sont présents.
 */
function anrhpub_sf_process_queue() {
	if ( ! anrhpub_sf_is_enabled() ) {
		return;
	}

	$queue = anrhpub_sf_get_queue();

	if ( empty( $queue ) ) {
		return;
	}

	// Garde la connexion vivante ; pas de POST métier tant que le mapping n’est pas défini.
	$ping = anrhpub_sf_ping();

	if ( is_wp_error( $ping ) ) {
		anrhpub_sf_log(
			'error',
			sprintf(
				/* translators: %s: error message */
				__( 'File Salesforce non traitée : %s', 'anrhpub_theme' ),
				$ping->get_error_message()
			),
			array( 'step' => 'queue', 'count' => count( $queue ) )
		);
		return;
	}

	$updated = false;

	foreach ( $queue as &$item ) {
		if ( empty( $item['status'] ) || 'pending_mapping' === $item['status'] ) {
			$item['status']     = 'pending_mapping';
			$item['attempts']   = isset( $item['attempts'] ) ? (int) $item['attempts'] + 1 : 1;
			$item['checked_at'] = time();
			$updated            = true;
		}
	}
	unset( $item );

	if ( $updated ) {
		anrhpub_sf_save_queue( $queue );
	}

	anrhpub_sf_log(
		'info',
		sprintf(
			/* translators: %d: queue size */
			__( 'File Salesforce : %d événement(s) en attente de mapping métier.', 'anrhpub_theme' ),
			count( $queue )
		),
		array( 'step' => 'queue', 'count' => count( $queue ) )
	);
}
add_action( ANRHPUB_SF_CRON_HOOK, 'anrhpub_sf_process_queue' );

/**
 * Planifie le cron file.
 */
function anrhpub_sf_schedule_cron() {
	if ( ! wp_next_scheduled( ANRHPUB_SF_CRON_HOOK ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'anrhpub_sf_five_minutes', ANRHPUB_SF_CRON_HOOK );
	}
}
add_action( 'init', 'anrhpub_sf_schedule_cron', 30 );

/**
 * Intervalle cron 5 minutes.
 *
 * @param array<string, array{interval: int, display: string}> $schedules Schedules.
 * @return array<string, array{interval: int, display: string}>
 */
function anrhpub_sf_cron_schedules( $schedules ) {
	if ( ! isset( $schedules['anrhpub_sf_five_minutes'] ) ) {
		$schedules['anrhpub_sf_five_minutes'] = array(
			'interval' => 5 * MINUTE_IN_SECONDS,
			'display'  => __( 'Toutes les 5 minutes (Salesforce)', 'anrhpub_theme' ),
		);
	}

	return $schedules;
}
add_filter( 'cron_schedules', 'anrhpub_sf_cron_schedules' );

/**
 * Meta utilisateur Salesforce Account / Contact.
 */
function anrhpub_sf_register_user_meta() {
	foreach ( array( ANRHPUB_SF_ACCOUNT_META, ANRHPUB_SF_CONTACT_META ) as $key ) {
		register_meta(
			'user',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'show_in_rest'      => false,
				'auth_callback'     => function () {
					return current_user_can( 'edit_users' );
				},
			)
		);
	}
}
add_action( 'init', 'anrhpub_sf_register_user_meta', 13 );
