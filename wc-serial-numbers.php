<?php
/**
 * Plugin Name:          Serial Numbers
 * Plugin URI:           https://pluginever.com/plugins/woocommerce-serial-numbers-pro/
 * Description:          Sell and manage license keys, serial numbers, and secret keys easily within your WooCommerce store.
 * Version:              2.4.0
 * Requires at least:    6.4
 * Tested up to:         7.0
 * Requires PHP:         7.4
 * Author:               PluginEver
 * Author URI:           https://pluginever.com/
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          wc-serial-numbers
 * Domain Path:          /languages
 * WC requires at least: 3.0.0
 * WC tested up to:      10.7
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

use WooCommerceSerialNumbers\Installer;
use WooCommerceSerialNumbers\Plugin;

defined( 'ABSPATH' ) || exit;

// Load the Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/deprecated.php';

$data = array(
	'version'      => '2.4.0',
	'short_name'   => 'wc_serial_numbers',
	'name'         => 'Serial Numbers',
	'plugin_uri'   => 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/',
	'settings_url' => admin_url( 'admin.php?page=wc-serial-numbers-settings' ),
	'pro_basename' => 'wc-serial-numbers-pro/wc-serial-numbers-pro.php',
	'upgrade_url'  => 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/',
	'store_url'    => 'https://pluginever.com',
	'docs_url'     => 'https://pluginever.com/docs/wocommerce-serial-numbers/',
	'support_url'  => 'https://pluginever.com/support/',
	'review_url'   => 'https://wordpress.org/support/plugin/wc-serial-numbers/reviews/#new-post',
);

Plugin::create( __FILE__, $data );

/**
 * Get the main plugin instance.
 *
 * @since 1.0.0
 * @return Plugin Plugin instance.
 */
function WCSN(): Plugin { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return Plugin::instance();
}

// Register the plugin activation and deactivation hooks.
WCSN()->on_activation( array( Installer::class, 'install' ) );
WCSN()->on_deactivation( array( Installer::class, 'deactivate' ) );

// Declare WooCommerce feature compatibility.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);

// Show a notice when WooCommerce is missing.
if ( is_admin() && ! WCSN()->plugin_active( 'woocommerce' ) ) {
	WCSN()->notices->add(
		array(
			'notice_id'   => 'wc_serial_numbers_missing_dependency',
			'type'        => 'error',
			'dismissible' => false,
			'capability'  => 'activate_plugins',
			'message'     => WCSN()->plugin_path( 'includes/Admin/views/html-notice-dependency.php' ),
		)
	);
}

// Boot the plugin.
WCSN()->bootstrap();
