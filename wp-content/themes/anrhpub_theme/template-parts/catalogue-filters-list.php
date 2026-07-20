<?php
/**
 * Liste des filtres catégories (sidebar catalogue) — dropdowns par univers.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$current_term = get_queried_object();
$is_tax       = is_tax( 'anr_category' );
$filter_term  = ( $is_tax && $current_term instanceof WP_Term ) ? $current_term : null;
?>
<nav class="catalogue-nav" aria-label="<?php esc_attr_e( 'Filtrer par catégorie', 'anrhpub_theme' ); ?>">
	<ul id="catalogue-filters-list" class="catalogue-nav__list">
		<li class="catalogue-nav__all">
			<a class="catalogue-nav__all-link<?php echo $is_tax ? '' : ' is-active'; ?>" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
				<span><?php esc_html_e( 'Toutes les catégories', 'anrhpub_theme' ); ?></span>
			</a>
		</li>
		<?php anrhpub_render_catalogue_filters( $filter_term ); ?>
	</ul>
</nav>
