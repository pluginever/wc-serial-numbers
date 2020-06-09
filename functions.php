<?php
/**
 * @param int $stock
 *
 * @return array
 * @since 1.0.0
 */
function serial_numbers_get_low_stocked_products( $force = false, $stock = 10 ) {
	$transient = md5( 'wcsn_low_stocked_products' . $stock );
	if ( $force || false == $low_stocks = get_transient( $transient ) ) {
		global $wpdb;
		$product_ids   = $wpdb->get_results( "select post_id product_id, 0 as count from $wpdb->postmeta where meta_key='_is_serial_number' AND meta_value='yes'" );
		$serial_counts = $wpdb->get_results( $wpdb->prepare( "SELECT product_id, count(id) as count FROM {$wpdb->prefix}wc_serial_numbers where status='available' AND product_id IN (select post_id from $wpdb->postmeta where meta_key='_is_serial_number' AND meta_value='yes')
																group by product_id having count < %d order by count asc", $stock ) );
		$serial_counts = wp_list_pluck( $serial_counts, 'count', 'product_id' );

		$product_ids   = wp_list_pluck( $product_ids, 'count', 'product_id' );
		$low_stocks    = array_replace( $product_ids, $serial_counts );
		set_transient( $transient, $low_stocks, time() + 60 * 20 );
	}

	return $low_stocks;
}
