<?php
/**
 * Bouton favori produit (connexion client obligatoire).
 *
 * @package anrhpub_theme
 *
 * @var array $args {
 *   post_id: int,
 *   is_fav: bool,
 *   logged_in: bool,
 *   variant?: string card|single
 * }
 */

defined( 'ABSPATH' ) || exit;

$post_id   = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
$is_fav    = ! empty( $args['is_fav'] );
$logged_in = ! empty( $args['logged_in'] );
$variant   = isset( $args['variant'] ) ? (string) $args['variant'] : 'card';

if ( ! $post_id ) {
	return;
}

$is_single = 'single' === $variant;

if ( $logged_in ) {
	$label = $is_fav
		? __( 'Retirer des favoris', 'anrhpub_theme' )
		: __( 'Ajouter aux favoris', 'anrhpub_theme' );
} else {
	$label = __( 'Se connecter pour ajouter aux favoris', 'anrhpub_theme' );
}

$classes = array( 'product-favorite' );
if ( $is_fav ) {
	$classes[] = 'is-active';
}
if ( ! $logged_in ) {
	$classes[] = 'product-favorite--guest';
}
if ( $is_single ) {
	$classes[] = 'product-favorite--single';
}

$heart_svg = '<svg class="product-favorite__svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path class="product-favorite__path" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" d="M12 20.25S3.75 15.1 3.75 9.4A4.65 4.65 0 0 1 8.5 4.75c1.55 0 2.7.75 3.5 1.85.8-1.1 1.95-1.85 3.5-1.85a4.65 4.65 0 0 1 4.75 4.65c0 5.7-8.25 10.85-8.25 10.85z"/></svg>';

if ( ! $logged_in ) {
	$login_url = add_query_arg(
		'account_notice',
		'favorite_login',
		anrhpub_login_url( get_permalink( $post_id ) ? get_permalink( $post_id ) : home_url( '/' ) )
	);
	?>
	<a
		class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
		href="<?php echo esc_url( $login_url ); ?>"
		aria-label="<?php echo esc_attr( $label ); ?>"
		title="<?php echo esc_attr( $label ); ?>"
		data-favorite-login="1"
	>
		<?php echo $heart_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
		<?php if ( $is_single ) : ?>
			<span class="product-favorite__text"><?php esc_html_e( 'Favoris', 'anrhpub_theme' ); ?></span>
		<?php endif; ?>
	</a>
	<?php
	return;
}
?>
<button
	type="button"
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	data-product-id="<?php echo esc_attr( (string) $post_id ); ?>"
	aria-pressed="<?php echo $is_fav ? 'true' : 'false'; ?>"
	aria-label="<?php echo esc_attr( $label ); ?>"
	title="<?php echo esc_attr( $label ); ?>"
>
	<?php echo $heart_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
	<?php if ( $is_single ) : ?>
		<span class="product-favorite__text">
			<?php echo $is_fav ? esc_html__( 'Dans mes favoris', 'anrhpub_theme' ) : esc_html__( 'Favoris', 'anrhpub_theme' ); ?>
		</span>
	<?php endif; ?>
</button>
