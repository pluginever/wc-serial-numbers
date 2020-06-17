<?php

namespace PluginEver\SerialNumbers;
defined( 'ABSPATH' ) || exit();

class CRON_Actions {
	public static function init() {
		add_action( 'wc_serial_numbers_hourly_event', array( __CLASS__, 'expire_outdated_serials' ) );
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

}

CRON_Actions::init();
