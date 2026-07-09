<?php
/**
 * Slider accueil — 3 visuels vitrine.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slides du slider accueil (ordre fixe).
 *
 * @return array<int, array{src: string, alt: string}>
 */
function anrhpub_get_home_slider_slides() {
	$base_remote = 'https://anr-pub.fr/modules/wpimageslider/views/img/';

	$slides = array(
		array(
			'file'    => '01-objets-pub.jpg',
			'remote'  => $base_remote . '25b60a8f3b14b8e82d203803f7a7f38a66c539a3_IMG-objets-pub.jpg',
			'alt'     => __( 'Objets publicitaires pour professionnels', 'anrhpub_theme' ),
		),
		array(
			'file'    => '02-com.jpg',
			'remote'  => $base_remote . 'ca302e9b90ea0dfda38772f9ca1cf9ed2cf6cc7d_IMG-COM.jpg',
			'alt'     => __( 'Communication et objets publicitaires', 'anrhpub_theme' ),
		),
		array(
			'file'    => '03-personnalisation.jpg',
			'remote'  => $base_remote . '6d946b3b3491b2c4949a5ba4bed17ef2aac9bc8b_IMG-personnalisation.jpg',
			'alt'     => __( 'Personnalisation et marquage', 'anrhpub_theme' ),
		),
	);

	$resolved = array();

	foreach ( $slides as $slide ) {
		$base_name  = pathinfo( $slide['file'], PATHINFO_FILENAME );
		$webp_path  = ANRHPUB_THEME_DIR . '/assets/img/home-slider/' . $base_name . '.webp';
		$local_path = ANRHPUB_THEME_DIR . '/assets/img/home-slider/' . $slide['file'];

		if ( file_exists( $webp_path ) ) {
			$src = ANRHPUB_THEME_URI . '/assets/img/home-slider/' . $base_name . '.webp';
		} elseif ( file_exists( $local_path ) ) {
			$src = ANRHPUB_THEME_URI . '/assets/img/home-slider/' . $slide['file'];
		} else {
			$src = $slide['remote'];
		}

		$resolved[] = array(
			'src' => esc_url( $src ),
			'alt' => $slide['alt'],
		);
	}

	return apply_filters( 'anrhpub_home_slider_slides', $resolved );
}

/**
 * Intervalle auto (ms) pour le slider accueil.
 *
 * @return int
 */
function anrhpub_get_home_slider_interval() {
	return (int) apply_filters( 'anrhpub_home_slider_interval', 5000 );
}

/**
 * Affiche le slider sur l’accueil.
 */
function anrhpub_render_home_slider() {
	if ( ! is_front_page() ) {
		return;
	}

	$slides = anrhpub_get_home_slider_slides();

	if ( empty( $slides ) ) {
		return;
	}

	get_template_part( 'template-parts/home', 'slider', array( 'slides' => $slides ) );
}

/**
 * Données JS slider accueil.
 */
function anrhpub_enqueue_home_slider_assets() {
	if ( ! is_front_page() ) {
		return;
	}

	$slides = anrhpub_get_home_slider_slides();

	if ( empty( $slides ) ) {
		return;
	}

	wp_localize_script(
		'anrhpub-main',
		'anrhpubHomeSlider',
		array(
			'interval' => anrhpub_get_home_slider_interval(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'anrhpub_enqueue_home_slider_assets', 50 );
