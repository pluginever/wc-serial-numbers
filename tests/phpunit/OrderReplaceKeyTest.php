<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for wcsn_order_replace_key(), the admin "replace key" action.
 *
 * The flow revokes the targeted keys through the migrated model layer
 * (fill + save with null date props) and re-runs fulfillment to assign
 * replacements from the available pool.
 */
class OrderReplaceKeyTest extends TestCase {

	/**
	 * Replacing cancels the assigned key, detaches it, and assigns the next
	 * available key to the order (reuse off).
	 */
	public function testReplaceCancelsOldAndAssignsNext(): void {
		$product = $this->create_product();
		$old     = $this->make_available_key( $product->get_id(), 'RPL-OLD' );
		$order   = $this->create_order( $product, 1, array( 'status' => 'completed' ) );

		wcsn_order_update_keys( $order->get_id() );
		$this->assertSame( 'sold', Key::find( $old->get_id() )->get_status() );

		// The replacement pool.
		$fresh = $this->make_available_key( $product->get_id(), 'RPL-NEW' );

		$this->assertTrue( wcsn_order_replace_key( $order->get_id(), $product->get_id() ) );

		$revoked = Key::find( $old->get_id() );
		$this->assertSame( 'cancelled', $revoked->get_status() );
		$this->assertSame( 0, $revoked->get_order_id() );

		$order_keys = Key::query( array( 'order_id' => $order->get_id(), 'limit' => -1 ) );
		$this->assertCount( 1, $order_keys );
		$this->assertSame( $fresh->get_id(), $order_keys[0]->get_id() );
		$this->assertSame( 'sold', $order_keys[0]->get_status() );
	}

	/**
	 * Replacing a specific key id revokes only that key and leaves the
	 * order's other keys untouched.
	 *
	 * No replacement is assigned here: the re-fulfillment pass skips the item
	 * because of the pre-existing `$delivered_qty >= $needed_count` guard in
	 * wcsn_order_update_keys() (unchanged by the migration), so a partially
	 * delivered multi-quantity item is not topped up.
	 */
	public function testReplaceSpecificKeyOnly(): void {
		$product = $this->create_product();
		$key_a   = $this->make_available_key( $product->get_id(), 'RPL-A' );
		$key_b   = $this->make_available_key( $product->get_id(), 'RPL-B' );
		$order   = $this->create_order( $product, 2, array( 'status' => 'completed' ) );

		wcsn_order_update_keys( $order->get_id() );
		$this->assertCount( 2, Key::query( array( 'order_id' => $order->get_id(), 'limit' => -1 ) ) );

		$key_c = $this->make_available_key( $product->get_id(), 'RPL-C' );

		$this->assertTrue( wcsn_order_replace_key( $order->get_id(), $product->get_id(), $key_a->get_id() ) );

		$this->assertSame( 'cancelled', Key::find( $key_a->get_id() )->get_status() );
		$this->assertSame( 'sold', Key::find( $key_b->get_id() )->get_status() );
		$this->assertSame( $order->get_id(), Key::find( $key_b->get_id() )->get_order_id() );
		$this->assertSame( 'available', Key::find( $key_c->get_id() )->get_status() );

		$order_key_ids = wp_list_pluck( Key::query( array( 'order_id' => $order->get_id(), 'limit' => -1 ) ), 'id' );
		$this->assertSame( array( $key_b->get_id() ), $order_key_ids );
	}

	/**
	 * Replacing on an order with no keys returns false.
	 */
	public function testReplaceReturnsFalseWithoutKeys(): void {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1, array( 'status' => 'completed' ) );

		$this->assertFalse( wcsn_order_replace_key( $order->get_id(), $product->get_id() ) );
	}
}
