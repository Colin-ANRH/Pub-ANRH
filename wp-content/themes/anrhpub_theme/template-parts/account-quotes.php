<?php
/**
 * Historique devis — espace client.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$quotes = function_exists( 'anrhpub_get_client_quotes' ) ? anrhpub_get_client_quotes() : array();
?>
<?php if ( empty( $quotes ) ) : ?>
	<p class="account-empty"><?php esc_html_e( 'Aucun devis enregistré pour le moment.', 'anrhpub_theme' ); ?></p>
<?php else : ?>
	<div class="account-quotes-list">
		<?php foreach ( $quotes as $quote ) : ?>
			<?php
			$quote_id = $quote->ID;
			$number   = anrhpub_get_quote_meta( $quote_id, 'number', $quote->post_title );
			$status   = anrhpub_get_quote_meta( $quote_id, 'status', 'pending' );
			$lines    = anrhpub_get_quote_lines( $quote_id );
			?>
			<article class="account-quote-card account-quote-card--<?php echo esc_attr( $status ); ?>">
				<header class="account-quote-card__head">
					<div>
						<strong><?php echo esc_html( $number ); ?></strong>
						<span class="account-quote-card__date"><?php echo esc_html( get_the_date( 'd/m/Y', $quote ) ); ?></span>
					</div>
					<span class="account-quote-card__status"><?php echo esc_html( anrhpub_get_quote_status_label( $status ) ); ?></span>
				</header>
				<?php if ( ! empty( $lines ) ) : ?>
					<ul class="account-quote-card__lines">
						<?php foreach ( array_slice( $lines, 0, 5 ) as $line ) : ?>
							<li><?php echo esc_html( ( $line['label'] ?? '' ) . ' × ' . (int) ( $line['qty'] ?? 1 ) ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
				<p class="account-quote-card__actions">
					<a class="btn btn--outline btn--sm" href="<?php echo esc_url( anrhpub_get_quote_pdf_url( $quote_id ) ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'PDF', 'anrhpub_theme' ); ?>
					</a>
				</p>
			</article>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
