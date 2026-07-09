<?php
/**
 * Notice compte (succès / info).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$notice = anrhpub_get_account_notice();

if ( ! $notice ) {
	return;
}
?>
<div class="account-alert account-alert--<?php echo esc_attr( $notice['type'] ); ?>" role="status">
	<?php echo esc_html( $notice['message'] ); ?>
</div>
