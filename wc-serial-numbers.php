<?php
/**
 * Plugin Name: WooCommerce Serial Numbers
 * Plugin URI:  https://www.pluginever.com/plugins/wocommerce-serial-numbers-pro/
 * Description: The best WooCommerce Plugin to sell license keys, redeem cards and other secret numbers!
 * Version:     1.2.3
 * Author:      pluginever
 * Author URI:  http://pluginever.com
 * Donate link: https://pluginever.com/contact
 * License:     GPLv2+
 * Text Domain: wc-serial-numbers
 * Domain Path: /i18n/languages/
 * Tested up to: 5.4.2
 * WC requires at least: 3.0.0
 * WC tested up to: 4.3.1
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

/**
 * WC_Serial_Numbers class.
 *
 * @class WC_Serial_Numbers contains everything for the plugin.
 */
class WC_Serial_Numbers {
	/**
	 * WC_Serial_Numbers version.
	 *
	 * @var string
	 * @since 1.2.0
	 */
	public $version = '1.2.3';

	/**
	 * This plugin's instance
	 *
	 * @var WC_Serial_Numbers The one true WC_Serial_Numbers
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * Main WC_Serial_Numbers Instance
	 *
	 * Insures that only one instance of WC_Serial_Numbers exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @return WC_Serial_Numbers The one true WC_Serial_Numbers
	 * @since 1.0.0
	 * @static var array $instance
	 */
	public static function init() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WC_Serial_Numbers ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Return plugin version.
	 *
	 * @return string
	 * @since 1.2.0
	 * @access public
	 **/
	public function get_version() {
		return $this->version;
	}

	/**
	 * Plugin URL getter.
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Plugin path getter.
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public function plugin_basename() {
		return plugin_basename( __FILE__ );
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wc-serial-numbers', false, plugin_basename( __FILE__ ) . '/i18n/languages/' );
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
	 * WooCommerce plugin dependency notice
	 * @since 1.2.0
	 */
	public function wc_missing_notice() {
		if ( ! $this->is_wc_active() ) {
			$message = sprintf( __( '<strong>WooCommerce Serial Numbers</strong> requires <strong>WooCommerce</strong> installed and activated. Please Install %s WooCommerce. %s', 'wc-serial-numbers' ),
				'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>' );
			echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', $message );
		}
	}

	/**
	 * Define constant if not already defined
	 *
	 * @param string $name
	 * @param string|bool $value
	 *
	 * @return void
	 * @since 1.2.0
	 *
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @access protected
	 * @return void
	 */

	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-serial-numbers' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access protected
	 * @return void
	 */

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-serial-numbers' ), '1.0.0' );
	}

	/**
	 * WC_Serial_Numbers constructor.
	 */
	private function __construct() {
		$this->define_constants();
		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate_plugin' ) );

		add_action( 'woocommerce_loaded', array( $this, 'init_plugin' ) );
		add_action( 'admin_notices', array( $this, 'wc_missing_notice' ) );
	}

	/**
	 * Define all constants
	 * @return void
	 * @since 1.2.0
	 */
	public function define_constants() {
		$this->define( 'WC_SERIAL_NUMBER_PLUGIN_VERSION', $this->version );
		$this->define( 'WC_SERIAL_NUMBER_PLUGIN_FILE', __FILE__ );
		$this->define( 'WC_SERIAL_NUMBER_PLUGIN_DIR', dirname( __FILE__ ) );
		$this->define( 'WC_SERIAL_NUMBER_PLUGIN_INC_DIR', dirname( __FILE__ ) . '/includes' );
	}

	/**
	 * Activate plugin.
	 *
	 * @return void
	 * @since 1.2.0
	 */
	public function activate_plugin() {
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-installer.php';
		WC_Serial_Numbers_Installer::install();
	}

	/**
	 * Deactivate plugin.
	 *
	 * @return void
	 * @since 1.2.0
	 */
	public function deactivate_plugin() {

	}

	/**
	 * Load the plugin when WooCommerce loaded.
	 *
	 * @return void
	 * @since 1.2.0
	 */
	public function init_plugin() {
		$this->includes();
		$this->init_hooks();
	}


	/**
	 * Include required core files used in admin and on the frontend.
	 * @since 1.2.0
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-functions.php';
		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-misc-functions.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-query.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-installer.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-order-handler.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-encryption.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-ajax.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-api.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-cron.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-compat.php';

		if ( is_admin() ) {
			require_once dirname( __FILE__ ) . '/includes/admin/class-wc-serial-numbers-admin.php';
		}
		do_action( 'wc_serial_numbers__loaded' );
	}


	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'localization_setup' ) );
		//add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), - 1 );
	}


	/**
	 * When WP has loaded all plugins, trigger the `wc_serial_numbers__loaded` hook.
	 *
	 * This ensures `wc_serial_numbers__loaded` is called only after all other plugins
	 * are loaded, to avoid issues caused by plugin directory naming changing
	 *
	 * @since 1.0.0
	 */
	public function on_plugins_loaded() {
		do_action( 'wc_serial_numbers__loaded' );
	}

}


/**
 * The main function responsible for returning the one true WC Serial Numbers
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @return WC_Serial_Numbers
 * @since 1.2.0
 */
function wc_serial_numbers() {
	return WC_Serial_Numbers::init();
}

//lets go.
wc_serial_numbers();
