<?php
/**
 * Avoirs — espace client.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$user_id  = get_current_user_id();
$balance  = anrhpub_get_client_credit_balance( $user_id );
$credits  = anrhpub_get_client_credits( $user_id );
?>
<div class="account-credits-summary">
	<p class="account-credits-summary__label"><?php esc_html_e( 'Solde avoirs disponibles', 'anrhpub_theme' ); ?></p>
	<p class="account-credits-summary__amount">
		<?php
		printf(
			'%s €',
			esc_html( number_format_i18n( $balance, 2 ) )
		);
		?>
	</p>
</div>

<?php if ( empty( $credits ) ) : ?>
	<p class="account-empty"><?php esc_html_e( 'Aucun avoir sur votre compte pour le moment.', 'anrhpub_theme' ); ?></p>
<?php else : ?>
	<table class="account-table account-credits-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'N° avoir', 'anrhpub_theme' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'anrhpub_theme' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Montant', 'anrhpub_theme' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Statut', 'anrhpub_theme' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Motif', 'anrhpub_theme' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $credits as $credit ) : ?>
				<?php
				$order_id = (int) anrhpub_get_credit_meta( $credit->ID, 'credit_order_id', 0 );
				$reason   = anrhpub_get_credit_meta( $credit->ID, 'credit_reason', '' );
				if ( $order_id && ! $reason ) {
					$reason = sprintf(
						/* translators: %s: order number */
						__( 'Suite annulation %s', 'anrhpub_theme' ),
						anrhpub_get_order_meta( $order_id, 'order_number', '' )
					);
				}
				?>
				<tr>
					<td><?php echo esc_html( anrhpub_get_credit_meta( $credit->ID, 'credit_number', $credit->post_title ) ); ?></td>
					<td><?php echo esc_html( get_the_date( 'd/m/Y', $credit ) ); ?></td>
					<td>
						<?php
						printf(
							'%s €',
							esc_html( number_format_i18n( (float) anrhpub_get_credit_meta( $credit->ID, 'credit_amount', 0 ), 2 ) )
						);
						?>
					</td>
					<td>
						<span class="account-credit-status account-credit-status--<?php echo esc_attr( anrhpub_get_credit_meta( $credit->ID, 'credit_status', 'available' ) ); ?>">
							<?php echo esc_html( anrhpub_get_credit_status_label( anrhpub_get_credit_meta( $credit->ID, 'credit_status', 'available' ) ) ); ?>
						</span>
					</td>
					<td><?php echo esc_html( $reason ?: '—' ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
