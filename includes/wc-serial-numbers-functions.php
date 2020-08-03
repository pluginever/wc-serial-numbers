<?php
defined( 'ABSPATH' ) || exit();
/**
 * Get serial number user role.
 *
 * @return mixed|void
 * @since 1.2.0
 */
function wc_serial_numbers_get_user_role() {
	return apply_filters( 'wc_serial_numbers_user_role', 'manage_woocommerce' );
}

/**
 * Sanitize boolean
 *
 * @param $string
 *
 * @return mixed
 * @since 1.2.0
 */
function wc_serial_numbers_validate_boolean( $string ) {
	return filter_var( $string, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
}

/**
 * Check if product enabled for selling serial numbers.
 *
 * @param $product_id
 *
 * @return bool
 * @since 1.2.0
 */
function wc_serial_numbers_product_serial_enabled( $product_id ) {
	return 'yes' == get_post_meta( $product_id, '_is_serial_number', true );
}

/**
 * Get refund statuses.
 *
 * @return array|bool|mixed
 * @since 1.2.0
 */
function wc_serial_numbers_get_revoke_statuses() {
	$refund_statuses = wp_cache_get( 'wc_serial_numbers_get_revoke_statuses' );
	if ( $refund_statuses == false ) {
		$refund_statuses = [];
		if ( 'yes' == get_option( 'wc_serial_numbers_revoke_status_refunded' ) ) {
			$refund_statuses[] = 'refunded';
		}
		if ( 'yes' == get_option( 'wc_serial_numbers_revoke_status_cancelled' ) ) {
			$refund_statuses[] = 'cancelled';
		}
		if ( 'yes' == get_option( 'wc_serial_numbers_revoke_status_failed' ) ) {
			$refund_statuses[] = 'failed';
		}
	}

	return $refund_statuses;
}

/**
 * Check if software disabled.
 *
 * @return bool
 * @since 1.2.0
 */
function wc_serial_numbers_software_support_disabled() {
	return 'yes' == get_option( 'wc_serial_numbers_disable_software_support' );
}

/**
 * Check if serial number is reusing.
 *
 * @return bool
 * @since 1.2.0
 */
function wc_serial_numbers_reuse_serial_numbers() {
	return 'yes' == get_option( 'wc_serial_numbers_reuse_serial_number' );
}

/**
 * Encrypt serial number.
 *
 * @param $key
 *
 * @return false|string
 * @since 1.2.0
 */
function wc_serial_numbers_encrypt_key( $key ) {
	return WC_Serial_Numbers_Encryption::maybeEncrypt( $key );
}

/**
 * Decrypt number.
 *
 * @param $key
 *
 * @return false|string
 * @since 1.2.0
 */
function wc_serial_numbers_decrypt_key( $key ) {
	return WC_Serial_Numbers_Encryption::maybeDecrypt( $key );
}

/**
 * Get serial number's statuses.
 *
 * since 1.2.0
 * @return array
 */
function wc_serial_numbers_get_serial_number_statuses() {
	$statuses = array(
		'available' => __( 'Available', 'wc-serial-numbers' ),
		'sold'      => __( 'Sold', 'wc-serial-numbers' ),
		'refunded'  => __( 'Refunded', 'wc-serial-numbers' ),
		'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ),
		'expired'   => __( 'Expired', 'wc-serial-numbers' ),
		'failed'    => __( 'Failed', 'wc-serial-numbers' ),
		'inactive'  => __( 'Inactive', 'wc-serial-numbers' ),
	);

	return apply_filters( 'wc_serial_numbers_serial_number_statuses', $statuses );
}

/**
 * Get key sources.
 *
 * @return mixed|void
 * @since 1.2.0
 */
function wc_serial_numbers_get_key_sources() {
	$sources = array(
		'custom_source' => __( 'Manually Generated serial number', 'wc-serial-numbers' ),
	);

	return apply_filters( 'wc_serial_numbers_key_sources', $sources );
}


/**
 * Check if order contains serial numbers.
 *
 * @param $order
 *
 * @return bool|int
 * @since 1.2.0
 */
function wc_serial_numbers_order_has_serial_numbers( $order ) {
	if ( is_numeric( $order ) ) {
		$order = wc_get_order( $order );
	}

	$order_id = $order->get_id();

	// bail for no order
	if ( ! $order_id ) {
		return false;
	}

	$quantity = 0;
	$items    = $order->get_items();

	foreach ( $items as $item ) {
		$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
		if ( ! wc_serial_numbers_product_serial_enabled( $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id() ) ) {
			continue;
		}

		$line_quantity     = $item->get_quantity();
		$per_item_quantity = absint( apply_filters( 'wc_serial_numbers_per_product_delivery_qty', 1, $product_id ) );
		$needed_quantity   = $line_quantity * ( empty( $per_item_quantity ) ? 1 : absint( $per_item_quantity ) );
		$quantity          += $needed_quantity;
	}

	return $quantity;
}

/**
 * Connect serial numbers with order.
 *
 * @param $order_id
 *
 * @return bool|int
 * @since 1.2.0
 */
function wc_serial_numbers_order_connect_serial_numbers( $order_id ) {
	global $wpdb;
	$order    = wc_get_order( $order_id );
	$order_id = $order->get_id();

	// bail for no order
	if ( ! $order_id ) {
		return false;
	}
	$items = $order->get_items();

	$total_added = 0;

	foreach ( $items as $item ) {
		/* @var $item WC_Order_Item_Product */
		$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();

		$quantity = $item->get_quantity();
		if ( ! wc_serial_numbers_product_serial_enabled( $product_id ) ) {
			continue;
		}

		$per_product_delivery_qty       = absint( apply_filters( 'wc_serial_numbers_per_product_delivery_qty', 1, $product_id ) );
		$per_product_total_delivery_qty = $quantity * $per_product_delivery_qty;
		$delivered_qty                  = WC_Serial_Numbers_Query::init()->table( 'serial_numbers' )->where( 'order_id', $order_id )->where( 'product_id', $product_id )->count();

		if ( $delivered_qty >= $per_product_total_delivery_qty ) {
			continue;
		}
		$total_delivery_qty = $per_product_total_delivery_qty - $delivered_qty;
		$source             = apply_filters( 'wc_serial_numbers_product_serial_source', 'custom_source', $product_id, $total_delivery_qty );
		do_action( 'wc_serial_numbers_pre_order_item_connect_serial_numbers', $product_id, $total_delivery_qty, $source, $order_id );

		$serials = WC_Serial_Numbers_Query::init()->table( 'serial_numbers' )
		                                  ->where( 'product_id', $product_id )
		                                  ->where( 'status', 'available' )
		                                  ->where( 'source', $source )
		                                  ->limit( $total_delivery_qty )
		                                  ->column( 0 );
		foreach ( $serials as $serial_id ) {
			$updated     = $wpdb->update(
				$wpdb->prefix . 'serial_numbers',
				array(
					'order_id'   => $order_id,
					'status'     => 'sold',
					'order_date' => current_time( 'mysql' ),
				),
				array(
					'id' => $serial_id
				) );
			$total_added += $updated ? 1 : 0;
		}
	}
	do_action( 'wc_serial_numbers_order_connect_serial_numbers', $order_id, $total_added );

	return $total_added;
}

/**
 * Disconnect serial numbers from order.
 *
 * @param $order_id
 *
 * @return bool
 * @since 1.2.0
 */
function wc_serial_numbers_order_disconnect_serial_numbers( $order_id ) {
	$order    = wc_get_order( $order_id );
	$order_id = $order->get_id();

	// bail for no order
	if ( ! $order_id ) {
		return false;
	}

	if ( ! wc_serial_numbers_order_has_serial_numbers( $order ) ) {
		return false;
	}

	$reuse_serial_number = wc_serial_numbers_reuse_serial_numbers();
	$data                = array(
		'status' => $order->get_status( 'edit' ) == 'completed' ? 'cancelled' : $order->get_status( 'edit' ),
	);
	if ( $reuse_serial_number ) {
		$data['status']     = 'available';
		$data['order_id']   = '';
		$data['order_date'] = '';
	}
	if ( $reuse_serial_number ) {
		global $wpdb;
		WC_Serial_Numbers_Query::init()->table( 'serial_numbers' )->whereRaw( $wpdb->prepare( "serial_id IN (SELECT id from {$wpdb->prefix}serial_numbers WHERE order_id=%d)", $order_id ) )->delete();
	}
	do_action( 'wc_serial_numbers_pre_order_disconnect_serial_numbers', $order_id );

	$total_disconnected = WC_Serial_Numbers_Query::init()->table( 'serial_numbers' )->where( 'order_id', $order_id )->update( $data );

	do_action( 'wc_serial_numbers_order_disconnect_serial_numbers', $order_id, $total_disconnected );

	return $total_disconnected;
}


/**
 * Insert serial number.
 *
 * @param $args
 *
 * @return int|WP_Error
 * @since 1.2.0
 */
function wc_serial_numbers_insert_serial_number( $args ) {
	global $wpdb;
	$update = false;
	$order  = false;
	$args   = apply_filters( 'wc_serial_numbers_insert_serial_number_args', $args );
	$id     = ! empty( $args['id'] ) ? absint( $args['id'] ) : 0;
	if ( isset( $args['id'] ) && ! empty( trim( $args['id'] ) ) ) {
		$id          = (int) $args['id'];
		$update      = true;
		$item_before = wc_serial_numbers_get_serial_number( $id );
		if ( is_null( $item_before ) ) {
			return new \WP_Error( 'invalid_action', __( 'Could not find the item to  update', 'wc-serial-numbers' ) );
		}

		$args = array_merge( get_object_vars( $item_before ), $args );
	}

	$args              = array_map( 'trim', $args );
	$default_vendor    = get_user_by( 'email', get_option( 'admin_email' ) );
	$default_vendor_id = isset( $default_vendor->ID ) ? $default_vendor->ID : 0;
	$serial_key        = isset( $args['serial_key'] ) ? sanitize_textarea_field( $args['serial_key'] ) : '';
	$product_id        = isset( $args['product_id'] ) ? intval( $args['product_id'] ) : null;
	$activation_limit  = ! empty( $args['activation_limit'] ) ? intval( $args['activation_limit'] ) : 0;
	$order_id          = ! empty( $args['order_id'] ) ? intval( $args['order_id'] ) : 0;
	$order_date        = isset( $args['order_date'] ) && ! empty( $order_id ) ? sanitize_text_field( $args['order_date'] ) : null;
	$vendor_id         = ! empty( $args['vendor_id'] ) ? intval( $args['vendor_id'] ) : $default_vendor_id;
	$status            = empty( $args['status'] ) ? 'available' : sanitize_text_field( $args['status'] );
	$source            = empty( $args['source'] ) ? 'custom_source' : sanitize_text_field( $args['source'] );
	$validity          = ! empty( $args['validity'] ) ? intval( $args['validity'] ) : null;
	$expire_date       = isset( $args['expire_date'] ) ? sanitize_text_field( $args['expire_date'] ) : '';
	$created_date      = isset( $args['created_date'] ) ? sanitize_text_field( $args['created_date'] ) : current_time( 'mysql' );


	//is set product id?
	if ( empty( $product_id ) ) {
		return new \WP_Error( 'empty_content', __( 'You must select a product to add serial number.', 'wc-serial-numbers' ) );
	}

	//product exist?
	if ( empty( get_post( $product_id ) ) ) {
		return new \WP_Error( 'invalid_content', __( 'Invalid product selected.', 'wc-serial-numbers' ) );
	}
	//is set serial key?
	if ( empty( $serial_key ) ) {
		return new \WP_Error( 'empty_content', __( 'The Serial Key is empty. Please enter a serial key and try again', 'wc-serial-numbers' ) );
	}

	//is duplicate
	if ( ! apply_filters( 'wc_serial_numbers_allow_duplicate_serial_number', false ) ) {
		$exist_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}serial_numbers WHERE product_id=%d AND serial_key=%s", $product_id, apply_filters( 'wc_serial_numbers_maybe_encrypt', $serial_key ) ) );
		if ( ! empty( $exist_id ) && $exist_id != $id ) {
			return new \WP_Error( 'duplicate_key', __( 'Duplicate key is not allowed', 'wc-serial-numbers' ) );
		}
	}

	//updating ordered item
	if ( ! empty( $order_id ) ) {
		$order = wc_get_order( absint( $order_id ) );
		if ( empty( $order ) ) {
			return new \WP_Error( 'invalid_order_id', __( 'Associated order is not valid.', 'wc-serial-numbers' ) );
		}
	}

	if ( ! array_key_exists( $status, wc_serial_numbers_get_serial_number_statuses() ) ) {
		return new \WP_Error( 'invalid_status', __( 'Unknown serial number status.', 'wc-serial-numbers' ) );
	}

	if ( $status == 'sold' && empty( $order ) ) {
		return new \WP_Error( 'invalid_status', __( 'Sold item must have a associated valid order.', 'wc-serial-numbers' ) );
	}

	if ( $order && $status == 'sold' ) {
		$items = $order->get_items();
		//error_log( print_r( $items, true ) );
		$valid_product = false;
		foreach ( $items as $item_id => $item ) {
//			if ( $item->get_id() === $product_id ) {
//				$valid_product = true;
//				break;
//			}
			$product        = $item->get_product();
			if ( $product->get_id() === $product_id ) {
				$valid_product = true;
				break;
			}
		}

		if ( ! $valid_product ) {
			return new \WP_Error( 'invalid_status', __( 'Order does not contains the product.', 'wc-serial-numbers' ) );
		}
	}

	//serial key set
	$serial_key = apply_filters( 'wc_serial_numbers_maybe_encrypt', sanitize_textarea_field( $serial_key ), $args );

	if ( $order && ( empty( $order_date ) || $order_date == '0000-00-00 00:00:00' ) && $order->get_date_completed() ) {
		$order_date = $order->get_date_completed()->format( 'Y-m-d H:i:s' );
	} elseif ( $order && ( empty( $order_date ) || $order_date == '0000-00-00 00:00:00' ) && ! $order->get_date_completed() ) {
		$order_date = current_time( 'mysql' );
	} elseif ( $order && ( ! empty( $order_date ) || $order_date == '0000-00-00 00:00:00' ) && $order->get_date_completed() ) {
		$order_date = date( 'Y-m-d H:i:s', strtotime( $order_date ) );
	} else {
		$order_date = null;
	}

	$data  = compact( 'id', 'serial_key', 'product_id', 'activation_limit', 'order_id', 'vendor_id', 'status', 'validity', 'expire_date', 'source', 'created_date', 'order_date' );
	$where = array( 'id' => $id );
	$data  = wp_unslash( $data );
	if ( $update ) {
		do_action( 'wc_serial_numbers_pre_update_serial_number', $id, $data );
		if ( false === $wpdb->update( "{$wpdb->prefix}serial_numbers", $data, $where ) ) {
			return new \WP_Error( 'db_update_error', __( 'Could not update serial number in the database', 'wc-serial-numbers' ), $wpdb->last_error );
		}
		do_action( 'wc_serial_numbers_update_serial_number', $id, $data, $item_before );
	} else {
		do_action( 'wc_serial_numbers_pre_insert_serial_number', $id, $data );
		if ( false === $wpdb->insert( "{$wpdb->prefix}serial_numbers", $data ) ) {

			return new \WP_Error( 'db_insert_error', __( 'Could not insert serial number into the database', 'wc-serial-numbers' ), $wpdb->last_error );
		}
		$id = (int) $wpdb->insert_id;
		do_action( 'wc_serial_numbers_insert_serial_number', $id, $data );
	}

	update_post_meta( $data['product_id'], '_is_serial_number', 'yes' );

	return $id;
}

/**
 * @param $args
 *
 * @return int|WP_Error
 * @since 1.2.0
 */
function wc_serial_numbers_update_serial_number( $args ) {
	$id = isset( $args['id'] ) ? absint( $args['id'] ) : 0;
	if ( empty( $id ) ) {
		return new \WP_Error( 'no-id-found', __( 'No serial number ID found for updating', 'wc-serial-numbers' ) );
	}

	return wc_serial_numbers_insert_serial_number( $args );
}

/**
 * Update status.
 *
 * @param $id
 * @param $status
 *
 * @return int|WP_Error
 * @since 1.2.0
 */
function wc_serial_numbers_update_serial_number_status( $id, $status ) {
	return wc_serial_numbers_update_serial_number( [ 'id' => intval( $id ), 'status' => $status ] );
}


/**
 * Delete serial number.
 *
 * @param $id
 *
 * @return bool
 * @since 1.2.0
 */
function wc_serial_numbers_delete_serial_number( $id ) {
	global $wpdb;
	$id = absint( $id );

	$item = wc_serial_numbers_get_serial_number( $id );
	if ( is_null( $item ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_pre_delete_serial_number', $id, $item );
	if ( false == $wpdb->delete( "{$wpdb->prefix}serial_numbers", array( 'id' => $id ), array( '%d' ) ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_delete_serial_number', $id, $item );

	return true;
}

/**
 * @param $id
 *
 * @return mixed
 * @since 1.2.0
 */
function wc_serial_numbers_get_serial_number( $id ) {
	return WC_Serial_Numbers_Query::init()->table( 'serial_numbers' )->find( intval( $id ) );
}

/**
 * Get activation
 *
 * @param $args
 *
 * @since 1.2.0
 */
function wc_serial_numbers_get_activation( $activation_id ) {
	return WC_Serial_Numbers_Query::init()->from( 'serial_numbers_activations' )->find( intval( $activation_id ) );
}

/**
 * @param $args
 *
 * @return int|WP_Error
 * @since 1.2.0
 */
function wc_serial_numbers_insert_activation( $args ) {
	global $wpdb;
	$update = false;
	$args   = apply_filters( 'wc_serial_numbers_insert_activation_args', $args );
	$id     = ! empty( $args['id'] ) ? absint( $args['id'] ) : 0;
	if ( isset( $args['id'] ) && ! empty( trim( $args['id'] ) ) ) {
		$id          = (int) $args['id'];
		$update      = true;
		$item_before = wc_serial_numbers_get_activation( $id );
		if ( is_null( $item_before ) ) {
			return new \WP_Error( 'invalid_action', __( 'Could not find the item to update', 'wc-serial-numbers' ) );
		}
		$args = array_merge( get_object_vars( $item_before ), $args );
	}

	$data = [
		'serial_id'       => isset( $args['serial_id'] ) ? absint( $args['serial_id'] ) : '',
		'instance'        => isset( $args['instance'] ) ? sanitize_text_field( $args['instance'] ) : '',
		'active'          => isset( $args['active'] ) ? intval( $args['active'] ) : '0',
		'platform'        => isset( $args['platform'] ) ? sanitize_text_field( $args['platform'] ) : '',
		'activation_time' => isset( $args['activation_time'] ) ? sanitize_text_field( $args['activation_time'] ) : current_time( 'mysql' ),
	];

	if ( empty( $data['serial_id'] ) ) {
		return new \WP_Error( 'empty_content', __( 'Serial ID is required.', 'wc-serial-numbers' ) );
	}

	$where = array( 'id' => $id );
	$data  = wp_unslash( $data );
	if ( $update ) {
		do_action( 'wc_serial_numbers_pre_update_activation', $id, $data );
		if ( false === $wpdb->update( "{$wpdb->prefix}serial_numbers_activations", $data, $where ) ) {
			return new \WP_Error( 'db_update_error', __( 'Could not update activation in the database', 'wc-serial-numbers' ), $wpdb->last_error );
		}
		do_action( 'wc_serial_numbers_update_activation', $id, $data, $item_before );
	} else {
		do_action( 'wc_serial_numbers_pre_insert_activation', $id, $data );
		if ( false === $wpdb->insert( "{$wpdb->prefix}serial_numbers_activations", $data ) ) {

			return new \WP_Error( 'db_insert_error', __( 'Could not insert activation into the database', 'wc-serial-numbers' ), $wpdb->last_error );
		}
		$id = (int) $wpdb->insert_id;
		do_action( 'wc_serial_numbers_insert_activation', $id, $data );
	}

	return $id;
}

/**
 * @param $args
 *
 * @return int|WP_Error
 * @since 1.2.0
 */
function wc_serial_numbers_update_activation( $args ) {
	$id = isset( $args['id'] ) ? absint( $args['id'] ) : 0;
	if ( empty( $id ) ) {
		return new \WP_Error( 'no-id-found', __( 'No Activation ID found for updating', 'wc-serial-numbers' ) );
	}

	return wc_serial_numbers_insert_activation( $args );
}

/**
 * @param $id
 *
 * @return bool
 * @since 1.2.0
 */
function wc_serial_numbers_delete_activation( $id ) {
	global $wpdb;
	$id = absint( $id );

	$item = wc_serial_numbers_get_activation( $id );
	if ( is_null( $item ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_pre_delete_activation', $id, $item );
	if ( false == $wpdb->delete( "{$wpdb->prefix}serial_numbers_activations", array( 'id' => $id ), array( '%d' ) ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_delete_activation', $id, $item );

	return true;
}

function wc_serial_numbers_update_activation_count( $id ) {
	$activation = WC_Serial_Numbers_Query::init()->from( 'serial_numbers_activations' )->find( $id );
	if ( empty( $activation ) ) {
		return false;
	}
	global $wpdb;
	$activation_count = $wpdb->get_var( $wpdb->prepare( "SELECT count(id) from {$wpdb->prefix}serial_numbers_activations WHERE serial_id=%d AND active='1'", $activation->serial_id ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}serial_numbers SET activation_count = %d WHERE id=%d", intval( $activation_count ), intval( $activation->serial_id ) ) );

	return $activation_count;
}

add_action( 'wc_serial_numbers_update_activation', 'wc_serial_numbers_update_activation_count' );
add_action( 'wc_serial_numbers_delete_activation', 'wc_serial_numbers_update_activation_count' );
add_action( 'wc_serial_numbers_insert_activation', 'wc_serial_numbers_update_activation_count' );

/**
 * @param $id
 * @param int $status
 *
 * @return int|WP_Error
 * @since 1.2.0
 */
function wc_serial_numbers_update_activation_status( $id, $status = 1 ) {
	if ( empty( $id ) ) {
		return new \WP_Error( 'no-id-found', __( 'No Activation ID found for updating', 'wc-serial-numbers' ) );
	}

	return wc_serial_numbers_insert_activation( array( [ 'id' => $id, 'active' => intval( $status ) ] ) );
}

/**
 * Serial number order table get columns.
 *
 * @return mixed|void
 * @since 1.2.0
 */
function wc_serial_numbers_get_order_table_columns() {
	$columns = array(
		'product'          => __( 'Product', 'wc-serial-numbers' ),
		'serial_key'       => __( 'Serial Number', 'wc-serial-numbers' ),
		'activation_email' => __( 'Email', 'wc-serial-numbers' ),
		'activation_limit' => __( 'Activation Limit', 'wc-serial-numbers' ),
		'expire_date'      => __( 'Expires', 'wc-serial-numbers' ),
	);

	return apply_filters( 'wc_serial_numbers_order_table_columns', $columns );
}

/**
 * Get product stock
 *
 * @param $product_id
 *
 * @return int
 * @since 1.2.0
 */
function wc_serial_numbers_get_stock_quantity( $product_id ) {
	$source = get_post_meta( $product_id, '_serial_key_source', true );
	if ( 'custom_source' == get_post_meta( $product_id, '_serial_key_source', true ) || empty($source) ) {
		return WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( [
			'product_id' => $product_id,
			'status'     => 'available'
		] )->count();
	}

	return 9999;
}

/**
 * @param $value
 * @param $product
 *
 * @return int
 * @since 1.2.0
 */
function wc_serial_numbers_find_stock_quantity( $value, $product ) {
	if ( wc_serial_numbers_product_serial_enabled( $product->get_id() ) ) {
		return wc_serial_numbers_get_stock_quantity( $product->get_id() );
	}

	return $value;
}

add_filter( 'woocommerce_product_get_stock_quantity', 'wc_serial_numbers_find_stock_quantity', 10, 2 );

/**
 * Control software related columns
 *
 * @param $columns
 *
 * @return mixed
 * @since 1.2.0
 */
function wc_serial_numbers_control_order_table_columns( $columns ) {
	if ( wc_serial_numbers_software_support_disabled() ) {
		$software_columns = [ 'activation_email', 'activation_limit', 'expire_date' ];
		foreach ( $columns as $key => $label ) {
			if ( in_array( $key, $software_columns ) ) {
				unset( $columns[ $key ] );
			}
		}
	}

	return $columns;
}

add_filter( 'wc_serial_numbers_order_table_columns', 'wc_serial_numbers_control_order_table_columns', 99 );
