<?php
/**
 * Navigation walker with accessible dropdown markup.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Primary menu walker.
 */
class Anrhpub_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Mega menu parent item ID (children skipped).
	 *
	 * @var int
	 */
	protected $mega_parent_id = 0;

	/**
	 * Opens sub-menu list.
	 *
	 * @param string $output Output.
	 * @param int    $depth  Depth.
	 * @param array  $args   Args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( $this->mega_parent_id && 0 === (int) $depth ) {
			return;
		}

		$indent  = str_repeat( "\t", $depth );
		$classes = 'sub-menu sub-menu--depth-' . (int) $depth;
		$output .= "\n$indent<ul class=\"$classes\">\n";
	}

	/**
	 * Closes sub-menu list.
	 *
	 * @param string $output Output.
	 * @param int    $depth  Depth.
	 * @param array  $args   Args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( $this->mega_parent_id && 0 === (int) $depth ) {
			return;
		}

		$indent  = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}

	/**
	 * Skip rendering WP submenu items under mega catalogue parent.
	 *
	 * @param object $element           Menu item.
	 * @param array  $children_elements Children map.
	 * @param int    $max_depth         Max depth.
	 * @param int    $depth             Depth.
	 * @param array  $args              Args.
	 * @param string $output            Output.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( $this->mega_parent_id && (int) $element->menu_item_parent === (int) $this->mega_parent_id ) {
			return;
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	}

	/**
	 * Menu item start.
	 *
	 * @param string $output Output.
	 * @param object $item   Item.
	 * @param int    $depth  Depth.
	 * @param array  $args   Args.
	 * @param int    $id     ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$indent       = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		$has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );
		$is_mega      = anrhpub_is_mega_catalogue_item( $item, $depth );

		if ( $is_mega ) {
			$this->mega_parent_id = (int) $item->ID;
			$has_children         = true;
		}

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-depth-' . (int) $depth;

		if ( $is_mega ) {
			$classes[] = 'menu-item--mega';
		}
		if ( $has_children ) {
			$classes[] = 'menu-item--dropdown';
		}

		$class_names = implode( ' ', array_map( 'sanitize_html_class', array_filter( $classes ) ) );
		$output     .= $indent . '<li class="' . esc_attr( $class_names ) . '">';

		$atts           = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']   = ! empty( $item->url ) ? $item->url : '';

		if ( $has_children && 0 === (int) $depth ) {
			$atts['class'] = 'nav-link nav-link--parent';
		} else {
			$atts['class'] = 'nav-link';
		}

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
			}
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );

		$item_output  = isset( $args->before ) ? $args->before : '';
		$item_output .= '<div class="nav-item__row">';

		$item_output .= '<a' . $attributes . '>';
		$item_output .= ( isset( $args->link_before ) ? $args->link_before : '' ) . esc_html( $title ) . ( isset( $args->link_after ) ? $args->link_after : '' );
		$item_output .= '</a>';

		if ( $has_children && 0 === (int) $depth ) {
			$toggle_label = $is_mega
				? __( 'Ouvrir le catalogue produits', 'anrhpub_theme' )
				: sprintf( __( 'Ouvrir le sous-menu : %s', 'anrhpub_theme' ), $title );

			$toggle_class = $is_mega ? 'nav-dropdown-toggle nav-dropdown-toggle--mega' : 'nav-dropdown-toggle';
			$toggle_extra = $is_mega ? ' aria-controls="mega-menu-catalogue"' : '';

			$item_output .= '<button type="button" class="' . esc_attr( $toggle_class ) . '" aria-expanded="false" aria-haspopup="true"' . $toggle_extra . ' aria-label="' . esc_attr( $toggle_label ) . '">';
			$item_output .= '<span class="nav-dropdown-toggle__icon" aria-hidden="true"></span>';
			$item_output .= '</button>';
		}

		$item_output .= '</div>';
		$item_output .= isset( $args->after ) ? $args->after : '';

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Menu item end.
	 *
	 * @param string $output Output.
	 * @param object $item   Item.
	 * @param int    $depth  Depth.
	 * @param array  $args   Args.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		if ( anrhpub_is_mega_catalogue_item( $item, $depth ) ) {
			ob_start();
			anrhpub_render_mega_menu_catalogue();
			$output .= ob_get_clean();
			$this->mega_parent_id = 0;
		}

		$output .= "</li>\n";
	}
}
