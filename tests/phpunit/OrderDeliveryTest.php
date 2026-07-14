<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;

/**
 * Tests for automatic key delivery on order status changes.
 */
class OrderDeliveryTest extends TestCase {

	/**
	 * Create an order containing one unit of a serial-enabled product.
	 *
	 * @param \WC_Product $product Product to order.
	 * @return \WC_Order
	 */
	protected function create_order( \WC_Product $product ): \WC_Order {
		$order = wc_create_order();
		$order->add_product( $product, 1 );
		$order->calculate_totals();
		$order->save();

		return $order;
	}

	/**
	 * Completing an order assigns an available key to it and marks it sold.
	 */
	public function testCompletedOrderAssignsKey(): void {
		$product = $this->create_product();
		$key     = $this->make_key(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'ORDER-KEY-001',
			)
		);

		$order = $this->create_order( $product );
		$order->update_status( 'completed' );

		$reloaded = Key::get( $key->get_id() );
		$this->assertSame( $order->get_id(), $reloaded->get_order_id() );
		$this->assertSame( 'sold', $reloaded->get_status() );
		$this->assertNotEmpty( $reloaded->get_order_item_id() );
		$this->assertNotEmpty( $reloaded->get_order_date() );

		$order_keys = wcsn_order_get_keys( $order->get_id() );
		$this->assertCount( 1, $order_keys );
		$this->assertSame( $key->get_id(), $order_keys[0]->get_id() );
		$this->assertTrue( wcsn_order_is_fullfilled( $order->get_id() ) );
	}

	/**
	 * A processing order also triggers key assignment.
	 */
	public function testProcessingOrderAssignsKey(): void {
		$product = $this->create_product();
		$key     = $this->make_key(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'ORDER-KEY-002',
			)
		);

		$order = $this->create_order( $product );
		$order->update_status( 'processing' );

		$reloaded = Key::get( $key->get_id() );
		$this->assertSame( $order->get_id(), $reloaded->get_order_id() );
		$this->assertSame( 'sold', $reloaded->get_status() );
	}

	/**
	 * Cancelling a delivered order revokes the key.
	 */
	public function testCancelledOrderRevokesKey(): void {
		$product = $this->create_product();
		$key     = $this->make_key(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'ORDER-KEY-003',
			)
		);

		$order = $this->create_order( $product );
		$order->update_status( 'completed' );
		$order->update_status( 'cancelled' );

		$reloaded = Key::get( $key->get_id() );
		$this->assertSame( 'cancelled', $reloaded->get_status() );
		$this->assertSame( 0, $reloaded->get_order_id() );
	}

	/**
	 * Without available keys the order stays unfulfilled.
	 */
	public function testOrderWithoutStockStaysUnfulfilled(): void {
		$product = $this->create_product();

		$order = $this->create_order( $product );
		$order->update_status( 'completed' );

		$this->assertCount( 0, wcsn_order_get_keys( $order->get_id() ) );
		$this->assertFalse( wcsn_order_is_fullfilled( $order->get_id() ) );
	}
}
