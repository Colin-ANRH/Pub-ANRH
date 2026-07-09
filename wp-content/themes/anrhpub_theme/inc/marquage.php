<?php
/**
 * Techniques de marquage — contenus et données.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Détail des techniques de marquage (ordre d’affichage).
 *
 * @return array<int, array{slug: string, label: string, intro: string, features: string[]}>
 */
function anrhpub_marquage_techniques() {
	return array(
		array(
			'slug'     => 'tampographie',
			'label'    => __( 'Tampographie', 'anrhpub_theme' ),
			'intro'    => __( 'La tampographie correspond à un marquage qui transfère l’encre depuis une plaque métallique gravée, via un tampon, sur le produit.', 'anrhpub_theme' ),
			'features' => array(
				__( 'Idéal pour un marquage en grande quantité.', 'anrhpub_theme' ),
				__( 'Applicable sur beaucoup de formes (plat, rond, concave…).', 'anrhpub_theme' ),
				__( 'Convient à tout type de revêtement (bois, plastique, métal, verre…).', 'anrhpub_theme' ),
				__( 'Technique de marquage peu onéreuse et polyvalente.', 'anrhpub_theme' ),
			),
		),
		array(
			'slug'     => 'laser',
			'label'    => __( 'Gravure laser', 'anrhpub_theme' ),
			'intro'    => __( 'La gravure laser est réalisée en utilisant un faisceau laser pour graver de manière très précise une matière.', 'anrhpub_theme' ),
			'features' => array(
				__( 'Rendu très qualitatif.', 'anrhpub_theme' ),
				__( 'Uniquement ton sur ton.', 'anrhpub_theme' ),
				__( 'Particulièrement adapté au métal, verre et bois.', 'anrhpub_theme' ),
			),
		),
		array(
			'slug'     => 'led-uv',
			'label'    => __( 'Impression LED UV', 'anrhpub_theme' ),
			'intro'    => __( 'L’impression par LED UV dépose des micro-gouttelettes d’encre sur le produit pour donner une image. L’encre est immédiatement séchée avec une lampe UV.', 'anrhpub_theme' ),
			'features' => array(
				__( 'Idéal pour les logos en plusieurs couleurs et les petites quantités.', 'anrhpub_theme' ),
				__( 'Beau rendu avec des finitions mates, brillantes ou embossées.', 'anrhpub_theme' ),
				__( 'Réalisation sur des surfaces planes ou très légèrement bombées.', 'anrhpub_theme' ),
			),
		),
		array(
			'slug'     => 'flocage',
			'label'    => __( 'Flocage', 'anrhpub_theme' ),
			'intro'    => __( 'Le flocage est une technique d’impression textile idéale pour les petites quantités et plus particulièrement pour les impressions à la pièce. La technique consiste à imprimer un motif sur un film en polyuréthane. Le film de transfert est ensuite découpé à l’aide d’un plotter de découpe puis collé sur le tissu avec une presse à chaud à haute température.', 'anrhpub_theme' ),
			'features' => array(
				__( 'Les couleurs d’un motif floqué restent telles quelles, même après plusieurs lavages.', 'anrhpub_theme' ),
			),
		),
		array(
			'slug'     => 'sublimation',
			'label'    => __( 'Sublimation numérique', 'anrhpub_theme' ),
			'intro'    => __( 'La sublimation numérique est une technique d’impression pour supports clairs utilisant des encres spécifiques. Cela consiste à imprimer, grâce à une imprimante de haute qualité et sur un papier spécial, un visuel au moyen d’encres à sublimation. Le papier est ensuite pressé à l’aide d’une presse à chaud. Elle est réservée exclusivement aux marquages textiles en polyester et aux supports durs préalablement traités d’un vernis industriel polyester comme la céramique, l’aluminium, le bois, l’ardoise…', 'anrhpub_theme' ),
			'features' => array(
				__( 'Convient pour les tissus polyester et les produits revêtus de polymères.', 'anrhpub_theme' ),
				__( 'La décoration résiste à l’usure et à la fissuration.', 'anrhpub_theme' ),
			),
		),
	);
}

/**
 * Libellé court pour aperçus (cartes, menu).
 *
 * @param string $slug Technique slug.
 * @return string
 */
function anrhpub_marquage_technique_short_desc( $slug ) {
	$short = array(
		'tampographie' => __( 'Marquage par tampon, polyvalent et économique.', 'anrhpub_theme' ),
		'laser'        => __( 'Gravure laser précise, ton sur ton.', 'anrhpub_theme' ),
		'led-uv'       => __( 'Impression quadri UV sur supports plats.', 'anrhpub_theme' ),
		'flocage'      => __( 'Transfert textile durable, petites séries.', 'anrhpub_theme' ),
		'sublimation'  => __( 'Encres sublimées, textile polyester et supports traités.', 'anrhpub_theme' ),
	);

	return $short[ $slug ] ?? '';
}
