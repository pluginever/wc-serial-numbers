<?php

namespace WooCommerceSerialNumbers;

use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Stocks.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class Stocks {

	/**
	 * Stocks constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_get_stock_quantity', array( __CLASS__, 'get_stock_quantity' ), 20, 2 );

		// Manage Stocks.
		add_action( 'wc_serial_numbers_key_inserted', array( __CLASS__, 'update_stocks' ) );
		add_action( 'wc_serial_numbers_key_updated', array( __CLASS__, 'update_stocks' ) );
		add_action( 'wc_serial_numbers_key_deleted', array( __CLASS__, 'update_stocks' ) );
	}

	/**
	 * Get stock quantity.
	 *
	 * @param int         $quantity Stock quantity.
	 * @param \WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public static function get_stock_quantity( $quantity, $product ) {
		if ( wcsn_is_product_enabled( $product->get_id() ) ) {
			$stocks = wcsn_get_stocks_count();
			if ( isset( $stocks[ $product->get_id() ] ) ) {
				$quantity = $stocks[ $product->get_id() ];
			}
		}

		return $quantity;
	}

	/**
	 * Update stocks.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public static function update_stocks( $key ) {
		if ( 'no' === get_option( 'wcsn_manage_stocks', 'no' ) ) {
			return; // Return if stock management is disabled.
		}

		$product = $key->get_product();

		// Check if product exists and stock management is enabled.
		if ( ! $product || ! $product->get_manage_stock() ) {
			return;
		}

		// Check if product is enabled for WCSN.
		if ( ! wcsn_is_product_enabled( $product->get_id() ) ) {
			return;
		}

		// Get the total stock quantity. This will be the sum of all available keys.
		$quantity = self::get_stock_quantity( $product->get_stock_quantity(), $product );

		// Update the product stock meta directly.
		$product->set_stock_quantity( $quantity );
		$product->save();
	}
}
