<?php
/**
 * Lien « Les nouveautés » — menu principal.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * HTML d’un item de menu « Les nouveautés ».
 *
 * @return string
 */
function anrhpub_get_nav_nouveautes_item_html() {
	$url = function_exists( 'anrhpub_nouveautes_catalogue_url' )
		? anrhpub_nouveautes_catalogue_url()
		: anrhpub_catalogue_url();

	ob_start();
	?>
	<li class="menu-item menu-item-depth-0 menu-item--nouveautes">
		<div class="nav-item__row">
			<a class="nav-link" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Les nouveautés', 'anrhpub_theme' ); ?></a>
		</div>
	</li>
	<?php
	return (string) ob_get_clean();
}

/**
 * Le menu contient-il déjà le lien nouveautés ?
 *
 * @param string $items Menu HTML.
 * @return bool
 */
function anrhpub_nav_has_nouveautes_link( $items ) {
	$slug = function_exists( 'anrhpub_nouveautes_category_slug' )
		? anrhpub_nouveautes_category_slug()
		: 'les-nouveautes-objets-pubs';

	return false !== strpos( $items, $slug );
}

/**
 * Corrige d’anciens liens menu pointant vers l’ancre accueil.
 *
 * @param string   $items HTML des items.
 * @param stdClass $args  Arguments wp_nav_menu.
 * @return string
 */
function anrhpub_filter_nav_menu_fix_nouveautes_href( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $items;
	}

	$target = function_exists( 'anrhpub_nouveautes_catalogue_url' )
		? anrhpub_nouveautes_catalogue_url()
		: '';

	if ( ! $target ) {
		return $items;
	}

	$legacy = array(
		home_url( '/#les-nouveautes' ),
		home_url( '#les-nouveautes' ),
	);

	foreach ( $legacy as $old ) {
		$items = str_replace( 'href="' . esc_url( $old ) . '"', 'href="' . esc_url( $target ) . '"', $items );
	}

	return $items;
}
add_filter( 'wp_nav_menu_items', 'anrhpub_filter_nav_menu_fix_nouveautes_href', 10, 2 );

/**
 * Insère l’item avant le lien catalogue si possible.
 *
 * @param string $items Menu HTML.
 * @param string $item  Item à insérer.
 * @return string
 */
function anrhpub_nav_insert_nouveautes_item( $items, $item ) {
	$archive = get_post_type_archive_link( 'anr_product' );
	if ( $archive ) {
		$needle = 'href="' . esc_url( untrailingslashit( $archive ) );
		$pos    = strpos( $items, $needle );
		if ( false === $pos ) {
			$needle = 'href="' . esc_url( trailingslashit( $archive ) );
			$pos    = strpos( $items, $needle );
		}
		if ( false !== $pos ) {
			$before  = substr( $items, 0, $pos );
			$li_start = strrpos( $before, '<li' );
			if ( false !== $li_start ) {
				return substr( $items, 0, $li_start ) . $item . substr( $items, $li_start );
			}
		}
	}

	return $item . $items;
}

/**
 * Ajoute « Les nouveautés » au menu WordPress assigné.
 *
 * @param string   $items HTML des items.
 * @param stdClass $args  Arguments wp_nav_menu.
 * @return string
 */
function anrhpub_filter_nav_menu_nouveautes( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
		return $items;
	}
	if ( anrhpub_nav_has_nouveautes_link( $items ) ) {
		return $items;
	}

	return anrhpub_nav_insert_nouveautes_item( $items, anrhpub_get_nav_nouveautes_item_html() );
}
add_filter( 'wp_nav_menu_items', 'anrhpub_filter_nav_menu_nouveautes', 15, 2 );
