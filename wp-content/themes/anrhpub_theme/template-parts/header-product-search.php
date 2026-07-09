<?php
/**
 * Recherche produit — header global.
 *
 * @package anrhpub_theme
 *
 * @var array $args {
 *   @type string $current_q Current search query.
 * }
 */

defined( 'ABSPATH' ) || exit;

$current_q = isset( $args['current_q'] ) ? (string) $args['current_q'] : '';
if ( '' === $current_q && function_exists( 'anrhpub_get_catalogue_search_term' ) ) {
	$current_q = anrhpub_get_catalogue_search_term();
}
?>
<div class="header-search" data-global-product-search>
	<label class="screen-reader-text" for="global-catalogue-search">
		<?php esc_html_e( 'Rechercher un produit', 'anrhpub_theme' ); ?>
	</label>
	<div class="header-search__field">
		<span class="header-search__icon" aria-hidden="true">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
				<path d="M20 20L16.5 16.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
		</span>
		<input
			type="search"
			id="global-catalogue-search"
			class="header-search__input"
			name="catalogue_q"
			value="<?php echo esc_attr( $current_q ); ?>"
			placeholder="<?php esc_attr_e( 'Rechercher un produit…', 'anrhpub_theme' ); ?>"
			autocomplete="off"
			spellcheck="false"
			aria-autocomplete="list"
			aria-controls="header-search-dropdown"
			aria-expanded="false"
		/>
		<button type="button" class="header-search__clear" data-search-clear aria-label="<?php esc_attr_e( 'Effacer la recherche', 'anrhpub_theme' ); ?>"<?php echo $current_q ? '' : ' hidden'; ?>>
			<span aria-hidden="true">×</span>
		</button>
	</div>
	<div id="header-search-dropdown" class="header-search__dropdown" data-search-dropdown hidden role="region" aria-live="polite"></div>
</div>
