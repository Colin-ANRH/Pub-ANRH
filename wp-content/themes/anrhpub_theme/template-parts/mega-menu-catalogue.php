<?php
/**
 * Méga-menu catalogue — moderne, sans décalage au survol.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$parents     = anrhpub_get_parent_categories( false );
$archive_url = anrhpub_catalogue_url();
?>
<div class="mega-menu" id="mega-menu-catalogue" role="region" aria-label="<?php esc_attr_e( 'Catalogue produits', 'anrhpub_theme' ); ?>">
	<div class="mega-menu__shell">
		<header class="mega-menu__toolbar">
			<div class="mega-menu__intro">
				<p class="mega-menu__kicker"><?php esc_html_e( 'Catalogue ANRH', 'anrhpub_theme' ); ?></p>
				<p class="mega-menu__headline">
					<span class="mega-menu__headline-main"><?php esc_html_e( 'Nos produits', 'anrhpub_theme' ); ?></span>
					<em class="mega-menu__headline-accent"><?php esc_html_e( 'publicitaires', 'anrhpub_theme' ); ?></em>
				</p>
			</div>
			<a class="mega-menu__catalogue-link btn btn--primary" href="<?php echo esc_url( $archive_url ); ?>">
				<?php esc_html_e( 'Tout le catalogue', 'anrhpub_theme' ); ?>
			</a>
		</header>
		<div class="mega-menu__grid" role="list">
			<?php foreach ( $parents as $parent ) : ?>
				<?php
				$children  = anrhpub_get_child_categories( $parent->term_id, false );
				$term_link = get_term_link( $parent );
				if ( is_wp_error( $term_link ) ) {
					$term_link = $archive_url;
				}
				?>
				<section class="mega-menu__col" role="listitem">
					<details class="mega-menu__details">
						<summary>
							<span class="mega-menu__parent">
								<?php echo esc_html( $parent->name ); ?>
							</span>
						</summary>
						<div class="mega-menu__details-body">
							<p class="mega-menu__parent-all">
								<a href="<?php echo esc_url( $term_link ); ?>">
									<?php
									printf(
										/* translators: %s: category name */
										esc_html__( 'Voir « %s »', 'anrhpub_theme' ),
										esc_html( $parent->name )
									);
									?>
								</a>
							</p>
							<?php if ( $children ) : ?>
								<ul class="mega-menu__children">
									<?php foreach ( $children as $child ) : ?>
										<li>
											<a href="<?php echo esc_url( get_term_link( $child ) ); ?>">
												<span><?php echo esc_html( $child->name ); ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</details>
					<h3 class="mega-menu__parent mega-menu__parent--desktop">
						<a href="<?php echo esc_url( $term_link ); ?>"><?php echo esc_html( $parent->name ); ?></a>
					</h3>
					<?php if ( $children ) : ?>
						<ul class="mega-menu__children mega-menu__children--desktop">
							<?php foreach ( $children as $child ) : ?>
								<li>
									<a href="<?php echo esc_url( get_term_link( $child ) ); ?>">
										<span><?php echo esc_html( $child->name ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
</div>
