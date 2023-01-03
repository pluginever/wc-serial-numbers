<?php
use LevelLevel\WPBrowserWooCommerce\WCTestCase;

// write a wpunit test for ordering a product with WooCommerce.
class OrderTest extends WCTestCase {

	protected $tester;

	public function test_order() {
		$product = $this->factory()->product->create_and_get(
			array(
				'name'          => 'test',
				'regular_price' => '12.12',
			)
		);

		$id = wc_serial_numbers_insert_serial_number(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'test',
			)
		);

		$this->assertNotEmpty( $id );
		$serial = wc_serial_numbers_get_serial_number( $id );
		$this->assertNotEmpty( 'available', $serial->status );

		$order = $this->factory()->order->create_and_get(
			array(
				'payment_method'       => 'bacs',
				'payment_method_title' => 'BACS',
				'set_paid'             => true,
				'line_items'           => array(
					array(
						'product_id' => $product->get_id(),
						'quantity'   => 2,
					),
				),
			)
		);

		$this->assertEquals( 24.24, $order->get_total() );

		// check database for serial numbers.
		$serial = wc_serial_numbers_get_serial_number( $id );
		$this->assertEquals( $order->get_id(), $serial->order_id );
		$this->assertEquals( 'sold', $serial->status );

		// update order status to complete.
		$order->update_status( 'completed' );

		// check database for serial numbers.
		$serial = wc_serial_numbers_get_serial_number( $id );
		$this->assertEquals( 'sold', $serial->status );

		// update order status to cancelled.
		$order->update_status( 'cancelled' );

		// check database for serial numbers.
		$serial = wc_serial_numbers_get_serial_number( $id );
		$this->assertEquals( 'cancelled', $serial->status );
	}
}
