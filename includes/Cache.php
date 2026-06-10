<?php

namespace WooCommerceSerialNumbers;

use WooCommerceSerialNumbers\B8\Component;
defined( 'ABSPATH' ) || exit;

/**
 * Class Cache.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class Cache extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'wc_serial_numbers_key_saved', array( $this, 'clear_order_keys_cache' ) );
		add_action( 'wc_serial_numbers_key_deleted', array( $this, 'clear_order_keys_cache' ) );
		add_action( 'wc_serial_numbers_order_remove_keys', array( $this, 'clear_order_keys_cache' ) );
		add_action( 'wc_serial_numbers_order_add_keys', array( $this, 'clear_order_keys_cache' ) );
	}

	/**
	 * Clear order keys cache.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clear_order_keys_cache() {
		delete_transient( 'wcsn_products_stock_count' );
	}
}
