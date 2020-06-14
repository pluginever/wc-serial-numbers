<?php


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
		add_action( 'wc_serial_numbers_hourly_event', array( $this, 'expire_outdated_serials' ) );
		add_action('wc_serial_numbers_daily_event', $this, 'send_stock_alert_email');
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
	 * Disable all expired serial numbers
	 *
	 * since 1.0.0
	 */
	public function expire_outdated_serials() {
		global $wpdb;
		$wpdb->query( "update {$wpdb->prefix}wc_serial_numbers set status='expired' where expire_date != '0000-00-00 00:00:00' AND expire_date < NOW()" );
		$wpdb->query( "update {$wpdb->prefix}wc_serial_numbers set status='expired' where validity !='0' AND (order_date + INTERVAL validity DAY ) < NOW()" );
	}


	/**
	 * Send low stock email notification.
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	public function send_stock_alert_email(){
		$notification = 'on' == $this->get_settings( 'stock_notification', 'on', 'wcsn_notification_settings' );
		if ( ! $notification ) {
			return false;
		}

		$stock_threshold    = $this->get_settings( 'stock_threshold', '5', 'wcsn_notification_settings' );
		$to = $this->get_settings( 'notification_recipient', '', 'wcsn_notification_settings' );
		if ( empty( $to ) ) {
			return false;
		}

		$low_stock_products = serial_numbers_get_low_stocked_products( $stock_threshold, true );
		if ( empty( $low_stock_products ) ) {
			return false;
		}

		$subject = __( 'Serial Numbers stock running low', 'wc-serial-numbers' );
		/** $woocommerce WooCommerce */
		global $woocommerce;
		$mailer = $woocommerce->mailer();

		ob_start();
		include dirname( __FILE__ ) . '/includes/admin/views/email-notification-body.php';
		$message = ob_get_contents();
		ob_get_clean();

		$message = $mailer->wrap_message( $subject, $message );
		$headers = apply_filters( 'woocommerce_email_headers', '', 'rewards_message' );
		$mailer->send( $to, $subject, $message, $headers, array() );

		exit();
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
	public function is_software_support_enabled() {
		return 'on' != $this->get_settings( 'disable_software_support', 'on', 'wcsn_general_settings' );
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
