<?php

/**
 * WooCommerce Serial Numbers: Plugin main class.
 */

namespace PluginEver\WC_Serial_Numbers;

use \ByteEver\PluginFramework\v1_0_0 as Framework;

defined( 'ABSPATH' ) || exit();

class Plugin extends Framework\Plugin {
	/**
	 * Single instance of plugin.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	protected static $instance;

	/**
	 * Settings options.
	 *
	 * @since 1.0.0
	 * @var Options
	 */
	public $options;

	/**
	 * Returns the main Plugin instance.
	 *
	 * Ensures only one instance is loaded at one time.
	 *
	 * @return \ByteEver\PluginFramework\v1_0_0\Plugin
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Checks the environment on loading WordPress.
	 *
	 * Check the required environment, dependencies
	 * if not met then add admin error and return false.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_environment_compatible() {
		$ret = parent::is_environment_compatible();

		if ( $ret && ! $this->is_plugin_active( 'woocommerce' ) ) {
			$this->add_admin_notice( 'install_woocommerce', 'error', sprintf(
				'%s requires WooCommerce to function. Please %sinstall WooCommerce &raquo;%s',
				'<strong>' . $this->get_plugin_name() . '</strong>',
				'<a href="' . esc_url( admin_url( 'plugin-install.php' ) ) . '">', '</a>'
			) );
			$this->deactivate_plugin();

			return false;
		}

		return $ret;
	}

	/**
	 * Gets the main plugin file.
	 *
	 * return __FILE__;
	 *
	 * @return string the full path and filename of the plugin file
	 * @since 1.0.0
	 */
	public function get_plugin_file() {
		return WC_SERIAL_NUMBER_PLUGIN_FILE;
	}

	/**
	 * Get plugin key prefix.
	 *
	 * Is will be saved as prefix of options.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_id_key() {
		return 'wc_serial_numbers';
	}

	/**
	 * Initialize the plugin.
	 *
	 * Call plugin specific functions, setup instances, etc.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function init() {
		//$this->register_hooks();
		do_action( 'wc_serial_numbers_loaded' );
	}
}
