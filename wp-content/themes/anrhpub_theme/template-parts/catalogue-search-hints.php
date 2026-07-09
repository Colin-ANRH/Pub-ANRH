<?php
/**
 * Catégories suggérées lors d'une recherche catalogue.
 *
 * @package anrhpub_theme
 *
 * @var array $args { terms: WP_Term[] }
 */

defined( 'ABSPATH' ) || exit;

$terms = isset( $args['terms'] ) && is_array( $args['terms'] ) ? $args['terms'] : array();

if ( empty( $terms ) ) {
	return;
}
?>
<div class="catalogue-search-hints">
	<p class="catalogue-search-hints__label"><?php esc_html_e( 'Catégories correspondantes', 'anrhpub_theme' ); ?></p>
	<ul class="catalogue-search-hints__list" role="list">
		<?php foreach ( $terms as $term ) : ?>
			<?php
			if ( ! $term instanceof WP_Term ) {
				continue;
			}
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			?>
			<li>
				<a class="catalogue-search-hints__link" href="<?php echo esc_url( $link ); ?>">
					<?php echo esc_html( $term->name ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
