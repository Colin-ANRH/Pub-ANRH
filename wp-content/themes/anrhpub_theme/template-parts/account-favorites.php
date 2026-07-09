<?php
/**
 * Grille des produits favoris (profil client).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$favorites = anrhpub_get_user_favorites();

if ( empty( $favorites ) ) {
	?>
	<p class="account-favorites-empty">
		<?php esc_html_e( 'Vous n’avez pas encore de favori.', 'anrhpub_theme' ); ?>
		<a href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>"><?php esc_html_e( 'Parcourir le catalogue', 'anrhpub_theme' ); ?></a>
	</p>
	<?php
	return;
}

$query = new WP_Query(
	array(
		'post_type'      => 'anr_product',
		'post_status'    => 'publish',
		'post__in'       => $favorites,
		'orderby'        => 'post__in',
		'posts_per_page' => -1,
	)
);

if ( ! $query->have_posts() ) {
	?>
	<p class="account-favorites-empty"><?php esc_html_e( 'Vos favoris ne sont plus disponibles dans le catalogue.', 'anrhpub_theme' ); ?></p>
	<?php
	return;
}
?>
<div class="product-grid product-grid--favorites">
	<?php
	while ( $query->have_posts() ) :
		$query->the_post();
		get_template_part( 'template-parts/product', 'card' );
	endwhile;
	wp_reset_postdata();
	?>
</div>
