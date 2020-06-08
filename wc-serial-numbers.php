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

/**
 * Main WC_Serial_Numbers Class.
 *
 * @class WC_Serial_Numbers
 */
final class WC_Serial_Numbers {

	/**
	 * WC_Serial_Numbers version.
	 *
	 * @var string
	 * @since 1.1.6
	 */
	public $version = '1.1.5';

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
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WC_Serial_Numbers ) ) {
			self::$instance = new WC_Serial_Numbers();
		}

		return self::$instance;
	}

	/**
	 * Return plugin version.
	 *
	 * @return string
	 * @since 1.1.6
	 * @access public
	 **/
	public function get_version() {
		return $this->version;
	}

	/**
	 * Plugin URL getter.
	 *
	 * @return string
	 * @since 1.1.6
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Plugin path getter.
	 *
	 * @return string
	 * @since 1.1.6
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @return string
	 * @since 1.1.6
	 */
	public function plugin_basename() {
		return plugin_basename( __FILE__ );
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
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @access protected
	 * @return void
	 */

	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-serial-numbers' ), WCSN_VERSION );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access protected
	 * @return void
	 */

	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-serial-numbers' ), WCSN_VERSION );
	}

	/**
	 * WC_Serial_Numbers constructor.
	 */
	protected function __construct() {
		$this->setup_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @return void
	 */

	private function setup_constants() {
		define( 'WCSN_VERSION', $this->version );
		define( 'WCSN_MIN_PHP_VERSION', '5.6' );
		define( 'WCSN_PLUGIN_FILE', __FILE__ );
		define( 'WCSN_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		define( 'WCSN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 * @since 1.1.5
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-install.php';
		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-functions.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-encryption.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-admin-notice.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-query.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-api.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-order.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-io.php';
		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-compat.php';
		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-template-hooks.php';
		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-template-functions.php';
		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-deprecated-functions.php';

		if ( is_admin() ) {
			require_once dirname( __FILE__ ) . '/includes/admin/class-wc-serial-numbers-admin.php';
		}

		WC_Serial_Numbers_Encryption::setEncryptionKey();

	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), - 1 );
		add_action( 'plugins_loaded', array( $this, 'localization_setup' ) );
		add_action( 'admin_notice', array( $this, 'wc_required_notice' ) );
		add_filter( 'wc_serial_numbers_pre_insert_key', 'wc_serial_numbers_encrypt_key');
	}

	/**
	 * When WP has loaded all plugins, trigger the `wcsn_loaded` hook.
	 *
	 * This ensures `wcsn_loaded` is called only after all other plugins
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
	 * Returns error message and deactivates plugin when wc not installed.
	 *
	 * @since 1.0.0
	 */
	public function wc_required_notice() {
		if ( current_user_can( 'manage_options' ) && ! $this->is_wc_active() ) {
			$message = sprintf( __( '<strong>WooCommerce Serial Numbers</strong> requires <strong>WooCommerce</strong> installed and activated. Please Install %s WooCommerce. %s', 'wc-serial-numbers' ),
				'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>' );
			echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', $message );
		}
	}

	/**
	 * get settings options
	 *
	 * since 1.0.0
	 *
	 * @param $key
	 * @param string $default
	 *
	 * @return string
	 */
	public function get_settings( $key, $default = '', $section = 'wcsn_general_settings' ) {

		$option = get_option( $section, [] );

		return ! empty( $option[ $key ] ) ? $option[ $key ] : $default;
	}

	/**
	 * Returns the label of serial number.
	 *
	 * @return mixed|void
	 * @since 1.1.5
	 */
	public function get_label() {
		return apply_filters( 'WC_Serial_Numbers_Serial_Item_label', __( 'Serial Number', 'wc-serial-numbers' ) );
	}

	/**
	 * Check if licensing enabled.
	 *
	 * @return bool
	 * @since 1.5.5
	 */
	public function api_enabled() {
		return $this->get_settings( 'disable_api', false, 'wcsn_general_settings' );
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
 * @since 1.0.0
 */
function wc_serial_numbers() {
	return WC_Serial_Numbers::get_instance();
}

// Get WC Serial Numbers Running
wc_serial_numbers();
