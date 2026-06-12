<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for the admin edit-key path: Key::insert() with an existing id.
 *
 * Admin\Requests::handle_edit_key() upserts through Key::insert(), so an id
 * in the payload must update the row in place and keep the serial encrypted
 * exactly once at rest.
 */
class KeyUpsertTest extends TestCase {

	/**
	 * Insert with an existing id updates the row instead of creating one.
	 */
	public function testInsertWithIdUpdatesExistingRow(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'UPSERT-1' );

		$updated = Key::insert(
			array(
				'id'               => $key->get_id(),
				'product_id'       => $product->get_id(),
				'serial_key'       => 'UPSERT-1',
				'activation_limit' => 5,
				'validity'         => 30,
				'status'           => 'available',
			)
		);

		$this->assertNotWPError( $updated );
		$this->assertSame( $key->get_id(), $updated->get_id() );
		$this->assertSame( 1, Key::count( array( 'product_id' => $product->get_id() ) ) );

		$reloaded = Key::find( $key->get_id() );
		$this->assertSame( 5, $reloaded->get_activation_limit() );
		$this->assertSame( 30, $reloaded->get_validity() );
	}

	/**
	 * An upsert that does not change the serial leaves the ciphertext as is.
	 */
	public function testUpsertUnchangedSerialKeepsCiphertext(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'UPSERT-2' );
		$before  = $this->get_raw_serial_key( $key->get_id() );

		$updated = Key::insert(
			array(
				'id'         => $key->get_id(),
				'product_id' => $product->get_id(),
				'serial_key' => 'UPSERT-2',
				'validity'   => 10,
			)
		);
		$this->assertNotWPError( $updated );

		$after = $this->get_raw_serial_key( $key->get_id() );
		$this->assertSame( $before, $after, 'Unchanged serial must not be rewritten.' );
		$this->assertSame( 'UPSERT-2', wcsn_decrypt_key( $after ), 'Serial must remain encrypted exactly once.' );
	}

	/**
	 * Changing the serial through an upsert re-encrypts the new value and the
	 * old plaintext no longer resolves.
	 */
	public function testUpsertNewSerialReEncrypts(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'UPSERT-OLD' );
		$before  = $this->get_raw_serial_key( $key->get_id() );

		$updated = Key::insert(
			array(
				'id'         => $key->get_id(),
				'product_id' => $product->get_id(),
				'serial_key' => 'UPSERT-NEW',
			)
		);
		$this->assertNotWPError( $updated );

		$after = $this->get_raw_serial_key( $key->get_id() );
		$this->assertNotSame( $before, $after );
		$this->assertNotSame( 'UPSERT-NEW', $after, 'New serial must be stored encrypted.' );
		$this->assertSame( 'UPSERT-NEW', wcsn_decrypt_key( $after ) );

		$found = Key::find( array( 'serial_key' => 'UPSERT-NEW' ) );
		$this->assertInstanceOf( Key::class, $found );
		$this->assertSame( $key->get_id(), $found->get_id() );
		$this->assertNull( Key::find( array( 'serial_key' => 'UPSERT-OLD' ) ) );
	}
}
