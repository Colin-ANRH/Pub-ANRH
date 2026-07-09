<?php
/**
 * Admin — devis clients.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta box devis.
 */
function anrhpub_quote_admin_meta_box() {
	add_meta_box(
		'anrhpub_quote_details',
		__( 'Détails du devis', 'anrhpub_theme' ),
		'anrhpub_quote_admin_meta_box_render',
		ANRHPUB_QUOTE_CPT,
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_quote_admin_meta_box' );

/**
 * Rendu meta box.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_quote_admin_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_quote_admin', 'anrhpub_quote_admin_nonce' );

	$status    = anrhpub_get_quote_meta( $post->ID, 'status', 'pending' );
	$client_id = (int) anrhpub_get_quote_meta( $post->ID, 'client_id', 0 );
	$lines     = anrhpub_get_quote_lines( $post->ID );
	$lines_txt = '';

	foreach ( $lines as $line ) {
		$lines_txt .= sprintf(
			"%d|%s|%s|%d\n",
			(int) ( $line['product_id'] ?? 0 ),
			$line['ref'] ?? '',
			$line['label'] ?? '',
			(int) ( $line['qty'] ?? 1 )
		);
	}
	?>
	<p>
		<a class="button" href="<?php echo esc_url( anrhpub_get_quote_pdf_url( $post->ID ) ); ?>" target="_blank" rel="noopener">
			<?php esc_html_e( 'Télécharger le PDF', 'anrhpub_theme' ); ?>
		</a>
	</p>
	<table class="form-table">
		<tr>
			<th><label for="anr_quote_status"><?php esc_html_e( 'Statut', 'anrhpub_theme' ); ?></label></th>
			<td>
				<select name="anr_quote_status" id="anr_quote_status">
					<?php foreach ( ANRHPUB_QUOTE_STATUSES as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="anr_quote_client_id"><?php esc_html_e( 'Client', 'anrhpub_theme' ); ?></label></th>
			<td>
				<select name="anr_quote_client_id" id="anr_quote_client_id">
					<?php foreach ( anrhpub_admin_client_choices() as $id => $label ) : ?>
						<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( $client_id, (int) $id ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="anr_quote_lines"><?php esc_html_e( 'Lignes (ID|ref|libellé|qté)', 'anrhpub_theme' ); ?></label></th>
			<td><textarea name="anr_quote_lines" id="anr_quote_lines" rows="10" class="large-text code"><?php echo esc_textarea( trim( $lines_txt ) ); ?></textarea></td>
		</tr>
	</table>
	<?php
}

/**
 * Sauvegarde admin devis.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_save_quote_admin_meta( $post_id ) {
	if ( ! isset( $_POST['anrhpub_quote_admin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_quote_admin_nonce'] ) ), 'anrhpub_save_quote_admin' ) ) {
		return;
	}
	if ( ANRHPUB_QUOTE_CPT !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$old_status = (string) anrhpub_get_quote_meta( $post_id, 'status', 'pending' );

	if ( isset( $_POST['anr_quote_status'] ) ) {
		$new_status = sanitize_key( wp_unslash( $_POST['anr_quote_status'] ) );
		if ( isset( ANRHPUB_QUOTE_STATUSES[ $new_status ] ) ) {
			anrhpub_update_quote_meta( $post_id, 'status', $new_status );
			if ( $old_status !== $new_status ) {
				do_action( 'anrhpub_quote_status_changed', $post_id, $new_status, $old_status );
			}
		}
	}

	if ( isset( $_POST['anr_quote_client_id'] ) ) {
		anrhpub_update_quote_meta( $post_id, 'client_id', absint( $_POST['anr_quote_client_id'] ) );
	}

	if ( isset( $_POST['anr_quote_lines'] ) ) {
		$lines = array();
		$rows  = preg_split( '/\r\n|\r|\n/', (string) wp_unslash( $_POST['anr_quote_lines'] ) );
		foreach ( $rows as $row ) {
			$row = trim( $row );
			if ( '' === $row ) {
				continue;
			}
			$parts = explode( '|', $row );
			$lines[] = array(
				'product_id' => isset( $parts[0] ) ? absint( $parts[0] ) : 0,
				'ref'        => $parts[1] ?? '',
				'label'      => $parts[2] ?? '',
				'qty'        => isset( $parts[3] ) ? max( 1, absint( $parts[3] ) ) : 1,
			);
		}
		anrhpub_update_quote_meta( $post_id, 'lines', wp_json_encode( $lines ) );
	}
}
add_action( 'save_post_' . ANRHPUB_QUOTE_CPT, 'anrhpub_save_quote_admin_meta' );
