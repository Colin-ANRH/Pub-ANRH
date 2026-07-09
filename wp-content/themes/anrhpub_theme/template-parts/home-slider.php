<?php
/**
 * Visuels vitrine accueil — 3 images affichées en même temps.
 *
 * @package anrhpub_theme
 * @var array $args['slides']
 */

defined( 'ABSPATH' ) || exit;

$slides = isset( $args['slides'] ) && is_array( $args['slides'] ) ? $args['slides'] : array();

if ( empty( $slides ) ) {
	return;
}

$slide_count = count( $slides );
$trio        = $slide_count > 1;
?>
<div
	class="home-slider<?php echo $trio ? ' home-slider--trio' : ' home-slider--single'; ?>"
	role="group"
	aria-label="<?php esc_attr_e( 'Visuels catalogue ANRH Peyruis', 'anrhpub_theme' ); ?>"
>
	<div class="home-slider__frame">
		<ul class="home-slider__track">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<li class="home-slider__slide">
					<img
						class="home-slider__img"
						src="<?php echo esc_url( $slide['src'] ); ?>"
						alt="<?php echo esc_attr( $slide['alt'] ); ?>"
						<?php echo 0 === $index ? ' fetchpriority="high"' : ' loading="lazy"'; ?>
						decoding="async"
					/>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
