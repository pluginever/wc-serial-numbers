<?php
defined( 'ABSPATH' ) || exit();

class WCSN_Cron_Handler {

	/**
	 * Cron constructor.
	 */
	public function __construct() {
		add_action( 'wcsn_hourly_event', array( __CLASS__, 'deactivate_expired_serial_numbers' ) );
	}

	/**
	 * Delete all expired serial numbers
	 *
	 * since 1.0.0
	 */
	public static function deactivate_expired_serial_numbers() {
		global $wpdb;
		$wpdb->query( "update $wpdb->wcsn_serials_numbers set status='expired' where expire_date != '0000-00-00 00:00:00' AND expire_date < NOW()" );
		$wpdb->query( "update $wpdb->wcsn_serials_numbers set status='expired' where validity !='0' AND (order_date + INTERVAL validity DAY ) < NOW()" );
	}

}

new WCSN_Cron_Handler();
