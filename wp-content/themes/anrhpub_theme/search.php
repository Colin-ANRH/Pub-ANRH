<?php
/**
 * Search results.
 *
 * @package anrhpub_theme
 */

get_header();
?>

<main id="main-content" class="section">
	<div class="container">
		<h1><?php printf( esc_html__( 'Résultats pour « %s »', 'anrhpub_theme' ), esc_html( get_search_query() ) ); ?></h1>
		<?php if ( have_posts() ) : ?>
			<div class="product-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					if ( 'anr_product' === get_post_type() ) {
						get_template_part( 'template-parts/product', 'card' );
					} else {
						?>
						<article <?php post_class( 'content-article' ); ?>>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						</article>
						<?php
					}
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucun résultat. Essayez un autre mot-clé.', 'anrhpub_theme' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
