<?php
/**
 * Techniques de marquage — grille compacte ou détail complet.
 *
 * @package anrhpub_theme
 *
 * @var array $args { mode: compact|detail }
 */

defined( 'ABSPATH' ) || exit;

$mode       = isset( $args['mode'] ) && 'detail' === $args['mode'] ? 'detail' : 'compact';
$techniques = anrhpub_marquage_techniques();

if ( empty( $techniques ) ) {
	return;
}

if ( 'detail' === $mode ) :
	?>
	<div class="marquage-detail" data-animate>
		<nav class="marquage-detail__nav" aria-label="<?php esc_attr_e( 'Techniques de marquage', 'anrhpub_theme' ); ?>">
			<p class="marquage-detail__nav-label"><?php esc_html_e( 'Aller à', 'anrhpub_theme' ); ?></p>
			<ul>
				<?php foreach ( $techniques as $tech ) : ?>
					<li>
						<a href="#marquage-<?php echo esc_attr( $tech['slug'] ); ?>">
							<span class="marquage-detail__nav-glyph" aria-hidden="true"><?php echo esc_html( mb_substr( $tech['label'], 0, 1 ) ); ?></span>
							<span><?php echo esc_html( $tech['label'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div class="marquage-detail__list">
			<?php foreach ( $techniques as $tech ) : ?>
				<article
					id="marquage-<?php echo esc_attr( $tech['slug'] ); ?>"
					class="technique-block technique-block--<?php echo esc_attr( $tech['slug'] ); ?>"
					data-animate
				>
					<div class="technique-block__head">
						<span class="technique-block__glyph" aria-hidden="true"><?php echo esc_html( mb_substr( $tech['label'], 0, 1 ) ); ?></span>
						<div>
							<h3 class="technique-block__title"><?php echo esc_html( $tech['label'] ); ?></h3>
							<p class="technique-block__short"><?php echo esc_html( anrhpub_marquage_technique_short_desc( $tech['slug'] ) ); ?></p>
						</div>
					</div>
					<div class="technique-block__body">
						<p class="technique-block__intro"><?php echo esc_html( $tech['intro'] ); ?></p>
						<?php if ( ! empty( $tech['features'] ) ) : ?>
							<div class="technique-block__features">
								<h4><?php esc_html_e( 'Points clés', 'anrhpub_theme' ); ?></h4>
								<ul>
									<?php foreach ( $tech['features'] as $feature ) : ?>
										<li><?php echo esc_html( $feature ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
	return;
endif;
?>
<div class="marquage-tech marquage-tech--compact" data-animate>
	<ul class="marquage-tech__grid" role="list">
		<?php foreach ( $techniques as $tech ) : ?>
			<li class="marquage-tech__card marquage-tech__card--<?php echo esc_attr( $tech['slug'] ); ?>">
				<a class="marquage-tech__link" href="<?php echo esc_url( home_url( '/marquage/#marquage-' . $tech['slug'] ) ); ?>">
					<div class="marquage-tech__visual" aria-hidden="true">
						<span class="marquage-tech__glyph"><?php echo esc_html( mb_substr( $tech['label'], 0, 1 ) ); ?></span>
					</div>
					<div class="marquage-tech__body">
						<h3 class="marquage-tech__title"><?php echo esc_html( $tech['label'] ); ?></h3>
						<p class="marquage-tech__desc"><?php echo esc_html( anrhpub_marquage_technique_short_desc( $tech['slug'] ) ); ?></p>
					</div>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
