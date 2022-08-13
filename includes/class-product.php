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
class Product extends \WC_Product {

	/**
	 * Get product by ID.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return Product
	 */
	public static function get( $product_id ) {
		return new self( $product_id );
	}

	/**
	 * Check if the product is a serial number product.
	 *
	 * @since 1.3.1
	 * @return boolean
	 */
	public function is_selling_serial_numbers() {
		return 'yes' === $this->get_meta( '_selling_serial_numbers' );
	}

	/**
	 * Get serial key source meta.
	 *
	 * @since 1.3.1
	 * @return string
	 */
	public function get_key_source() {
		return $this->get_meta( '_serial_numbers_key_source' );
	}

	/**
	 * Get serial key source meta.
	 *
	 * @since 1.3.1
	 * @return string
	 */
	public function get_generator_id() {
		return $this->get_meta( '_serial_numbers_generator_id' );
	}

	/**
	 * Get pattern meta.
	 *
	 * @since 1.3.1
	 * @return string
	 */
	public function get_pattern() {
		return $this->get_meta( '_serial_numbers_pattern' );
	}

	/**
	 * Get activation limit meta.
	 *
	 * @since 1.3.1
	 * @return string
	 */
	public function get_activation_limit() {
		return $this->get_meta( '_serial_numbers_activation_limit' );
	}

	/**
	 * Get software version meta.
	 *
	 * @since 1.3.1
	 * @return string
	 */
	public function get_software_version() {
		return $this->get_meta( '_serial_numbers_software_version' );
	}

	/**
	 * Get software author meta.
	 *
	 * @since 1.3.1
	 * @return string
	 */
	public function get_software_author() {
		return $this->get_meta( '_serial_numbers_software_author' );
	}

	/**
	 * Get software last updated meta.
	 *
	 * @since 1.3.1
	 * @return string
	 */
	public function get_software_last_updated() {
		return $this->get_meta( '_serial_numbers_software_last_updated' );
	}

	/**
	 * Get software upgrade notice meta.
	 *
	 * @since 1.3.1
	 * @return string
	 */
	public function get_software_upgrade_notice() {
		return $this->get_meta( '_serial_numbers_software_upgrade_notice' );
	}

	/**
	 * Get valid for meta.
	 *
	 * @since 1.3.1
	 * @return string
	 */
	public function get_valid_for() {
		return $this->get_meta( '_serial_numbers_valid_for' );
	}

	/**
	 * Get the total number of serial numbers will be delivered.
	 *
	 * @param int $qty Quantity.
	 *
	 * @return int The number of serial numbers will be delivered.
	 */
	public function get_delivery_quantity( $qty ) {
		return apply_filters( 'wc_serial_numbers_delivery_quantity', $qty, $this );
	}

	/**
	 * Get the total number of serial numbers available.
	 *
	 * @since 1.3.1
	 * @return int The number of serial numbers available.
	 */
	public function get_key_stock_count() {
		return (int ) Keys::query([
			'product_id__in' => [ $this->get_id() ],
			'status'        => 'available',
		], true );
	}
}
