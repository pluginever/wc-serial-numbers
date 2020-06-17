<?php

namespace PluginEver\SerialNumbers;
defined( 'ABSPATH' ) || exit();

class CRON_Actions {
	public static function init() {
		add_action( 'wc_serial_numbers_hourly_event', array( __CLASS__, 'expire_outdated_serials' ) );
		add_action( 'wc_serial_numbers_daily_event', __CLASS__, 'send_stock_alert_email' );
	}

	/**
	 * Disable all expired serial numbers
	 *
	 * since 1.0.0
	 */
	public static function expire_outdated_serials() {
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
		if ( ! wc_serial_numbers()->get_settings('stock_notification', false, true ) ) {
			return false;
		}

		$stock_threshold    = wc_serial_numbers()->get_settings( 'stock_threshold', '5');
		$to = wc_serial_numbers()->get_settings( 'notification_recipient');
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
		include dirname( __FILE__ ) . '/admin/views/email-notification-body.php';
		$message = ob_get_contents();
		ob_get_clean();

		$message = $mailer->wrap_message( $subject, $message );
		$headers = apply_filters( 'woocommerce_email_headers', '', 'rewards_message' );
		$mailer->send( $to, $subject, $message, $headers, array() );

		exit();
	}

}

CRON_Actions::init();
