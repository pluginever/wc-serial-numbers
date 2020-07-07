<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_CRON {

	/**
	 * WC_Serial_Numbers_CRON constructor.
	 */
	public static function init() {
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
		$headers = apply_filters( 'woocommerce_email_headers', '', 'rewards_message' );
		$mailer->send( $to, $subject, $message, $headers, array() );

		exit();
	}
}

WC_Serial_Numbers_CRON::init();
