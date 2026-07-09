<?php
/**
 * Admin — commandes & avoirs clients.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Liste clients pour select admin.
 *
 * @return array<int, string> ID => label.
 */
function anrhpub_admin_client_choices() {
	$users = get_users(
		array(
			'role'    => ANRHPUB_CLIENT_ROLE,
			'orderby' => 'display_name',
			'order'   => 'ASC',
			'number'  => 500,
		)
	);

	$choices = array( '' => __( '— Choisir un client —', 'anrhpub_theme' ) );

	foreach ( $users as $user ) {
		$choices[ $user->ID ] = sprintf(
			'%s (%s)',
			$user->display_name,
			$user->user_email
		);
	}

	return $choices;
}

/**
 * Meta box commande.
 */
function anrhpub_order_admin_meta_box() {
	add_meta_box(
		'anrhpub_order_details',
		__( 'Détails commande', 'anrhpub_theme' ),
		'anrhpub_order_admin_meta_box_render',
		ANRHPUB_ORDER_CPT,
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_order_admin_meta_box' );

/**
 * Rendu meta box commande.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_order_admin_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_order', 'anrhpub_order_nonce' );

	$client_id   = (int) anrhpub_get_order_meta( $post->ID, 'client_id', 0 );
	$status      = anrhpub_get_order_meta( $post->ID, 'order_status', 'pending' );
	$number      = anrhpub_get_order_meta( $post->ID, 'order_number', '' );
	$total       = anrhpub_get_order_meta( $post->ID, 'order_total', 0 );
	$lines       = anrhpub_get_order_lines( $post->ID );
	$lines_text  = '';
	$address     = anrhpub_get_order_delivery_address( $post->ID );
	$admin_notes = anrhpub_get_order_meta( $post->ID, 'admin_notes', '' );

	if ( ! empty( $lines ) ) {
		foreach ( $lines as $line ) {
			$lines_text .= sprintf(
				"%s | %s | %d\n",
				$line['ref'] ?? '',
				$line['label'] ?? '',
				(int) ( $line['qty'] ?? 1 )
			);
		}
	}

	if ( ! $number && 'auto-draft' !== $post->post_status ) {
		$number = anrhpub_generate_order_number();
	}
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="anr_order_number"><?php esc_html_e( 'N° commande', 'anrhpub_theme' ); ?></label></th>
			<td><input type="text" name="anr_order_number" id="anr_order_number" value="<?php echo esc_attr( $number ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="anr_client_id"><?php esc_html_e( 'Client', 'anrhpub_theme' ); ?></label></th>
			<td>
				<select name="anr_client_id" id="anr_client_id" class="regular-text">
					<?php foreach ( anrhpub_admin_client_choices() as $id => $label ) : ?>
						<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( $client_id, (int) $id ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="anr_order_status"><?php esc_html_e( 'Statut', 'anrhpub_theme' ); ?></label></th>
			<td>
				<select name="anr_order_status" id="anr_order_status">
					<?php foreach ( ANRHPUB_ORDER_STATUSES as $code => $label ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $status, $code ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="anr_order_total"><?php esc_html_e( 'Montant total (€)', 'anrhpub_theme' ); ?></label></th>
			<td><input type="number" name="anr_order_total" id="anr_order_total" value="<?php echo esc_attr( (string) $total ); ?>" min="0" step="0.01" style="width:8rem;" /></td>
		</tr>
		<tr>
			<th><label for="anr_order_lines"><?php esc_html_e( 'Lignes produit', 'anrhpub_theme' ); ?></label></th>
			<td>
				<textarea name="anr_order_lines" id="anr_order_lines" rows="6" class="large-text" placeholder="<?php esc_attr_e( 'Réf. | Désignation | Qté (une ligne par produit)', 'anrhpub_theme' ); ?>"><?php echo esc_textarea( trim( $lines_text ) ); ?></textarea>
				<p class="description"><?php esc_html_e( 'Format : REF | Nom produit | Quantité', 'anrhpub_theme' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Adresse de livraison', 'anrhpub_theme' ); ?></th>
			<td>
				<p><input type="text" name="anr_delivery_label" placeholder="<?php esc_attr_e( 'Libellé (ex. Siège)', 'anrhpub_theme' ); ?>" value="<?php echo esc_attr( $address['label'] ?? '' ); ?>" class="regular-text" /></p>
				<p><input type="text" name="anr_delivery_company" placeholder="<?php esc_attr_e( 'Société', 'anrhpub_theme' ); ?>" value="<?php echo esc_attr( $address['company'] ?? '' ); ?>" class="regular-text" /></p>
				<p>
					<input type="text" name="anr_delivery_first_name" placeholder="<?php esc_attr_e( 'Prénom', 'anrhpub_theme' ); ?>" value="<?php echo esc_attr( $address['first_name'] ?? '' ); ?>" />
					<input type="text" name="anr_delivery_last_name" placeholder="<?php esc_attr_e( 'Nom', 'anrhpub_theme' ); ?>" value="<?php echo esc_attr( $address['last_name'] ?? '' ); ?>" />
				</p>
				<p><input type="text" name="anr_delivery_address_1" placeholder="<?php esc_attr_e( 'Adresse', 'anrhpub_theme' ); ?>" value="<?php echo esc_attr( $address['address_1'] ?? '' ); ?>" class="large-text" /></p>
				<p><input type="text" name="anr_delivery_address_2" placeholder="<?php esc_attr_e( 'Complément', 'anrhpub_theme' ); ?>" value="<?php echo esc_attr( $address['address_2'] ?? '' ); ?>" class="large-text" /></p>
				<p>
					<input type="text" name="anr_delivery_postcode" placeholder="<?php esc_attr_e( 'Code postal', 'anrhpub_theme' ); ?>" value="<?php echo esc_attr( $address['postcode'] ?? '' ); ?>" style="width:6rem;" />
					<input type="text" name="anr_delivery_city" placeholder="<?php esc_attr_e( 'Ville', 'anrhpub_theme' ); ?>" value="<?php echo esc_attr( $address['city'] ?? '' ); ?>" />
					<input type="text" name="anr_delivery_country" placeholder="<?php esc_attr_e( 'Pays', 'anrhpub_theme' ); ?>" value="<?php echo esc_attr( $address['country'] ?? 'France' ); ?>" />
				</p>
				<p><input type="text" name="anr_delivery_phone" placeholder="<?php esc_attr_e( 'Téléphone', 'anrhpub_theme' ); ?>" value="<?php echo esc_attr( $address['phone'] ?? '' ); ?>" class="regular-text" /></p>
			</td>
		</tr>
		<tr>
			<th><label for="anr_admin_notes"><?php esc_html_e( 'Notes internes', 'anrhpub_theme' ); ?></label></th>
			<td><textarea name="anr_admin_notes" id="anr_admin_notes" rows="3" class="large-text"><?php echo esc_textarea( $admin_notes ); ?></textarea></td>
		</tr>
	</table>

	<?php if ( 'cancelled' === $status ) : ?>
		<div class="anrhpub-admin-credit-box" style="margin-top:1rem;padding:1rem;background:#fff8f0;border-left:4px solid #e85d04;">
			<h3 style="margin:0 0 0.5rem;"><?php esc_html_e( 'Avoir suite annulation', 'anrhpub_theme' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Créez un avoir pour ce client (visible dans Mon compte → Avoirs).', 'anrhpub_theme' ); ?></p>
			<p>
				<label><?php esc_html_e( 'Montant (€)', 'anrhpub_theme' ); ?></label>
				<input type="number" name="anr_create_credit_amount" min="0" step="0.01" value="<?php echo esc_attr( (string) $total ); ?>" style="width:8rem;margin-left:0.5rem;" />
			</p>
			<p>
				<label><?php esc_html_e( 'Motif', 'anrhpub_theme' ); ?></label>
				<input type="text" name="anr_create_credit_reason" value="<?php esc_attr_e( 'Annulation commande', 'anrhpub_theme' ); ?>" class="regular-text" style="margin-left:0.5rem;" />
			</p>
			<p>
				<label><input type="checkbox" name="anr_create_credit_now" value="1" /> <?php esc_html_e( 'Créer l’avoir à l’enregistrement', 'anrhpub_theme' ); ?></label>
			</p>
		</div>
	<?php endif; ?>
	<?php
}

/**
 * Parse lignes commande depuis textarea admin.
 *
 * @param string $text Texte.
 * @return array
 */
function anrhpub_parse_order_lines_text( $text ) {
	$lines = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $row ) {
		$row = trim( $row );
		if ( '' === $row ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $row ) );
		$lines[] = array(
			'ref'   => $parts[0] ?? '',
			'label' => $parts[1] ?? ( $parts[0] ?? '' ),
			'qty'   => isset( $parts[2] ) ? max( 1, (int) $parts[2] ) : 1,
		);
	}

	return $lines;
}

/**
 * Sauvegarde commande admin.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_save_order_admin( $post_id ) {
	if ( ! isset( $_POST['anrhpub_order_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_order_nonce'] ) ), 'anrhpub_save_order' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$number = isset( $_POST['anr_order_number'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_order_number'] ) ) : '';
	if ( ! $number ) {
		$number = anrhpub_generate_order_number();
	}

	$old_status = (string) anrhpub_get_order_meta( $post_id, 'order_status', 'pending' );

	update_post_meta( $post_id, 'anr_order_number', $number );
	update_post_meta( $post_id, 'anr_client_id', isset( $_POST['anr_client_id'] ) ? absint( $_POST['anr_client_id'] ) : 0 );
	$new_status = isset( $_POST['anr_order_status'] ) ? sanitize_key( wp_unslash( $_POST['anr_order_status'] ) ) : 'pending';
	update_post_meta( $post_id, 'anr_order_status', $new_status );

	if ( $old_status !== $new_status ) {
		do_action( 'anrhpub_order_status_changed', $post_id, $new_status, $old_status );
	}
	update_post_meta( $post_id, 'anr_order_total', isset( $_POST['anr_order_total'] ) ? round( (float) $_POST['anr_order_total'], 2 ) : 0 );

	$lines_text = isset( $_POST['anr_order_lines'] ) ? wp_unslash( $_POST['anr_order_lines'] ) : '';
	update_post_meta( $post_id, 'anr_order_lines', wp_json_encode( anrhpub_parse_order_lines_text( $lines_text ) ) );

	$address = array(
		'label'      => isset( $_POST['anr_delivery_label'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_delivery_label'] ) ) : '',
		'company'    => isset( $_POST['anr_delivery_company'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_delivery_company'] ) ) : '',
		'first_name' => isset( $_POST['anr_delivery_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_delivery_first_name'] ) ) : '',
		'last_name'  => isset( $_POST['anr_delivery_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_delivery_last_name'] ) ) : '',
		'address_1'  => isset( $_POST['anr_delivery_address_1'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_delivery_address_1'] ) ) : '',
		'address_2'  => isset( $_POST['anr_delivery_address_2'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_delivery_address_2'] ) ) : '',
		'postcode'   => isset( $_POST['anr_delivery_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_delivery_postcode'] ) ) : '',
		'city'       => isset( $_POST['anr_delivery_city'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_delivery_city'] ) ) : '',
		'country'    => isset( $_POST['anr_delivery_country'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_delivery_country'] ) ) : 'France',
		'phone'      => isset( $_POST['anr_delivery_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_delivery_phone'] ) ) : '',
	);
	update_post_meta( $post_id, 'anr_delivery_address', wp_json_encode( $address ) );

	if ( isset( $_POST['anr_admin_notes'] ) ) {
		update_post_meta( $post_id, 'anr_admin_notes', sanitize_textarea_field( wp_unslash( $_POST['anr_admin_notes'] ) ) );
	}

	wp_update_post(
		array(
			'ID'         => $post_id,
			'post_title' => $number,
		)
	);

	if ( ! empty( $_POST['anr_create_credit_now'] ) && 'cancelled' === sanitize_key( wp_unslash( $_POST['anr_order_status'] ?? '' ) ) ) {
		$amount = isset( $_POST['anr_create_credit_amount'] ) ? (float) $_POST['anr_create_credit_amount'] : 0;
		$reason = isset( $_POST['anr_create_credit_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_create_credit_reason'] ) ) : '';
		anrhpub_create_credit_for_order( $post_id, $amount, $reason );
	}
}
add_action( 'save_post_' . ANRHPUB_ORDER_CPT, 'anrhpub_save_order_admin' );

/**
 * Meta box avoir.
 */
function anrhpub_credit_admin_meta_box() {
	add_meta_box(
		'anrhpub_credit_details',
		__( 'Détails avoir', 'anrhpub_theme' ),
		'anrhpub_credit_admin_meta_box_render',
		ANRHPUB_CREDIT_CPT,
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_credit_admin_meta_box' );

/**
 * Rendu meta box avoir.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_credit_admin_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_credit', 'anrhpub_credit_nonce' );

	$client_id = (int) anrhpub_get_credit_meta( $post->ID, 'credit_client_id', 0 );
	$order_id  = (int) anrhpub_get_credit_meta( $post->ID, 'credit_order_id', 0 );
	$amount    = anrhpub_get_credit_meta( $post->ID, 'credit_amount', 0 );
	$status    = anrhpub_get_credit_meta( $post->ID, 'credit_status', 'available' );
	$number    = anrhpub_get_credit_meta( $post->ID, 'credit_number', '' );
	$reason    = anrhpub_get_credit_meta( $post->ID, 'credit_reason', '' );

	if ( ! $number ) {
		$number = anrhpub_generate_credit_number();
	}

	$orders = array( '' => __( '— Aucune commande liée —', 'anrhpub_theme' ) );
	if ( $client_id ) {
		foreach ( anrhpub_get_client_orders( $client_id ) as $order ) {
			$orders[ $order->ID ] = anrhpub_get_order_meta( $order->ID, 'order_number', $order->post_title );
		}
	}
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="anr_credit_number"><?php esc_html_e( 'N° avoir', 'anrhpub_theme' ); ?></label></th>
			<td><input type="text" name="anr_credit_number" id="anr_credit_number" value="<?php echo esc_attr( $number ); ?>" class="regular-text" /></td>
		</tr>
		<tr>
			<th><label for="anr_credit_client_id"><?php esc_html_e( 'Client', 'anrhpub_theme' ); ?></label></th>
			<td>
				<select name="anr_credit_client_id" id="anr_credit_client_id">
					<?php foreach ( anrhpub_admin_client_choices() as $id => $label ) : ?>
						<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( $client_id, (int) $id ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="anr_credit_order_id"><?php esc_html_e( 'Commande liée', 'anrhpub_theme' ); ?></label></th>
			<td>
				<select name="anr_credit_order_id" id="anr_credit_order_id">
					<?php foreach ( $orders as $id => $label ) : ?>
						<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( $order_id, (int) $id ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="anr_credit_amount"><?php esc_html_e( 'Montant (€)', 'anrhpub_theme' ); ?></label></th>
			<td><input type="number" name="anr_credit_amount" id="anr_credit_amount" value="<?php echo esc_attr( (string) $amount ); ?>" min="0" step="0.01" style="width:8rem;" /></td>
		</tr>
		<tr>
			<th><label for="anr_credit_status"><?php esc_html_e( 'Statut', 'anrhpub_theme' ); ?></label></th>
			<td>
				<select name="anr_credit_status" id="anr_credit_status">
					<?php foreach ( ANRHPUB_CREDIT_STATUSES as $code => $label ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $status, $code ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="anr_credit_reason"><?php esc_html_e( 'Motif', 'anrhpub_theme' ); ?></label></th>
			<td><input type="text" name="anr_credit_reason" id="anr_credit_reason" value="<?php echo esc_attr( $reason ); ?>" class="large-text" /></td>
		</tr>
	</table>
	<?php
}

/**
 * Sauvegarde avoir admin.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_save_credit_admin( $post_id ) {
	if ( ! isset( $_POST['anrhpub_credit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_credit_nonce'] ) ), 'anrhpub_save_credit' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$number = isset( $_POST['anr_credit_number'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_credit_number'] ) ) : anrhpub_generate_credit_number();

	update_post_meta( $post_id, 'anr_credit_number', $number );
	update_post_meta( $post_id, 'anr_credit_client_id', isset( $_POST['anr_credit_client_id'] ) ? absint( $_POST['anr_credit_client_id'] ) : 0 );
	update_post_meta( $post_id, 'anr_credit_order_id', isset( $_POST['anr_credit_order_id'] ) ? absint( $_POST['anr_credit_order_id'] ) : 0 );
	update_post_meta( $post_id, 'anr_credit_amount', isset( $_POST['anr_credit_amount'] ) ? round( (float) $_POST['anr_credit_amount'], 2 ) : 0 );
	update_post_meta( $post_id, 'anr_credit_status', isset( $_POST['anr_credit_status'] ) ? sanitize_key( wp_unslash( $_POST['anr_credit_status'] ) ) : 'available' );
	update_post_meta( $post_id, 'anr_credit_reason', isset( $_POST['anr_credit_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_credit_reason'] ) ) : '' );

	wp_update_post(
		array(
			'ID'         => $post_id,
			'post_title' => $number,
		)
	);
}
add_action( 'save_post_' . ANRHPUB_CREDIT_CPT, 'anrhpub_save_credit_admin' );

/**
 * Colonnes liste commandes.
 */
function anrhpub_order_columns( $columns ) {
	return array(
		'cb'                => $columns['cb'] ?? '',
		'anr_order_number'  => __( 'N° commande', 'anrhpub_theme' ),
		'anr_client'        => __( 'Client', 'anrhpub_theme' ),
		'anr_status'        => __( 'Statut', 'anrhpub_theme' ),
		'anr_total'         => __( 'Montant', 'anrhpub_theme' ),
		'date'              => $columns['date'] ?? __( 'Date', 'anrhpub_theme' ),
	);
}
add_filter( 'manage_' . ANRHPUB_ORDER_CPT . '_posts_columns', 'anrhpub_order_columns' );

/**
 * Contenu colonnes commandes.
 */
function anrhpub_order_column_content( $column, $post_id ) {
	if ( 'anr_order_number' === $column ) {
		echo esc_html( anrhpub_get_order_meta( $post_id, 'order_number', get_the_title( $post_id ) ) );
		return;
	}
	if ( 'anr_client' === $column ) {
		$uid = (int) anrhpub_get_order_meta( $post_id, 'client_id', 0 );
		if ( $uid ) {
			$user = get_userdata( $uid );
			echo $user ? esc_html( $user->display_name ) : '—';
		} else {
			echo '—';
		}
		return;
	}
	if ( 'anr_status' === $column ) {
		$status = anrhpub_get_order_meta( $post_id, 'order_status', 'pending' );
		echo '<span class="anrhpub-status anrhpub-status--' . esc_attr( $status ) . '">' . esc_html( anrhpub_get_order_status_label( $status ) ) . '</span>';
		return;
	}
	if ( 'anr_total' === $column ) {
		printf( '%s €', esc_html( number_format_i18n( (float) anrhpub_get_order_meta( $post_id, 'order_total', 0 ), 2 ) ) );
	}
}
add_action( 'manage_' . ANRHPUB_ORDER_CPT . '_posts_custom_column', 'anrhpub_order_column_content', 10, 2 );

/**
 * Colonnes liste avoirs.
 */
function anrhpub_credit_columns( $columns ) {
	return array(
		'cb'              => $columns['cb'] ?? '',
		'anr_credit_num'  => __( 'N° avoir', 'anrhpub_theme' ),
		'anr_client'      => __( 'Client', 'anrhpub_theme' ),
		'anr_amount'      => __( 'Montant', 'anrhpub_theme' ),
		'anr_status'      => __( 'Statut', 'anrhpub_theme' ),
		'anr_order'       => __( 'Commande', 'anrhpub_theme' ),
		'date'            => __( 'Date', 'anrhpub_theme' ),
	);
}
add_filter( 'manage_' . ANRHPUB_CREDIT_CPT . '_posts_columns', 'anrhpub_credit_columns' );

/**
 * Contenu colonnes avoirs.
 */
function anrhpub_credit_column_content( $column, $post_id ) {
	if ( 'anr_credit_num' === $column ) {
		echo esc_html( anrhpub_get_credit_meta( $post_id, 'credit_number', get_the_title( $post_id ) ) );
		return;
	}
	if ( 'anr_client' === $column ) {
		$uid = (int) anrhpub_get_credit_meta( $post_id, 'credit_client_id', 0 );
		$user = $uid ? get_userdata( $uid ) : null;
		echo $user ? esc_html( $user->display_name ) : '—';
		return;
	}
	if ( 'anr_amount' === $column ) {
		printf( '%s €', esc_html( number_format_i18n( (float) anrhpub_get_credit_meta( $post_id, 'credit_amount', 0 ), 2 ) ) );
		return;
	}
	if ( 'anr_status' === $column ) {
		$status = anrhpub_get_credit_meta( $post_id, 'credit_status', 'available' );
		echo esc_html( anrhpub_get_credit_status_label( $status ) );
		return;
	}
	if ( 'anr_order' === $column ) {
		$oid = (int) anrhpub_get_credit_meta( $post_id, 'credit_order_id', 0 );
		echo $oid ? esc_html( anrhpub_get_order_meta( $oid, 'order_number', '#' . $oid ) ) : '—';
	}
}
add_action( 'manage_' . ANRHPUB_CREDIT_CPT . '_posts_custom_column', 'anrhpub_credit_column_content', 10, 2 );

/**
 * Styles admin statuts.
 */
function anrhpub_orders_admin_styles() {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( ANRHPUB_ORDER_CPT, ANRHPUB_CREDIT_CPT ), true ) ) {
		return;
	}
	?>
	<style>
		.anrhpub-status--cancelled { color: #b43232; font-weight: 600; }
		.anrhpub-status--delivered { color: #4c8c4a; font-weight: 600; }
		.anrhpub-status--shipped { color: #1d3557; font-weight: 600; }
	</style>
	<?php
}
add_action( 'admin_head', 'anrhpub_orders_admin_styles' );
