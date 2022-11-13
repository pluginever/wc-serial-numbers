<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit();

/**
 * Key handler class.
 *
 * @since 1.4.0
 * @package WooCommerceSerialNumbers
 */
class Key extends Framework\Data {
	/**
	 * Table name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $table_name = 'serial_numbers';

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $columns = array(
		'serial_key'       => '',
		'product_id'       => 0,
		'activation_limit' => 0,
		'activation_count' => 0,
		'order_id'         => 0,
		'vendor_id'        => 0,
		'status'           => 'available',
		'validity'         => '',
		'expire_date'      => null,
		'order_date'       => null,
		'source'           => 'custom_source',
		'created_date'     => null,
	);

	/**
	 * Get serial number's statuses.
	 *
	 * since 1.2.0
	 *
	 * @return array
	 */
	public static function get_statuses() {
		$statuses = array(
			'available' => __( 'Available', 'wc-serial-numbers' ),
			'sold'      => __( 'Sold', 'wc-serial-numbers' ),
			'refunded'  => __( 'Refunded', 'wc-serial-numbers' ),
			'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ),
			'expired'   => __( 'Expired', 'wc-serial-numbers' ),
			'failed'    => __( 'Failed', 'wc-serial-numbers' ),
			'inactive'  => __( 'Inactive', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_key_statuses', $statuses );
	}

	/**
	 * Get key sources.
	 *
	 * @since 1.2.0
	 * @return mixed|void
	 */
	public static function get_key_sources() {
		$sources = array(
			'custom_source' => __( 'Manually generated serial number', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_key_sources', $sources );
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Methods for getting data from the object.
	|
	*/

	/**
	 * Get serial key.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public function get_serial_key( $context = 'edit' ) {
		return $this->get_prop( 'serial_key', $context );
	}

	/**
	 * Get product ID.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return int
	 */
	public function get_product_id( $context = 'edit' ) {
		return $this->get_prop( 'product_id', $context );
	}

	/**
	 * Get activation limit.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return int
	 */
	public function get_activation_limit( $context = 'edit' ) {
		return $this->get_prop( 'activation_limit', $context );
	}

	/**
	 * Get activation count.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return int
	 */
	public function get_activation_count( $context = 'edit' ) {
		return $this->get_prop( 'activation_count', $context );
	}

	/**
	 * Get order ID.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return int
	 */
	public function get_order_id( $context = 'edit' ) {
		return $this->get_prop( 'order_id', $context );
	}

	/**
	 * Get vendor ID.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return int
	 */
	public function get_vendor_id( $context = 'edit' ) {
		return $this->get_prop( 'vendor_id', $context );
	}

	/**
	 * Get status.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public function get_status( $context = 'edit' ) {
		return $this->get_prop( 'status', $context );
	}

	/**
	 * Get valid for.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public function get_validity( $context = 'edit' ) {
		return $this->get_prop( 'validity', $context );
	}

	/**
	 * Get date ordered.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public function get_order_date( $context = 'edit' ) {
		return $this->get_prop( 'order_date', $context );
	}

	/**
	 * Get source.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public function get_source( $context = 'edit' ) {
		return $this->get_prop( 'source', $context );
	}

	/**
	 * Get date created.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public function get_date_created( $context = 'edit' ) {
		return $this->get_prop( 'date_created', $context );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting boll data. These should not update anything in the
	| database itself and should only change what is stored in the class
	| object.
	|
	*/

	/**
	 * Set serial key.
	 *
	 * @param string $serial_key Serial key.
	 *
	 * @since 1.4.0
	 */
	public function set_serial_key( $serial_key ) {
		$this->set_prop( 'serial_key', $serial_key );
	}

	/**
	 * Set product ID.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @since 1.4.0
	 */
	public function set_product_id( $product_id ) {
		$this->set_prop( 'product_id', absint( $product_id ) );
	}

	/**
	 * Set activation limit.
	 *
	 * @param int $activation_limit Activation limit.
	 *
	 * @since 1.4.0
	 */
	public function set_activation_limit( $activation_limit ) {
		$this->set_prop( 'activation_limit', absint( $activation_limit ) );
	}

	/**
	 * Set activation count.
	 *
	 * @param int $activation_count Activation count.
	 *
	 * @since 1.4.0
	 */
	public function set_activation_count( $activation_count ) {
		$this->set_prop( 'activation_count', absint( $activation_count ) );
	}

	/**
	 * Set order ID.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @since 1.4.0
	 */
	public function set_order_id( $order_id ) {
		$this->set_prop( 'order_id', absint( $order_id ) );
	}

	/**
	 * Set vendor ID.
	 *
	 * @param int $vendor_id Vendor ID.
	 *
	 * @since 1.4.0
	 */
	public function set_vendor_id( $vendor_id ) {
		$this->set_prop( 'vendor_id', absint( $vendor_id ) );
	}

	/**
	 * Set status.
	 *
	 * @param string $status Status.
	 *
	 * @since 1.4.0
	 */
	public function set_status( $status ) {
		if ( ! array_key_exists( $status, self::get_statuses() ) ) {
			$status = 'available';
		}

		$this->set_prop( 'status', $status );
	}

	/**
	 * Set validity.
	 *
	 * @param string $validity Validity.
	 *
	 * @since 1.4.0
	 */
	public function set_validity( $validity ) {
		$this->set_prop( 'validity', $validity );
	}

	/**
	 * Set order date.
	 *
	 * @param string $order_date Order date.
	 *
	 * @since 1.4.0
	 */
	public function set_order_date( $order_date ) {
		$this->set_date_prop( 'order_date', $order_date );
	}

	/**
	 * Set source.
	 *
	 * @param string $source Source.
	 *
	 * @since 1.4.0
	 */
	public function set_source( $source ) {
		$this->set_prop( 'source', $source );
	}

	/**
	 * Set date created.
	 *
	 * @param string $date_created Date created.
	 *
	 * @since 1.4.0
	 */
	public function set_date_created( $date_created ) {
		$this->set_date_prop( 'date_created', $date_created );
	}

	/**
	 * Sanitizes the data.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|true
	 */
	protected function sanitize_data() {
		if ( empty( $this->get_serial_key() ) ) {
			return new \WP_Error( 'invalid-serial-key', __( 'You must select a product to add serial number.', 'wc-serial-numbers' ) );
		}

		if ( empty( $this->get_product_id() ) ) {
			return new \WP_Error( 'invalid-product-id', __( 'Invalid product ID.', 'boll' ) );
		}

		if ( ! array_key_exists( $this->get_status(), self::get_statuses() ) ) {
			return new \WP_Error( 'invalid-status', __( 'Invalid status.', 'wc-serial-numbers' ) );
		}

		// if status is sold, then order id must be set.
		if ( 'sold' === $this->get_status() && empty( $this->get_order_id() ) ) {
			return new \WP_Error( 'invalid-order-id', __( 'Invalid order ID.', 'wc-serial-numbers' ) );
		}

		// is duplicate.
		if ( ! apply_filters( 'wc_serial_numbers_allow_duplicate_serial_number', false ) ) {
			$serial_key = Encryption::encrypt( $this->get_serial_key() );
			$key        = self::get( $serial_key, 'serial_key' );
			if ( ! empty( $key ) && $key->get_id() !== $this->get_id() ) {
				return new \WP_Error( 'duplicate_key', __( 'Duplicate key is not allowed', 'wc-serial-numbers' ) );
			}
		}

		return parent::sanitize_data();
	}
}
