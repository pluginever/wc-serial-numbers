<?php
/**
 * Plugin Name: WooCommerce Serial Numbers
 * Plugin URI:  https://www.pluginever.com/plugins/wocommerce-serial-numbers-pro/
 * Description: The best WooCommerce Plugin to sell license keys, redeem cards and other secret numbers!
 * Version:     1.1.5
 * Author:      pluginever
 * Author URI:  http://pluginever.com
 * Donate link: https://pluginever.com/contact
 * License:     GPLv2+
 * Text Domain: wc-serial-numbers
 * Domain Path: /i18n/languages/
 * Tested up to: 5.3
 * WC requires at least: 3.0.0
 * WC tested up to: 3.8.0
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

if ( ! defined( 'WC_SERIAL_NUMBERS_FILE' ) ) {
	define( 'WC_SERIAL_NUMBERS_FILE', __FILE__ );
}

// Include the main WC_Serial_Numbers class.
if ( ! class_exists( 'WC_Serial_Numbers', false ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers.php';
}

/**
 * WooCommerce plugin dependency notice
 * @since 1.2.0
 */
function serial_numbers_wc_missing_notice() {
	$message = sprintf( __( '<strong>WooCommerce Serial Numbers</strong> requires <strong>WooCommerce</strong> installed and activated. Please Install %s WooCommerce. %s', 'wc-serial-numbers' ),
		'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>' );
	echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', $message );
}

/**
 * The main function responsible for returning the one true WC Serial Numbers
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @return \pluginever\SerialNumbers\WC_Serial_Numbers
 * @since 1.2.0
 */
function wc_serial_numbers() {
	return \pluginever\SerialNumbers\WC_Serial_Numbers::instance();
}

/**
 * Get WC Serial Numbers Running
 * @since 1.2.0
 */
function serial_numbers_init() {
	load_plugin_textdomain( 'wc-serial-numbers', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages/' );

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'serial_numbers_wc_missing_notice' );

		return;
	}

	wc_serial_numbers();
}

add_action( 'plugins_loaded', 'serial_numbers_init', 10 );
