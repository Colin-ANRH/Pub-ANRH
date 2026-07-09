<?php
/**
 * Carrousel nouveautés — hero accueil.
 *
 * @package anrhpub_theme
 * @var array $args { query: WP_Query, compact?: bool }
 */

defined( 'ABSPATH' ) || exit;

$query   = isset( $args['query'] ) && $args['query'] instanceof WP_Query ? $args['query'] : null;
$compact = ! empty( $args['compact'] );

if ( ! $query || ! $query->have_posts() ) {
	return;
}

$total = (int) $query->post_count;
$index = 0;

$classes = array(
	'hero-spotlight',
	'hero-spotlight--carousel',
);

if ( $compact ) {
	$classes[] = 'hero-spotlight--in-intro';
	$classes[] = 'hero-spotlight--epure';
}

if ( $total < 2 ) {
	$classes[] = 'hero-spotlight--static';
}
?>
<aside
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	<?php echo $total > 1 ? ' data-spotlight-carousel' : ''; ?>
	data-animate
	aria-roledescription="carousel"
	aria-label="<?php esc_attr_e( 'Nouveautés du catalogue', 'anrhpub_theme' ); ?>"
>
	<header class="hero-spotlight__head">
		<div class="hero-spotlight__head-main">
			<span class="hero-spotlight__eyebrow"><?php esc_html_e( 'Nouveautés', 'anrhpub_theme' ); ?></span>
			<?php if ( $compact ) : ?>
				<span class="hero-spotlight__head-tag" aria-hidden="true"><?php esc_html_e( 'Sélection du moment', 'anrhpub_theme' ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( $total > 1 ) : ?>
			<p class="hero-spotlight__counter" data-spotlight-counter aria-live="polite">
				<span class="hero-spotlight__counter-current" data-spotlight-current>1</span>
				<span class="hero-spotlight__counter-sep" aria-hidden="true">/</span>
				<span class="hero-spotlight__counter-total"><?php echo esc_html( (string) $total ); ?></span>
			</p>
		<?php endif; ?>
	</header>

	<div class="hero-spotlight__viewport">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			$ref     = get_post_meta( get_the_ID(), 'anr_reference', true );
			$price   = get_post_meta( get_the_ID(), 'anr_price_label', true );
			$add_url = anrhpub_get_product_add_url( get_the_ID() );
			$active  = 0 === $index;
			++$index;
			?>
			<article
				class="hero-spotlight__slide<?php echo $active ? ' is-active' : ''; ?>"
				id="hero-spotlight-slide-<?php echo esc_attr( (string) $index ); ?>"
				role="group"
				aria-roledescription="slide"
				aria-label="<?php echo esc_attr( sprintf( __( 'Nouveauté %1$d sur %2$d', 'anrhpub_theme' ), $index, $total ) ); ?>"
				<?php echo ! $active ? ' hidden' : ''; ?>
				data-carousel-slide
			>
				<div class="hero-spotlight__card">
					<a class="hero-spotlight__media" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium_large', array( 'class' => 'hero-spotlight__img' ) ); ?>
						<?php else : ?>
							<div class="hero-spotlight__placeholder" aria-hidden="true"></div>
						<?php endif; ?>
						<span class="hero-spotlight__badge"><?php esc_html_e( 'Nouveau', 'anrhpub_theme' ); ?></span>
					</a>

					<div class="hero-spotlight__body">
						<?php if ( $ref ) : ?>
							<span class="hero-spotlight__ref"><?php echo esc_html( $ref ); ?></span>
						<?php endif; ?>
						<h2 class="hero-spotlight__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>
						<p class="hero-spotlight__hook">
							<?php
							echo esc_html(
								wp_trim_words(
									get_the_excerpt(),
									$compact ? 12 : 16,
									'…'
								)
							);
							?>
						</p>
						<?php if ( $price ) : ?>
							<p class="hero-spotlight__price">
								<span class="hero-spotlight__price-label" aria-hidden="true"><?php esc_html_e( 'Tarif', 'anrhpub_theme' ); ?></span>
								<?php echo esc_html( $price ); ?>
							</p>
						<?php endif; ?>
						<div class="hero-spotlight__actions">
							<a class="btn btn--primary<?php echo $compact ? ' btn--sm' : ' btn--block'; ?>" href="<?php echo esc_url( $add_url ); ?>">
								<?php esc_html_e( 'Ajouter au panier', 'anrhpub_theme' ); ?>
							</a>
							<a class="btn btn--outline<?php echo $compact ? ' btn--sm' : ' btn--block'; ?>" href="<?php the_permalink(); ?>">
								<?php esc_html_e( 'Voir la fiche', 'anrhpub_theme' ); ?>
							</a>
						</div>
					</div>
				</div>
			</article>
		<?php endwhile; ?>
	</div>

	<footer class="hero-spotlight__footer<?php echo $compact ? '' : ' hero-spotlight__footer--stacked'; ?>">
		<?php if ( $total > 1 ) : ?>
			<div class="hero-spotlight__nav" aria-hidden="false">
				<div class="hero-spotlight__dots<?php echo $compact ? ' hero-spotlight__dots--bar' : ''; ?>" role="tablist" aria-label="<?php esc_attr_e( 'Choisir une nouveauté', 'anrhpub_theme' ); ?>">
					<?php for ( $i = 0; $i < $total; $i++ ) : ?>
						<button
							type="button"
							class="hero-spotlight__dot<?php echo 0 === $i ? ' is-active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
							aria-controls="hero-spotlight-slide-<?php echo esc_attr( (string) ( $i + 1 ) ); ?>"
							data-carousel-dot
							data-slide-index="<?php echo esc_attr( (string) $i ); ?>"
						>
							<span class="screen-reader-text"><?php printf( esc_html__( 'Nouveauté %d', 'anrhpub_theme' ), $i + 1 ); ?></span>
						</button>
					<?php endfor; ?>
				</div>
			</div>
		<?php endif; ?>

		<p class="hero-spotlight__more">
			<a class="hero-spotlight__catalog-link" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
				<?php esc_html_e( 'Voir tout le catalogue', 'anrhpub_theme' ); ?>
				<span class="hero-spotlight__catalog-icon" aria-hidden="true">→</span>
			</a>
		</p>
	</footer>
</aside>
<?php
wp_reset_postdata();
