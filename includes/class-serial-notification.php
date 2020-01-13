<?php
defined( 'ABSPATH' ) || exit();

/**
 * Class Serial_Numbers_Notification
 */
class Serial_Numbers_Notification{
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $instance = null;

	/**
	 * Serial_Numbers_Notification constructor.
	 */
	public function __construct() {
		add_action('wc_serial_numbers_daily_event', __CLASS__, 'send_stock_alert_email');
	}

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @return self Main instance.
	 * @since  1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sends low stock notification email
	 *
	 * @return bool
	 */
	public static function send_stock_alert_email(){
		$notification = wc_serial_numbers_get_settings( 'low_stock_notification', false );
		if ( ! $notification ) {
			return false;
		}

		$stock_threshold    = wc_serial_numbers_get_settings( 'low_stock_threshold', 10 );
		$to = wc_serial_numbers_get_settings( 'low_stock_notification_email', '' );
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
		wc_serial_numbers_get_views('email-notification-body.php', compact('low_stock_products'));
		$message = ob_get_contents();
		ob_get_clean();

		$message = $mailer->wrap_message( $subject, $message );
		$headers = apply_filters( 'woocommerce_email_headers', '', 'rewards_message' );
		$mailer->send( $to, $subject, $message, $headers, array() );

		exit();
	}


}

Serial_Numbers_Notification::instance();
