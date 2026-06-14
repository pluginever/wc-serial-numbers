<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for the Key date prop boundaries after the migration.
 *
 * Expiry math (cron + REST validate) depends entirely on order_date, so these
 * pin which write paths produce a usable date and which do not.
 */
class KeyDatePropsTest extends TestCase {

	/**
	 * An explicit order_date string persists through save and reload.
	 */
	public function testExplicitOrderDatePersists(): void {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1, array( 'status' => 'completed' ) );

		$key = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'DATE-1',
				'status'     => 'sold',
				'order_id'   => $order->get_id(),
				'order_date' => '2026-01-15 08:30:00',
			)
		);
		$this->assertNotWPError( $key );

		$this->assertSame( '2026-01-15 08:30:00', Key::find( $key->get_id() )->get_order_date() );
	}

	/**
	 * Fulfillment sets order_date from the order created date, keeping expiry
	 * math functional for assigned keys.
	 */
	public function testAssignmentSetsOrderDateFromOrder(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'DATE-ASGN', array( 'validity' => 30 ) );
		$order = $this->create_order( $product, 1, array( 'status' => 'completed' ) );

		wcsn_order_update_keys( $order->get_id() );

		$keys = Key::query( array( 'order_id' => $order->get_id(), 'limit' => -1 ) );
		$this->assertCount( 1, $keys );
		$this->assertNotEmpty( $keys[0]->get_order_date() );
		$this->assertSame( $order->get_date_created()->format( 'Y-m-d H:i:s' ), $keys[0]->get_order_date() );
		$this->assertFalse( $keys[0]->is_expired() );
		$this->assertNotEmpty( $keys[0]->get_expire_date() );
	}

	/**
	 * The save() autofill now derives order_date from the order's completed date.
	 * The old model dropped it because sanitize_date() rejected the raw timestamp;
	 * the b8 datetime cast accepts the DateTime object, so expiry math works for
	 * keys assigned to completed orders.
	 */
	public function testAutofillFromCompletedOrderSetsOrderDate(): void {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1, array( 'status' => 'completed' ) );
		$this->assertNotNull( $order->get_date_completed() );

		$key = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'DATE-TS',
				'status'     => 'sold',
				'order_id'   => $order->get_id(),
			)
		);
		$this->assertNotWPError( $key );

		$this->assertSame(
			$order->get_date_completed()->format( 'Y-m-d H:i:s' ),
			Key::find( $key->get_id() )->get_order_date()
		);
	}

	/**
	 * Without a completed date the autofill falls back to wp_date() and a
	 * valid order_date is stored.
	 */
	public function testAutofillWithoutCompletedDateUsesCurrentTime(): void {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1, array( 'status' => 'processing' ) );
		$this->assertNull( $order->get_date_completed() );

		$key = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'DATE-NOW',
				'status'     => 'sold',
				'order_id'   => $order->get_id(),
			)
		);
		$this->assertNotWPError( $key );

		$order_date = Key::find( $key->get_id() )->get_order_date();
		$this->assertNotEmpty( $order_date );
		$this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $order_date );
	}

	/**
	 * An invalid date string is stored as empty, not as garbage.
	 */
	public function testInvalidDateStringStoredAsEmpty(): void {
		$product = $this->create_product();

		$key = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'DATE-BAD',
				'status'     => 'sold',
				'order_date' => 'not-a-date',
			)
		);
		$this->assertNotWPError( $key );

		$this->assertEmpty( Key::find( $key->get_id() )->get_order_date() );
	}
}
