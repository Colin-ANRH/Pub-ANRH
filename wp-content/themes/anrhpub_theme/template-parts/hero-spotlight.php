<?php
/**
 * Mise en avant vendeur — nouveauté hero accueil.
 *
 * @package anrhpub_theme
 *
 * @var array $args { query: WP_Query }
 */

defined( 'ABSPATH' ) || exit;

$query = isset( $args['query'] ) && $args['query'] instanceof WP_Query ? $args['query'] : null;

if ( ! $query || ! $query->have_posts() ) {
	return;
}

$query->the_post();

$ref         = get_post_meta( get_the_ID(), 'anr_reference', true );
$price       = get_post_meta( get_the_ID(), 'anr_price_label', true );
$add_url = anrhpub_get_product_add_url( get_the_ID() );
?>
<aside class="hero-spotlight" data-animate>
	<p class="hero-spotlight__eyebrow"><?php esc_html_e( 'Nouveauté', 'anrhpub_theme' ); ?></p>

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
			<?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?>
		</p>
		<p class="hero-spotlight__promise">
			<?php esc_html_e( 'Personnalisez avec votre logo — ajoutez au panier puis demandez votre devis.', 'anrhpub_theme' ); ?>
		</p>
		<?php if ( $price ) : ?>
			<p class="hero-spotlight__price"><?php echo esc_html( $price ); ?></p>
		<?php endif; ?>
		<div class="hero-spotlight__actions">
			<a class="btn btn--primary btn--block" href="<?php echo esc_url( $add_url ); ?>">
				<?php esc_html_e( 'Ajouter au panier', 'anrhpub_theme' ); ?>
			</a>
			<a class="btn btn--outline btn--block" href="<?php the_permalink(); ?>">
				<?php esc_html_e( 'Voir ce produit', 'anrhpub_theme' ); ?>
			</a>
		</div>
	</div>

	<p class="hero-spotlight__more">
		<a href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
			<?php esc_html_e( 'Voir tout le catalogue', 'anrhpub_theme' ); ?> →
		</a>
	</p>
</aside>
<?php
wp_reset_postdata();
