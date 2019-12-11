<?php
defined( 'ABSPATH' ) || exit();

/**
 * Get serial number's statuses
 * since 1.2.0
 * @return array
 */
function wc_serial_numbers_get_serial_number_statuses() {
	return array(
		'available' => __( 'Available', 'wc-serial-numbers' ),
		'inactive'  => __( 'Inactive', 'wc-serial-numbers' ),
		'active'    => __( 'Active', 'wc-serial-numbers' ),
		'refunded'  => __( 'Refunded', 'wc-serial-numbers' ),
		'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ),
		'expired'   => __( 'Expired', 'wc-serial-numbers' ),
		'failed'    => __( 'Failed', 'wc-serial-numbers' ),
	);
}

/**
 *
 * since 1.2.0
 *
 * @param $args
 *
 * @return int|WP_Error|null
 */
function wc_serial_numbers_insert_serial_number( $args ) {
	global $wpdb;
	$update = false;
	$id     = null;

	$args = (array) apply_filters( 'wc_serial_numbers_insert_serial_number', $args );
	if ( isset( $args['id'] ) && ! empty( trim( $args['id'] ) ) ) {
		$id          = (int) $args['id'];
		$update      = true;
		$item_before = (array) wc_serial_numbers_get_serial_number( $id );
		if ( is_null( $item_before ) ) {
			return new \WP_Error( 'invalid_action', __( 'Could not find the item to  update', 'wc-serial-numbers' ) );
		}

		$args = array_merge( $item_before, $args );
	}

	$statuses = wc_serial_numbers_get_serial_number_statuses();
	$default_vendor = get_user_by('email', get_option('admin_email'));
	$data = array(
		'id'               => empty( $args['id'] ) ? null : absint( $args['id'] ),
		'serial_key'       => isset( $args['serial_key'] ) ? sanitize_textarea_field( $args['serial_key'] ) : '',
		'serial_image'     => '',
		'product_id'       => isset( $args['product_id'] ) ? absint( $args['product_id'] ) : null,
		'activation_limit' => isset( $args['activation_limit'] ) ? absint( $args['activation_limit'] ) : '1',
		'order_id'         => isset( $args['order_id'] ) ? absint( $args['order_id'] ) : '',
		'customer_id'      => isset( $args['customer_id'] ) ? absint( $args['customer_id'] ) : '',
		'vendor_id'        => isset( $args['vendor_id'] ) ? absint( $args['vendor_id'] ) : $default_vendor->ID,
		'activation_email' => isset( $args['activation_email'] ) ? sanitize_email( $args['activation_email'] ) : null,
		'status'           => isset( $args['status'] ) && array_key_exists( $args['status'], $statuses ) ? sanitize_key( $args['status'] ) : 'new',
		'validity'         => isset( $args['validity'] ) ? absint( $args['validity'] ) : '',
		'expire_date'      => empty( $args['expire_date'] ) || ( '0000-00-00 00:00:00' == $args['expire_date'] ) ? null : $args['expire_date'],
		'order_date'       => empty( $args['order_date'] ) || ( '0000-00-00 00:00:00' == $args['order_date'] ) ? null : $args['order_date'],
		'created'          => date( 'Y-m-d H:i:s' ),
	);

	if ( empty( $data['product_id'] ) ) {
		return new WP_Error( 'empty_content', __( 'You must select a product to add serial number.', 'wc-serial-numbers' ) );
	}

	if ( empty( $data['serial_key'] ) ) {
		return new WP_Error( 'empty_content', __( 'The Serial Number is empty. Please enter a serial number and try again', 'wc-serial-numbers' ) );
	}

	$allow_duplicate = wc_serial_numbers_is_allowed_duplicate_serial_numbers();

	if ( ! $allow_duplicate ) {
		$exists = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->wcsn_serials_numbers WHERE serial_key=%s AND product_id=%d", $args['serial_key'], $args['product_id'] ) );
		if ( ! empty( $exists ) && $exists->id != $id ) {
			return new WP_Error( 'duplicate_key', __( 'Duplicate key is not allowed', 'wc-serial-numbers' ) );
		}
	}

	$where = array( 'id' => $id );
	$data  = wp_unslash( $data );

	if ( $update ) {
		do_action( 'wc_serial_numbers_pre_serial_number_update', $id, $data );
		if ( false === $wpdb->update( $wpdb->wcsn_serials_numbers, $data, $where ) ) {
			return new WP_Error( 'db_update_error', __( 'Could not update serial number in the database', 'wc-serial-numbers' ), $wpdb->last_error );
		}
		do_action( 'wc_serial_numbers_serial_number_update', $id, $data, $item_before );
	} else {
		do_action( 'wc_serial_numbers_pre_serial_number_insert', $id, $data );
		if ( false === $wpdb->insert( $wpdb->wcsn_serials_numbers, $data ) ) {

			return new WP_Error( 'db_insert_error', __( 'Could not insert serial number into the database', 'wc-serial-numbers' ), $wpdb->last_error );
		}
		$id = (int) $wpdb->insert_id;
		do_action( 'wc_serial_numbers_serial_number_insert', $id, $data );
	}

	update_post_meta( $data['product_id'], '_is_serial_number', 'yes' );

	return $id;
}

/**
 * Delete serial number
 *
 * since 1.2.0
 *
 * @param $id
 *
 * @return bool
 */
function wc_serial_numbers_delete_serial_number( $id ) {
	global $wpdb;
	$id = absint( $id );

	$account = wc_serial_numbers_get_serial_number( $id );
	if ( is_null( $account ) ) {
		return false;
	}

	do_action( 'wc_serial_numbers_pre_serial_number_delete', $id, $account );
	if ( false == $wpdb->delete( $wpdb->wcsn_serials_numbers, array( 'id' => $id ), array( '%d' ) ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_serial_number_delete', $id, $account );

	return true;
}

/**
 * since 1.0.0
 *
 * @param $id
 * @param string $by
 *
 * @return array|object|void|null
 */
function wc_serial_numbers_get_serial_number( $id, $by = 'id' ) {
	global $wpdb;
	switch ( $by ) {
		case 'serial_key':
			$serial_key = (string) $id;
			$sql        = $wpdb->prepare( "SELECT * FROM $wpdb->wcsn_serials_numbers WHERE serial_key=%s", $serial_key );
			break;
		case 'id':
		default:
			$id  = absint( $id );
			$sql = $wpdb->prepare( "SELECT * FROM $wpdb->wcsn_serials_numbers WHERE id=%d", $id );
			break;
	}

	return $wpdb->get_row( $sql );
}

/**
 * Query serial numbers
 *
 * since 1.0.0
 *
 * @param array $args
 * @param bool $count
 *
 * @return array|object|string|null
 */
function wc_serial_numbers_get_serial_numbers( $args = array(), $count = false ) {
	global $wpdb;
	$query_fields  = '';
	$query_from    = '';
	$query_where   = '';
	$query_orderby = '';
	$query_limit   = '';

	$default = array(
		'include'        => array(),
		'exclude'        => array(),
		'search'         => '',
		'orderby'        => 'created',
		'order'          => 'DESC',
		'fields'         => 'all',
		'search_columns' => array( 'serial_key', 'product_id', 'order_id', 'activation_email' ),
		'per_page'       => 20,
		'page'           => 1,
		'offset'         => 0,
		'expire_date'    => current_time( 'mysql' ),
	);


	$args        = wp_parse_args( $args, $default );
	$query_from  = "FROM $wpdb->wcsn_serials_numbers";
	$query_where = 'WHERE 1=1';

	if ( $args['per_page'] < 1 ) {
		$args['per_page'] = 999999999;
	}

	//id
	if ( ! empty( $args['id'] ) ) {
		$ids         = implode( ',', wp_parse_id_list( $args['id'] ) );
		$query_where .= " AND id IN( {$ids} ) ";
	}

	//order_id
	if ( ! empty( $args['order_id'] ) ) {
		$order_ids   = implode( ',', wp_parse_id_list( $args['order_id'] ) );
		$query_where .= " AND order_id IN( {$order_ids} ) ";
	}

	//product_id
	if ( ! empty( $args['product_id'] ) ) {
		$product_ids = implode( ',', wp_parse_id_list( $args['product_id'] ) );
		$query_where .= " AND product_id IN( {$product_ids} ) ";
	}

	//activation_email
	if ( ! empty( $args['activation_email'] ) ) {
		$activation_email = sanitize_email( $args['activation_email'] );
		$query_where      .= " AND `activation_email` = '{$activation_email}' ";
	}

	//serial_key
	if ( ! empty( $args['serial_key'] ) ) {
		$serial_key  = sanitize_textarea_field( $args['serial_key'] );
		$query_where .= " AND `serial_key` = '{$serial_key}' ";
	}


	//status
	if ( ! empty( $args['status'] ) ) {

		$status      = sanitize_key( $args['status'] );
		$query_where .= " AND `status` = '{$status}' ";
	}

	//fields
	if ( is_array( $args['fields'] ) ) {
		$args['fields'] = array_unique( $args['fields'] );

		$query_fields = array();
		foreach ( $args['fields'] as $field ) {
			$field          = 'id' === $field ? 'id' : sanitize_key( $field );
			$query_fields[] = "$wpdb->wcsn_serials_numbers.$field";
		}
		$query_fields = implode( ',', $query_fields );
	} elseif ( 'all' == $args['fields'] ) {
		$query_fields = "$wpdb->wcsn_serials_numbers.*";
	} else {
		$query_fields = "$wpdb->wcsn_serials_numbers.id";
	}

	//include
	$include = false;
	if ( ! empty( $args['include'] ) ) {
		$include = wp_parse_id_list( $args['include'] );
	}

	if ( ! empty( $include ) ) {
		// Sanitized earlier.
		$ids         = implode( ',', $include );
		$query_where .= " AND id IN ($ids)";
	} elseif ( ! empty( $args['exclude'] ) ) {
		$ids         = implode( ',', wp_parse_id_list( $args['exclude'] ) );
		$query_where .= " AND id NOT IN ($ids)";
	}

	//search
	$search = '';
	if ( isset( $args['search'] ) ) {
		$search = trim( $args['search'] );
	}

	if ( $search ) {
		$searches = array();
		$cols     = array_map( 'sanitize_key', $args['search_columns'] );
		foreach ( $cols as $col ) {
			$like       = '%' . $wpdb->esc_like( $col == 'serial_key' ? wc_serial_numbers_encrypt_serial_number( $search ) : $search ) . '%';
			$searches[] = $wpdb->prepare( "$col LIKE %s", $like );
		}

		$query_where .= ' AND (' . implode( ' OR ', $searches ) . ')';
	}

	//ordering
	$order         = isset( $args['order'] ) ? esc_sql( strtoupper( $args['order'] ) ) : 'ASC';
	$order_by      = esc_sql( $args['orderby'] );
	$query_orderby = sprintf( " ORDER BY %s %s ", $order_by, $order );

	// limit
	if ( isset( $args['per_page'] ) && $args['per_page'] > 0 ) {
		if ( $args['offset'] ) {
			$query_limit = $wpdb->prepare( 'LIMIT %d, %d', $args['offset'], $args['per_page'] );
		} else {
			$query_limit = $wpdb->prepare( 'LIMIT %d, %d', $args['per_page'] * ( $args['page'] - 1 ), $args['per_page'] );
		}
	}

	if ( $count ) {
		return $wpdb->get_var( "SELECT count(id) $query_from $query_where" );
	}


	$request = "SELECT $query_fields $query_from $query_where $query_orderby $query_limit";

	if ( is_array( $args['fields'] ) || 'all' == $args['fields'] ) {
		return $wpdb->get_results( $request );
	}

	return $wpdb->get_col( $request );
}

/**
 * Change status
 *
 * since 1.0.0
 *
 * @param $id
 * @param $status
 *
 * @return bool|int|WP_Error|null
 */
function wc_serial_numbers_change_serial_number_status( $id, $status ) {
	if ( empty( $id = absint( $id ) ) ) {
		return false;
	}
	if ( empty( $status ) || ! in_array( $status, array_keys( wc_serial_numbers_get_serial_number_statuses() ) ) ) {
		return false;
	}

	return wc_serial_numbers_insert_serial_number( array(
		'id'     => $id,
		'status' => $status
	) );
}

/**
 * since 1.0.0
 *
 * @param $key
 *
 * @return string|WP_Error
 */
function wc_serial_numbers_encrypt_serial_number( $key ) {
	$p_key      = wc_serial_numbers_get_encrypt_key();
	$encryption = WC_Serial_Numbers_Encryption::instance();
	try {
		return $string = $encryption->encrypt( $key, $p_key, 'kcv4tu0FSCB9oJyH' );
	} catch ( Exception $exception ) {
		return new WP_Error( 'failed_decryption', $exception->getMessage() );
	}
}

/**
 * since 1.0.0
 *
 * @param $key
 *
 * @return string|WP_Error
 */
function wc_serial_numbers_decrypt_serial_number( $key ) {

	$p_key      = wc_serial_numbers_get_encrypt_key();
	$encryption = WC_Serial_Numbers_Encryption::instance();
	try {
		return $string = $encryption->decrypt( $key, $p_key, 'kcv4tu0FSCB9oJyH' );
	} catch ( Exception $exception ) {
		return new WP_Error( 'failed_decryption', $exception->getMessage() );
	}

}
