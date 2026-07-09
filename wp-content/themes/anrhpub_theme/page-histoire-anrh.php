<?php
/**
 * Template — Histoire de l’ANRH (résumé + lien anrh.fr).
 *
 * @package anrhpub_theme
 */

get_header();
?>

<main id="main-content" class="page-anrh-history">
	<?php
	anrhpub_page_hero(
		array(
			'kicker' => __( 'Association', 'anrhpub_theme' ),
			'title'  => __( 'Découvrez l’histoire de l’ANRH', 'anrhpub_theme' ),
			'lead'   => __( 'Depuis 1954, l’ANRH œuvre pour l’emploi durable des personnes en situation de handicap, en conciliant inclusion sociale et performance économique.', 'anrhpub_theme' ),
			'class'  => 'page-hero--anrh-history',
		)
	);
	?>

	<section class="section section--anrh-history" data-animate>
		<div class="container anrh-history">
			<?php get_template_part( 'template-parts/anrh', 'history-content' ); ?>
		</div>
	</section>
</main>

<?php
get_footer();
