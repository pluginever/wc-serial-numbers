<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for the Key model.
 */
class KeyModelTest extends TestCase {

	/**
	 * A valid key persists, is encrypted at rest and round-trips back to plain text.
	 */
	public function testInsertPersistsAndRoundtrips(): void {
		global $wpdb;

		$product = $this->create_product();
		$key     = wcsn_insert_key(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'SERIAL-123-ABC',
			)
		);
		$this->assertNotWPError( $key );
		$this->assertNotEmpty( $key->get_id() );

		// Re-read from the database and confirm the plain key round-trips.
		$reloaded = Key::get( $key->get_id() );
		$this->assertSame( 'SERIAL-123-ABC', $reloaded->get_serial_key() );
		$this->assertSame( 'SERIAL-123-ABC', $reloaded->get_key() );
		$this->assertSame( $product->get_id(), $reloaded->get_product_id() );
		$this->assertSame( 'available', $reloaded->get_status() );
		$this->assertSame( 'custom_source', $reloaded->get_source() );

		// The stored value is encrypted at rest, not the plain key.
		$raw = $wpdb->get_var( $wpdb->prepare( "SELECT serial_key FROM {$wpdb->prefix}serial_numbers WHERE id = %d", $key->get_id() ) );
		$this->assertNotSame( 'SERIAL-123-ABC', $raw );
		$this->assertSame( 'SERIAL-123-ABC', wcsn_decrypt_key( $raw ) );
	}

	/**
	 * Saving without a product id is rejected.
	 */
	public function testSaveRequiresProductId(): void {
		$key = wcsn_insert_key(
			array(
				'serial_key' => 'NO-PRODUCT-KEY',
			)
		);
		$this->assertWPError( $key );
		$this->assertSame( 'missing-required', $key->get_error_code() );
	}

	/**
	 * Saving without a serial key is rejected.
	 */
	public function testSaveRequiresSerialKey(): void {
		$key = wcsn_insert_key(
			array(
				'product_id' => $this->create_product()->get_id(),
			)
		);
		$this->assertWPError( $key );
		$this->assertSame( 'missing-required', $key->get_error_code() );
	}

	/**
	 * Saving with an invalid product id is rejected.
	 */
	public function testSaveRejectsInvalidProduct(): void {
		$key = wcsn_insert_key(
			array(
				'product_id' => 999999,
				'serial_key' => 'BAD-PRODUCT-KEY',
			)
		);
		$this->assertWPError( $key );
		$this->assertSame( 'invalid-data', $key->get_error_code() );
	}

	/**
	 * Duplicate serial keys are rejected by default.
	 */
	public function testDuplicateKeyRejectedByDefault(): void {
		$product = $this->create_product();
		$this->make_key(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'DUPLICATE-KEY',
			)
		);

		$duplicate = wcsn_insert_key(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'DUPLICATE-KEY',
			)
		);
		$this->assertWPError( $duplicate );
		$this->assertSame( 'invalid-data', $duplicate->get_error_code() );
	}

	/**
	 * A key transitions from available to sold via setters and save.
	 */
	public function testStatusTransitionAvailableToSold(): void {
		$key = $this->make_key();
		$this->assertSame( 'available', $key->get_status() );

		$key->set_status( 'sold' );
		$this->assertNotWPError( $key->save() );

		$reloaded = Key::get( $key->get_id() );
		$this->assertSame( 'sold', $reloaded->get_status() );
	}

	/**
	 * The status setter ignores values outside the registered statuses.
	 */
	public function testStatusSetterIgnoresUnknownStatus(): void {
		$key = $this->make_key();
		$key->set_status( 'bogus-status' );
		$this->assertSame( 'available', $key->get_status() );
	}

	/**
	 * Deleting a key removes it.
	 */
	public function testDeleteRemovesKey(): void {
		$key = $this->make_key();
		$id  = $key->get_id();

		$this->assertTrue( (bool) wcsn_delete_key( $id ) );
		$this->assertEmpty( Key::get( $id ) );
		$this->assertFalse( wcsn_get_key( $id ) );
	}
}
