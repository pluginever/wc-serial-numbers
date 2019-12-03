<?php
defined( 'ABSPATH' ) || exit();

/**
 * Get serial numbers
 *
 * @param $id
 * @param string $by
 *
 * @return array|object|void|null
 * @since 1.0.0
 */
function wc_serial_numbers_get_activation( $id, $by = 'id' ) {
	global $wpdb;
	switch ( $by ) {
		case 'serial_id':
			$serial_id = absint( $id );
			$sql       = $wpdb->prepare( "SELECT * FROM $wpdb->wcsn_activations WHERE serial_id=%s", $serial_id );
			break;
		case 'id':
		default:
			$id  = absint( $id );
			$sql = $wpdb->prepare( "SELECT * FROM $wpdb->wcsn_activations WHERE id=%d", $id );
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
		'instance'       => '',
		'active'         => '',
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
	$query_from  = "FROM $wpdb->wcsn_activations.* LEFT JOIN $wpdb->wcsn_serials_numbers ON $wpdb->wcsn_activations.serial_id=$wpdb->wcsn_serials_numbers.id ";
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
		$query_where .= " AND $wpdb->wcsn_activations.id IN( {$ids} ) ";
	}

	//order_id
	if ( ! empty( $args['order_id'] ) ) {
		$order_ids   = implode( ',', wp_parse_id_list( $args['order_id'] ) );
		$query_where .= " AND $wpdb->wcsn_serials_numbers.order_id IN( {$order_ids} ) ";
	}

	//product_id
	if ( ! empty( $args['product_id'] ) ) {
		$product_ids = implode( ',', wp_parse_id_list( $args['product_id'] ) );
		$query_where .= " AND $wpdb->wcsn_serials_numbers.product_id IN( {$product_ids} ) ";
	}

	//instance
	if ( ! empty( $args['instance'] ) ) {
		$instance    = sanitize_text_field( $args['instance'] );
		$query_where .= " AND $wpdb->wcsn_activations.instance = '{$instance}' ";
	}

	//instance
	if ( ! empty( $args['platform'] ) ) {
		$platform    = sanitize_text_field( $args['platform'] );
		$query_where .= " AND $wpdb->wcsn_activations.platform = '{$platform}' ";
	}

	//include
	$include = false;
	if ( ! empty( $args['include'] ) ) {
		$include = wp_parse_id_list( $args['include'] );
	}

	if ( ! empty( $include ) ) {
		// Sanitized earlier.
		$ids         = implode( ',', $include );
		$query_where .= " AND $wpdb->wcsn_activations.id IN ($ids)";
	} elseif ( ! empty( $args['exclude'] ) ) {
		$ids         = implode( ',', wp_parse_id_list( $args['exclude'] ) );
		$query_where .= " AND $wpdb->wcsn_activations.id NOT IN ($ids)";
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
			$searches[] = $wpdb->prepare( "{$wpdb->wcsn_activations}.{$col} LIKE %s", $like );
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
		return $wpdb->get_var( "SELECT count(id) $query_from $query_where" );
	}


	$request = "SELECT $query_fields $query_from $query_where $query_orderby $query_limit";

	if ( is_array( $args['fields'] ) || 'all' == $args['fields'] ) {
		return $wpdb->get_results( $request );
	}

	return $wpdb->get_col( $request );

}


function wc_serial_numbers_activate_serial_number( $serial_number_id, $instance, $platform ) {

}


function wc_serial_numbers_get_remaining_activations( $serial_number_id ) {

}

function wc_serial_numbers_deactivate_serial_number( $serial_number_id, $instance ) {

}
