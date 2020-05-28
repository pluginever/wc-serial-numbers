<?php
/**
 * Plugin Name: WooCommerce Serial Numbers
 * Plugin URI:  https://www.pluginever.com/plugins/wocommerce-serial-numbers-pro/
 * Description: The best WooCommerce Plugin to sell license keys, redeem cards and other secret numbers!
 * Version:     1.1.4
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
 * Main WC_Serial_Numbers Class.
 *
 * @class WC_Serial_Numbers
 */
final class WC_Serial_Numbers {

	/**
	 * WC_Serial_Numbers version.
	 *
	 * @var string
	 */
	public $version = '1.1.4';

	/**
	 * @var WC_Serial_Numbers The one true WC_Serial_Numbers
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'WooCommerce Serial Numbers';

	/**
	 * Main WC_Serial_Numbers Instance
	 *
	 * Insures that only one instance of WC_Serial_Numbers exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @return WC_Serial_Numbers The one true WC_Serial_Numbers
	 * @since 1.0
	 * @static
	 * @static var array $instance
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
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
		$this->define_tables();
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
	 * Register custom tables within $wpdb object.
	 */
	private function define_tables() {
		global $wpdb;
		$wpdb->wcsn_serials_numbers = $wpdb->prefix . 'wcsn_serial_numbers';
		$wpdb->wcsn_activations     = $wpdb->prefix . 'wcsn_activations';
	}


	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/includes/class-wcsn-install.php';
		require_once dirname( __FILE__ ) . '/includes/class-wcsn-cron-handler.php';
		require_once dirname( __FILE__ ) . '/includes/class-wcsn-serial-number.php';
		require_once dirname( __FILE__ ) . '/includes/class-wcsn-product.php';
		require_once dirname( __FILE__ ) . '/includes/class-wcsn-encryption.php';

		if(is_admin()){
			require_once dirname( __FILE__ ) . '/includes/admin/class-wcsn-admin.php';
		}
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'WCSN_Install', 'activate' ) );
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), - 1 );
		add_action( 'plugins_loaded', array( $this, 'localization_setup' ) );
		add_action( 'init', array( $this, 'init_update' ) );

		add_filter( 'cron_schedules', array( $this, 'custom_cron_schedules' ), 20 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
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
	 * Run Update
	 * since 1.0.0
	 */
	public function init_update() {
		if ( class_exists( 'WCSN_Update' ) && current_user_can( 'manage_options' ) ) {
			$updater = new WCSN_Update();
			if ( $updater->needs_update() ) {
				$updater->perform_updates();
			}
		}
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
	 * Plugin action links
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="https://www.pluginever.com/docs/woocommerce-serial-numbers/">' . __( 'Documentation', 'wc-serial-numbers' ) . '</a>';
		if ( ! $this->is_pro_active() ) {
			$links[] = '<a href="https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=plugin_action_link&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro" style="color: red;font-weight: bold;" target="_blank">' . __( 'Upgrade to PRO', 'wc-serial-numbers' ) . '</a>';
		}

		return $links;
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
	 * Returns error message and deactivates plugin when wc not installed.
	 *
	 * @since 1.0.0
	 */
	public function wc_required_notice() {
		if ( current_user_can( 'manage_options' ) ) {
			$message = sprintf( __( '<strong>WooCommerce Serial Numbers</strong> requires <strong>WooCommerce</strong> installed and activated. Please Install %s WooCommerce. %s', 'wc-serial-numbers' ),
				'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>' );
			echo sprintf( '<div class="notice notice-error"><p>%s</p></div>', $message );
		}
	}
}


if ( ! function_exists( 'wc_serial_numbers' ) ) {
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
		return WC_Serial_Numbers::instance();
	}

	// Get WC Serial Numbers Running
	wc_serial_numbers();
}
