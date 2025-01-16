<?php

namespace WooCommerceSerialNumbers;

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
		add_filter( 'woocommerce_product_get_stock_quantity', array( __CLASS__, 'get_stock_quantity' ), 10, 2 );
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
}
