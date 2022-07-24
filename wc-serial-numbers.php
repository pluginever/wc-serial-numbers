<?php
/**
 * Plugin Name: WooCommerce Serial Numbers
 * Plugin URI:  https://www.pluginever.com/plugins/wocommerce-serial-numbers-pro/
 * Description: The best WooCommerce Plugin to sell license keys, redeem cards and other secret numbers!
 * Version:     1.2.10
 * Author:      pluginever
 * Author URI:  http://pluginever.com
 * Donate link: https://pluginever.com/contact
 * License:     GPLv2+
 * Text Domain: wc-serial-numbers
 * Domain Path: /languages/
 * Tested up to: 5.9.3
 * WC requires at least: 3.0.0
 * WC tested up to: 6.5.1
 *
 * @package PluginEver\WooCommerceSerialNumbers
 * @author  pluginever
 * @link    https://pluginever.com/plugins/wc-serial-numbers/
 *
 * Copyright (c) 2019 pluginever (email : support@pluginever.com)
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

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly
defined( 'ABSPATH' ) || exit();

// Load framework.
require_once __DIR__ . '/deps/bootstrap.php';

/**
 * Main instance of the plugin.
 *
 * Returns the main instance of the plugin to prevent the need to use globals.
 *
 * @since 1.0.0
 *
 * @return Plugin
 */
function wc_serial_numbers() {
	require_once __DIR__ . '/includes/class-plugin.php';

	return Plugin::init( __FILE__ );
}

wc_serial_numbers()->setup();
