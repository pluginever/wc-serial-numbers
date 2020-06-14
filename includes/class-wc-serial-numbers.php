<?php

namespace pluginever\SerialNumbers;
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers {

	/**
	 * WC_Serial_Numbers version.
	 *
	 * @var string
	 * @since 1.2.0
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
	 * @var bool
	 * @since 1.2.0
	 */
	protected $support_licensing = true;

	/**
	 * @var bool
	 * @since 1.2.0
	 */
	protected $allow_duplicate = false;

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
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WC_Serial_Numbers ) ) {
			self::$instance = new WC_Serial_Numbers();
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
		return untrailingslashit( plugins_url( '/', WC_SERIAL_NUMBERS_FILE ) );
	}

	/**
	 * Plugin path getter.
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( WC_SERIAL_NUMBERS_FILE ) );
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @return string
	 * @since 1.2.0
	 */
	public function plugin_basename() {
		return plugin_basename( WC_SERIAL_NUMBERS_FILE );
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
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-serial-numbers' ), WCSN_VERSION );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @access protected
	 * @return void
	 */

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wc-serial-numbers' ), WCSN_VERSION );
	}

	/**
	 * WC_Serial_Numbers constructor.
	 */
	protected function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 * @since 1.2.0
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/functions.php';
		require_once dirname( __FILE__ ) . '/class-assets.php';
		require_once dirname( __FILE__ ) . '/class-query.php';
		require_once dirname( __FILE__ ) . '/class-query-serials.php';
		require_once dirname( __FILE__ ) . '/class-query-activations.php';
		require_once dirname( __FILE__ ) . '/class-activations-query.php';
		require_once dirname( __FILE__ ) . '/class-query-products.php';
		require_once dirname( __FILE__ ) . '/class-orders-query.php';

		require_once dirname( __FILE__ ) . '/class-encryption.php';
		require_once dirname( __FILE__ ) . '/class-helper.php';
		require_once dirname( __FILE__ ) . '/class-sanitization.php';
		require_once dirname( __FILE__ ) . '/class-order.php';


//		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-functions.php';
//		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-encryption.php';
//		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-admin-notice.php';
//		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-query.php';
//		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-api.php';
//		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-order.php';
//		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-io.php';
//		require_once dirname( __FILE__ ) . '/includes/class-wc-serial-numbers-compat.php';
//		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-template-hooks.php';
//		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-template-functions.php';
//		require_once dirname( __FILE__ ) . '/includes/wc-serial-numbers-deprecated-functions.php';

		if ( is_admin() ) {
			require_once dirname( __FILE__ ) . '/class-admin.php';
		}
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), - 1 );
	}

	/**
	 * When WP has loaded all plugins, trigger the `wcsn_loaded` hook.
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
	 * @return bool
	 * @since 1.2.0
	 */
	public function support_licensing() {
		return $this->support_licensing;
	}

	/**
	 * @return bool
	 * @since 1.2.0
	 */
	public function allow_duplicate() {
		return $this->allow_duplicate;
	}

	/**
	 * Get plugin settings.
	 *
	 * @param $key
	 * @param string $default
	 *
	 * @return bool|string
	 * @since 1.2.0
	 */
	public function get_settings( $key, $default = '', $bool = false ) {
		$settings = get_option( 'serial_numbers_settings', [] );

		$value = ! empty( $settings[ $key ] ) ? $settings[ $key ] : $default;

		return $bool ? wc_serial_numbers_sanitize_bool( $value ) : $value;
	}

}
