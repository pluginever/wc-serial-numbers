<?php
//phpcs:ignoreFile

namespace PluginEver\SerialNumbers\Tests;

/**
 * Tests for stock counting of available keys.
 */
class StocksTest extends TestCase {

	/**
	 * Stock counts reflect the available keys of a custom-source product.
	 */
	public function testStocksCountReflectsAvailableKeys(): void {
		$product = $this->create_product();

		$this->make_key( array( 'product_id' => $product->get_id(), 'serial_key' => 'STOCK-KEY-1' ) );
		$this->make_key( array( 'product_id' => $product->get_id(), 'serial_key' => 'STOCK-KEY-2' ) );
		$this->make_key( array( 'product_id' => $product->get_id(), 'serial_key' => 'STOCK-KEY-3' ) );
		$this->make_key(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'STOCK-KEY-SOLD',
				'status'     => 'sold',
			)
		);

		$counts = wcsn_get_stocks_count();
		$this->assertArrayHasKey( $product->get_id(), $counts );
		$this->assertEquals( 3, $counts[ $product->get_id() ] );
	}

	/**
	 * Per-product stock helper returns the available count, zero when unknown.
	 */
	public function testProductStockHelper(): void {
		$product = $this->create_product();
		$this->make_key( array( 'product_id' => $product->get_id(), 'serial_key' => 'STOCK-KEY-4' ) );

		$this->assertEquals( 1, wcsn_get_product_stock( $product->get_id() ) );
		$this->assertSame( 0, wcsn_get_product_stock( 999999 ) );
	}

	/**
	 * The WooCommerce stock quantity of an enabled product is overridden by key stock.
	 */
	public function testStockQuantityFilterUsesKeyStock(): void {
		$product = $this->create_product();
		$this->make_key( array( 'product_id' => $product->get_id(), 'serial_key' => 'STOCK-KEY-5' ) );
		$this->make_key( array( 'product_id' => $product->get_id(), 'serial_key' => 'STOCK-KEY-6' ) );

		$this->assertEquals( 2, wc_get_product( $product->get_id() )->get_stock_quantity() );
	}
}
