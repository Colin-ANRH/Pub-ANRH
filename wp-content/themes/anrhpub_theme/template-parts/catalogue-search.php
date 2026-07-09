<?php
/**
 * Barre de recherche catalogue.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

$current_q = anrhpub_get_catalogue_search_term();
?>
<div class="catalogue-search" role="search">
	<label class="catalogue-search__label" for="catalogue-search-input">
		<?php esc_html_e( 'Rechercher dans le catalogue', 'anrhpub_theme' ); ?>
	</label>
	<div class="catalogue-search__field">
		<input
			type="search"
			id="catalogue-search-input"
			class="catalogue-search__input"
			name="catalogue_q"
			value="<?php echo esc_attr( $current_q ); ?>"
			placeholder="<?php esc_attr_e( 'Produit, référence, catégorie…', 'anrhpub_theme' ); ?>"
			autocomplete="off"
			spellcheck="false"
			minlength="2"
			aria-controls="catalogue-results"
		/>
		<button type="button" class="catalogue-search__clear" id="catalogue-search-clear" aria-label="<?php esc_attr_e( 'Effacer la recherche', 'anrhpub_theme' ); ?>"<?php echo $current_q ? '' : ' hidden'; ?>>
			<span aria-hidden="true">×</span>
		</button>
	</div>
	<p class="catalogue-search__hint">
		<?php esc_html_e( 'Minimum 2 caractères — recherche sur les noms, références et catégories.', 'anrhpub_theme' ); ?>
	</p>
</div>
