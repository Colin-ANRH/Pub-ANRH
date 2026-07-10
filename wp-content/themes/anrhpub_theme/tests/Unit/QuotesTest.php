<?php
/**
 * @package anrhpub_theme
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class QuotesTest extends TestCase {
	protected function setUp(): void {
		anrhpub_reset_test_state();
	}

	public function test_admin_can_download_quote_pdf(): void {
		$GLOBALS['anrhpub_test_caps']['edit_post'] = true;

		$this->assertTrue( anrhpub_user_can_download_quote_pdf( 55 ) );
	}

	public function test_client_owner_can_download_quote_pdf(): void {
		$GLOBALS['anrhpub_test_current_user'] = 9;
		$GLOBALS['anrhpub_test_post_meta'][55]['anr_quote_client_id'] = 9;

		$this->assertTrue( anrhpub_user_can_download_quote_pdf( 55 ) );
	}

	public function test_stranger_cannot_download_quote_pdf(): void {
		$this->assertFalse( anrhpub_user_can_download_quote_pdf( 55 ) );
	}
}
