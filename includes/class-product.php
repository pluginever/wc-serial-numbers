<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Product class.
 *
 * @since   1.3.1
 * @author  pluginever
 * @link    https://pluginever.com/plugins/wc-serial-numbers/
 * @package WooCommerceSerialNumbers
 */
class Product {
	/**
	 * Product object instance.
	 *
	 * @since 1.0.0
	 * @var \WC_Product
	 */
	protected $product;

	/**
	 * Product data.
	 *
	 * @since #.#.#
	 * @var array
	 */
	public $data = array();

	/**
	 * Product constructor.
	 *
	 * @param \WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 */
	protected function __construct( $product ) {
		$this->product = $product;
		$data          = array();
		if ( $product && $product->exists() ) {
			$data['key_source']              = get_post_meta( $product->get_id(), '_wcsn_key_source', true );
			$data['generator_id']            = (int) get_post_meta( $product->get_id(), '_wcsn_generator_id', true );
			$data['valid_for_days']          = (int) get_post_meta( $product->get_id(), '_wcsn_valid_for_days', true );
			$data['activation_limit']        = (int) get_post_meta( $product->get_id(), '_wcsn_activation_limit', true );
			$data['software_version']        = get_post_meta( $product->get_id(), '_wcsn_software_version', true );
			$data['software_author']         = get_post_meta( $product->get_id(), '_wcsn_software_author', true );
			$data['software_last_updated']   = get_post_meta( $product->get_id(), '_wcsn_software_last_updated', true );
			$data['software_upgrade_notice'] = get_post_meta( $product->get_id(), '_wcsn_software_upgrade_notice', true );
			$data['keys_per_qty']            = 1;
		}

		$this->data = apply_filters( 'wc_serial_numbers_product_data', $data, $this );
	}

	/**
	 * Executes when calling any function on an instance of this class.
	 *
	 * @param string $name The name of the function being called.
	 * @param array  $arguments An array of the arguments to the function call.
	 */
	public function __call( $name, $arguments ) {
		$property = substr( $name, 4 );
		if ( 0 === strpos( $name, 'get' ) && array_key_exists( $property, $this->data ) ) {
			return $this->data[ $property ];
		}

		if ( is_callable( array( $this->product, $name ) ) ) {
			return call_user_func_array( array( $this->product, $name ), $arguments );
		}

		throw new \Exception( sprintf( 'Method %s does not exist.', $name ) );
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
	 * Get the source of serial numbers for this product.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	public function get_key_source() {
		return array_key_exists( 'key_source', $this->data ) ? $this->data['key_source'] : '';
	}

	/**
	 * Get id of the generator for this product.
	 *
	 * @since 3.1.1
	 * @return int
	 */
	public function get_generator_id() {
		return (int) array_key_exists( 'generator_id', $this->data ) ? $this->data['generator_id'] : 0;
	}

	/**
	 * Get the number of days the serial numbers will be valid for after purchase.
	 *
	 * @since 3.1.1
	 * @return int
	 */
	public function get_valid_for_days() {
		return (int) array_key_exists( 'valid_for_days', $this->data ) ? $this->data['valid_for_days'] : 0;
	}

	/**
	 * Get the software version of this product.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	public function get_software_version() {
		return array_key_exists( 'software_version', $this->data ) ? $this->data['software_version'] : '';
	}

	/**
	 * Get the author of the software
	 *
	 * @since 3.1.1
	 * @return array|mixed|string
	 */
	public function get_software_author() {
		return array_key_exists( 'software_author', $this->data ) ? $this->data['software_author'] : '';
	}

	/**
	 * Get the date of last update.
	 *
	 * @since 3.1.1
	 * @return array|mixed|string
	 */
	public function get_software_last_updated() {
		return array_key_exists( 'software_last_updated', $this->data ) ? $this->data['software_last_updated'] : '';
	}

	/**
	 * Get software upgrade notice.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	public function get_software_upgrade_notice() {
		return array_key_exists( 'software_upgrade_notice', $this->data ) ? $this->data['software_upgrade_notice'] : '';
	}

	/**
	 * Get  the number of serial numbers will be delivered per item
	 *
	 * @since 3.1.1
	 * @return int
	 */
	public function get_keys_per_qty() {
		return (int) array_key_exists( 'keys_per_qty', $this->data ) ? absint( $this->data['keys_per_qty'] ) : 1;
	}

	/**
	 * Return parent  product ID.
	 *
	 * @since #.#.#
	 * @return bool|int
	 */
	public function get_parent_id() {
		if ( is_callable( array( $this->product, 'get_parent_id' ) ) ) {
			return $this->product->is_type( 'variation' ) ? $this->product->get_parent_id() : null;
		}

		return null;
	}

	/*
	|--------------------------------------------------------------------------
	| Conditionals
	|--------------------------------------------------------------------------
	|
	| Checks if a condition is true or false.
	|
	*/
	/**
	 * Check if this product type is enabled.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	public function is_type_enabled() {
		return in_array( $this->get_type(), Helper::get_enabled_product_types(), true );
	}

	/**
	 * If serial numbers is enabled for this product.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	public function is_enabled() {
		return 'yes' === $this->get_meta( '_wcsn_is_enabled', true );
	}

	/**
	 * Checks if the product is a subscription.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	public function is_subscription() {
		$regular_types = apply_filters( 'wc_serial_numbers_subscription_product_types', [ 'subscription', 'variable-subscription' ] );

		return ! empty( $this->get_type() ) && in_array( $this->get_type(), $regular_types, true );
	}

	/*
	|--------------------------------------------------------------------------
	| Helper
	|--------------------------------------------------------------------------
	|
	| Helper methods.
	|
	*/

	/**
	 * Get keys for the product.
	 *
	 * @param int $count Number of serial numbers to receive.
	 *
	 * @since 3.1.1
	 * @return Key[] Keys of the product.
	 */
	public function get_keys( $count ) {
		if ( empty( $count ) ) {
			return [];
		}

		return $this->get_instock_keys( $count );
	}

	/**
	 * Get keys from instock.
	 *
	 * @param int $count Number of keys to generate.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public function get_instock_keys( $count ) {
		return Key::query(
			[
				'product_id__in' => $this->get_id(),
				'status__in'     => 'instock',
				'per_page'       => $count,
			]
		);
	}

	/**
	 * Get keys from generator.
	 *
	 * @param int $count Number of keys to generate.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public function generate_keys( $count ) {
		$generator = Generator::get( $this->get_generator_id() );
		if ( ! $generator ) {
			return [];
		}

		return $generator->generate( $this->get_id(), $count );
	}

	/*
	|--------------------------------------------------------------------------
	| Query methods
	|--------------------------------------------------------------------------
	|
	| Methods for querying data.
	|
	*/

	/**
	 * Get the product's instance.
	 *
	 * @param int|\WC_Product|object $product Product object.
	 *
	 * @return Product|false
	 */
	public static function get( $product ) {
		$product = is_object( $product ) ? $product : wc_get_product( $product );
		if ( ! $product ) {
			return null;
		}

		return new self( $product );
	}
}
