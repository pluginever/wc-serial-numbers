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
 * @return Product
 */
class Product {
	/**
	 * Register action and filter hooks.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function init() {
	}



	/**
	 * Update product metadata.
	 *
	 * @since #.#.#
	 *
	 * @param int|\WC_Product $product Product object.
	 * @param string          $meta_key Meta key.
	 * @param mixed           $meta_value Meta value.
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


	/**
	 * Update product metadata.
	 *
	 * @since #.#.#
	 *
	 * @param int|\WC_Product $product Product object.
	 * @param string          $meta_key Meta key.
	 *
	 * @return mixed
	 */
	public static function get_meta( $product, $meta_key ) {
		$product = Helper::get_product_object( $product );
		$meta    = '';
		if ( $product ) {
			if ( Helper::is_woocommerce_pre( '3.0' ) ) {
				$meta = get_post_meta( $product->get_id(), $meta_key, true );
			} else {
				$meta = $product->get_meta_data( $meta_key );
			}
		}

		return $meta;
	}
}

Product::init();

