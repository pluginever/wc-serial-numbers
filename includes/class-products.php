<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Product.
 *
 * Handles product related actions.
 *
 * @since  1.0.0
 * @return Products
 */
class Products {
	/**
	 * Products constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_get_stock_quantity', array( __CLASS__, 'set_stock_quantity' ), 10, 2 );
	}

	/**
	 * Set stock quantity.
	 *
	 * @param int $stock_quantity Stock quantity.
	 * @param \WC_Product $wc_product Product object.
	 *
	 * @since 3.1.0
	 */
	public static function set_stock_quantity( $stock_quantity, $wc_product ) {
		$product = Product::get( $wc_product->get_id() );
		if ( $product->is_selling_serial_numbers() && 'pre_generated' === $product->get_key_source() && $product->managing_stock() ) {
			$stock_quantity = $product->get_key_stock_count();
		}

		return $stock_quantity;
	}

	/**
	 * Update product metadata.
	 *
	 * @since #.#.#
	 *
	 * @param int|\WC_Product $product Product object.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value.
	 */
//	public static function update_meta( $product, $meta_key, $meta_value ) {
//		$product = Helper::get_product_object( $product );
//
//		if ( $product ) {
//			if ( Helper::is_woocommerce_pre( '3.0' ) ) {
//				update_post_meta( $product->get_id(), $meta_key, $meta_value );
//			} else {
//				$product->update_meta_data( $meta_key, $meta_value );
//				$product->save_meta_data();
//			}
//		}
//	}

}
