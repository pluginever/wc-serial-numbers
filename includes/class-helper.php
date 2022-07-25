<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Useful helper functions for the plugin
 *
 * @package PluginEver\WooCommerceStarterPlugin
 * @since   1.0.0
 */
class Helper {


	/**
	 * Check if product enabled for selling serial numbers.
	 *
	 * @param int $product_id Product ID
	 *
	 * since 1.3.1 function moved from function file.
	 *
	 * @return bool
	 * @since 1.2.0
	 */
	public static function product_is_enabled( $product_id ) {
		return 'yes' === get_post_meta( $product_id, '_is_serial_number', true );
	}
}
