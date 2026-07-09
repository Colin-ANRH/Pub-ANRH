<?php
/**
 * Fiche technique produit — champs gérés dans WordPress.
 *
 * @package anrhpub_theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistrement des meta fiche technique.
 */
function anrhpub_register_product_technical_meta() {
	$fields = array(
		'anr_hub1_reference'   => 'string',
		'anr_marking_max_size'  => 'string',
		'anr_dimensions'        => 'string',
		'anr_tech_sheet_id'     => 'integer',
	);

	foreach ( $fields as $key => $type ) {
		register_post_meta(
			'anr_product',
			$key,
			array(
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'integer' === $type ? 'absint' : 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'anrhpub_register_product_technical_meta', 12 );

/**
 * Données fiche technique pour affichage.
 *
 * @param int $post_id Post ID.
 * @return array<string, string>
 */
function anrhpub_get_product_technical_sheet( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	$hub1 = get_post_meta( $post_id, 'anr_hub1_reference', true );
	if ( ! $hub1 ) {
		$hub1 = get_post_meta( $post_id, 'anr_reference', true );
	}

	$sheet_id = (int) get_post_meta( $post_id, 'anr_tech_sheet_id', true );
	$sheet_url = $sheet_id ? wp_get_attachment_url( $sheet_id ) : '';

	return array(
		'hub1_reference'    => (string) $hub1,
		'marking_max_size'  => (string) get_post_meta( $post_id, 'anr_marking_max_size', true ),
		'dimensions'        => (string) get_post_meta( $post_id, 'anr_dimensions', true ),
		'tech_sheet_url'    => $sheet_url ? (string) $sheet_url : '',
		'tech_sheet_title'  => $sheet_id ? get_the_title( $sheet_id ) : '',
	);
}

/**
 * Au moins un champ fiche technique renseigné.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function anrhpub_product_has_technical_sheet( $post_id = 0 ) {
	$data = anrhpub_get_product_technical_sheet( $post_id );

	return (bool) (
		$data['hub1_reference']
		|| $data['marking_max_size']
		|| $data['dimensions']
		|| $data['tech_sheet_url']
	);
}

/**
 * Meta box admin — fiche technique.
 */
function anrhpub_product_technical_meta_box() {
	add_meta_box(
		'anrhpub_product_technical',
		__( 'Fiche technique', 'anrhpub_theme' ),
		'anrhpub_product_technical_meta_box_render',
		'anr_product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'anrhpub_product_technical_meta_box' );

/**
 * Rendu meta box fiche technique.
 *
 * @param WP_Post $post Post.
 */
function anrhpub_product_technical_meta_box_render( $post ) {
	wp_nonce_field( 'anrhpub_save_product_technical', 'anrhpub_product_technical_nonce' );

	$data     = anrhpub_get_product_technical_sheet( $post->ID );
	$sheet_id = (int) get_post_meta( $post->ID, 'anr_tech_sheet_id', true );
	$sheet_url = $sheet_id ? wp_get_attachment_url( $sheet_id ) : '';
	?>
	<table class="form-table anrhpub-tech-admin" role="presentation">
		<tr>
			<th scope="row"><label for="anr_hub1_reference"><?php esc_html_e( 'Référence HUB1', 'anrhpub_theme' ); ?></label></th>
			<td>
				<input type="text" name="anr_hub1_reference" id="anr_hub1_reference" value="<?php echo esc_attr( $data['hub1_reference'] ); ?>" class="regular-text" placeholder="ex. HUB1-12345" />
				<p class="description"><?php esc_html_e( 'Affichée sur la fiche produit. Si vide, la référence catalogue est utilisée.', 'anrhpub_theme' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Fiche technique (PDF)', 'anrhpub_theme' ); ?></th>
			<td>
				<input type="hidden" name="anr_tech_sheet_id" id="anr_tech_sheet_id" value="<?php echo esc_attr( (string) $sheet_id ); ?>" />
				<p>
					<button type="button" class="button" id="anr_tech_sheet_select"><?php esc_html_e( 'Choisir un fichier', 'anrhpub_theme' ); ?></button>
					<button type="button" class="button" id="anr_tech_sheet_remove" <?php echo $sheet_id ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Retirer', 'anrhpub_theme' ); ?></button>
				</p>
				<p id="anr_tech_sheet_preview" class="description">
					<?php if ( $sheet_url ) : ?>
						<a href="<?php echo esc_url( $sheet_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( basename( $sheet_url ) ); ?></a>
					<?php endif; ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="anr_marking_max_size"><?php esc_html_e( 'Taille de marquage maximum', 'anrhpub_theme' ); ?></label></th>
			<td>
				<input type="text" name="anr_marking_max_size" id="anr_marking_max_size" value="<?php echo esc_attr( $data['marking_max_size'] ); ?>" class="regular-text" placeholder="ex. 50 x 7 mm" />
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="anr_dimensions"><?php esc_html_e( 'Dimension', 'anrhpub_theme' ); ?></label></th>
			<td>
				<input type="text" name="anr_dimensions" id="anr_dimensions" value="<?php echo esc_attr( $data['dimensions'] ); ?>" class="regular-text" placeholder="ex. L. 7,5 x l. 6,4 x h. 1,5 cm" />
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Sauvegarde fiche technique.
 *
 * @param int $post_id Post ID.
 */
function anrhpub_save_product_technical_meta( $post_id ) {
	if ( ! isset( $_POST['anrhpub_product_technical_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anrhpub_product_technical_nonce'] ) ), 'anrhpub_save_product_technical' ) ) {
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

	update_post_meta( $post_id, 'anr_hub1_reference', isset( $_POST['anr_hub1_reference'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_hub1_reference'] ) ) : '' );
	update_post_meta( $post_id, 'anr_marking_max_size', isset( $_POST['anr_marking_max_size'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_marking_max_size'] ) ) : '' );
	update_post_meta( $post_id, 'anr_dimensions', isset( $_POST['anr_dimensions'] ) ? sanitize_text_field( wp_unslash( $_POST['anr_dimensions'] ) ) : '' );
	update_post_meta( $post_id, 'anr_tech_sheet_id', isset( $_POST['anr_tech_sheet_id'] ) ? absint( $_POST['anr_tech_sheet_id'] ) : 0 );
}
add_action( 'save_post_anr_product', 'anrhpub_save_product_technical_meta' );

/**
 * Scripts média admin (PDF fiche technique).
 *
 * @param string $hook Hook.
 */
function anrhpub_product_technical_admin_scripts( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();

	if ( ! $screen || 'anr_product' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	?>
	<script>
		jQuery(function ($) {
			var frame;
			var $id = $('#anr_tech_sheet_id');
			var $preview = $('#anr_tech_sheet_preview');
			var $remove = $('#anr_tech_sheet_remove');

			$('#anr_tech_sheet_select').on('click', function (e) {
				e.preventDefault();
				if (frame) {
					frame.open();
					return;
				}
				frame = wp.media({
					title: '<?php echo esc_js( __( 'Fiche technique (PDF)', 'anrhpub_theme' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Utiliser ce fichier', 'anrhpub_theme' ) ); ?>' },
					library: { type: 'application/pdf' },
					multiple: false
				});
				frame.on('select', function () {
					var attachment = frame.state().get('selection').first().toJSON();
					$id.val(attachment.id);
					$preview.html('<a href="' + attachment.url + '" target="_blank" rel="noopener noreferrer">' + attachment.filename + '</a>');
					$remove.show();
				});
				frame.open();
			});

			$remove.on('click', function (e) {
				e.preventDefault();
				$id.val('');
				$preview.empty();
				$remove.hide();
			});
		});
	</script>
	<?php
}
add_action( 'admin_enqueue_scripts', 'anrhpub_product_technical_admin_scripts' );

/**
 * Lignes de la fiche technique pour affichage.
 *
 * @param int $post_id Post ID.
 * @return array<int, array{label: string, value: string, html?: bool}>
 */
function anrhpub_get_product_technical_sheet_rows( $post_id = 0 ) {
	$data = anrhpub_get_product_technical_sheet( $post_id );
	$rows = array();

	if ( $data['hub1_reference'] ) {
		$rows[] = array(
			'label' => __( 'Référence HUB1', 'anrhpub_theme' ),
			'value' => $data['hub1_reference'],
		);
	}

	if ( $data['tech_sheet_url'] ) {
		$rows[] = array(
			'label' => __( 'Document PDF', 'anrhpub_theme' ),
			'value' => '<a class="product-tech-sheet__link" href="' . esc_url( $data['tech_sheet_url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Télécharger la fiche technique (PDF)', 'anrhpub_theme' ) . '</a>',
			'html'  => true,
		);
	}

	if ( $data['marking_max_size'] ) {
		$rows[] = array(
			'label' => __( 'Taille de marquage maximum', 'anrhpub_theme' ),
			'value' => $data['marking_max_size'],
		);
	}

	if ( $data['dimensions'] ) {
		$rows[] = array(
			'label' => __( 'Dimensions', 'anrhpub_theme' ),
			'value' => $data['dimensions'],
		);
	}

	return $rows;
}

/**
 * Affiche le tableau fiche technique.
 *
 * @param array<int, array{label: string, value: string, html?: bool}> $rows Lignes.
 * @param string                                                       $class Classe BEM supplémentaire.
 */
function anrhpub_render_product_technical_sheet_table( $rows, $class = '' ) {
	if ( empty( $rows ) ) {
		return;
	}

	$list_class = 'product-tech-sheet__list';
	if ( $class ) {
		$list_class .= ' ' . sanitize_html_class( $class );
	}
	?>
	<dl class="<?php echo esc_attr( $list_class ); ?>">
		<?php foreach ( $rows as $row ) : ?>
			<div class="product-tech-sheet__row">
				<dt class="product-tech-sheet__label"><?php echo esc_html( $row['label'] ); ?></dt>
				<dd class="product-tech-sheet__value">
					<?php
					if ( ! empty( $row['html'] ) ) {
						echo wp_kses_post( $row['value'] );
					} else {
						echo esc_html( $row['value'] );
					}
					?>
				</dd>
			</div>
		<?php endforeach; ?>
	</dl>
	<?php
}

/**
 * Affiche la fiche technique (bloc autonome — rétrocompatibilité).
 *
 * @param int $post_id Post ID.
 */
function anrhpub_render_product_technical_sheet( $post_id = 0 ) {
	$rows = anrhpub_get_product_technical_sheet_rows( $post_id );

	if ( empty( $rows ) ) {
		return;
	}
	?>
	<section class="product-tech-sheet" aria-labelledby="product-tech-sheet-title">
		<h2 id="product-tech-sheet-title" class="product-tech-sheet__title"><?php esc_html_e( 'Fiche technique', 'anrhpub_theme' ); ?></h2>
		<?php anrhpub_render_product_technical_sheet_table( $rows ); ?>
	</section>
	<?php
}
