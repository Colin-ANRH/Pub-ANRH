<?php
/**
 * @package anrhpub_theme
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class QuoteCartTest extends TestCase {
	protected function setUp(): void {
		anrhpub_reset_test_state();
		$GLOBALS['anrhpub_test_posts'][101] = array(
			'type'   => 'anr_product',
			'status' => 'publish',
			'title'  => 'Produit test',
		);
		$GLOBALS['anrhpub_test_posts'][102] = array(
			'type'   => 'anr_product',
			'status' => 'publish',
			'title'  => 'Autre produit',
		);
	}

	public function test_line_key_is_stable(): void {
		$this->assertSame( '12:0', anrhpub_quote_cart_line_key( 12, 0 ) );
		$this->assertSame( '12:5', anrhpub_quote_cart_line_key( 12, 5 ) );
	}

	public function test_sanitize_merges_duplicate_lines(): void {
		$items = anrhpub_sanitize_quote_cart_items(
			array(
				array( 'product_id' => 101, 'qty' => 2, 'color_id' => 0 ),
				array( 'product_id' => 101, 'qty' => 3, 'color_id' => 0 ),
				array( 'product_id' => 102, 'qty' => 1, 'color_id' => 0 ),
			)
		);

		$this->assertCount( 2, $items );
		$by_product = array();
		foreach ( $items as $item ) {
			$by_product[ (int) $item['product_id'] ] = (int) $item['qty'];
		}
		$this->assertSame( 5, $by_product[101] );
		$this->assertSame( 1, $by_product[102] );
	}

	public function test_sanitize_skips_unpublished_products(): void {
		$GLOBALS['anrhpub_test_posts'][200] = array(
			'type'   => 'anr_product',
			'status' => 'draft',
			'title'  => 'Brouillon',
		);

		$items = anrhpub_sanitize_quote_cart_items(
			array(
				array( 'product_id' => 200, 'qty' => 4, 'color_id' => 0 ),
			)
		);

		$this->assertSame( array(), $items );
	}
}
