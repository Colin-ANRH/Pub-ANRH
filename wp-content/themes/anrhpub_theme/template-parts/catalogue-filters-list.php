<?php
/**
 * Liste des filtres catégories (sidebar catalogue).
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$current_term = get_queried_object();
$is_tax       = is_tax( 'anr_category' );
$filter_term  = ( $is_tax && $current_term instanceof WP_Term ) ? $current_term : null;
?>
<ul id="catalogue-filters-list">
	<li>
		<a class="<?php echo $is_tax ? '' : 'is-active'; ?>" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
			<?php esc_html_e( 'Toutes', 'anrhpub_theme' ); ?>
		</a>
	</li>
	<?php anrhpub_render_catalogue_filters( $filter_term ); ?>
</ul>
