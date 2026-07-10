<?php
/** Admin fiche produit couleurs/stock. @package anrhpub_theme */

defined( 'ABSPATH' ) || exit;

/**
 * Meta couleurs / stock sur la fiche produit.
 */
function anrhpub_product_color_stock_meta_box() {
	add_meta_box(
		'anrhpub_product_color_stock',
		__( 'Couleurs & disponibilités', 'anrhpub_theme' ),
		'anrhpub_product_color_stock_meta_box_render',
		'anr_product',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_product_color_stock_meta_box' );

/**
 * Rendu metabox couleurs par produit.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_product_color_stock_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_product_color_stock', 'anrhpub_product_color_stock_nonce' );

	$all_colors = anrhpub_get_all_catalog_colors();
	$saved      = array();

	foreach ( anrhpub_get_product_color_stock_rows( $post->ID ) as $row ) {
		$saved[ (int) $row['color_id'] ] = (int) $row['stock'];
	}

	if ( empty( $all_colors ) ) {
		echo '<p>' . esc_html__( 'Aucune couleur dans le catalogue. Ajoutez-en via Catalogue → Couleurs.', 'anrhpub_theme' ) . '</p>';
		return;
	}
	?>
	<div class="anrhpub-color-picker-bar">
		<label for="anrhpub-color-picker-select" class="anrhpub-color-picker-bar__label">
			<?php esc_html_e( 'Ajouter une couleur au produit', 'anrhpub_theme' ); ?>
		</label>
		<div class="anrhpub-color-picker-bar__controls">
			<span class="anrhpub-color-picker-bar__preview" data-color-select-preview aria-hidden="true"></span>
			<select id="anrhpub-color-picker-select" class="anrhpub-color-picker-select">
				<option value=""><?php esc_html_e( '— Choisir une couleur —', 'anrhpub_theme' ); ?></option>
				<?php foreach ( $all_colors as $term ) : ?>
					<?php
					$color_id = (int) $term->term_id;
					$hex      = anrhpub_get_color_hex( $color_id );
					?>
					<option value="<?php echo esc_attr( (string) $color_id ); ?>" data-hex="<?php echo esc_attr( $hex ); ?>">
						<?php echo esc_html( $term->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button type="button" class="button button-primary" id="anrhpub-color-picker-add">
				<?php esc_html_e( 'Ajouter', 'anrhpub_theme' ); ?>
			</button>
		</div>
		<p class="description">
			<?php esc_html_e( 'Sélectionnez une couleur dans la liste ou cliquez sur une pastille ci-dessous, puis indiquez la quantité disponible.', 'anrhpub_theme' ); ?>
			<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=anr_color&post_type=anr_product' ) ); ?>">
				<?php esc_html_e( 'Gérer le catalogue de couleurs', 'anrhpub_theme' ); ?>
			</a>
		</p>
	</div>
	<div class="anrhpub-admin-colors-grid" role="group" aria-label="<?php esc_attr_e( 'Couleurs disponibles pour ce produit', 'anrhpub_theme' ); ?>">
		<?php foreach ( $all_colors as $term ) : ?>
			<?php
			$color_id = (int) $term->term_id;
			$hex      = anrhpub_get_color_hex( $color_id );
			$enabled  = array_key_exists( $color_id, $saved );
			$stock    = $enabled ? (int) $saved[ $color_id ] : 0;
			?>
			<div
				class="anrhpub-admin-color-card<?php echo $enabled ? ' is-active' : ''; ?>"
				data-admin-color-card
				data-color-id="<?php echo esc_attr( (string) $color_id ); ?>"
			>
				<label class="anrhpub-admin-color-card__pick">
					<input
						type="checkbox"
						name="anr_product_colors[<?php echo esc_attr( (string) $color_id ); ?>][enabled]"
						value="1"
						class="anrhpub-product-color-enabled"
						<?php checked( $enabled ); ?>
					/>
					<span class="anrhpub-admin-color-card__swatch" style="background-color:<?php echo esc_attr( $hex ); ?>;" title="<?php echo esc_attr( $term->name ); ?>"></span>
					<span class="anrhpub-admin-color-card__name"><?php echo esc_html( $term->name ); ?></span>
				</label>
				<div class="anrhpub-admin-color-card__stock" data-admin-color-stock>
					<label class="screen-reader-text" for="anr_color_stock_<?php echo esc_attr( (string) $color_id ); ?>">
						<?php
						printf(
							/* translators: %s: color name */
							esc_html__( 'Quantité pour %s', 'anrhpub_theme' ),
							esc_html( $term->name )
						);
						?>
					</label>
					<input
						type="number"
						id="anr_color_stock_<?php echo esc_attr( (string) $color_id ); ?>"
						class="anrhpub-product-color-stock"
						name="anr_product_colors[<?php echo esc_attr( (string) $color_id ); ?>][stock]"
						value="<?php echo esc_attr( (string) $stock ); ?>"
						min="0"
						max="999999"
						step="1"
						<?php disabled( ! $enabled ); ?>
					/>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Sauvegarde metabox couleurs produit.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_save_product_color_stock_meta( $post_id ) {
	if ( ! isset( $_POST['anrhpub_product_color_stock_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_product_color_stock_nonce'] ) ), 'anrhpub_save_product_color_stock' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'anr_product' !== get_post_type( $post_id ) ) {
		return;
	}

	$posted = isset( $_POST['anr_product_colors'] ) && is_array( $_POST['anr_product_colors'] )
		? wp_unslash( $_POST['anr_product_colors'] )
		: array();

	$rows = array();

	foreach ( $posted as $color_id => $data ) {
		$color_id = (int) $color_id;

		if ( $color_id <= 0 || ! is_array( $data ) ) {
			continue;
		}

		if ( empty( $data['enabled'] ) ) {
			continue;
		}

		$rows[] = array(
			'color_id' => $color_id,
			'stock'    => isset( $data['stock'] ) ? absint( $data['stock'] ) : 0,
		);
	}

	anrhpub_save_product_color_stock_rows( $post_id, $rows );
}
add_action( 'save_post_anr_product', 'anrhpub_save_product_color_stock_meta' );

/**
 * Scripts admin fiche produit (couleurs).
 */
function anrhpub_product_color_admin_scripts( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'anr_product' !== $screen->post_type ) {
		return;
	}
	?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var select = document.getElementById('anrhpub-color-picker-select');
			var preview = document.querySelector('[data-color-select-preview]');
			var addBtn = document.getElementById('anrhpub-color-picker-add');

			function cardForColorId(colorId) {
				return document.querySelector('[data-admin-color-card][data-color-id="' + colorId + '"]');
			}

			function enableColorOnProduct(colorId) {
				var card = cardForColorId(colorId);
				if (!card) {
					return false;
				}
				var checkbox = card.querySelector('.anrhpub-product-color-enabled');
				if (!checkbox) {
					return false;
				}
				if (!checkbox.checked) {
					checkbox.checked = true;
					checkbox.dispatchEvent(new Event('change', { bubbles: true }));
				}
				card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
				return true;
			}

			function syncSelectPreview() {
				if (!select || !preview) {
					return;
				}
				var option = select.options[select.selectedIndex];
				var hex = option && option.getAttribute('data-hex') ? option.getAttribute('data-hex') : '#e8e8e8';
				preview.style.backgroundColor = hex;
				preview.hidden = !select.value;
			}

			if (select) {
				select.addEventListener('change', syncSelectPreview);
				syncSelectPreview();
			}

			if (addBtn && select) {
				addBtn.addEventListener('click', function () {
					var colorId = select.value;
					if (!colorId) {
						select.focus();
						return;
					}
					enableColorOnProduct(colorId);
				});
			}

			document.querySelectorAll('[data-admin-color-card]').forEach(function (card) {
				var checkbox = card.querySelector('.anrhpub-product-color-enabled');
				var stockInput = card.querySelector('.anrhpub-product-color-stock');

				if (!checkbox) {
					return;
				}

				function sync() {
					card.classList.toggle('is-active', checkbox.checked);
					if (stockInput) {
						stockInput.disabled = !checkbox.checked;
					}
					if (!checkbox.checked) {
						if (stockInput) {
							stockInput.value = '0';
						}
					} else if (stockInput && parseInt(stockInput.value, 10) < 1) {
						stockInput.value = '1';
						stockInput.focus();
					}
				}

				checkbox.addEventListener('change', sync);
				if (stockInput) {
					stockInput.addEventListener('click', function (e) {
						e.stopPropagation();
					});
				}
				sync();
			});
		});
	</script>
	<?php
}
add_action( 'admin_footer', 'anrhpub_product_color_admin_scripts' );

/**
 * Sélecteur visuel hex (écran Couleurs catalogue).
 */
function anrhpub_color_hex_picker_admin_scripts() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || 'edit-anr_color' !== $screen->id ) {
		return;
	}
	?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('.anrhpub-color-hex-field').forEach(function (wrap) {
				var picker = wrap.querySelector('.anrhpub-hex-picker');
				var text = wrap.querySelector('.anrhpub-hex-text');
				var preview = wrap.querySelector('[data-hex-preview]');
				if (!picker || !text) {
					return;
				}
				function normalizeHex(value) {
					var v = (value || '').trim();
					if (/^#[0-9A-Fa-f]{6}$/.test(v)) {
						return v.toUpperCase();
					}
					return '';
				}
				function apply(hex) {
					var clean = normalizeHex(hex) || '#888888';
					picker.value = clean;
					text.value = clean;
					if (preview) {
						preview.style.backgroundColor = clean;
					}
				}
				picker.addEventListener('input', function () {
					apply(picker.value);
				});
				text.addEventListener('input', function () {
					var clean = normalizeHex(text.value);
					if (clean) {
						apply(clean);
					}
				});
				text.addEventListener('blur', function () {
					apply(text.value);
				});
				apply(text.value || picker.value);
			});
		});
	</script>
	<?php
}
add_action( 'admin_footer', 'anrhpub_color_hex_picker_admin_scripts' );

/**
 * Styles admin couleurs (liste + fiche produit).
 */
function anrhpub_color_admin_styles_extended() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen ) {
		return;
	}

	$allowed = array( 'edit-anr_color', 'anr_product' );

	if ( ! in_array( $screen->id, $allowed, true ) && 'anr_product' !== $screen->post_type ) {
		return;
	}
	?>
	<style>
		.anrhpub-color-swatch-preview {
			display: inline-block;
			width: 1.5rem;
			height: 1.5rem;
			margin-right: 0.5rem;
			vertical-align: middle;
			border: 1px solid #ccc;
			border-radius: 3px;
		}
		.anrhpub-color-swatch-preview--lg {
			width: 2.5rem;
			height: 2.5rem;
			margin-right: 0;
		}
		.anrhpub-color-hex-controls {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 0.5rem;
		}
		.anrhpub-hex-picker {
			width: 3rem;
			height: 2.5rem;
			padding: 0;
			border: 1px solid #8c8f94;
			border-radius: 4px;
			cursor: pointer;
			background: transparent;
		}
		.anrhpub-hex-text {
			width: 7rem;
		}
		.anrhpub-admin-colors-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(7.5rem, 1fr));
			gap: 0.65rem;
			margin-top: 0.75rem;
		}
		.anrhpub-admin-color-card {
			border: 2px solid #dcdcde;
			border-radius: 8px;
			padding: 0.5rem;
			background: #fff;
			transition: border-color 0.15s ease, box-shadow 0.15s ease;
		}
		.anrhpub-admin-color-card.is-active {
			border-color: #2271b1;
			box-shadow: 0 0 0 1px #2271b1;
		}
		.anrhpub-admin-color-card__pick {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 0.35rem;
			margin: 0;
			cursor: pointer;
			text-align: center;
		}
		.anrhpub-admin-color-card__pick input[type="checkbox"] {
			position: absolute;
			opacity: 0;
			pointer-events: none;
		}
		.anrhpub-admin-color-card__swatch {
			display: block;
			width: 3rem;
			height: 3rem;
			border-radius: 50%;
			border: 2px solid rgba(0, 0, 0, 0.12);
			box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
		}
		.anrhpub-admin-color-card__name {
			font-size: 0.75rem;
			font-weight: 600;
			line-height: 1.25;
			color: #1d2327;
		}
		.anrhpub-admin-color-card__stock {
			margin-top: 0.45rem;
			text-align: center;
		}
		.anrhpub-admin-color-card__stock input {
			width: 100%;
			max-width: 5.5rem;
			text-align: center;
		}
		.anrhpub-admin-color-card:not(.is-active) .anrhpub-admin-color-card__stock {
			opacity: 0.45;
		}
		.anrhpub-color-picker-bar {
			margin: 0 0 1rem;
			padding: 0.75rem 0.85rem;
			background: #f6f7f7;
			border: 1px solid #dcdcde;
			border-radius: 6px;
		}
		.anrhpub-color-picker-bar__label {
			display: block;
			margin-bottom: 0.5rem;
			font-weight: 600;
		}
		.anrhpub-color-picker-bar__controls {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 0.5rem;
		}
		.anrhpub-color-picker-bar__preview {
			display: inline-block;
			width: 2.25rem;
			height: 2.25rem;
			border-radius: 50%;
			border: 2px solid #c3c4c7;
			flex-shrink: 0;
		}
		.anrhpub-color-picker-bar__preview[hidden] {
			display: none;
		}
		.anrhpub-color-picker-select {
			min-width: 14rem;
			max-width: 100%;
		}
	</style>
	<?php
}
add_action( 'admin_head', 'anrhpub_color_admin_styles_extended' );
