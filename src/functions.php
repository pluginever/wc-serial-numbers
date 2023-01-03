<?php
/**
 * Essential functions for the plugin.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get manager role.
 *
 * @since 1.4.2
 * @return string
 */
function wc_serial_numbers_get_manager_role() {
	return apply_filters( 'wc_serial_numbers_manager_role', 'manage_woocommerce' );
}
