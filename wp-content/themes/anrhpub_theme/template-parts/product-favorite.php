<?php
/**
 * Bouton favori produit.
 *
 * @package anrhpub_theme
 *
 * @var array $args { post_id: int, is_fav: bool, logged_in: bool }
 */

defined( 'ABSPATH' ) || exit;

$post_id   = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
$is_fav    = ! empty( $args['is_fav'] );
$logged_in = ! empty( $args['logged_in'] );

if ( ! $post_id ) {
	return;
}

$label = $is_fav
	? __( 'Retirer des favoris', 'anrhpub_theme' )
	: __( 'Ajouter aux favoris', 'anrhpub_theme' );
?>
<button
	type="button"
	class="product-favorite<?php echo $is_fav ? ' is-active' : ''; ?><?php echo $logged_in ? '' : ' product-favorite--guest'; ?>"
	data-product-id="<?php echo esc_attr( (string) $post_id ); ?>"
	aria-pressed="<?php echo $is_fav ? 'true' : 'false'; ?>"
	aria-label="<?php echo esc_attr( $label ); ?>"
	title="<?php echo esc_attr( $label ); ?>"
>
	<span class="product-favorite__icon" aria-hidden="true">♥</span>
</button>
