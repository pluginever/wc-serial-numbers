<?php

namespace WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Useful helper functions for the plugin
 *
 * @package WooCommerceSerialNumbers
 * @since   1.0.0
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
}
