<?php
/**
 * Plugin Name:  WooCommerce Serial Numbers
 * Description:  The best WooCommerce Plugin to sell license keys, redeem cards and other secret numbers!
 * Version:     1.3.11
 * Plugin URI:   https://pluginever.com/plugins/woocommerce-serial-numbers/
 * Author:       PluginEver
 * Author URI:   https://pluginever.com/
 * Text Domain:  wc-serial-numbers
 * Domain Path: /languages/
 * Requires PHP: 6.0
 * WC requires at least: 3.0.0
 * WC tested up to: 7.0
 *
 * @package WooCommerceSerialNumbers
 * @author  pluginever
 * Support URI:     https://pluginever.com/support/
 * Document URI:    https://pluginever.com/docs/
 * Review URI:      https://pluginever.com/plugins/wocommerce-starter-plugin/
 * Settings Path:   admin.php?page=wc-serial-numbers
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

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

// Load files.
require_once __DIR__ . '/includes/class-autoloader.php';

/**
 * Missing WooCommerce notice.
 *
 * @return void
 * @since 1.0.0
 */
function wc_serial_numbers_missing_wc_notice() {
	$notice = sprintf(
	/* translators: tags */
		__( '%1$sWooCommerce Serial Numbers%2$s is inactive. %3$sWooCommerce%4$s plugin must be active for the plugin to work. Please activate WooCommerce on the %5$splugin page%6$s once it is installed.', 'wc-serial-numbers' ),
		'<strong>',
		'</strong>',
		'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">',
		'</a>',
		'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">',
		'</a>'
	);

	echo '<div class="notice notice-error"><p>' . wp_kses_post( $notice ) . '</p></div>';
}

// Check if WooCommerce is active.
if ( ! Plugin::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	add_action( 'admin_notices', 'wc_serial_numbers_missing_wc_notice' );
	return;
}

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
	return Plugin::create( __FILE__ );
}

// Initialize the plugin.
wc_serial_numbers();
