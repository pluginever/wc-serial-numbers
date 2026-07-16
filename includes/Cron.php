<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Cron.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class Cron {

	/**
	 * Cron constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wc_serial_numbers_hourly_event', array( __CLASS__, 'expire_outdated_serials' ) );
		add_action( 'wc_serial_numbers_daily_event', array( __CLASS__, 'send_stock_alert_email' ) );
	}

	/**
	 * Disable all expired serial numbers
	 *
	 * since 1.0.0
	 */
	public static function expire_outdated_serials() {
		global $wpdb;
		$wpdb->query( "update {$wpdb->prefix}serial_numbers set status='expired' where validity !='0' AND (order_date + INTERVAL validity DAY ) < NOW()" );
	}

	/**
	 * Send low stock email notification.
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	public static function send_stock_alert_email() {
		if ( 'yes' !== get_option( 'wc_serial_numbers_enable_stock_notification' ) ) {
			return false;
		}

		$stock_threshold = get_option( 'wc_serial_numbers_stock_threshold', 5 );
		$to              = get_option( 'wc_serial_numbers_notification_recipient', get_option( 'admin_email' ) );
		if ( empty( $to ) ) {
			return false;
		}

		$low_stock_products = wcsn_get_stocks_count( $stock_threshold );
		if ( empty( $low_stock_products ) ) {
			return false;
		}

		$subject = __( 'Serial Numbers stock running low', 'wc-serial-numbers' );
		/** $woocommerce WooCommerce */
		global $woocommerce;
		$mailer = $woocommerce->mailer();

		ob_start();
		wcsn_get_template( 'email-stock-notification.php', array( 'low_stock_products' => $low_stock_products ) );
		$message = ob_get_contents();
		ob_get_clean();

		$message = $mailer->wrap_message( $subject, $message );
		$headers = apply_filters( 'woocommerce_email_headers', '', 'wc_serial_numbers_low_stock_notification', $mailer );
		$mailer->send( $to, $subject, $message, $headers, array() );

		exit();
	}
}
