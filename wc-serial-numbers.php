<?php
/**
 * Plugin Name:          Serial Numbers
 * Plugin URI:           https://pluginever.com/plugins/woocommerce-serial-numbers-pro/
 * Description:          Sell and manage license keys, serial numbers, and secret keys easily within your WooCommerce store.
 * Version:              2.3.2
 * Requires at least:    5.2
 * Tested up to:         6.9
 * Requires PHP:         7.4
 * Author:               PluginEver
 * Author URI:           https://pluginever.com/
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          wc-serial-numbers
 * Domain Path:          /languages
 * WC requires at least: 3.0.0
 * WC tested up to:      10.4
 * Requires Plugins:     woocommerce
 *
 * @link                 https://pluginever.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * @author              Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @copyright           2026 ByteEver
 * @license             GPL-2.0+
 * @package             WooCommerceSerialNumbers
 */

use WooCommerceSerialNumbers\Plugin;

defined( 'ABSPATH' ) || exit;

// Autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Get the plugin instance.
 *
 * @since 1.0.0
 * @return Plugin
 */
function WCSN() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	$data = array(
		'file'             => __FILE__,
		'settings_url'     => admin_url( 'admin.php?page=wc-serial-numbers-settings' ),
		'support_url'      => 'https://pluginever.com/support/',
		'docs_url'         => 'https://pluginever.com/docs/wocommerce-serial-numbers/',
		'premium_url'      => 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/',
		'premium_basename' => 'wc-serial-numbers-pro',
		'review_url'       => 'https://wordpress.org/support/plugin/wc-serial-numbers/reviews/#new-post',
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
