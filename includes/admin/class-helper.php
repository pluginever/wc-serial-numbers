<?php

namespace WooCommerceSerialNumbers\Admin;

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
	 * Get serial number user role.
	 * Capability to manage serial numbers.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public static function get_manager_role() {
		return apply_filters( 'wc_serial_numbers_manager_role', 'manage_woocommerce' );
	}

	/**
	 * Check if software disabled.
	 *
	 * @return bool
	 * @since 1.2.0
	 */
	public static function is_software_support_disabled() {
		return 'yes' === get_option( 'wc_serial_numbers_disable_software_support' );
	}
}
