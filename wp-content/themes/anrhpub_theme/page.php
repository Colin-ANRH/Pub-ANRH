<?php
/**
 * Page template.
 *
 * @package anrhpub_theme
 */

get_header();
?>

<main id="main-content">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		$hero_lead = '';
		if ( is_page( 'contact' ) ) {
			$hero_lead = __( 'Demande d’information ou de devis personnalisé — notre équipe vous répond sous 48 h ouvrées.', 'anrhpub_theme' );
		}

		anrhpub_page_hero(
			array(
				'title' => get_the_title(),
				'lead'  => $hero_lead,
			)
		);
		?>
		<section class="section page-content<?php echo is_page( 'contact' ) ? ' page-content--contact' : ''; ?>" data-animate>
			<div class="container entry-content">
				<?php the_content(); ?>
				<?php if ( is_page( 'contact' ) ) : ?>
					<?php anrhpub_render_contact_form(); ?>
					<?php get_template_part( 'template-parts/contact', 'panel' ); ?>
				<?php endif; ?>
			</div>
		</section>
	<?php endwhile; ?>
</main>

<?php
get_footer();
