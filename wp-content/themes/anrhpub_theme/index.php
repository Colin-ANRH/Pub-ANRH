<?php
/**
 * Main template fallback.
 *
 * @package anrhpub_theme
 */

get_header();
?>

<main id="main-content" class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'content-article' ); ?>>
					<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
					<div class="entry-content"><?php the_content(); ?></div>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucun contenu trouvé.', 'anrhpub_theme' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
