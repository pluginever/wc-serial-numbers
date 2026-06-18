<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for the Key model.
 */
class KeyModelTest extends TestCase {

	/**
	 * A saved key is stored encrypted but reads back as plaintext.
	 */
	public function testKeyStoredEncryptedReadsPlaintext(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'PLAINTEXT-XYZ' );

		$raw = $this->get_raw_serial_key( $key->get_id() );
		$this->assertNotSame( 'PLAINTEXT-XYZ', $raw, 'Serial key must not be stored in plaintext.' );
		$this->assertSame( 'PLAINTEXT-XYZ', wcsn_decrypt_key( $raw ) );

		$reloaded = Key::find( $key->get_id() );
		$this->assertSame( 'PLAINTEXT-XYZ', $reloaded->get_serial_key() );
	}

	/**
	 * Lookup by plaintext serial key matches the encrypted row.
	 */
	public function testGetBySerialKeyMatchesEncryptedRow(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'PLAINTEXT-XYZ' );

		$found = Key::find(
			array(
				'serial_key' => 'PLAINTEXT-XYZ',
				'product_id' => $product->get_id(),
			)
		);

		$this->assertInstanceOf( Key::class, $found );
		$this->assertSame( $key->get_id(), $found->get_id() );
	}

	/**
	 * Saving without a product id is rejected.
	 */
	public function testSaveRequiresProductId(): void {
		$key = new Key();
		$key->set_serial_key( 'NOPROD-1' );
		$this->assertWPError( $key->save() );
	}

	/**
	 * Product existence is no longer validated on save; only a product_id is required.
	 */
	public function testSaveAllowsUnknownProductId(): void {
		$key             = new Key();
		$key->product_id = 99999999;
		$key->serial_key = 'BADPROD-1';
		$this->assertNotWPError( $key->save() );
	}

	/**
	 * Duplicate serial keys are rejected by default.
	 */
	public function testDuplicateSerialKeyRejected(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'DUP-1' );

		$key = new Key();
		$key->set_product_id( $product->get_id() );
		$key->set_serial_key( 'DUP-1' );
		$this->assertWPError( $key->save() );
	}

	/**
	 * set_status() ignores values outside the status whitelist.
	 */
	public function testSetStatusIgnoresUnknownStatus(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'STAT-1' );

		$key->set_status( 'refunded' );
		$this->assertSame( 'available', $key->get_status() );
	}

	/**
	 * Saving as available clears order linkage and activation count.
	 */
	public function testAvailableStatusClearsOrderFields(): void {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1, array( 'status' => 'completed' ) );

		$key = $this->make_available_key( $product->get_id(), 'CLR-1' );
		$key->set_order_id( $order->get_id() );
		$key->set_status( 'sold' );
		$key->save();
		$this->assertSame( $order->get_id(), $key->get_order_id() );

		$key->set_status( 'available' );
		$key->save();
		$this->assertSame( 0, $key->get_order_id() );
		$this->assertEmpty( $key->get_order_date() );
		$this->assertSame( 0, $key->get_activation_count() );
	}

	/**
	 * Expiry date and activations-left math (unlimited returns 9999).
	 */
	public function testExpiryAndActivationsLeftMath(): void {
		$key = new Key();
		$key->set_validity( 30 );
		$key->set_order_date( '2026-01-01 00:00:00' );
		$this->assertSame( '2026-01-31 00:00:00', $key->get_expire_date() );

		$unlimited = new Key();
		$unlimited->set_activation_limit( 0 );
		$this->assertSame( 9999, $unlimited->get_activations_left() );

		$limited = new Key();
		$limited->set_activation_limit( 5 );
		$limited->set_activation_count( 2 );
		$this->assertSame( 3, $limited->get_activations_left() );
	}

	/**
	 * Regression #527: find() returns null (not a non-existent object) for a
	 * missing id. The admin edit screen must null-guard the result before
	 * calling ->exists(), or it fatals on a stale/deleted edit link.
	 */
	public function testFindReturnsNullForMissingId(): void {
		$this->assertNull( Key::find( 999999 ) );

		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'FIND-ME' );
		$found   = Key::find( $key->get_id() );
		$this->assertInstanceOf( Key::class, $found );
		$this->assertTrue( $found->exists() );
	}
}
