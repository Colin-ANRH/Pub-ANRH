<?php
/**
 * Template — Notre activité (/societe/).
 *
 * @package anrhpub_theme
 */

get_header();

$catalogue_product_count  = function_exists( 'anrhpub_get_catalogue_product_count' ) ? anrhpub_get_catalogue_product_count() : 0;
$catalogue_category_count = function_exists( 'anrhpub_get_parent_category_count' ) ? anrhpub_get_parent_category_count( false ) : 0;
$product_display          = $catalogue_product_count > 0 ? $catalogue_product_count : 450;
$category_display         = $catalogue_category_count > 0 ? $catalogue_category_count : 10;
?>

<main id="main-content" class="page-societe page-societe--atelier">
	<?php while ( have_posts() ) : the_post(); ?>

		<section class="societe-hero" data-animate aria-labelledby="societe-hero-title">
			<div class="container societe-hero__inner">
				<p class="societe-hero__brand">ANRH Peyruis</p>
				<h1 id="societe-hero-title" class="societe-hero__title">
					<?php esc_html_e( 'Notre activité', 'anrhpub_theme' ); ?>
				</h1>
				<p class="societe-hero__lead">
					<?php esc_html_e( 'Entreprise adaptée : objets publicitaires personnalisés pour entreprises, collectivités et associations — insertion professionnelle et qualité ISO 9001.', 'anrhpub_theme' ); ?>
				</p>
				<div class="societe-hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
						<?php esc_html_e( 'Parcourir le catalogue', 'anrhpub_theme' ); ?>
					</a>
					<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/marquage/' ) ); ?>">
						<?php esc_html_e( 'Nos marquages', 'anrhpub_theme' ); ?>
					</a>
				</div>
			</div>
		</section>

		<section class="societe-strip" data-animate aria-label="<?php esc_attr_e( 'Chiffres clés', 'anrhpub_theme' ); ?>">
			<div class="container societe-strip__grid">
				<div class="societe-strip__item">
					<span class="societe-strip__value"><?php echo esc_html( '+' . number_format_i18n( $product_display ) ); ?></span>
					<span class="societe-strip__label"><?php esc_html_e( 'références catalogue', 'anrhpub_theme' ); ?></span>
				</div>
				<div class="societe-strip__item">
					<span class="societe-strip__value"><?php echo esc_html( (string) (int) $category_display ); ?></span>
					<span class="societe-strip__label"><?php esc_html_e( 'univers produits', 'anrhpub_theme' ); ?></span>
				</div>
				<div class="societe-strip__item">
					<span class="societe-strip__value">ISO 9001</span>
					<span class="societe-strip__label"><?php esc_html_e( 'certification groupe 2015', 'anrhpub_theme' ); ?></span>
				</div>
				<div class="societe-strip__item">
					<span class="societe-strip__value"><?php esc_html_e( 'EA', 'anrhpub_theme' ); ?></span>
					<span class="societe-strip__label"><?php esc_html_e( 'entreprise adaptée Peyruis', 'anrhpub_theme' ); ?></span>
				</div>
			</div>
		</section>

		<section class="societe-chapter" data-animate aria-labelledby="societe-who-title">
			<div class="container societe-chapter__grid">
				<div class="societe-chapter__intro">
					<p class="societe-chapter__kicker"><?php esc_html_e( 'Identité', 'anrhpub_theme' ); ?></p>
					<h2 id="societe-who-title" class="societe-chapter__title"><?php esc_html_e( 'Qui sommes-nous ?', 'anrhpub_theme' ); ?></h2>
				</div>
				<div class="societe-chapter__body">
					<p>
						<?php esc_html_e( 'ANRH Peyruis (Association pour l’insertion et la Réinsertion professionnelle et humaine des Handicapés) propose une activité commerciale dédiée aux objets publicitaires personnalisés.', 'anrhpub_theme' ); ?>
					</p>
					<p>
						<?php esc_html_e( 'Le but premier de l’ANRH est l’insertion professionnelle et sociale des personnes adultes handicapées physiques et/ou psychiques (Article 1er des statuts).', 'anrhpub_theme' ); ?>
					</p>
					<p class="societe-chapter__note">
						<?php esc_html_e( 'Pour davantage d’informations :', 'anrhpub_theme' ); ?>
						<a href="https://www.anrh.fr" target="_blank" rel="noopener noreferrer">www.anrh.fr</a>
						<span class="societe-chapter__sep" aria-hidden="true">·</span>
						<a href="<?php echo esc_url( anrhpub_anrh_history_url() ); ?>"><?php esc_html_e( 'Histoire de l’ANRH', 'anrhpub_theme' ); ?></a>
					</p>
				</div>
			</div>
		</section>

		<section class="societe-chapter societe-chapter--tint" data-animate aria-labelledby="societe-activity-title">
			<div class="container societe-chapter__grid">
				<div class="societe-chapter__intro">
					<p class="societe-chapter__kicker"><?php esc_html_e( 'Mission', 'anrhpub_theme' ); ?></p>
					<h2 id="societe-activity-title" class="societe-chapter__title"><?php esc_html_e( 'Notre activité', 'anrhpub_theme' ); ?></h2>
				</div>
				<div class="societe-chapter__body">
					<p>
						<?php esc_html_e( 'Notre activité est dédiée aux professionnels, entreprises, collectivités et associations.', 'anrhpub_theme' ); ?>
					</p>
					<blockquote class="societe-quote">
						<p><?php esc_html_e( 'En travaillant avec l’EA de Peyruis, vous agissez de façon responsable en devenant un acteur de l’insertion professionnelle des travailleurs handicapés.', 'anrhpub_theme' ); ?></p>
					</blockquote>
				</div>
			</div>
		</section>

		<section class="societe-chapter" data-animate aria-labelledby="societe-offer-title">
			<div class="container societe-chapter__grid">
				<div class="societe-chapter__intro">
					<p class="societe-chapter__kicker"><?php esc_html_e( 'Catalogue', 'anrhpub_theme' ); ?></p>
					<h2 id="societe-offer-title" class="societe-chapter__title"><?php esc_html_e( 'Notre offre', 'anrhpub_theme' ); ?></h2>
				</div>
				<div class="societe-chapter__body">
					<p>
						<?php
						printf(
							/* translators: 1: product count, 2: category count */
							esc_html__( 'Plus de %1$s produits, répartis dans %2$s catégories — dont certains composés de matières recyclées et/ou recyclables.', 'anrhpub_theme' ),
							esc_html( number_format_i18n( $product_display ) ),
							esc_html( number_format_i18n( $category_display ) )
						);
						?>
					</p>
					<p>
						<?php esc_html_e( 'Du plus classique (stylos, blocs-notes, tasses, t-shirts…) au plus original et hi-tech (diffuseurs, lampes, réveils, accessoires de cuisine, chargeurs solaires…).', 'anrhpub_theme' ); ?>
					</p>
					<p>
						<?php esc_html_e( 'Personnalisation à votre image pour vos communications B to B : évènements, cadeaux CSE ou cadeaux clients — logo, coordonnées ou message, pour vous démarquer.', 'anrhpub_theme' ); ?>
					</p>
				</div>
			</div>
		</section>

		<section class="societe-iso" data-animate aria-labelledby="societe-iso-title">
			<div class="container societe-iso__inner">
				<p class="societe-iso__kicker" id="societe-iso-title">ISO 9001</p>
				<p class="societe-iso__text">
					<?php esc_html_e( 'Fort d’une certification ISO 9001 groupe version 2015, vous êtes assuré d’acheter des produits de qualité, tout en participant à l’économie solidaire.', 'anrhpub_theme' ); ?>
				</p>
			</div>
		</section>

		<section class="societe-marking" data-animate aria-labelledby="societe-marking-title">
			<div class="container">
				<header class="societe-marking__header">
					<div>
						<p class="societe-chapter__kicker"><?php esc_html_e( 'Atelier', 'anrhpub_theme' ); ?></p>
						<h2 id="societe-marking-title" class="societe-chapter__title"><?php esc_html_e( 'Nos marquages', 'anrhpub_theme' ); ?></h2>
						<p class="societe-marking__lead">
							<?php esc_html_e( 'Plusieurs techniques selon le produit choisi — toutes réalisées dans nos locaux à Peyruis.', 'anrhpub_theme' ); ?>
						</p>
					</div>
					<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/marquage/' ) ); ?>">
						<?php esc_html_e( 'Découvrir les techniques', 'anrhpub_theme' ); ?>
					</a>
				</header>
				<?php get_template_part( 'template-parts/marquage', 'techniques', array( 'mode' => 'compact' ) ); ?>
			</div>
		</section>

		<section class="societe-footer-cta" data-animate>
			<div class="container societe-footer-cta__inner">
				<div class="societe-footer-cta__copy">
					<p class="societe-footer-cta__brand">ANRH Peyruis</p>
					<h2 class="societe-footer-cta__title"><?php esc_html_e( 'Prêts à préparer votre devis ?', 'anrhpub_theme' ); ?></h2>
					<p class="societe-footer-cta__lead">
						<?php esc_html_e( 'Parcourez le catalogue, ajoutez vos références et envoyez votre demande — notre équipe vous répond sous 48 h ouvrées.', 'anrhpub_theme' ); ?>
					</p>
				</div>
				<div class="societe-footer-cta__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( anrhpub_catalogue_url() ); ?>">
						<?php esc_html_e( 'Catalogue produits', 'anrhpub_theme' ); ?>
					</a>
					<a class="btn btn--outline" href="<?php echo esc_url( anrhpub_quote_cart_url() ); ?>" data-quote-cart-link>
						<?php esc_html_e( 'Mon panier devis', 'anrhpub_theme' ); ?>
					</a>
				</div>
			</div>
		</section>

	<?php endwhile; ?>
</main>

<?php
get_footer();
