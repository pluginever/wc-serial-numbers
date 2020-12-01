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

/**
 * WC_Serial_Numbers class.
 *
 * @class WC_Serial_Numbers contains everything for the plugin.
 */
class WC_Serial_Numbers {
	/**
	 * WC_Serial_Numbers version.
	 *
	 * @since 1.2.0
	 * @var string
	 */
	public $version = '1.2.7';

	/**
	 * This plugin's instance
	 *
	 * @since 1.0
	 * @var WC_Serial_Numbers The one true WC_Serial_Numbers
	 */
	private static $instance;

	/**
	 * Main WC_Serial_Numbers Instance
	 *
	 * Insures that only one instance of WC_Serial_Numbers exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since  1.0.0
	 * @static var array $instance
	 * @return WC_Serial_Numbers The one true WC_Serial_Numbers
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
	 * @since  1.2.0
	 * @access public
	 **@return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Plugin URL getter.
	 *
	 * @since 1.2.0
	 * @since 1.2.8 path param added.
	 *
	 * @param string path Relative path
	 *
	 * @return string
	 */
	public function plugin_url( $path = '' ) {
		$url = untrailingslashit( plugins_url( '/', __FILE__ ) );
		if ( $path && is_string( $path ) ) {
			$url = trailingslashit( $url );
			$url .= ltrim( $path, '/' );
		}

		return $url;
	}

	/**
	 * Plugin path getter.
	 *
	 * @since 1.2.8 path param added.
	 * @since 1.2.0
	 *
	 * @param string path relative path.
	 *
	 * @return string
	 */
	public function plugin_path( $path = '' ) {
		$plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		if ( $path && is_string( $path ) ) {
			$plugin_path = trailingslashit( $plugin_path );
			$plugin_path .= ltrim( $path, '/' );
		}

		return $plugin_path;
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @since 1.2.0
	 * @return string
	 */
	public function plugin_basename() {
		return plugin_basename( __FILE__ );
	}

	/**
	 * Get Ajax URL.
	 *
	 * @since 1.2.8
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
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
	 * Determines if the pro version active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_pro_active() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'wc-serial-numbers-pro/wc-serial-numbers-pro.php' ) == true;
	}

	/**
	 * Determines if the wc active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_wc_active() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'woocommerce/woocommerce.php' ) == true;
	}

	/**
	 * WC_Serial_Numbers constructor.
	 */
	private function __construct() {
		$this->define_constants();
		add_action( 'admin_notices', array( $this, 'wc_missing_notice' ) );
		add_action( 'plugins_loaded', array( $this, 'localization_setup' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wc_serial_numbers_scripts' ) );
		register_activation_hook( __FILE__, array( $this, 'activate_plugin' ) );
		add_action( 'woocommerce_loaded', array( $this, 'includes' ) );
	}

	/**
	 * Define all constants
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function define_constants() {
		define( 'WC_SERIAL_NUMBER_PLUGIN_VERSION', $this->version );
		define( 'WC_SERIAL_NUMBER_PLUGIN_FILE', __FILE__ );
		define( 'WC_SERIAL_NUMBER_PLUGIN_DIR', dirname( __FILE__ ) );
		define( 'WC_SERIAL_NUMBER_PLUGIN_INC_DIR', dirname( __FILE__ ) . '/includes' );
	}

	/**
	 * Activate plugin.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function activate_plugin() {
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-installer.php';
		WC_Serial_Numbers_Installer::install();
	}


	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @since 1.2.0
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-functions.php';
		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-misc-functions.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-query.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-installer.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-order-handler.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-account-handler.php';
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
	 * Initialize plugin for localization
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wc-serial-numbers', false, plugin_basename( dirname( __FILE__ ) ) . '/i18n/languages' );
	}

	/**
	 * WooCommerce plugin dependency notice
	 *
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
	 * Register frontend scripts for serial numbers
	 *
	 * @since 1.2.8
	 */
	public function wc_serial_numbers_scripts() {
		wp_enqueue_script( 'wc-serial-numbers', wc_serial_numbers()->plugin_url() . '/assets/js/wc-serial-numbers.js', array( 'jquery' ), time(), true );
	}


}


/**
 * The main function responsible for returning the one true WC Serial Numbers
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * @since 1.2.0
 * @return WC_Serial_Numbers
 */
function wc_serial_numbers() {
	return WC_Serial_Numbers::init();
}

//lets go.
wc_serial_numbers();
