<?php
//phpcs:ignoreFile

namespace WooCommerceSerialNumbers\Tests;

use WooCommerceSerialNumbers\Models\Key;
use WooCommerceSerialNumbers\Orders;

/**
 * Tests for stock counting and the checkout stock guard.
 */
class StockTest extends TestCase {

	/**
	 * The available-key count reports as the product stock.
	 */
	public function testStockCountReflectsAvailableKeys(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'S-1' );
		$this->make_available_key( $product->get_id(), 'S-2' );
		$this->make_available_key( $product->get_id(), 'S-3' );

		// A sold key (not tied to an order) must not count toward available stock.
		$sold = Key::insert(
			array(
				'product_id' => $product->get_id(),
				'serial_key' => 'S-SOLD',
				'status'     => 'sold',
			)
		);
		$this->assertNotWPError( $sold );

		$this->assertSame( 3, (int) wcsn_get_product_stock( $product->get_id() ) );
	}

	/**
	 * Checkout is blocked when there are not enough available keys.
	 */
	public function testCheckoutBlockedWhenNotEnough(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'C-1' );

		if ( is_null( WC()->cart ) ) {
			wc_load_cart();
		}
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $product->get_id(), 2 );
		wc_clear_notices();

		Orders::validate_checkout();

		$this->assertNotEmpty( wc_get_notices( 'error' ) );

		WC()->cart->empty_cart();
		wc_clear_notices();
	}

	/**
	 * Checkout passes when there is enough stock.
	 */
	public function testCheckoutPassesWithEnough(): void {
		$product = $this->create_product();
		$this->make_available_key( $product->get_id(), 'C-1' );
		$this->make_available_key( $product->get_id(), 'C-2' );

		if ( is_null( WC()->cart ) ) {
			wc_load_cart();
		}
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $product->get_id(), 2 );
		wc_clear_notices();

		Orders::validate_checkout();

		$this->assertEmpty( wc_get_notices( 'error' ) );

		WC()->cart->empty_cart();
		wc_clear_notices();
	}

	/**
	 * Low-stock selection returns only products at or below the threshold.
	 */
	public function testLowStockThresholdSelection(): void {
		$low = $this->create_product();
		$this->make_available_key( $low->get_id(), 'L-1' );
		$this->make_available_key( $low->get_id(), 'L-2' );

		$high = $this->create_product();
		for ( $i = 1; $i <= 10; $i++ ) {
			$this->make_available_key( $high->get_id(), 'H-' . $i );
		}

		$counts = wcsn_get_stocks_count( 5 );

		$this->assertArrayHasKey( $low->get_id(), $counts );
		$this->assertArrayNotHasKey( $high->get_id(), $counts );
	}
}
