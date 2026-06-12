<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Cron;
use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for the serial-key expiry cron.
 */
class CronExpiryTest extends TestCase {

	/**
	 * Create a sold key with a given validity and order date.
	 *
	 * @param string $serial     Serial key.
	 * @param int    $validity   Validity in days.
	 * @param string $order_date Order date (Y-m-d H:i:s).
	 * @return Key
	 */
	protected function make_dated_key( string $serial, int $validity, string $order_date ): Key {
		$product = $this->create_product();
		$key     = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => $serial,
				'status'     => 'sold',
				'validity'   => $validity,
				'order_date' => $order_date,
			)
		);
		$this->assertNotWPError( $key );

		return $key;
	}

	/**
	 * A key past its validity window is expired.
	 */
	public function testExpiresKeyPastValidity(): void {
		$key = $this->make_dated_key( 'EXP-1', 30, gmdate( 'Y-m-d H:i:s', strtotime( '-40 days' ) ) );

		Cron::expire_outdated_serials();

		$this->assertSame( 'expired', Key::find( $key->get_id() )->get_status() );
	}

	/**
	 * A key still within its validity window is not expired.
	 */
	public function testDoesNotExpireWithinValidity(): void {
		$key = $this->make_dated_key( 'EXP-2', 30, gmdate( 'Y-m-d H:i:s', strtotime( '-5 days' ) ) );

		Cron::expire_outdated_serials();

		$this->assertSame( 'sold', Key::find( $key->get_id() )->get_status() );
	}

	/**
	 * A key with zero validity (lifetime) never expires.
	 */
	public function testZeroValidityNeverExpires(): void {
		$key = $this->make_dated_key( 'EXP-3', 0, gmdate( 'Y-m-d H:i:s', strtotime( '-400 days' ) ) );

		Cron::expire_outdated_serials();

		$this->assertSame( 'sold', Key::find( $key->get_id() )->get_status() );
	}
}
