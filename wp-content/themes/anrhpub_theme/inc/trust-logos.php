<?php
/**
 * Logos « Ils nous font confiance » / « Nos partenaires ».
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ANRHPUB_TRUST_LOGO_POST_TYPE', 'anrh_trust_logo' );
define( 'ANRHPUB_TRUST_LOGO_META_TYPE', 'anrhpub_trust_type' ); // clients|partners
define( 'ANRHPUB_TRUST_LOGO_META_LINK', 'anrhpub_trust_link' ); // URL
define( 'ANRHPUB_TRUST_LOGO_META_ORDER', 'anrhpub_trust_order' ); // int

/**
 * Enregistrement CPT.
 */
function anrhpub_register_trust_logos_cpt() {
	register_post_type(
		ANRHPUB_TRUST_LOGO_POST_TYPE,
		array(
			'labels'              => array(
				'name'          => __( 'Références (logos)', 'anrhpub_theme' ),
				'singular_name' => __( 'Logo référence', 'anrhpub_theme' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'      => 26,
			'has_archive'         => false,
			'supports'            => array( 'title', 'thumbnail' ),
			'show_in_rest'        => false,
		)
	);
}
add_action( 'init', 'anrhpub_register_trust_logos_cpt' );

/**
 * Meta enregistrée (pour cohérence).
 */
function anrhpub_register_trust_logos_meta() {
	register_post_meta(
		ANRHPUB_TRUST_LOGO_POST_TYPE,
		ANRHPUB_TRUST_LOGO_META_TYPE,
		array(
			'single'            => true,
			'type'              => 'string',
			'sanitize_callback' => function ( $v ) {
				$v = (string) $v;
				return in_array( $v, array( 'clients', 'partners' ), true ) ? $v : 'clients';
			},
			'show_in_rest'      => false,
		)
	);

	register_post_meta(
		ANRHPUB_TRUST_LOGO_POST_TYPE,
		ANRHPUB_TRUST_LOGO_META_LINK,
		array(
			'single'            => true,
			'type'              => 'string',
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => false,
		)
	);

	register_post_meta(
		ANRHPUB_TRUST_LOGO_POST_TYPE,
		ANRHPUB_TRUST_LOGO_META_ORDER,
		array(
			'single'            => true,
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'show_in_rest'      => false,
		)
	);
}
add_action( 'init', 'anrhpub_register_trust_logos_meta', 20 );

/**
 * Boîte de métadonnées (type / lien / ordre).
 */
function anrhpub_trust_logos_add_meta_box() {
	add_meta_box(
		'anrhpub_trust_logo_fields',
		__( 'Infos logo', 'anrhpub_theme' ),
		'anrhpub_trust_logos_render_meta_box',
		ANRHPUB_TRUST_LOGO_POST_TYPE,
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_trust_logos_add_meta_box' );

/**
 * Render meta box.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_trust_logos_render_meta_box( $post ) {
	$nonce = wp_create_nonce( 'anrhpub_trust_logo_meta' );

	$type  = (string) get_post_meta( $post->ID, ANRHPUB_TRUST_LOGO_META_TYPE, true );
	$type  = in_array( $type, array( 'clients', 'partners' ), true ) ? $type : 'clients';

	$link  = (string) get_post_meta( $post->ID, ANRHPUB_TRUST_LOGO_META_LINK, true );
	$order = absint( (int) get_post_meta( $post->ID, ANRHPUB_TRUST_LOGO_META_ORDER, true ) );

	?>
	<input type="hidden" name="anrhpub_trust_logo_meta_nonce" value="<?php echo esc_attr( $nonce ); ?>">

	<p>
		<label for="anrhpub_trust_logo_type"><strong><?php esc_html_e( 'Zone', 'anrhpub_theme' ); ?></strong></label>
		<select name="anrhpub_trust_logo_type" id="anrhpub_trust_logo_type">
			<option value="clients" <?php selected( $type, 'clients' ); ?>><?php esc_html_e( 'Clients (Ils nous font confiance)', 'anrhpub_theme' ); ?></option>
			<option value="partners" <?php selected( $type, 'partners' ); ?>><?php esc_html_e( 'Partenaires (Nos partenaires)', 'anrhpub_theme' ); ?></option>
		</select>
	</p>

	<p>
		<label for="anrhpub_trust_logo_link"><strong><?php esc_html_e( 'Lien (optionnel)', 'anrhpub_theme' ); ?></strong></label>
		<input
			type="url"
			name="anrhpub_trust_logo_link"
			id="anrhpub_trust_logo_link"
			class="widefat"
			value="<?php echo esc_attr( $link ); ?>"
			placeholder="https://..."
		/>
	</p>

	<p>
		<label for="anrhpub_trust_logo_order"><strong><?php esc_html_e( 'Ordre d’affichage', 'anrhpub_theme' ); ?></strong></label>
		<input
			type="number"
			name="anrhpub_trust_logo_order"
			id="anrhpub_trust_logo_order"
			class="small-text"
			value="<?php echo esc_attr( (string) $order ); ?>"
			min="0"
		/>
	</p>
	<?php
}

/**
 * Sauvegarde meta box.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_trust_logos_save_meta( $post_id ) {
	if ( ! isset( $_POST['anrhpub_trust_logo_meta_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( (string) wp_unslash( $_POST['anrhpub_trust_logo_meta_nonce'] ), 'anrhpub_trust_logo_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$type = isset( $_POST['anrhpub_trust_logo_type'] ) ? (string) wp_unslash( $_POST['anrhpub_trust_logo_type'] ) : 'clients';
	$type = in_array( $type, array( 'clients', 'partners' ), true ) ? $type : 'clients';

	$link = isset( $_POST['anrhpub_trust_logo_link'] ) ? (string) wp_unslash( $_POST['anrhpub_trust_logo_link'] ) : '';
	$link = $link ? esc_url_raw( $link ) : '';

	$order = isset( $_POST['anrhpub_trust_logo_order'] ) ? absint( (int) wp_unslash( $_POST['anrhpub_trust_logo_order'] ) ) : 0;

	update_post_meta( $post_id, ANRHPUB_TRUST_LOGO_META_TYPE, $type );
	update_post_meta( $post_id, ANRHPUB_TRUST_LOGO_META_LINK, $link );
	update_post_meta( $post_id, ANRHPUB_TRUST_LOGO_META_ORDER, $order );
}
add_action( 'save_post_' . ANRHPUB_TRUST_LOGO_POST_TYPE, 'anrhpub_trust_logos_save_meta' );

/**
 * Colonnes liste admin.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function anrhpub_trust_logos_columns( $columns ) {
	$columns['anrhpub_trust_type']  = __( 'Zone', 'anrhpub_theme' );
	$columns['anrhpub_trust_order'] = __( 'Ordre', 'anrhpub_theme' );
	$columns['anrhpub_trust_link']  = __( 'Lien', 'anrhpub_theme' );
	return $columns;
}
add_filter( 'manage_' . ANRHPUB_TRUST_LOGO_POST_TYPE . '_posts_columns', 'anrhpub_trust_logos_columns' );

/**
 * Contenu colonnes.
 *
 * @param string $column_name Column name.
 * @param int    $post_id Post ID.
 */
function anrhpub_trust_logos_custom_column( $column_name, $post_id ) {
	if ( 'anrhpub_trust_type' === $column_name ) {
		$type = (string) get_post_meta( $post_id, ANRHPUB_TRUST_LOGO_META_TYPE, true );
		$type = in_array( $type, array( 'clients', 'partners' ), true ) ? $type : 'clients';
		echo esc_html( 'clients' === $type ? __( 'Clients', 'anrhpub_theme' ) : __( 'Partenaires', 'anrhpub_theme' ) );
		return;
	}

	if ( 'anrhpub_trust_order' === $column_name ) {
		echo esc_html( (string) absint( (int) get_post_meta( $post_id, ANRHPUB_TRUST_LOGO_META_ORDER, true ) ) );
		return;
	}

	if ( 'anrhpub_trust_link' === $column_name ) {
		$link = (string) get_post_meta( $post_id, ANRHPUB_TRUST_LOGO_META_LINK, true );
		if ( $link ) {
			echo '<a href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer">↗</a>';
		}
		return;
	}
}
add_action( 'manage_' . ANRHPUB_TRUST_LOGO_POST_TYPE . '_posts_custom_column', 'anrhpub_trust_logos_custom_column', 10, 2 );

