<?php

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
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $min_php = '5.6';

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
	 * WCSerialNumbers constructor.
	 */
	public function __construct() {
//		register_activation_hook( __FILE__, array( $this, 'activation_check' ) );

//		add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
//		add_action( 'init', array( $this, 'localization_setup' ) );
//		add_action( 'admin_init', array( $this, 'init_update' ) );
//		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
//		add_action( 'woocommerce_init', array( $this, 'automatic_notification' ) );
//		add_filter( 'cron_schedules', array( $this, 'custom_cron_schedules' ) );
//
//		if ( $this->is_plugin_compatible() ) {
//			$this->define_constants();
//			$this->includes();
//			$this->serial_number     = new WC_Serial_Numbers_Serial_Number();
//			$this->tmp_serial_number = new WC_Serial_Numbers_TMP_Serial_Number();
//			$this->activation        = new WC_Serial_Numbers_Activation();
//			$this->encryption        = new CryptoLib();
//
//			// API
//			$this->api_url  = add_query_arg( 'wc-api', 'serial-numbers-api', home_url( '/' ) );
//			$this->elements = new Ever_Elements();
//			do_action('wc_serial_numbers_loaded');
//		}
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
	 * Determines if the plugin compatible.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	protected function is_plugin_compatible() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$this->add_notice( 'error', sprintf(
				'<strong>%s</strong> requires <strong>WooCommerce</strong> installed and active.',
				$this->plugin_name
			) );

			return false;
		}

		return true;
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wc-serial-numbers', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages/' );
	}

	/**
	 * Determines if the pro version installed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_pro_installed() {
		return is_plugin_active( 'wc-serial-numbers-pro/wc-serial-numbers-pro.php' ) == true;
	}

	/**
	 * Plugin action links
	 *
	 * @param  array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="https://www.pluginever.com/docs/woocommerce-serial-numbers/">' . __( 'Documentation', 'wc-serial-numbers' ) . '</a>';
		if ( ! $this->is_pro_installed() ) {
			$links[] = '<a href="https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=plugin_action_link&utm_medium=link&utm_campaign=wc-serial-numbers&utm_content=Upgrade%20to%20Pro" style="color: red;font-weight: bold;" target="_blank">' . __( 'Upgrade to PRO', 'wc-serial-numbers' ) . '</a>';
		}

		return $links;
	}

	public function init_update() {
		if ( class_exists( 'WCSN_Updates' ) ) {
			$updater = new WCSN_Updates();
			if ( $updater->needs_update() ) {
				$updater->perform_updates();
			}
		}
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
		define( 'WC_SERIAL_NUMBERS_TEMPLATES', WC_SERIAL_NUMBERS_PATH . '/templates' );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-cryptolib.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-install.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-install.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-updates.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/core-functions.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/scripts-functions.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-crud.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-serial-number.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-tmp-serial-number.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-activation.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-elements.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-wc-handler.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-serial-numbers-api.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/hook-functions.php' );

		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/class-wcsn-activation.php' );

		//admin
		if ( ! $this->is_pro_installed() ) {
			require_once( WC_SERIAL_NUMBERS_INCLUDES . '/admin/class-promotion.php' );
		}
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/admin/class-menu.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/admin/class-form-handler.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/admin/class-ajax.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/admin/class-insight.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/admin/class-tracker.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/admin/class-settings-api.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/admin/class-settings.php' );
		require_once( WC_SERIAL_NUMBERS_INCLUDES . '/admin/metabox-functions.php' );
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
	 * Returns the plugin loader main instance.
	 *
	 * @since 1.0.0
	 * @return \WCSerialNumbers
	 */
	public static function instance() {

		if ( null === self::$instance ) {

			self::$instance = new self();
		}

		return self::$instance;
	}

}

function wc_serial_numbers() {
	return WCSerialNumbers::instance();
}

//fire off the plugin
wc_serial_numbers();

