<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers
 */
final class Plugin extends Framework\Plugin {

	/**
	 * Setup plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function setup() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

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
	 * Include required core files used in admin and on the frontend.
	 *
	 * @since 1.2.0
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/deprecated/deprecated-functions.php';
	}

	/**
	 * Setup plugin hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'add_controllers' ) );
		add_action( 'wc_serial_numbers_hourly_event', array( __CLASS__, 'expire_outdated_serials' ) );
		add_action( 'wc_serial_numbers_daily_event', array( __CLASS__, 'send_stock_alert_email' ) );
	}

	/**
	 * Initialize controllers.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_controllers() {
		$controller = array(
			'installer'  => Installer::class,
			'encryption' => Encryption::class,
			'store'      => Store::class,
		);
		if ( self::is_request( 'admin' ) ) {
			$controller['admin'] = Admin\Admin::class;
		}
		$this->add_controller( $controller );
	}

	/**
	 * Disable all expired serial numbers
	 *
	 * since 1.0.0
	 */
	public static function expire_outdated_serials() {
		global $wpdb;
		$wpdb->query( "update {$wpdb->prefix}serial_numbers set status='expired' where expire_date != '0000-00-00 00:00:00' AND expire_date < NOW()" );
		$wpdb->query( "update {$wpdb->prefix}serial_numbers set status='expired' where validity !='0' AND (order_date + INTERVAL validity DAY ) < NOW()" );
	}

	/**
	 * Send low stock email notification.
	 *
	 * @return bool
	 * @since 1.2.0
	 */
	public static function send_stock_alert_email() {
		if ( ! wc_serial_numbers_validate_boolean( get_option( 'wc_serial_numbers_enable_stock_notification' ) ) ) {
			return false;
		}

		$stock_threshold = get_option( 'wc_serial_numbers_stock_threshold', 5 );
		$to              = get_option( 'wc_serial_numbers_notification_recipient', get_option( 'admin_email' ) );
		if ( empty( $to ) ) {
			return false;
		}

		$low_stock_products = wc_serial_numbers_get_low_stock_products( true, $stock_threshold );
		if ( empty( $low_stock_products ) ) {
			return false;
		}

		$subject = __( 'Serial Numbers stock running low', 'wc-serial-numbers' );
		/** $woocommerce WooCommerce */
		global $woocommerce;
		$mailer = $woocommerce->mailer();

		ob_start();
		include dirname( __FILE__ ) . '/admin/views/email-notification-body.php';
		$message = ob_get_contents();
		ob_get_clean();

		$message = $mailer->wrap_message( $subject, $message );
		$headers = apply_filters( 'woocommerce_email_headers', '', 'rewards_message', 'null' );
		$mailer->send( $to, $subject, $message, $headers, array() );

		exit();
	}

	/**
	 * Is premium version active.
	 *
	 * @since 1.2.0
	 * @retun bool
	 */
	public function is_pro_active() {
		return self::is_plugin_active( 'wc-serial-numbers-pro/wc-serial-numbers-pro.php' );
	}
}
