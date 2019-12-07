<?php
/**
 * Plugin Name: WooCommerce Serial Numbers
 * Plugin URI:  https://www.pluginever.com/plugins/wocommerce-serial-numbers-pro/
 * Description: The best WooCommerce Plugin to sell license keys, redeem cards and other secret numbers!
 * Version:     1.1.3
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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WCSerialNumbers Class.
 *
 * @class WCSerialNumbers
 */
final class WCSerialNumbers {
	/**
	 * WCSerialNumbers version.
	 *
	 * @var string
	 */
	public $version = '1.1.3';

	/**
	 * The single instance of the class.
	 *
	 * @var WCSerialNumbers
	 * @since 1.0.0
	 */
	protected static $instance = null;

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'WooCommerce Serial Numbers';


	/**
	 * Returns the plugin loader main instance.
	 *
	 * @return \WCSerialNumbers
	 * @since 1.0.0
	 */
	public static function instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'wc-serial-numbers' ), '1.0.0' );
	}

	/**
	 * Universalizing instances of this class is forbidden.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'wc-serial-numbers' ), '1.0.0' );
	}


	/**
	 * Determines if the pro version active.
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function is_pro_active() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'wc-serial-numbers-pro/wc-serial-numbers-pro.php' ) == true;
	}

	/**
	 * Determines if the wc active.
	 *
	 * @return bool
	 * @since 1.0.0
	 *
	 */
	public function is_wc_active() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'woocommerce/woocommerce.php' ) == true;
	}


	/**
	 *  WCSerialNumbers Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->define_tables();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * define plugin constants
	 *
	 * since 1.0.0
	 */
	private function define_constants() {
		define( 'WC_SERIAL_NUMBERS_VERSION', $this->version );
		define( 'WC_SERIAL_NUMBERS_FILE', __FILE__ );
		define( 'WC_SERIAL_NUMBERS_PATH', dirname( WC_SERIAL_NUMBERS_FILE ) );
		define( 'WC_SERIAL_NUMBERS_INCLUDES', WC_SERIAL_NUMBERS_PATH . '/includes' );
		define( 'WC_SERIAL_NUMBERS_URL', plugins_url( '', WC_SERIAL_NUMBERS_FILE ) );
		define( 'WC_SERIAL_NUMBERS_ASSETS_URL', WC_SERIAL_NUMBERS_URL . '/assets' );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-serial-install.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-update.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/serial-numbers-functions.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/activation-functions.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/product-functions.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/checkout-functions.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/order-functions.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/misc-functions.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/formatting-functions.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/notification-functions.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-encryption.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-ajax.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-api.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/deprecated/deprecated-functions.php' );

		if ( is_admin() ) {
			require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-form.php' );
			require_once( WC_SERIAL_NUMBERS_INCLUDES . '/admin/class-admin.php' );
		}
	}

	/**
	 * Register custom tables within $wpdb object.
	 */
	private function define_tables() {
		global $wpdb;
		$wpdb->wcsn_serials_numbers = $wpdb->prefix . 'wcsn_serial_numbers';
		$wpdb->wcsn_activations     = $wpdb->prefix . 'wcsn_activations';
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		register_activation_hook( WC_SERIAL_NUMBERS_FILE, array( 'WC_Serial_Numbers_Install', 'install' ) );
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), - 1 );
		add_action( 'init', array( $this, 'localization_setup' ) );
		add_action( 'activated_plugin', array( $this, 'activated_plugin' ) );
		add_action( 'deactivated_plugin', array( $this, 'deactivated_plugin' ) );
		add_action( 'admin_init', array( $this, 'deactivated_plugin' ) );
		add_action( 'wcsn_hourly_event', array( $this, 'check_expired_serial_numbers' ) );
	}


	/**
	 * When WP has loaded all plugins, trigger the `wc_serial_numbers_loaded` hook.
	 *
	 * This ensures `wc_serial_numbers_loaded` is called only after all other plugins
	 * are loaded, to avoid issues caused by plugin directory naming changing
	 *
	 * @since 1.0.0
	 */
	public function on_plugins_loaded() {
		do_action( 'wc_serial_numbers_loaded' );
	}


	/**
	 * Initialize plugin for localization
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wc-serial-numbers', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages/' );
	}

	/**
	 * Ran when any plugin is activated.
	 *
	 * @param string $filename The filename of the activated plugin.
	 *
	 * @since 1.0.0
	 */
	public function activated_plugin( $filename ) {

	}

	/**
	 * Ran when any plugin is deactivated.
	 *
	 * @param string $filename The filename of the deactivated plugin.
	 *
	 * @since 1.0.0
	 */
	public function deactivated_plugin( $filename ) {

	}

	/**
	 * Run Update
	 * since 1.0.0
	 */
	public function init_update() {
		if ( class_exists( 'WC_Serial_Numbers_Update' ) && current_user_can( 'manage_options' ) ) {
			$updater = new WC_Serial_Numbers_Update();
			if ( $updater->needs_update() ) {
				$updater->perform_updates();
			}
		}
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', WC_SERIAL_NUMBERS_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( WC_SERIAL_NUMBERS_FILE ) );
	}

	/**
	 * Add custom cron schedule
	 *
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public function custom_cron_schedules( $schedules ) {
		$schedules ['once_a_minute'] = array(
			'interval' => 60,
			'display'  => __( 'Once a Minute', 'wc-serial-numbers' )
		);
		return $schedules;
	}

	/**
	 * Disable all expired serial numbers
	 *
	 * since 1.0.0
	 */
	public function check_expired_serial_numbers() {
		global $wpdb;
		$wpdb->query( "update $wpdb->wcsn_serial_numbers set status='expired' where expire_date != '0000-00-00 00:00:00' AND expire_date < NOW()" );
		$wpdb->query( "update $wpdb->wcsn_serial_numbers set status='expired' where validity !='0' AND (order_date + INTERVAL validity DAY ) < NOW()" );
	}

}

function wc_serial_numbers() {
	return WCSerialNumbers::instance();
}

//fire off the plugin
wc_serial_numbers();
