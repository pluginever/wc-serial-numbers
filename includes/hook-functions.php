<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Disable all expired serial numbers
 *
 * since 1.0.0
 */
function wcsn_check_expired_serial_numbers() {
	global $wpdb;
	$wpdb->query( "update {$wpdb->prefix}wcsn_serial_numbers set status='expired' where expire_date != '0000-00-00 00:00:00' AND expire_date < NOW()" );
	$wpdb->query( "update {$wpdb->prefix}wcsn_serial_numbers set status='expired' where validity !='0' AND (order_date + INTERVAL validity DAY ) < NOW()" );
}

add_action( 'wcsn_hourly_event', 'wcsn_check_expired_serial_numbers' );

/**
 * Show serial number details on order details table
 *
 * @since 1.0.0
 *
 * @param $order
 */

function wcsn_order_table_serial_number_details( $order ) {

	if ( 'completed' != $order->get_status() ) {
		return;
	}

	$serial_numbers = wcsn_get_serial_numbers( [ 'order_id' => $order->get_id() ] );

	if ( empty( $serial_numbers ) ) {
		return;
	}
	
	wc_get_template( '/html-order-details-table.php', array( 'serial_numbers' => $serial_numbers ), '', WC_SERIAL_NUMBERS_TEMPLATES );
}

add_action( 'woocommerce_order_details_after_order_table', 'wcsn_order_table_serial_number_details' );

