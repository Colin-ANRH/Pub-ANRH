<?php
/**
 * Newsletter accueil — inscription front + CPT abonnés.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_NEWSLETTER_CPT', 'anr_newsletter_sub' );
define( 'ANRHPUB_NEWSLETTER_SETTINGS_OPTION', 'anrhpub_newsletter_settings' );

/**
 * Réglages par défaut du bloc accueil.
 *
 * @return array<string, string|bool>
 */
function anrhpub_get_newsletter_default_settings() {
	return array(
		'enabled'         => true,
		'kicker'          => __( 'Newsletter · ANRH Peyruis', 'anrhpub_theme' ),
		'title'           => __( 'Restez informé', 'anrhpub_theme' ),
		'title_em'        => __( 'de nos nouveautés', 'anrhpub_theme' ),
		'text'            => __( 'Recevez nos actualités catalogue, idées d’objets publicitaires et offres réservées aux professionnels. Une fois par mois, sans spam.', 'anrhpub_theme' ),
		'button_label'    => __( 'S’inscrire', 'anrhpub_theme' ),
		'consent_text'    => __( 'J’accepte de recevoir la newsletter ANRH Peyruis. Désinscription possible à tout moment.', 'anrhpub_theme' ),
		'success_message' => __( 'Merci ! Votre inscription est enregistrée.', 'anrhpub_theme' ),
		'notify_email'    => '',
	);
}

/**
 * Réglages newsletter (fusion avec défauts).
 *
 * @return array<string, string|bool>
 */
function anrhpub_get_newsletter_settings() {
	$stored = get_option( ANRHPUB_NEWSLETTER_SETTINGS_OPTION, array() );

	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	$settings = array_merge( anrhpub_get_newsletter_default_settings(), $stored );
	$settings['enabled'] = ! empty( $settings['enabled'] );

	return $settings;
}

/**
 * Bloc newsletter actif sur l’accueil ?
 *
 * @return bool
 */
function anrhpub_is_newsletter_enabled() {
	$settings = anrhpub_get_newsletter_settings();

	return ! empty( $settings['enabled'] );
}

/**
 * Enregistrement CPT abonnés.
 */
function anrhpub_register_newsletter_cpt() {
	register_post_type(
		ANRHPUB_NEWSLETTER_CPT,
		array(
			'labels'              => array(
				'name'               => __( 'Newsletter', 'anrhpub_theme' ),
				'singular_name'      => __( 'Abonné newsletter', 'anrhpub_theme' ),
				'search_items'       => __( 'Rechercher un abonné', 'anrhpub_theme' ),
				'not_found'          => __( 'Aucun abonné.', 'anrhpub_theme' ),
				'not_found_in_trash' => __( 'Aucun abonné dans la corbeille.', 'anrhpub_theme' ),
				'menu_name'          => __( 'Newsletter', 'anrhpub_theme' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=anr_product',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
			),
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'rewrite'             => false,
		)
	);
}
add_action( 'init', 'anrhpub_register_newsletter_cpt', 12 );

/**
 * Recherche abonné par e-mail.
 *
 * @param string $email E-mail.
 * @return WP_Post|null
 */
function anrhpub_get_newsletter_subscriber_by_email( $email ) {
	$email = sanitize_email( $email );

	if ( ! is_email( $email ) ) {
		return null;
	}

	$posts = get_posts(
		array(
			'post_type'              => ANRHPUB_NEWSLETTER_CPT,
			'post_status'            => array( 'publish', 'draft', 'private' ),
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'   => 'anr_email',
					'value' => $email,
				),
			),
		)
	);

	return ! empty( $posts[0] ) ? $posts[0] : null;
}

/**
 * Statut abonné.
 *
 * @param int $post_id Post ID.
 * @return string active|unsubscribed
 */
function anrhpub_get_newsletter_status( $post_id ) {
	$status = get_post_meta( $post_id, 'anr_newsletter_status', true );

	return 'unsubscribed' === $status ? 'unsubscribed' : 'active';
}

/**
 * Enregistre ou réactive un abonné.
 *
 * @param string $email E-mail.
 * @return array{ok: bool, code: string, post_id?: int}
 */
function anrhpub_subscribe_newsletter( $email ) {
	$email = sanitize_email( $email );

	if ( ! is_email( $email ) ) {
		return array(
			'ok'   => false,
			'code' => 'invalid_email',
		);
	}

	$existing = anrhpub_get_newsletter_subscriber_by_email( $email );

	if ( $existing ) {
		if ( 'active' === anrhpub_get_newsletter_status( $existing->ID ) ) {
			return array(
				'ok'   => false,
				'code' => 'already_subscribed',
			);
		}

		wp_update_post(
			array(
				'ID'          => $existing->ID,
				'post_status' => 'publish',
			)
		);
		update_post_meta( $existing->ID, 'anr_newsletter_status', 'active' );
		update_post_meta( $existing->ID, 'anr_email', $email );
		update_post_meta( $existing->ID, 'anr_subscribed_at', current_time( 'mysql' ) );

		return array(
			'ok'      => true,
			'code'    => 'reactivated',
			'post_id' => $existing->ID,
		);
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => ANRHPUB_NEWSLETTER_CPT,
			'post_title'  => $email,
			'post_status' => 'publish',
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return array(
			'ok'   => false,
			'code' => 'save_failed',
		);
	}

	update_post_meta( $post_id, 'anr_email', $email );
	update_post_meta( $post_id, 'anr_newsletter_status', 'active' );
	update_post_meta( $post_id, 'anr_subscribed_at', current_time( 'mysql' ) );
	update_post_meta( $post_id, 'anr_consent', '1' );
	update_post_meta( $post_id, 'anr_ip', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );

	return array(
		'ok'      => true,
		'code'    => 'subscribed',
		'post_id' => (int) $post_id,
	);
}

/**
 * Notification optionnelle à l’admin.
 *
 * @param string $email E-mail inscrit.
 */
function anrhpub_notify_newsletter_subscription( $email ) {
	$settings = anrhpub_get_newsletter_settings();
	$to       = ! empty( $settings['notify_email'] ) && is_email( $settings['notify_email'] )
		? sanitize_email( $settings['notify_email'] )
		: anrhpub_get_contact_email();

	if ( ! is_email( $to ) ) {
		return;
	}

	$subject = sprintf(
		/* translators: %s: site name */
		__( '[%s] Nouvelle inscription newsletter', 'anrhpub_theme' ),
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
	);

	$body = sprintf(
		__( "Nouvelle inscription à la newsletter accueil :\n\nE-mail : %s\nDate : %s\n\n— %s", 'anrhpub_theme' ),
		$email,
		wp_date( 'd/m/Y H:i' ),
		home_url()
	);

	wp_mail( $to, $subject, $body, array( 'Content-Type: text/plain; charset=UTF-8' ) );
}

/**
 * Messages utilisateur inscription.
 *
 * @param string $code Code retour.
 * @return string
 */
function anrhpub_newsletter_user_message( $code ) {
	$settings = anrhpub_get_newsletter_settings();

	switch ( $code ) {
		case 'subscribed':
		case 'reactivated':
			return (string) $settings['success_message'];
		case 'already_subscribed':
			return __( 'Cette adresse est déjà inscrite à notre newsletter.', 'anrhpub_theme' );
		case 'invalid_email':
			return __( 'Veuillez saisir une adresse e-mail valide.', 'anrhpub_theme' );
		case 'consent_required':
			return __( 'Veuillez accepter de recevoir la newsletter.', 'anrhpub_theme' );
		case 'save_failed':
		default:
			return __( 'L’inscription a échoué. Réessayez dans quelques instants.', 'anrhpub_theme' );
	}
}

/**
 * AJAX — inscription newsletter.
 */
function anrhpub_ajax_newsletter_subscribe() {
	check_ajax_referer( 'anrhpub_newsletter', 'nonce' );

	if ( ! empty( $_POST['anrhpub_website'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Requête refusée.', 'anrhpub_theme' ) ), 400 );
	}

	if ( function_exists( 'anrhpub_rate_limit_exceeded' ) && anrhpub_rate_limit_exceeded( 'newsletter_ip', 10, 15 * MINUTE_IN_SECONDS ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Trop de tentatives. Réessayez dans quelques minutes.', 'anrhpub_theme' ) ),
			429
		);
	}

	$email   = isset( $_POST['newsletter_email'] ) ? sanitize_email( wp_unslash( $_POST['newsletter_email'] ) ) : '';
	$consent = ! empty( $_POST['newsletter_consent'] );

	if ( ! $consent ) {
		wp_send_json_error(
			array( 'message' => anrhpub_newsletter_user_message( 'consent_required' ) ),
			400
		);
	}

	if ( function_exists( 'anrhpub_rate_limit_exceeded_for_email' ) && anrhpub_rate_limit_exceeded_for_email( 'newsletter_email', $email, 3, HOUR_IN_SECONDS ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Cette adresse a déjà été utilisée récemment. Réessayez plus tard.', 'anrhpub_theme' ) ),
			429
		);
	}

	$result = anrhpub_subscribe_newsletter( $email );

	if ( ! $result['ok'] ) {
		wp_send_json_error(
			array( 'message' => anrhpub_newsletter_user_message( $result['code'] ) ),
			400
		);
	}

	anrhpub_notify_newsletter_subscription( $email );

	wp_send_json_success(
		array(
			'message' => anrhpub_newsletter_user_message( $result['code'] ),
		)
	);
}
add_action( 'wp_ajax_anrhpub_newsletter_subscribe', 'anrhpub_ajax_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_anrhpub_newsletter_subscribe', 'anrhpub_ajax_newsletter_subscribe' );

/**
 * Affiche le bloc newsletter accueil.
 */
function anrhpub_render_home_newsletter() {
	if ( ! is_front_page() || ! anrhpub_is_newsletter_enabled() ) {
		return;
	}

	get_template_part( 'template-parts/home', 'cta' );
}

/**
 * Assets front newsletter.
 */
function anrhpub_enqueue_newsletter_assets() {
	if ( ! is_front_page() || ! anrhpub_is_newsletter_enabled() ) {
		return;
	}

	wp_localize_script(
		'anrhpub-main',
		'anrhpubNewsletter',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'anrhpub_newsletter' ),
			'action'  => 'anrhpub_newsletter_subscribe',
			'i18n'    => array(
				'sending' => __( 'Inscription en cours…', 'anrhpub_theme' ),
				'error'   => __( 'Une erreur est survenue. Réessayez.', 'anrhpub_theme' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_newsletter_assets', 25 );
