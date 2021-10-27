<?php
/**
 * Plugin Name: WooCommerce Serial Numbers
 * Plugin URI:  https://www.pluginever.com/plugins/wocommerce-serial-numbers-pro/
 * Description: The best WooCommerce Plugin to sell license keys, redeem cards and other secret numbers!
 * Version:     1.2.7
 * Author:      pluginever
 * Author URI:  http://pluginever.com
 * Donate link: https://pluginever.com/contact
 * License:     GPLv2+
 * Text Domain: wc-serial-numbers
 * Domain Path: /i18n/languages/
 * Tested up to: 5.5
 * WC requires at least: 3.0.0
 * WC tested up to: 4.4.1
 */

/**
 * Copyright (c) 2019 pluginever (email : support@pluginever.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// don't call the file directly
defined( 'ABSPATH' ) || exit();


if ( ! defined( 'WC_SERIAL_NUMBER_PLUGIN_FILE' ) ) {
	define( 'WC_SERIAL_NUMBER_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WC_SERIAL_NUMBER_PLUGIN_DIR' ) ) {
	define( 'WC_SERIAL_NUMBER_PLUGIN_DIR', __DIR__ );
}

// Autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main function responsible for returning the one true WC Serial Numbers
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @return \PluginEver\WC_Serial_Numbers\Plugin
 * @since 1.2.0
 */
function wc_serial_numbers() {
	return \PluginEver\WC_Serial_Numbers\Plugin::instance();
}

//lets go.
wc_serial_numbers();
