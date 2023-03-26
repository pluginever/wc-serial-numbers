<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Cache.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class Cache  extends Lib\Singleton {

	/**
	 * Cache constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'wc_serial_numbers_order_remove_keys', array( __CLASS__, 'clear_order_keys_cache' ));
		add_action( 'wc_serial_numbers_order_add_keys', array( __CLASS__, 'clear_order_keys_cache' ));
	}

	/**
	 * Clear order keys cache.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function clear_order_keys_cache() {
		delete_transient( 'wcsn_products_stock_count' );
	}
}
