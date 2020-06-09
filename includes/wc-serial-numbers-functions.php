<?php
defined( 'ABSPATH' ) || exit();
/**
 * Get serial number's statuses.
 *
 * since 1.2.0
 * @return array
 */
function wc_serial_numbers_get_item_statuses() {
	$statuses = array(
		'available' => __( 'Available', 'wc-serial-numbers' ),
		'sold'      => __( 'Sold', 'wc-serial-numbers' ),
		'refunded'  => __( 'Refunded', 'wc-serial-numbers' ),
		'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ),
		'expired'   => __( 'Expired', 'wc-serial-numbers' ),
		'failed'    => __( 'Failed', 'wc-serial-numbers' ),
		'inactive'  => __( 'Inactive', 'wc-serial-numbers' ),
	);

	return apply_filters( 'wc_serial_numbers_item_statuses', $statuses );
}

/**
 * Get serial number status.
 *
 * @param $item
 * @param string $context
 *
 * @return mixed|null
 * @since 1.1.6
 */
function wc_serial_numbers_get_item_status( $item, $context = 'edit' ) {
	if ( empty( $item ) ) {
		return null;
	}

	if ( is_numeric( $item ) ) {
		$item = wc_serial_numbers_get_item( $item );
	}

	$status  = null;
	$statues = wc_serial_numbers_get_item_statuses();

	if ( ! empty( $item ) && array_key_exists( $item->status, $statues ) ) {
		$status = 'edit' === $context ? $item->status : $statues[ $item->status ];
	}

	return $status;
}

/**
 * Get the serial number.
 *
 * @param $id
 * @param string $by
 *
 * @return object|null
 * @since 1.1.5
 */
function wc_serial_numbers_get_item( $id, $by = 'id' ) {
	global $wpdb;
	switch ( $by ) {
		case 'serial_key':
			$serial_key = (string) $id;
			$sql        = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wc_serial_numbers WHERE serial_key=%s", $serial_key );
			break;
		case 'id':
		default:
			$id  = absint( $id );
			$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wc_serial_numbers WHERE id=%d", $id );
			break;
	}

	return $wpdb->get_row( $sql );
}

/**
 * Insert serial number
 *
 * @param $args
 *
 * @return int|WP_Error
 * @since 1.1.5
 */
function wc_serial_numbers_insert_item( $args ) {
	global $wpdb;
	$update = false;
	$id     = null;

	$args = apply_filters( 'wc_serial_numbers_insert_item', $args );

	if ( isset( $args['id'] ) && ! empty( trim( $args['id'] ) ) ) {
		$id          = (int) $args['id'];
		$update      = true;
		$item_before = get_object_vars( wc_serial_numbers_get_item( $id ) );
		if ( is_null( $item_before ) ) {
			return new \WP_Error( 'invalid_action', __( 'Could not find the item to  update', 'wc-serial-numbers' ) );
		}

		$args = array_merge( $item_before, $args );
	}

	$args = wc_serial_numbers_sanitize_item_fields( $args );

	if ( is_wp_error( $args ) ) {
		return $args;
	}

	$default_vendor    = get_user_by( 'email', get_option( 'admin_email' ) );
	$default_vendor_id = isset( $default_vendor->ID ) ? $default_vendor->ID : null;
	$serial_key        = isset( $args['serial_key'] ) ? sanitize_textarea_field( $args['serial_key'] ) : '';
	$product_id        = isset( $args['product_id'] ) ? intval( $args['product_id'] ) : '';
	$activation_limit  = isset( $args['activation_limit'] ) ? intval( $args['activation_limit'] ) : '';
	$order_id          = isset( $args['order_id'] ) ? intval( $args['order_id'] ) : '';
	$order_date        = isset( $args['order_date'] ) && ! empty( $order_id ) ? sanitize_text_field( $args['order_date'] ) : null;
	$vendor_id         = isset( $args['vendor_id'] ) ? intval( $args['vendor_id'] ) : $default_vendor_id;
	$status            = empty( $args['status'] ) ? 'available' : sanitize_text_field( $args['status'] );
	$validity          = isset( $args['validity'] ) ? intval( $args['validity'] ) : '';
	$expire_date       = isset( $args['expire_date'] ) ? sanitize_text_field( $args['expire_date'] ) : '';
	$created_date      = isset( $args['created_date'] ) ? sanitize_text_field( $args['created_date'] ) : current_time( 'mysql' );

	if ( ! apply_filters( 'wc_serial_numbers_allow_duplicate_item', false ) ) {
		$exists = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wc_serial_numbers WHERE `serial_key`=%s AND product_id=%d", apply_filters( 'wc_serial_numbers_pre_insert_key', $serial_key ), $product_id ) );
		if ( ! empty( $exists ) && $exists->id != $id ) {
			return new WP_Error( 'duplicate_key', __( 'Duplicate key is not allowed', 'wc-serial-numbers' ) );
		}
	}

	$serial_key = apply_filters( 'wc_serial_numbers_pre_insert_key', $serial_key, $args );

	$data = compact( 'id', 'serial_key', 'product_id', 'activation_limit', 'order_id', 'vendor_id', 'status', 'validity', 'expire_date', 'created_date', 'order_date' );

	$where = array( 'id' => $id );

	$data = wp_unslash( $data );

	if ( $update ) {
		do_action( 'wc_serial_numbers_pre_update_item', $id, $data );
		if ( false === $wpdb->update( "{$wpdb->prefix}wc_serial_numbers", $data, $where ) ) {
			return new WP_Error( 'db_update_error', __( 'Could not update serial number in the database', 'wc-serial-numbers' ), $wpdb->last_error );
		}
		do_action( 'wc_serial_numbers_update_item', $id, $data, $item_before );
	} else {
		do_action( 'wc_serial_numbers_pre_insert_item', $id, $data );
		if ( false === $wpdb->insert( "{$wpdb->prefix}wc_serial_numbers", $data ) ) {

			return new WP_Error( 'db_insert_error', __( 'Could not insert serial number into the database', 'wc-serial-numbers' ), $wpdb->last_error );
		}
		$id = (int) $wpdb->insert_id;
		do_action( 'wc_serial_numbers_insert_item', $id, $data );
	}

	update_post_meta( $data['product_id'], '_is_serial_number', 'yes' );

	return $id;
}

/**
 * Sanitize serial number fields before inserting
 *
 * @param array $args
 *
 * @return array|WP_Error
 * @since
 */
function wc_serial_numbers_sanitize_item_fields( $args ) {
	$order = false;

	if ( empty( $args['product_id'] ) ) {
		return new WP_Error( 'empty_content', __( 'You must select a product to add serial number.', 'wc-serial-numbers' ) );
	}

	if ( empty( $args['serial_key'] ) ) {
		return new WP_Error( 'empty_content', __( 'The Serial Key is empty. Please enter a serial key and try again', 'wc-serial-numbers' ) );
	}

	//updating ordered item
	if ( ! empty( $args['order_id'] ) ) {
		$order = wc_get_order( $args['order_id'] );
		if ( empty( $order ) ) {
			return new WP_Error( 'invalid_order_id', __( 'Associated order is not valid.', 'wp-serial-numbers' ) );
		}
	}

	//set status if not exist
	if ( empty( $args['status'] ) ) {
		$args['status'] = 'available';
	}

	if ( $args['status'] == 'available' && ! empty( $order ) ) {
		return new WP_Error( 'invalid_status', __( 'Item with available status could not be assigned with order.', 'wp-serial-numbers' ) );
	}

	if ( $args['status'] == 'sold' && empty( $order ) ) {
		return new WP_Error( 'invalid_status', __( 'Sold item must have a associated valid order.', 'wp-serial-numbers' ) );
	}

	if ( $order && $args['status'] == 'sold' ) {
		$items         = $order->get_items();
		$valid_product = false;
		foreach ( $items as $item_id => $item ) {
			$product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
			if ( $product_id === intval( $args['product_id'] ) ) {
				$valid_product = true;
				break;
			}
		}

		if ( ! $valid_product ) {
			return new WP_Error( 'invalid_status', __( 'Order does not contains the product.', 'wp-serial-numbers' ) );
		}
	}

	if ( $order && ( empty( $args['order_date'] ) || $args['order_date'] == '0000-00-00 00:00:00' ) && $order->get_date_completed() ) {
		$args['order_date'] = $order->get_date_completed()->format( 'Y-m-d H:i:s' );
	}

	if ( $order && ( empty( $args['order_date'] ) || $args['order_date'] == '0000-00-00 00:00:00' ) && ! $order->get_date_completed() ) {
		$args['order_date'] = current_time( 'mysql' );
	}

	if ( empty( $order ) ) {
		$args['order_id']   = null;
		$args['order_date'] = '0000-00-00 00:00:00';
	}

	$args['activation_limit'] = intval( $args['activation_limit'] );
	$args['validity']         = intval( $args['validity'] );
	$args['product_id']       = intval( $args['product_id'] );
	$args['order_id']         = intval( $args['order_id'] );


	return $args;
}

/**
 *
 * @param $id
 *
 * @return bool
 * @since 1.1.6
 */
function wc_serial_numbers_delete_item( $id ) {
	global $wpdb;
	$id = absint( $id );

	$account = wc_serial_numbers_get_item( $id );
	if ( is_null( $account ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_pre_delete_item', $id, $account );
	if ( false == $wpdb->delete( "{$wpdb->prefix}wc_serial_numbers", array( 'id' => $id ), array( '%d' ) ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_delete_item', $id, $account );

	return true;
}

/**
 * Get serial numbers.
 *
 * @param array $args
 * @param bool $count
 *
 * @return int|Object
 * @throws Exception
 * @since 1.1.6
 */
function wc_serial_numbers_get_items( $args = array(), $count = false ) {
	$default = array(
		'include'        => array(),
		'exclude'        => array(),
		'search'         => '',
		'orderby'        => 'created_date',
		'order'          => 'DESC',
		'search_columns' => array( 'serial_key', 'product_id', 'order_id' ),
		'per_page'       => 20,
		'page'           => 1,
		'offset'         => 0,
		'expire_date'    => current_time( 'mysql' ),
	);
	$args    = wp_parse_args( $args, $default );
	if ( $args['per_page'] < 1 ) {
		$args['per_page'] = 999999999;
	}
	$query = WC_Serial_Numbers_Query::init();
	$query->from( 'wc_serial_numbers' );

	//id
	if ( ! empty( $args['id'] ) ) {
		$query->whereIn( 'id', wp_parse_id_list( $args['id'] ) );
	}

	//include
	if ( ! empty( $args['include'] ) ) {
		$query->whereIn( 'id', wp_parse_id_list( $args['include'] ) );
	}

	//order_id
	if ( ! empty( $args['order_id'] ) ) {
		$query->whereIn( 'order_id', wp_parse_id_list( $args['order_id'] ) );
	}

	//product_id
	if ( ! empty( $args['product_id'] ) ) {
		$query->whereIn( 'product_id', wp_parse_id_list( $args['product_id'] ) );
	}

	//serial_key
	if ( ! empty( $args['serial_key'] ) ) {
		$key = wc_serial_numbers_encrypt_key( sanitize_textarea_field( $args['serial_key'] ) );
		$query->andWhere( 'serial_key', $key );
	}

	//status
	if ( ! empty( $args['status'] ) ) {
		$status = sanitize_key( $args['status'] );
		$query->andWhere( 'status', $status );
	}

	//search
	$search = '';
	if ( isset( $args['search'] ) ) {
		$search = trim( $args['search'] );
	}

	if ( $search ) {
		$cols = array_map( 'sanitize_key', $args['search_columns'] );
		foreach ( $cols as $col ) {
			$search = $col == 'serial_key' ? wc_serial_numbers_encrypt_key( $search ) : $search;
			$query->orWhere( $col, 'LIKE', $search );
		}
	}

	//ordering
	$order    = isset( $args['order'] ) ? esc_sql( strtoupper( $args['order'] ) ) : 'ASC';
	$order_by = esc_sql( $args['orderby'] );
	$query->order_by( $order_by, $order );

	// limit
	if ( isset( $args['per_page'] ) && $args['per_page'] > 0 ) {
		if ( $args['offset'] ) {
			$query->limit( intval( $args['offset'] ), $args['per_page'] );
		} else {
			$query->page( $args['page'], $args['per_page'] );
		}
	}

	if ( $count ) {
		return $query->count();
	}

	return $query->get();
}

/**
 * Encrypt serial number.
 *
 * @param $key
 *
 * @return false|string
 * @since 1.1.6
 */
function wc_serial_numbers_encrypt_key( $key ) {
	return WC_Serial_Numbers_Encryption::encrypt( $key );
}

/**
 * Decrypt number.
 *
 * @param $key
 *
 * @return false|string
 * @since 1.1.6
 */
function wc_serial_numbers_decrypt_key( $key ) {
	return WC_Serial_Numbers_Encryption::decrypt( $key );
}

/**
 * Get products.
 *
 * @param array $args
 * @param bool $count
 *
 * @return int|Object
 * @throws Exception
 * @since 1.1.6
 */
function wc_serial_numbers_get_products( $args = array(), $count = false ) {
	global $wpdb;
	$default = array(
		'include'          => array(),
		'exclude'          => array(),
		'search'           => '',
		'orderby'          => 'title',
		'order'            => 'DESC',
		'search_columns'   => array( 'post_title' ),
		'post_type'        => array( 'product' ),
		'post_type_not_in' => array( 'product_variation' ),
		'serial_number'    => false,
		'fields'           => 'all',
		'per_page'         => 20,
		'page'             => 1,
		'offset'           => 0,
	);
	$args    = apply_filters( 'serial_numbers_query_products_args', wp_parse_args( $args, $default ) );
	$query   = WC_Serial_Numbers_Query::init();
	$query->from( 'posts' );

	//status
	$query->where( 'post_status', 'publish' );

	//post_type
	$query->whereIn( 'post_type', $args['post_type'] );
	if ( ! empty( $args['post_type_not_in'] ) ) {
		$query->whereNotIn( 'post_type', $args['post_type_not_in'] );
	}

	//if serial_number
	if ( $args['serial_number'] ) {
		$query->whereIn( 'ID', $wpdb->prepare( "SELECT post_id from $wpdb->postmeta WHERE meta_key='_is_serial_number' AND meta_value=%", 'yes' ) );
	}

	//search
	$search = '';
	if ( isset( $args['search'] ) ) {
		$search = trim( $args['search'] );
	}

	if ( $search ) {
		$cols = array_map( 'sanitize_key', $args['search_columns'] );
		foreach ( $cols as $col ) {
			$query->orWhere( $col, 'LIKE', $search );
		}
	}

	//ordering
	$order    = isset( $args['order'] ) ? esc_sql( strtoupper( $args['order'] ) ) : 'ASC';
	$order_by = esc_sql( $args['orderby'] );
	$query->order_by( $order_by, $order );

	// limit
	if ( isset( $args['per_page'] ) && $args['per_page'] > 0 ) {
		if ( $args['offset'] ) {
			$query->limit( intval( $args['offset'] ), $args['per_page'] );
		} else {
			$query->page( $args['page'], $args['per_page'] );
		}
	}

	if ( $count ) {
		return $query->count();
	}

	return $query->get();
}

/**
 * Get total ordered serial numbers quantity.
 *
 * @param $order_id
 *  array[ 'product_id' => 'quantity' ]
 *
 * @return array
 * @since 1.1.6
 */
function wc_serial_numbers_get_ordered_items_quantity( $order_id ) {
	$order = wc_get_order( $order_id );
	$items = $order->get_items();
	$keys  = [];

	foreach ( $items as $item ) {
		$product_id = $item->get_product_id();
		$quantity   = $item->get_quantity();
		if ( 'yes' != get_post_meta( $product_id, '_is_serial_number', true ) ) {
			continue;
		}
		$delivery_quantity = (int) get_post_meta( $product_id, '_delivery_quantity', true );
		$delivery_quantity = empty( $delivery_quantity ) ? 1 : absint( $delivery_quantity );
		$needed_quantity   = $quantity * $delivery_quantity;
		if ( $needed_quantity ) {
			$keys[ $product_id ] = $needed_quantity;
		}
	}

	return $keys;
}

/***
 * Assign ordered serial numbers.
 *
 * @param $order_id
 *
 * @return bool
 * @since 1.5.5
 */
function wc_serial_numbers_order_add_items( $order_id ) {
	$ordered_items = wc_serial_numbers_get_ordered_items_quantity( $order_id );
	if ( empty( $ordered_items ) ) {
		return false;
	}

	$total_added = 0;
	foreach ( $ordered_items as $product_id => $quantity ) {
		$already_assigned = (int) wc_serial_numbers_get_items( [
			'order_id'   => $order_id,
			'product_id' => $product_id,
			'per_page'   => '-1'
		], true );


		$to_assign = $quantity - $already_assigned;
		if ( empty( $to_assign ) ) {
			//already assigned all keys
			continue;
		}
		$keys = wc_serial_numbers_get_items( [
			'product_id' => $product_id,
			'status'     => 'available',
			'per_page'   => $to_assign
		] );

		foreach ( $keys as $key ) {
			$data = [
				'id'         => $key->id,
				'order_id'   => $order_id,
				'status'     => 'sold',
				'order_date' => current_time( 'mysql' ),
			];

			if ( is_numeric( wc_serial_numbers_insert_item( $data ) ) ) {
				$total_added += 1;
			};
		}
	}

	return $total_added;
}

/**
 * unassign serial numbers from order.
 *
 * @param $order_id
 *
 * @since 1.5.5
 */
function wc_serial_numbers_order_remove_items( $order_id ) {
	$order    = wc_get_order( $order_id );
	$order_id = $order->get_id();

	// bail for no order
	if ( ! $order_id ) {
		return false;
	}

	$reuse          = 'on' == wc_serial_numbers()->get_settings( 'reuse_serial_number', '', 'wcsn_general_settings' );
	$serial_numbers = wc_serial_numbers_get_items( [
		'order_id' => $order_id,
		'per_page' => - 1,
	] );

	$total_removed = 0;
	foreach ( $serial_numbers as $serial_number ) {
		$data = array(
			'id'     => $serial_number->id,
			'status' => $order->get_status( 'edit' ),
		);
		if ( $reuse ) {
			$data['status']     = 'available';
			$data['order_id']   = '';
			$data['order_date'] = '';
		}
		if ( is_numeric( wc_serial_numbers_insert_item( $data ) ) ) {
			$total_removed += 1;
		};
	}

	return $total_removed;
}


/**
 * @param $id
 * @param string $by
 *
 * @return array|object|void|null
 * @since 1.1.6
 */
function wc_serial_numbers_get_activation( $id, $by = 'id' ) {
	global $wpdb;
	switch ( $by ) {
		case 'serial_id':
			$serial_id = absint( $id );
			$sql       = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wc_serial_numbers_activations WHERE serial_id=%s", $serial_id );
			break;
		case 'id':
		default:
			$id  = absint( $id );
			$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wc_serial_numbers_activations WHERE id=%d", $id );
			break;
	}

	return $wpdb->get_row( $sql );
}

/**
 * Get activations
 *
 * @param array $args
 * @param bool $count
 *
 * @return array|object|string|null
 * @since 1.0.0
 */
function wc_serial_numbers_get_activations( $args = array(), $count = false ) {
	global $wpdb;
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
		'serial_id'      => '',
		'instance'       => '',
		'status'         => '',
		'platform'       => '',
		'orderby'        => 'activation_time',
		'order'          => 'DESC',
		'fields'         => 'all',
		'search_columns' => array( 'platform', 'instance', 'serial_id', ),
		'per_page'       => 20,
		'page'           => 1,
		'offset'         => 0,
	);

	$args        = wp_parse_args( $args, $default );
	$query_from  = "FROM {$wpdb->prefix}wc_serial_numbers_activations LEFT JOIN {$wpdb->prefix}wc_serial_numbers ON {$wpdb->prefix}wc_serial_numbers_activations.serial_id={$wpdb->prefix}wc_serial_numbers.id ";
	$query_where = 'WHERE 1=1';

	if ( $args['per_page'] < 1 ) {
		$args['per_page'] = 999999999;
	}

	$base_fields = array(
		'serial_id',
		'instance',
		'active',
		'platform',
		'activation_time',
	);

	//id
	if ( ! empty( $args['id'] ) ) {
		$ids         = implode( ',', wp_parse_id_list( $args['id'] ) );
		$query_where .= " AND {$wpdb->prefix}wc_serial_numbers_activations.id IN( {$ids} ) ";
	}

	//order_id
	if ( ! empty( $args['order_id'] ) ) {
		$order_ids   = implode( ',', wp_parse_id_list( $args['order_id'] ) );
		$query_where .= " AND {$wpdb->prefix}wc_serial_numbers.order_id IN( {$order_ids} ) ";
	}

	//product_id
	if ( ! empty( $args['product_id'] ) ) {
		$product_ids = implode( ',', wp_parse_id_list( $args['product_id'] ) );
		$query_where .= " AND {$wpdb->prefix}wc_serial_numbers.product_id IN( {$product_ids} ) ";
	}

	//serial_id
	if ( ! empty( $args['serial_id'] ) ) {
		$serial_ids  = implode( ',', wp_parse_id_list( $args['serial_id'] ) );
		$query_where .= " AND {$wpdb->prefix}wc_serial_numbers_activations.serial_id IN( {$serial_ids} ) ";
	}

	//instance
	if ( ! empty( $args['instance'] ) ) {
		$instance    = sanitize_text_field( $args['instance'] );
		$query_where .= " AND {$wpdb->prefix}wc_serial_numbers_activations.instance = '{$instance}' ";
	}

	//platform
	if ( ! empty( $args['platform'] ) ) {
		$platform    = sanitize_text_field( $args['platform'] );
		$query_where .= " AND {$wpdb->prefix}wc_serial_numbers_activations.platform = '{$platform}' ";
	}

	//status
	if ( ! empty( $args['status'] ) ) {
		$status      = $args['status'] == 'active' ? '1' : '0';
		$query_where .= " AND {$wpdb->prefix}wc_serial_numbers_activations.active = '{$status}' ";
	}

	//include
	$include = false;
	if ( ! empty( $args['include'] ) ) {
		$include = wp_parse_id_list( $args['include'] );
	}

	if ( ! empty( $include ) ) {
		// Sanitized earlier.
		$ids         = implode( ',', $include );
		$query_where .= " AND {$wpdb->prefix}wc_serial_numbers_activations.id IN ($ids)";
	} elseif ( ! empty( $args['exclude'] ) ) {
		$ids         = implode( ',', wp_parse_id_list( $args['exclude'] ) );
		$query_where .= " AND {$wpdb->prefix}wc_serial_numbers_activations.id NOT IN ($ids)";
	}

	//search
	$search = '';
	if ( isset( $args['search'] ) ) {
		$search = trim( $args['search'] );
	}

	if ( $search ) {
		$searches = array();
		$cols     = array_map( 'sanitize_key', $args['search_columns'] );
		$like     = '%' . $wpdb->esc_like( $search ) . '%';
		foreach ( $cols as $col ) {
			$searches[] = $wpdb->prepare( "{{$wpdb->prefix}wc_serial_numbers_activations}.{$col} LIKE %s", $like );
		}

		$query_where .= ' AND (' . implode( ' OR ', $searches ) . ')';
	}

	// limit
	if ( isset( $args['per_page'] ) && $args['per_page'] > 0 ) {
		if ( $args['offset'] ) {
			$query_limit = $wpdb->prepare( 'LIMIT %d, %d', $args['offset'], $args['per_page'] );
		} else {
			$query_limit = $wpdb->prepare( 'LIMIT %d, %d', $args['per_page'] * ( $args['page'] - 1 ), $args['per_page'] );
		}
	}

	if ( $count ) {
		return $wpdb->get_var( "SELECT count({$wpdb->prefix}wc_serial_numbers_activations.id) $query_from $query_where" );
	}


	$request = "SELECT {$wpdb->prefix}wc_serial_numbers_activations.*,{$wpdb->prefix}wc_serial_numbers.product_id, {$wpdb->prefix}wc_serial_numbers.order_id, {$wpdb->prefix}wc_serial_numbers.expire_date, {$wpdb->prefix}wc_serial_numbers.expire_date  $query_fields $query_from $query_where $query_orderby $query_limit";

	if ( is_array( $args['fields'] ) || 'all' == $args['fields'] ) {
		return $wpdb->get_results( $request );
	}

	return $wpdb->get_col( $request );

}

/**
 * since 1.0.0
 *
 * @param $serial_number_id
 * @param $instance
 * @param string $platform
 *
 * @return bool|int
 */
function wc_serial_numbers_activate_activation( $serial_number_id, $instance, $platform = '' ) {
	global $wpdb;
	$where = $wpdb->prepare( " WHERE serial_id=%d", $serial_number_id );
	$where .= $wpdb->prepare( " AND instance=%s", $instance );
	if ( ! empty( $platform ) ) {
		$where .= $wpdb->prepare( " AND platform=%s", $platform );
	}
	$activation = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wc_serial_numbers_activations $where" );
	if ( $activation && $activation->active ) {
		return $activation->id;
	} else if ( $activation && false != $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wc_serial_numbers_activations SET active=1 WHERE id=%d", $activation->id ) ) ) {
		return $activation->id;
	}

	$data = array(
		'serial_id'       => $serial_number_id,
		'instance'        => $instance,
		'active'          => '1',
		'platform'        => $platform,
		'activation_time' => current_time( 'mysql' )
	);

	if ( false === $wpdb->insert( "{$wpdb->prefix}wc_serial_numbers_activations", $data, array(
			'%d',
			'%s',
			'%s',
			'%s',
			'%s'
		) ) ) {
		return false;
	}
	$activation_id = (int) $wpdb->insert_id;
	error_log( 'wc_serial_numbers_activation_activated' );
	error_log( $serial_number_id );
	error_log( $activation_id );
	do_action( 'wc_serial_numbers_activation_activated', $serial_number_id, $activation_id );

	return $activation_id;
}

/**
 * @param $activation_id
 *
 * @return bool
 * @since 1.0.0
 */
function wc_serial_numbers_deactivate_activation( $activation_id ) {
	global $wpdb;
	$activation = wc_serial_numbers_get_activation( $activation_id );
	if ( empty( $activation ) ) {
		return false;
	}

	if ( false != $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wc_serial_numbers_activations set active=%d WHERE id=%d", 0, $activation_id ) ) ) {
		do_action( 'wc_serial_numbers_activation_deactivated', $activation->serial_id, $activation_id );

		return true;
	};

	return false;
}

/**
 * since 1.0.0
 *
 * @param $serial_number_id
 *
 * @return string|null
 */
function wc_serial_numbers_get_activations_count( $serial_number_id ) {
	global $wpdb;

	return $wpdb->get_var( $wpdb->prepare( "SELECT count(id) from {$wpdb->prefix}wc_serial_numbers_activations WHERE serial_id=%d AND active='1'", $serial_number_id ) );
}

/**
 * Sync activation count
 *
 * @param $serial_number_id
 *
 * @since 1.1.6
 */
function wc_serial_numbers_sync_activation_count( $serial_number_id ) {
	global $wpdb;
	$activation_count = wc_serial_numbers_get_activations_count( $serial_number_id );
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wc_serial_numbers SET activation_count = %d WHERE id=%d", intval( $activation_count ), intval( $serial_number_id ) ) );
}


/**
 *
 * @param $id
 *
 * @return bool
 * @since 1.1.6
 */
function wc_serial_numbers_delete_activation( $id ) {
	global $wpdb;
	$id = absint( $id );

	$activation = wc_serial_numbers_get_activation( $id );
	if ( is_null( $activation ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_pre_delete_activation', $id, $activation );
	if ( false == $wpdb->delete( "{$wpdb->prefix}wc_serial_numbers_activations", array( 'id' => $id ), array( '%d' ) ) ) {
		return false;
	}
	do_action( 'wc_serial_numbers_delete_activation', $id, $activation );
	wc_serial_numbers_sync_activation_count( $activation->serial_id );

	return true;
}
