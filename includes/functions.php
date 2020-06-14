<?php
defined( 'ABSPATH' ) || exit();

use pluginever\SerialNumbers\Sanitization;
use \pluginever\SerialNumbers\Query_Serials;

/**
 * Get serial number's statuses.
 *
 * since 1.2.0
 * @return array
 */
function wc_serial_numbers_get_serial_statuses() {
	$statuses = array(
		'available' => __( 'Available', 'wc-serial-numbers' ),
		'sold'      => __( 'Sold', 'wc-serial-numbers' ),
		'refunded'  => __( 'Refunded', 'wc-serial-numbers' ),
		'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ),
		'expired'   => __( 'Expired', 'wc-serial-numbers' ),
		'failed'    => __( 'Failed', 'wc-serial-numbers' ),
		'inactive'  => __( 'Inactive', 'wc-serial-numbers' ),
	);

	return apply_filters( 'wc_serial_numbers_serial_statuses', $statuses );
}

/**
 * Get key sources.
 *
 * @since 1.2.0
 * @return mixed|void
 */
function wc_serial_numbers_get_key_sources(){
	$sources = array(
		'custom_source'  => __( 'Manually Generated serial number', 'wc-serial-numbers-pro' ),
	);

	return apply_filters('wc_serial_numbers_key_sources', $sources);
}


/**
 * Insert serial number.
 *
 * @param $args
 *
 * @return array|int|WP_Error|null
 * @since 1.2.0
 */
function wc_serial_numbers_insert_serial( $args ) {
	global $wpdb;
	$update = false;
	$id     = null;

	$args = apply_filters( 'wc_serial_numbers_insert_serial_args', $args );

	if ( isset( $args['id'] ) && ! empty( trim( $args['id'] ) ) ) {
		$id          = (int) $args['id'];
		$update      = true;
		$item_before = get_object_vars( Query_Serials::init()->find( $id ) );
		if ( is_null( $item_before ) ) {
			return new \WP_Error( 'invalid_action', __( 'Could not find the item to  update', 'wc-serial-numbers' ) );
		}

		$args = array_merge( $item_before, $args );
	}

	$args = Sanitization::sanitize_serial_args( $args );

	if ( is_wp_error( $args ) ) {
		return $args;
	}

	$default_vendor    = get_user_by( 'email', get_option( 'admin_email' ) );
	$default_vendor_id = isset( $default_vendor->ID ) ? $default_vendor->ID : null;
	$serial_key        = isset( $args['serial_key'] ) ? Sanitization::sanitize_key( $args['serial_key'] ) : '';
	$product_id        = isset( $args['product_id'] ) ? intval( $args['product_id'] ) : null;
	$activation_limit  = ! empty( $args['activation_limit'] ) ? intval( $args['activation_limit'] ) : 0;
	$order_id          = ! empty( $args['order_id'] ) ? intval( $args['order_id'] ) : null;
	$order_date        = isset( $args['order_date'] ) && ! empty( $order_id ) ? Sanitization::sanitize_date( $args['order_date'] ) : null;
	$vendor_id         = ! empty( $args['vendor_id'] ) ? intval( $args['vendor_id'] ) : $default_vendor_id;
	$status            = empty( $args['status'] ) ? 'available' : Sanitization::sanitize_status( $args['status'] );
	$source            = empty( $args['source'] ) ? 'custom_source' : sanitize_text_field( $args['source'] );
	$validity          = ! empty( $args['validity'] ) ? intval( $args['validity'] ) : null;
	$expire_date       = isset( $args['expire_date'] ) ? Sanitization::sanitize_date( $args['expire_date'] ) : '';
	$created_date      = isset( $args['created_date'] ) ? Sanitization::sanitize_date( $args['created_date'] ) : current_time( 'mysql' );

	if ( ! apply_filters( 'wc_serial_numbers_allow_duplicate_serial', false ) ) {
		$exists = Query_Serials::init()->where( 'product_id', $product_id )->where( 'serial_key', apply_filters( 'wc_serial_numbers_maybe_encrypt', $serial_key ) )->first();
		if ( ! empty( $exists ) && $exists->id != $id ) {
			return new \WP_Error( 'duplicate_key', __( 'Duplicate key is not allowed', 'wc-serial-numbers' ) );
		}
	}

	$serial_key = apply_filters( 'wc_serial_numbers_maybe_encrypt', $serial_key, $args );
	$data       = compact( 'id', 'serial_key', 'product_id', 'activation_limit', 'order_id', 'vendor_id', 'status', 'validity', 'expire_date', 'source', 'created_date', 'order_date' );
	$where      = array( 'id' => $id );
	$data       = wp_unslash( $data );

	if ( $update ) {
		do_action( 'wc_serial_numbers_pre_update_serial', $id, $data );
		if ( false === $wpdb->update( "{$wpdb->prefix}wc_serial_numbers", $data, $where ) ) {
			return new \WP_Error( 'db_update_error', __( 'Could not update serial number in the database', 'wc-serial-numbers' ), $wpdb->last_error );
		}
		do_action( 'wc_serial_numbers_update_serial', $id, $data, $item_before );
	} else {
		do_action( 'wc_serial_numbers_pre_insert_serial', $id, $data );
		if ( false === $wpdb->insert( "{$wpdb->prefix}wc_serial_numbers", $data ) ) {

			return new \WP_Error( 'db_insert_error', __( 'Could not insert serial number into the database', 'wc-serial-numbers' ), $wpdb->last_error );
		}
		$id = (int) $wpdb->insert_id;
		do_action( 'wc_serial_numbers_insert_serial', $id, $data );
	}

	update_post_meta( $data['product_id'], '_is_serial_number', 'yes' );

	return $id;
}


/**
 * Delete serial number.
 *
 * @param $id
 *
 * @return bool
 * @since 1.2.0
 */
function wc_serial_numbers_delete_serial( $id ) {
	global $wpdb;
	$id = absint( $id );

	$serial = Query_Serials::init()->find( $id );
	if ( is_null( $serial ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_pre_delete_serial', $id, $serial );
	if ( false == $wpdb->delete( "{$wpdb->prefix}wc_serial_numbers", array( 'id' => $id ), array( '%d' ) ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_delete_serial', $id, $serial );

	return true;
}

/**
 * Sanitize boolean
 * @since 1.2.0
 * @param $string
 *
 * @return mixed
 */
function wc_serial_numbers_sanitize_bool( $string ) {
	return filter_var( $string, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
}

/**
 * Get Low stock products.
 *
 * @param int $stock
 *
 * @return array
 * @since 1.0.0
 */
function serial_numbers_get_low_stock_products( $force = false, $stock = 10 ) {
	$transient = md5( 'wcsn_low_stock_products' . $stock );
	if ( $force || false == $low_stocks = get_transient( $transient ) ) {
		global $wpdb;
		$product_ids   = $wpdb->get_results( "select post_id product_id, 0 as count from $wpdb->postmeta where meta_key='_is_serial_number' AND meta_value='yes'" );
		$serial_counts = $wpdb->get_results( $wpdb->prepare( "SELECT product_id, count(id) as count FROM {$wpdb->prefix}wc_serial_numbers where status='available' AND product_id IN (select post_id from $wpdb->postmeta where meta_key='_is_serial_number' AND meta_value='yes')
																group by product_id having count < %d order by count asc", $stock ) );
		$serial_counts = wp_list_pluck( $serial_counts, 'count', 'product_id' );

		$product_ids = wp_list_pluck( $product_ids, 'count', 'product_id' );
		$low_stocks  = array_replace( $product_ids, $serial_counts );
		set_transient( $transient, $low_stocks, time() + 60 * 20 );
	}

	return $low_stocks;
}


function wc_serial_numbers_parse_template( $string, $product_id ) {

	return $string;
}
