<?php

namespace WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Helper class.
 *
 * @since 1.0.0
 */
class Helper {
	/**
	 * Check if software support is enabled or not.
	 *
	 * @since #.#.#
	 * @return bool
	 */
	public static function is_software_support_enabled() {
		return 'yes' !== get_option( 'wc_serial_numbers_disable_software_support', 'no' );
	}

	/**
	 * Check if the key reusing is enabled or not.
	 *
	 * @since #.#.#
	 * @return bool
	 */
	public static function is_reusing_key() {
		return apply_filters( 'wc_serial_numbers_is_reusing_key', false );
	}

	/**
	 * Check if automatic key delivery is enabled or not.
	 *
	 * @since #.#.#
	 * @return bool
	 */
	public static function is_automatic_delivery_enabled() {
		return 'yes' === get_option( 'wc_serial_numbers_automatic_delivery', 'no' );
	}

	/**
	 * Is duplicate key allowed or not.
	 *
	 * @since #.#.#
	 * @return bool
	 */
	public static function is_duplicate_key_allowed() {
		return apply_filters( 'wc_serial_numbers_is_duplicate_allowed', false );
	}

	/**
	 * Return parent  product ID.
	 *
	 * @since #.#.#
	 *
	 * @param int|\WC_Product|\WC_Order_Item $product WooCommerce Product object.
	 *
	 * @return bool|int
	 */
	public static function get_parent_product_id( $product ) {
		$product = wc_get_product( $product );

		if ( $product ) {
			if ( is_callable( array( $product, 'get_parent_id', 'is_type' ) ) ) {
				return $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
			}

			if ( is_callable( array( $product, 'get_product_id' ) ) ) {
				return ! empty( $product->get_product_id() ) ? $product->get_product_id() : $product->get_id();
			}

			return $product->get_id();
		}

		return false;
	}
}
