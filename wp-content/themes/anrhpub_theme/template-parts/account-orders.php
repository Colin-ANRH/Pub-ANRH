<?php
/**
 * Liste commandes — espace client.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$orders = anrhpub_get_client_orders();
?>
<?php if ( empty( $orders ) ) : ?>
	<p class="account-empty"><?php esc_html_e( 'Vous n’avez pas encore de commande enregistrée.', 'anrhpub_theme' ); ?></p>
<?php else : ?>
	<div class="account-orders-list">
		<?php foreach ( $orders as $order ) : ?>
			<?php
			$order_id   = $order->ID;
			$number     = anrhpub_get_order_meta( $order_id, 'order_number', $order->post_title );
			$status     = anrhpub_get_order_meta( $order_id, 'order_status', 'pending' );
			$total      = (float) anrhpub_get_order_meta( $order_id, 'order_total', 0 );
			$lines      = anrhpub_get_order_lines( $order_id );
			$address    = anrhpub_get_order_delivery_address( $order_id );
			$date       = get_the_date( 'd/m/Y', $order );
			?>
			<article class="account-order-card account-order-card--<?php echo esc_attr( $status ); ?>">
				<header class="account-order-card__head">
					<div>
						<strong class="account-order-card__number"><?php echo esc_html( $number ); ?></strong>
						<span class="account-order-card__date"><?php echo esc_html( $date ); ?></span>
					</div>
					<span class="account-order-card__status"><?php echo esc_html( anrhpub_get_order_status_label( $status ) ); ?></span>
				</header>
				<?php if ( $total > 0 ) : ?>
					<p class="account-order-card__total">
						<?php
						printf(
							/* translators: %s: formatted amount */
							esc_html__( 'Montant : %s €', 'anrhpub_theme' ),
							esc_html( number_format_i18n( $total, 2 ) )
						);
						?>
					</p>
				<?php endif; ?>
				<?php if ( ! empty( $lines ) ) : ?>
					<ul class="account-order-card__lines">
						<?php foreach ( $lines as $line ) : ?>
							<li>
								<?php if ( ! empty( $line['ref'] ) ) : ?>
									<span class="account-order-card__ref"><?php echo esc_html( $line['ref'] ); ?></span>
								<?php endif; ?>
								<?php echo esc_html( $line['label'] ?? '' ); ?>
								× <?php echo (int) ( $line['qty'] ?? 1 ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<?php if ( ! empty( $address ) ) : ?>
					<div class="account-order-card__address">
						<strong><?php esc_html_e( 'Livraison', 'anrhpub_theme' ); ?></strong>
						<pre class="account-order-card__address-text"><?php echo esc_html( anrhpub_format_address_text( $address ) ); ?></pre>
					</div>
				<?php endif; ?>
				<?php if ( 'cancelled' === $status ) : ?>
					<p class="account-order-card__note"><?php esc_html_e( 'Commande annulée — un avoir peut avoir été émis sur votre compte.', 'anrhpub_theme' ); ?></p>
				<?php endif; ?>
				<?php if ( function_exists( 'anrhpub_get_reorder_url' ) ) : ?>
					<p class="account-order-card__actions">
						<a class="btn btn--outline btn--sm" href="<?php echo esc_url( anrhpub_get_reorder_url( $order_id ) ); ?>">
							<?php esc_html_e( 'Re-commander', 'anrhpub_theme' ); ?>
						</a>
					</p>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
