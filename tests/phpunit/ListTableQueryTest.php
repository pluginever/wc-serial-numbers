<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for Key::query()/count() with the exact argument shape the admin
 * KeysTable passes (per_page/paged, null filters, include, search).
 *
 * The search arg crosses the encryption boundary via Key::prepare_search(),
 * so a full plaintext serial must match the encrypted column.
 */
class ListTableQueryTest extends TestCase {

	/**
	 * Build args the way Admin\ListTables\KeysTable::prepare_items() does,
	 * where absent request fields arrive as null.
	 *
	 * @param array $overrides Arg overrides.
	 * @return array
	 */
	private function table_args( array $overrides = array() ): array {
		return array_merge(
			array(
				'per_page'    => 20,
				'paged'       => 1,
				'orderby'     => null,
				'order'       => null,
				'status'      => null,
				'product_id'  => null,
				'order_id'    => null,
				'customer_id' => null,
				'include'     => null,
				'search'      => null,
			),
			$overrides
		);
	}

	/**
	 * Null filter args are ignored and all rows come back.
	 */
	public function testNullArgsReturnAllRows(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'LT-1' );
		$this->make_available_key( $product->get_id(), 'LT-2' );
		$this->make_available_key( $product->get_id(), 'LT-3' );

		$items = Key::query( $this->table_args() );

		$this->assertCount( 3, $items );
		$this->assertInstanceOf( Key::class, $items[0] );
	}

	/**
	 * Status filter and the per-status counts the table renders.
	 */
	public function testStatusFilterAndCounts(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'LT-A1' );
		$this->make_available_key( $product->get_id(), 'LT-A2' );
		$sold = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'LT-S1',
				'status'     => 'sold',
			)
		);
		$this->assertNotWPError( $sold );

		$sold_items = Key::query( $this->table_args( array( 'status' => 'sold' ) ) );
		$this->assertCount( 1, $sold_items );
		$this->assertSame( 'sold', $sold_items[0]->get_status() );

		$this->assertSame( 2, Key::count( array_merge( $this->table_args(), array( 'status' => 'available' ) ) ) );
		$this->assertSame( 1, Key::count( array_merge( $this->table_args(), array( 'status' => 'sold' ) ) ) );
		$this->assertSame( 0, Key::count( array_merge( $this->table_args(), array( 'status' => 'expired' ) ) ) );
	}

	/**
	 * Searching a full plaintext serial matches the encrypted column.
	 */
	public function testSearchByFullPlaintextSerialFindsKey(): void {
		$product = $this->create_product();
		$alpha   = $this->make_available_key( $product->get_id(), 'KEY-ALPHA-001' );
		$this->make_available_key( $product->get_id(), 'KEY-BETA-002' );

		$items = Key::query( $this->table_args( array( 'search' => 'KEY-ALPHA-001' ) ) );

		$this->assertCount( 1, $items );
		$this->assertSame( $alpha->get_id(), $items[0]->get_id() );
		$this->assertSame( 'KEY-ALPHA-001', $items[0]->get_serial_key() );
	}

	/**
	 * A partial serial cannot match the value at rest (encrypted column means
	 * full-value search only).
	 */
	public function testSearchByPartialSerialReturnsEmpty(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'KEY-ALPHA-001' );

		$items = Key::query( $this->table_args( array( 'search' => 'ALPHA' ) ) );

		$this->assertSame( array(), $items );
	}

	/**
	 * The include arg (id deep-link filter) restricts results to that key.
	 */
	public function testIncludeArgRestrictsToId(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'LT-INC-1' );
		$target = $this->make_available_key( $product->get_id(), 'LT-INC-2' );

		$items = Key::query( $this->table_args( array( 'include' => $target->get_id() ) ) );

		$this->assertCount( 1, $items );
		$this->assertSame( $target->get_id(), $items[0]->get_id() );
	}

	/**
	 * per_page/paged paginate into distinct slices like the table expects.
	 */
	public function testPerPagePagedPagination(): void {
		$product = $this->create_product();
		for ( $i = 1; $i <= 5; $i++ ) {
			$this->make_available_key( $product->get_id(), sprintf( 'LT-PG-%03d', $i ) );
		}

		$page1 = Key::query( $this->table_args( array( 'per_page' => 2, 'paged' => 1 ) ) );
		$page2 = Key::query( $this->table_args( array( 'per_page' => 2, 'paged' => 2 ) ) );
		$page3 = Key::query( $this->table_args( array( 'per_page' => 2, 'paged' => 3 ) ) );

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
}
