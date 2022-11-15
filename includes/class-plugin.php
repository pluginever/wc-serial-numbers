<?php

namespace WooCommerceSerialNumbers;

// don't call the file directly.

defined( 'ABSPATH' ) || exit();

/**
 * Main plugin class.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers
 */
final class Plugin extends Framework\Premium_Plugin {
	/**
	 * Setup plugin constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants() {
		define( 'WC_SERIAL_NUMBERS_VERSION', $this->get_version() );
		define( 'WC_SERIAL_NUMBERS_FILE', $this->get_file() );
		define( 'WC_SERIAL_NUMBERS_PATH', $this->get_plugin_path() );
		define( 'WC_SERIAL_NUMBERS_URL', $this->get_plugin_url() );
		define( 'WC_SERIAL_NUMBERS_ASSETS', $this->get_assets_url() );
	}

	/**
	 * Setup plugin hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'add_controllers' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'custom_cron_schedules' ), 20 );
	}

	/**
	 * Initialize controllers.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_controllers() {
		$controllers = array(
			'installer' => Installer::class,
		);
		if ( self::is_request( 'admin' ) ) {
			$controllers['admin'] = Admin\Admin::class;
		}
		$this->add_controller( $controllers );
	}


	/**
	 * Add custom cron schedule
	 *
	 * @param array $schedules list of cron schedules.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function custom_cron_schedules( $schedules ) {
		$schedules ['once_a_minute'] = array(
			'interval' => 60,
			'display'  => esc_html__( 'Once a Minute', 'wc-serial-numbers' ),
		);

		return $schedules;
	}

	/**
	 * Is pro version active?
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function is_pro_active() {
		return class_exists( 'WooCommerceSerialNumbersPro\Plugin' );
	}
}
