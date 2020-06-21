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
		'custom_source' => __( 'Manually Generated serial number', 'wc-serial-numbers-pro' ),
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
		if ( ! wc_serial_numbers_product_serial_enabled( $item->get_id() ) ) {
			continue;
		}
		$quantity += 1;
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
		$product_id = $item->get_id();

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
		do_action( 'wc_serial_numbers_pre_order_connect_serial_numbers', $product_id, $total_delivery_qty, $source, $order_id );

		$serials = WC_Serial_Numbers_Query::init()->table( 'serial_numbers_activations' )
		                                  ->where( 'product_id', $product_id )
		                                  ->where( 'status', 'available' )
		                                  ->where( 'source', $source )
		                                  ->limit( $total_delivery_qty )
		                                  ->column( 0 );

		foreach ( $serials as $serial_id ) {
			$updated     = $wpdb->update(
				$wpdb->prefix . 'wc_serial_numbers',
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
		'status' => $order->get_status( 'edit' ),
	);
	if ( $reuse_serial_number ) {
		$data['status']     = 'available';
		$data['order_id']   = '';
		$data['order_date'] = '';
	}
	if ( $reuse_serial_number ) {
		global $wpdb;
		WC_Serial_Numbers_Query::init()->table( 'serial_numbers' )->whereRaw( $wpdb->prepare( "serial_id IN (SELECT id from {$wpdb->prefix}wc_serial_numbers WHERE order_id=%d)", $order_id ) )->delete();
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
	$data   = [];
	$args   = apply_filters( 'wc_serial_numbers_insert_item', $args );
	$id     = ! empty( $args['id'] ) ? absint( $args['id'] ) : 0;
	if ( isset( $args['id'] ) && ! empty( trim( $args['id'] ) ) ) {
		$id          = (int) $args['id'];
		$update      = true;
		$item_before = $this->get( $id );
		if ( is_null( $item_before ) ) {
			return new \WP_Error( 'invalid_action', __( 'Could not find the item to  update', 'wc-serial-numbers' ) );
		}

		$args = array_merge( get_object_vars( $item_before ), $args );
	}
	$args = array_map( 'trim', $args );

	//is set product id?
	if ( empty( $args['product_id'] ) ) {
		return new \WP_Error( 'empty_content', __( 'You must select a product to add serial number.', 'wc-serial-numbers' ) );
	}

	//product exist?
	if ( get_post( $args['product_id'] ) ) {
		return new \WP_Error( 'invalid_content', __( 'Invalid product selected.', 'wc-serial-numbers' ) );
	}

	//product id set
	$data['product_id'] = absint( $args['product_id'] );

	//is set serial key?
	if ( empty( $args['serial_key'] ) ) {
		return new \WP_Error( 'empty_content', __( 'The Serial Key is empty. Please enter a serial key and try again', 'wc-serial-numbers' ) );
	}

	//is duplicate
	if ( ! apply_filters( 'wc_serial_numbers_allow_duplicate_serial_number', false ) ) {
		$exist_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wc_serial_numbers WHERE product_id=%d AND serial_key=%s", $data['product_id'], apply_filters( 'serial_numbers_maybe_encrypt', $args['serial_key'] ) ) );
		if ( ! empty( $exist_id ) && $exist_id != $id ) {
			return new \WP_Error( 'duplicate_key', __( 'Duplicate key is not allowed', 'wc-serial-numbers' ) );
		}
	}

	//serial key set
	$data['serial_key'] = apply_filters( 'wc_serial_numbers_maybe_encrypt', sanitize_textarea_field( $args['serial_key'] ), $args );

	//updating ordered item
	if ( ! empty( $args['order_id'] ) ) {
		$order = wc_get_order( absint( $args['order_id'] ) );
		if ( empty( $order ) ) {
			return new \WP_Error( 'invalid_order_id', __( 'Associated order is not valid.', 'wp-serial-numbers' ) );
		}

		$data['order_id'] = absint( $args['order_id'] );
	}

	if ( empty( $args['status'] ) ) {
		$args['status'] = 'available';
	}

	if ( ! array_key_exists( $args['status'], wc_serial_numbers_get_serial_number_statuses() ) ) {
		return new \WP_Error( 'invalid_status', __( 'Unknown serial number status.', 'wp-serial-numbers' ) );
	}

	$data['status'] = $args['status'];

	if ( $data['status'] == 'sold' && empty( $order ) ) {
		return new \WP_Error( 'invalid_status', __( 'Sold item must have a associated valid order.', 'wp-serial-numbers' ) );
	}

	if ( $order && $data['status'] == 'sold' ) {
		$items         = $order->get_items();
		$valid_product = false;
		foreach ( $items as $item_id => $item ) {
			if ( $item->get_id() === $data['product_id'] ) {
				$valid_product = true;
				break;
			}
		}

		if ( ! $valid_product ) {
			return new \WP_Error( 'invalid_status', __( 'Order does not contains the product.', 'wp-serial-numbers' ) );
		}
	}

	if ( $order && ( empty( $args['order_date'] ) || $args['order_date'] == '0000-00-00 00:00:00' ) && $order->get_date_completed() ) {
		$data['order_date'] = $order->get_date_completed()->format( 'Y-m-d H:i:s' );
	} elseif ( $order && ( empty( $args['order_date'] ) || $args['order_date'] == '0000-00-00 00:00:00' ) && ! $order->get_date_completed() ) {
		$data['order_date'] = current_time( 'mysql' );
	} else {
		$data['order_date'] = null;
	}
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
 * @since 1.2.0
 * @param $id
 * @param $status
 *
 * @return int|WP_Error
 */
function wc_serial_numbers_update_serial_number_status($id, $status){
	return wc_serial_numbers_update_serial_number(['id' => intval($id), 'status' => $status ]);
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

	$item = $this->get( $id );
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


function wc_serial_numbers_insert_activation( $args ) {


}

function wc_serial_numbers_update_activation( $args ) {

}

function wc_serial_numbers_get_activation( $args ) {

}

function wc_serial_numbers_delete_activation( $args ) {

}

function wc_serial_numbers_update_activation_status($id, $status) {

}



