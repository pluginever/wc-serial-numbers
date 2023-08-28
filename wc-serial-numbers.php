<?php
/**
 * Plugin Name: WC Serial Numbers
 * Plugin URI:  https://www.pluginever.com/plugins/wocommerce-serial-numbers-pro/
 * Description: Sell and manage license keys/ serial numbers/ secret keys easily within your WooCommerce store.
 * Version:     1.5.9
 * Author:      PluginEver
 * Author URI:  http://pluginever.com
 * License:     GPLv2+
 * Text Domain: wc-serial-numbers
 * Domain Path: /languages
 * Tested up to: 6.2
 * WC requires at least: 5.0
 * WC tested up to: 7.8
 *
 * @package WooCommerceSerialNumbers
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

use WooCommerceSerialNumbers\Plugin;

// Don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Autoload function.
 *
 * @param string $class_name Class name.
 *
 * @since 1.0.0
 * @return void
 */
function wc_serial_numbers_autoload( $class_name ) {
	// Bail out if the class name doesn't start with our prefix.
	if ( strpos( $class_name, 'WooCommerceSerialNumbers\\' ) !== 0 ) {
		return;
	}

	// Remove the prefix from the class name.
	$class_name = substr( $class_name, strlen( 'WooCommerceSerialNumbers\\' ) );

	// Replace the namespace separator with the directory separator.
	$class_name = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );

	// Add the .php extension.
	$class_name = $class_name . '.php';

	$file_paths = array(
		__DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class_name,
		__DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $class_name,
	);

	foreach ( $file_paths as $file_path ) {
		if ( file_exists( $file_path ) ) {
			require_once $file_path;
			break;
		}
	}
}

spl_autoload_register( 'wc_serial_numbers_autoload' );

/**
 * Get the plugin instance.
 *
 * @since 1.0.0
 * @return Plugin
 */
function WCSN() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid.
	$data = array(
		'file'             => __FILE__,
		'settings_url'     => admin_url( 'admin.php?page=wc-serial-numbers-settings' ),
		'support_url'      => 'https://pluginever.com/support/',
		'docs_url'         => 'https://pluginever.com/docs/wocommerce-serial-numbers/',
		'premium_url'      => 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/',
		'premium_basename' => 'wc-serial-numbers-pro',
		'review_url'       => 'https://wordpress.org/support/plugin/wc-serial-numbers/reviews/?filter=5#new-post',
	);

	return Plugin::create( $data );
}

/**
 * Alias of WCSN().
 *
 * @since 1.5.6
 * @return Plugin
 * @deprecated 1.5.6
 */
function wc_serial_numbers() {
	return WCSN();
}

// Initialize the plugin.
WCSN();
