<?php
/**
 * Template helpers.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product badge label.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_product_badge( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$badge   = get_post_meta( $post_id, 'anr_badge', true );

	if ( ! $badge ) {
		return;
	}

	$labels = array(
		'nouveau' => __( 'Nouveau', 'anrhpub_theme' ),
		'promo'   => __( 'Promo', 'anrhpub_theme' ),
	);

	if ( ! isset( $labels[ $badge ] ) ) {
		return;
	}

	printf(
		'<span class="product-card__badge product-card__badge--%1$s">%2$s</span>',
		esc_attr( $badge ),
		esc_html( $labels[ $badge ] )
	);
}

/**
 * Product reference.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_product_reference( $post_id = 0 ) {
	$post_id   = $post_id ? $post_id : get_the_ID();
	$reference = get_post_meta( $post_id, 'anr_reference', true );

	if ( $reference ) {
		printf( '<span class="product-card__ref">%s</span>', esc_html( $reference ) );
	}
}

/**
 * Product price / devis label.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_product_price( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( function_exists( 'anrhpub_get_product_price_label' ) ) {
		$label = anrhpub_get_product_price_label( $post_id );
	} else {
		$label = get_post_meta( $post_id, 'anr_price_label', true );
		if ( ! $label ) {
			$label = __( 'Sur devis', 'anrhpub_theme' );
		}
	}

	printf( '<span class="product-card__price">%s</span>', esc_html( $label ) );

	if ( function_exists( 'anrhpub_product_price_b2b_extras' ) ) {
		anrhpub_product_price_b2b_extras( $post_id );
	}
}

/**
 * Placeholder thumbnail when no featured image.
 *
 * @param int    $post_id Post ID.
 * @param string $class   Extra CSS class.
 */
function anrhpub_product_thumbnail( $post_id = 0, $class = '' ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, 'medium_large', array( 'class' => 'product-card__img ' . esc_attr( $class ) ) );
		return;
	}

	$terms = get_the_terms( $post_id, 'anr_category' );
	$slug  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : 'default';
	$label = 'A';
	if ( $terms && ! is_wp_error( $terms ) && ! empty( $terms[0]->name ) ) {
		$label = strtoupper( substr( $terms[0]->name, 0, 1 ) );
	}

	printf(
		'<div class="product-card__placeholder product-card__placeholder--%1$s" aria-hidden="true"><span>%2$s</span></div>',
		esc_attr( $slug ),
		esc_html( $label )
	);
}

/**
 * Query featured / popular products.
 *
 * @param string $type popular|new.
 * @param int    $count Number of posts.
 * @return WP_Query
 */
function anrhpub_get_products_query( $type = 'popular', $count = 8 ) {
	$meta_query = array();

	if ( 'new' === $type ) {
		$meta_query[] = array(
			'key'   => 'anr_badge',
			'value' => 'nouveau',
		);
	} else {
		$meta_query[] = array(
			'key'   => 'anr_featured',
			'value' => '1',
		);
	}

	return new WP_Query(
		array(
			'post_type'      => 'anr_product',
			'posts_per_page' => $count,
			'meta_query'     => $meta_query,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

/**
 * Primary navigation categories for mega menu.
 */
function anrhpub_nav_categories() {
	return anrhpub_get_parent_categories( false );
}

/**
 * Render catalogue mega menu panel.
 */
function anrhpub_render_mega_menu_catalogue() {
	get_template_part( 'template-parts/mega-menu', 'catalogue' );
}

/**
 * Check if a menu item should use the catalogue mega menu.
 *
 * @param object $item  Menu item.
 * @param int    $depth Depth.
 */
function anrhpub_is_mega_catalogue_item( $item, $depth = 0 ) {
	if ( 0 !== (int) $depth ) {
		return false;
	}

	$classes = (array) $item->classes;

	if ( in_array( 'mega-catalogue', $classes, true ) ) {
		return true;
	}

	$archive = get_post_type_archive_link( 'anr_product' );
	if ( $archive && ! empty( $item->url ) && untrailingslashit( $item->url ) === untrailingslashit( $archive ) ) {
		return true;
	}

	return false;
}

/**
 * Fallback primary navigation.
 */
function anrhpub_fallback_menu() {
	?>
	<ul class="site-nav__list">
		<li class="menu-item-depth-0">
			<div class="nav-item__row">
				<a class="nav-link" href="<?php echo esc_url( home_url( '/societe/' ) ); ?>"><?php esc_html_e( 'Notre activité', 'anrhpub_theme' ); ?></a>
			</div>
		</li>
		<li class="menu-item-depth-0">
			<div class="nav-item__row">
				<a class="nav-link" href="<?php echo esc_url( anrhpub_anrh_history_url() ); ?>"><?php esc_html_e( 'Histoire ANRH', 'anrhpub_theme' ); ?></a>
			</div>
		</li>
		<?php
		if ( function_exists( 'anrhpub_get_nav_nouveautes_item_html' ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML menu item contrôlé.
			echo anrhpub_get_nav_nouveautes_item_html();
		}
		?>
		<li class="menu-item--mega menu-item--dropdown menu-item-has-children menu-item-depth-0">
			<div class="nav-item__row">
				<a class="nav-link nav-link--parent" href="<?php echo esc_url( get_post_type_archive_link( 'anr_product' ) ); ?>"><?php esc_html_e( 'Nos produits', 'anrhpub_theme' ); ?></a>
				<button type="button" class="nav-dropdown-toggle nav-dropdown-toggle--mega" aria-expanded="false" aria-haspopup="true" aria-controls="mega-menu-catalogue" aria-label="<?php esc_attr_e( 'Ouvrir le catalogue produits', 'anrhpub_theme' ); ?>">
					<span class="nav-dropdown-toggle__icon" aria-hidden="true"></span>
				</button>
			</div>
			<?php anrhpub_render_mega_menu_catalogue(); ?>
		</li>
		<li class="menu-item-depth-0">
			<div class="nav-item__row">
				<a class="nav-link" href="<?php echo esc_url( home_url( '/marquage/' ) ); ?>"><?php esc_html_e( 'Marquage', 'anrhpub_theme' ); ?></a>
			</div>
		</li>
		<li class="menu-item-depth-0">
			<div class="nav-item__row">
				<a class="nav-link" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'anrhpub_theme' ); ?></a>
			</div>
		</li>
	</ul>
	<?php
}

/**
 * Affiche le hero unifié des pages intérieures.
 *
 * @param array $args Voir template-parts/page-hero.php.
 */
function anrhpub_page_hero( $args = array() ) {
	$defaults = array(
		'kicker' => __( 'ANRH Peyruis', 'anrhpub_theme' ),
		'title'  => '',
		'lead'   => '',
		'class'  => '',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( '' === $args['title'] && is_singular() ) {
		$args['title'] = get_the_title();
	}

	get_template_part( 'template-parts/page', 'hero', $args );
}
