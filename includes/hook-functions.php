<?php
defined( 'ABSPATH' ) || exit();

function wc_serial_numbers_maybe_hide_software_related_columns($columns){
	if(wc_serial_numbers_software_disabled()){
		unset($columns['activation_limit']);
		unset($columns['activation_count']);
		unset($columns['validity']);
	}
	return $columns;
}
add_filter('serial_numbers_serials_table_columns', 'wc_serial_numbers_maybe_hide_software_related_columns');

/**
 * @since 1.0.0
 * @param $order WC_Order
 */
function wc_serial_numbers_print_serial_numbers_order_details($order){
	if ( 'completed' != $order->get_status() ) {
		return;
	}

	$serial_numbers = wc_serial_numbers_get_serial_numbers([
		'order_id' => $order->get_id
	]);

	var_dump($serial_numbers);

}
add_action('woocommerce_order_details_after_order_table', 'wc_serial_numbers_print_serial_numbers_order_details');
