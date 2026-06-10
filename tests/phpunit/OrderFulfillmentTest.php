<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;
use WooCommerceSerialNumbers\Orders;

/**
 * Tests for the order fulfillment lifecycle.
 */
class OrderFulfillmentTest extends TestCase {

	/**
	 * A completed order assigns available keys and marks them sold.
	 */
	public function testKeysAssignedOnCompletedOrder(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'K-1' );
		$this->make_available_key( $product->get_id(), 'K-2' );
		$order = $this->create_order( $product, 2, array( 'status' => 'completed' ) );

		wcsn_order_update_keys( $order->get_id() );

		$keys = Key::query( array( 'order_id' => $order->get_id(), 'limit' => -1 ) );
		$this->assertCount( 2, $keys );
		foreach ( $keys as $key ) {
			$this->assertSame( 'sold', $key->get_status() );
		}
	}

	/**
	 * Not enough available keys assigns nothing.
	 */
	public function testInsufficientStockAssignsNothing(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'K-1' );
		$order = $this->create_order( $product, 3, array( 'status' => 'completed' ) );

		wcsn_order_update_keys( $order->get_id() );

		$this->assertCount( 0, Key::query( array( 'order_id' => $order->get_id(), 'limit' => -1 ) ) );
	}

	/**
	 * A fully refunded line item gets no keys assigned.
	 */
	public function testFullRefundAssignsNoKeys(): void {
		$product = $this->create_product();
		// Complete the order before any keys exist, so nothing is assigned at creation.
		$order = $this->create_order( $product, 2, array( 'status' => 'completed' ) );

		$items  = $order->get_items();
		$item   = current( $items );
		$refund = wc_create_refund(
			array(
				'order_id'   => $order->get_id(),
				'amount'     => 0,
				'line_items' => array(
					$item->get_id() => array(
						'qty'          => 2,
						'refund_total' => 0,
					),
				),
			)
		);
		$this->assertNotWPError( $refund );

		// Make keys available; the fully refunded item must not pull any.
		$this->make_available_key( $product->get_id(), 'K-1' );
		$this->make_available_key( $product->get_id(), 'K-2' );

		wcsn_order_update_keys( $order->get_id() );

		$this->assertCount( 0, Key::query( array( 'order_id' => $order->get_id(), 'limit' => -1 ) ) );
	}

	/**
	 * Cancelling an order revokes its keys to cancelled (reuse off).
	 */
	public function testCancelRevokesKeysToCancelled(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'K-1' );
		$order = $this->create_order( $product, 1, array( 'status' => 'completed' ) );

		wcsn_order_update_keys( $order->get_id() );
		$keys = Key::query( array( 'order_id' => $order->get_id(), 'limit' => -1 ) );
		$this->assertCount( 1, $keys );
		$key_id = $keys[0]->get_id();

		$order->set_status( 'cancelled' );
		$order->save();
		wcsn_order_update_keys( $order->get_id() );

		$revoked = Key::get( $key_id );
		$this->assertSame( 'cancelled', $revoked->get_status() );
		$this->assertSame( 0, $revoked->get_order_id() );
	}

	/**
	 * With reuse enabled, cancelling returns keys to available.
	 */
	public function testReuseSettingReturnsKeysToAvailable(): void {
		$this->set_option( 'wc_serial_numbers_reuse_serial_number', 'yes' );

		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'K-1' );
		$order = $this->create_order( $product, 1, array( 'status' => 'completed' ) );

		wcsn_order_update_keys( $order->get_id() );
		$key_id = Key::query( array( 'order_id' => $order->get_id(), 'limit' => -1 ) )[0]->get_id();

		$order->set_status( 'cancelled' );
		$order->save();
		wcsn_order_update_keys( $order->get_id() );

		$this->assertSame( 'available', Key::get( $key_id )->get_status() );
	}

	/**
	 * Running fulfillment twice does not assign extra keys.
	 */
	public function testIdempotentNoDoubleAssign(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'K-1' );
		$this->make_available_key( $product->get_id(), 'K-2' );
		$this->make_available_key( $product->get_id(), 'K-3' );
		$order = $this->create_order( $product, 2, array( 'status' => 'completed' ) );

		wcsn_order_update_keys( $order->get_id() );
		wcsn_order_update_keys( $order->get_id() );

		$this->assertCount( 2, Key::query( array( 'order_id' => $order->get_id(), 'limit' => -1 ) ) );
		$this->assertSame( 1, (int) Key::count( array( 'product_id' => $product->get_id(), 'status' => 'available' ) ) );
	}

	/**
	 * Autocomplete promotes a processing order to completed when enabled.
	 */
	public function testAutocompleteOrderStatusFilter(): void {
		$product = $this->create_product();
		$order   = $this->create_order( $product, 1 );

		$this->set_option( 'wc_serial_numbers_autocomplete_order', 'yes' );
		$this->assertSame( 'completed', Orders::maybe_autocomplete_order( 'processing', $order->get_id(), $order ) );

		$this->set_option( 'wc_serial_numbers_autocomplete_order', 'no' );
		$this->assertSame( 'processing', Orders::maybe_autocomplete_order( 'processing', $order->get_id(), $order ) );
	}
}
