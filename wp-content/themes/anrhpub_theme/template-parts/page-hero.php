<?php
/**
 * Hero unifié — pages intérieures (catalogue, société, marquage, contact…).
 *
 * @package anrhpub_theme
 *
 * @var array $args {
 *   @type string $kicker Label au-dessus du titre.
 *   @type string $title  Titre H1.
 *   @type string $lead   Chapô optionnel.
 *   @type string $class  Classes additionnelles sur <section>.
 * }
 */

defined( 'ABSPATH' ) || exit;

$kicker = isset( $args['kicker'] ) ? (string) $args['kicker'] : '';
$title  = isset( $args['title'] ) ? (string) $args['title'] : '';
$lead   = isset( $args['lead'] ) ? (string) $args['lead'] : '';
$class  = isset( $args['class'] ) ? (string) $args['class'] : '';

if ( '' === $title ) {
	return;
}

$section_class = 'page-hero page-hero--epure';
if ( $class ) {
	$section_class .= ' ' . $class;
}
?>
<section class="<?php echo esc_attr( $section_class ); ?>" data-animate>
	<div class="container page-hero__inner">
		<?php if ( $kicker ) : ?>
			<p class="page-hero__kicker"><?php echo esc_html( $kicker ); ?></p>
		<?php endif; ?>
		<h1 class="page-hero__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $lead ) : ?>
			<p class="page-hero__lead"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>
	</div>
</section>
