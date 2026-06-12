<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for the Key::query() surface the pro plugin depends on.
 *
 * Pro paginates key exports with Key::query( array( 'product_id' => X, 'limit' => N, 'paged' => P ) ),
 * looping until a page comes back empty. These pin that contract: paged advances the offset, results
 * decrypt, and an out-of-range page terminates the loop.
 */
class KeyQueryTest extends TestCase {

	/**
	 * Seed a product with a known number of available keys.
	 */
	private function seed_keys( int $product_id, int $count ): void {
		for ( $i = 1; $i <= $count; $i++ ) {
			$this->make_available_key( $product_id, sprintf( 'KEY-%03d', $i ) );
		}
	}

	/**
	 * Query with args returns model instances, not a builder.
	 */
	public function testQueryWithArgsReturnsItems(): void {
		$product = $this->create_product();
		$this->seed_keys( $product->get_id(), 3 );

		$keys = Key::query( array( 'product_id' => $product->get_id() ) );

		$this->assertIsArray( $keys );
		$this->assertCount( 3, $keys );
		$this->assertInstanceOf( Key::class, $keys[0] );
	}

	/**
	 * paged advances the offset so each page returns a distinct slice.
	 */
	public function testPagedAdvancesThroughPages(): void {
		$product = $this->create_product();
		$this->seed_keys( $product->get_id(), 5 );

		$args  = array( 'product_id' => $product->get_id(), 'limit' => 2 );
		$page1 = Key::query( array_merge( $args, array( 'paged' => 1 ) ) );
		$page2 = Key::query( array_merge( $args, array( 'paged' => 2 ) ) );
		$page3 = Key::query( array_merge( $args, array( 'paged' => 3 ) ) );

		$this->assertCount( 2, $page1 );
		$this->assertCount( 2, $page2 );
		$this->assertCount( 1, $page3 );

		$ids = array_merge(
			wp_list_pluck( $page1, 'id' ),
			wp_list_pluck( $page2, 'id' ),
			wp_list_pluck( $page3, 'id' )
		);
		$this->assertCount( 5, array_unique( $ids ), 'Each page must return a distinct slice.' );
	}

	/**
	 * An out-of-range page returns empty, terminating the export loop.
	 */
	public function testPageBeyondRangeReturnsEmpty(): void {
		$product = $this->create_product();
		$this->seed_keys( $product->get_id(), 2 );

		$beyond = Key::query(
			array(
				'product_id' => $product->get_id(),
				'limit'      => 20,
				'paged'      => 2,
			)
		);

		$this->assertSame( array(), $beyond );
	}

	/**
	 * Queried keys expose decrypted serial keys to callers.
	 */
	public function testQueriedKeysDecrypt(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'PLAINTEXT-QUERY' );

		$keys = Key::query( array( 'product_id' => $product->get_id() ) );

		$this->assertSame( 'PLAINTEXT-QUERY', $keys[0]->get_serial_key() );
	}
}
