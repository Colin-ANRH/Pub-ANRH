<?php
/**
 * @package anrhpub_theme
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class B2BAuthTest extends TestCase {
	protected function setUp(): void {
		anrhpub_reset_test_state();
	}

	public function test_empty_status_is_pending_and_not_approved(): void {
		$GLOBALS['anrhpub_test_current_user'] = 42;

		$this->assertSame( 'pending', anrhpub_get_account_status( 42 ) );
		$this->assertFalse( anrhpub_client_is_approved( 42 ) );
		$this->assertFalse( anrhpub_can_view_prices() );
	}

	public function test_approved_client_can_view_prices(): void {
		$GLOBALS['anrhpub_test_user_meta'][7] = array(
			ANRHPUB_ACCOUNT_STATUS_META => 'approved',
		);
		$GLOBALS['anrhpub_test_current_user'] = 7;

		$this->assertTrue( anrhpub_client_is_approved( 7 ) );
		$this->assertTrue( anrhpub_can_view_prices() );
	}

	public function test_administrator_bypasses_pending(): void {
		$GLOBALS['anrhpub_test_current_user'] = 1;
		$GLOBALS['anrhpub_test_caps']['manage_options'] = true;

		$this->assertTrue( anrhpub_client_is_approved( 1 ) );
	}
}
