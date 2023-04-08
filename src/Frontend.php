<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Frontend.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class Frontend extends Lib\Singleton {

	/**
	 * Frontend constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'wc_serial_numbers_before_display_order_keys', 'wcsn_display_order_keys_title', 10, 2 );
		add_action( 'wc_serial_numbers_display_order_keys', 'wcsn_display_order_keys_table', 10, 2 );
	}
}
