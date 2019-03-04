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

/**
 * Auto Complete Order
 *
 * @since 1.0.0
 *
 * @param $order
 */
function wcsn_auto_complete_order( $payment_complete_status, $order_id, $order ) {
	if ( 'yes' !== wcsn_get_settings( 'wsn_auto_complete_order', '', 'wsn_delivery_settings' ) ) {
		return;
	}

	$current_status = $order->get_status();
	// We only want to update the status to 'completed' if it's coming from one of the following statuses:
	$allowed_current_statuses = array( 'on-hold', 'pending', 'failed' );
	if ( 'processing' === $payment_complete_status && in_array( $current_status, $allowed_current_statuses ) ) {
		$items = $order->get_items();
		foreach ( $items as $item_data ) {
			$product    = $item_data->get_product();
			$product_id = $product->get_id();
			$is_serial_number_enabled = get_post_meta( $product_id, '_is_serial_number', true ); //Check if the serial number enabled for this product.

			if ( 'yes' == $is_serial_number_enabled ) {
				$order->update_status( 'completed' );
			}
		}
	}


}

add_action( 'woocommerce_payment_complete_order_status', 'wcsn_auto_complete_order', 10, 3 );

