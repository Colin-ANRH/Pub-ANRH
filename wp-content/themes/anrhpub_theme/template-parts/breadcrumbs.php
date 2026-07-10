<?php
/**
 * Fil d'Ariane.
 *
 * @package anrhpub_theme
 *
 * @var array $args Template args.
 */

defined( 'ABSPATH' ) || exit;

$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : anrhpub_get_breadcrumb_items();
$count = count( $items );

if ( $count < 1 ) {
	return;
}
?>
<div id="site-breadcrumb">
<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Fil d\'Ariane', 'anrhpub_theme' ); ?>" data-animate>
	<div class="container">
		<ol class="breadcrumb__list" itemscope itemtype="https://schema.org/BreadcrumbList">
			<?php foreach ( $items as $index => $item ) : ?>
				<?php
				$position = $index + 1;
				$is_last  = ( $position === $count );
				$label    = $item['label'] ?? '';
				$url      = $item['url'] ?? '';
				?>
				<li class="breadcrumb__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<?php if ( ! $is_last && $url ) : ?>
						<a class="breadcrumb__link" href="<?php echo esc_url( $url ); ?>" itemprop="item">
							<span itemprop="name"><?php echo esc_html( $label ); ?></span>
						</a>
					<?php else : ?>
						<span class="breadcrumb__current" itemprop="name" aria-current="page"><?php echo esc_html( $label ); ?></span>
					<?php endif; ?>
					<meta itemprop="position" content="<?php echo esc_attr( (string) $position ); ?>" />
					<?php if ( ! $is_last ) : ?>
						<span class="breadcrumb__sep" aria-hidden="true">/</span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</nav>
</div>
