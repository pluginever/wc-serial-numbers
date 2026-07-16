<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for the b8 model cache layer.
 *
 * The migration introduced object caching on Key::find() and query results.
 * These pin that cached reads still decrypt and that every write path
 * (insert, update, delete) invalidates stale entries within a request.
 */
class ModelCacheTest extends TestCase {

	/**
	 * find() primes the per-id cache and a cached read still decrypts.
	 */
	public function testFindPrimesCacheAndCachedReadDecrypts(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'CACHE-DEC-1' );

		$first = Key::find( $key->get_id() );
		$this->assertSame( 'CACHE-DEC-1', $first->get_serial_key() );

		$cached_row = wp_cache_get( $key->get_id(), 'serial_numbers' );
		$this->assertNotFalse( $cached_row, 'find() must prime the per-id cache.' );
		$this->assertNotSame( 'CACHE-DEC-1', $cached_row->serial_key, 'Cache must hold the value at rest, not plaintext.' );

		$second = Key::find( $key->get_id() );
		$this->assertSame( 'CACHE-DEC-1', $second->get_serial_key() );
		$this->assertSame( $key->get_id(), $second->get_id() );
	}

	/**
	 * Updating through one instance invalidates the find() cache for others.
	 */
	public function testUpdateInvalidatesFindCache(): void {
		$product = $this->create_product();
		$key     = $this->make_available_key( $product->get_id(), 'CACHE-UPD-1' );

		// Prime the cache.
		$this->assertSame( 0, Key::find( $key->get_id() )->get_activation_limit() );

		$editor = Key::find( $key->get_id() );
		$editor->set_activation_limit( 5 );
		$this->assertNotWPError( $editor->save() );

		$this->assertSame( 5, Key::find( $key->get_id() )->get_activation_limit() );
	}

	/**
	 * Inserting a key invalidates cached query and count results.
	 */
	public function testInsertInvalidatesQueryAndCountCache(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'CACHE-INS-1' );

		$this->assertCount( 1, Key::query( array( 'product_id' => $product->get_id() ) ) );
		$this->assertSame( 1, Key::count( array( 'product_id' => $product->get_id() ) ) );

		$this->make_available_key( $product->get_id(), 'CACHE-INS-2' );

		$this->assertCount( 2, Key::query( array( 'product_id' => $product->get_id() ) ) );
		$this->assertSame( 2, Key::count( array( 'product_id' => $product->get_id() ) ) );
	}

	/**
	 * Deleting a key invalidates cached query results and the per-id cache.
	 */
	public function testDeleteInvalidatesQueryAndFindCache(): void {
		$product = $this->create_product();
		$first   = $this->make_available_key( $product->get_id(), 'CACHE-DEL-1' );
		$this->make_available_key( $product->get_id(), 'CACHE-DEL-2' );

		$this->assertCount( 2, Key::query( array( 'product_id' => $product->get_id() ) ) );
		$this->assertInstanceOf( Key::class, Key::find( $first->get_id() ) );

		$this->assertTrue( $first->delete() );

		$this->assertCount( 1, Key::query( array( 'product_id' => $product->get_id() ) ) );
		$this->assertNull( Key::find( $first->get_id() ) );
	}

	/**
	 * A composite find() by serial key reflects a later status change.
	 */
	public function testCompositeFindReflectsStatusChange(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'CACHE-CMP-1' );

		$found = Key::find( array( 'serial_key' => 'CACHE-CMP-1' ) );
		$this->assertSame( 'available', $found->get_status() );

		$found->set_status( 'sold' );
		$this->assertNotWPError( $found->save() );

		$refound = Key::find( array( 'serial_key' => 'CACHE-CMP-1' ) );
		$this->assertInstanceOf( Key::class, $refound );
		$this->assertSame( 'sold', $refound->get_status() );
	}
}
