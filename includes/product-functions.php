<?php
defined( 'ABSPATH' ) || exit();

/**
 * get products
 *
 * since 1.0.0
 *
 * @param array $args
 * @param bool $count
 *
 * @return array|object|string|null
 */
function wc_serial_numbers_get_products( $args = array(), $count = false ) {
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
		'orderby'        => 'title',
		'order'          => 'DESC',
		'search_columns' => array( 'post_title' ),
		'post_type'      => array( 'product' ),
		'serial_number'  => false,
		'fields'         => 'all',
		'per_page'       => 20,
		'page'           => 1,
		'offset'         => 0,
	);

	$args = apply_filters( 'serial_numbers_query_products_args', wp_parse_args( $args, $default ) );

	$query_from  = "FROM $wpdb->posts";
	$query_where = "WHERE 1=1 AND $wpdb->posts.post_status='publish' ";

	//post_type
	$post_types  = $args['post_type'];
	$post_types  = implode( "','", $post_types );
	$query_where .= " AND $wpdb->posts.post_type IN ('$post_types')";

	$query_where .= " AND $wpdb->posts.ID NOT IN (SELECT post_parent FROM $wpdb->posts WHERE post_type='product_variation') ";


	//if serial_number
	if ( $args['serial_number'] ) {
		$query_where .= " AND $wpdb->posts.ID IN ( SELECT post_id from $wpdb->postsmeta WHERE meta_key='_is_serial_number' AND meta_value='yes') ";
	}

	//include
	$include = false;
	if ( ! empty( $args['include'] ) ) {
		$include = wp_parse_id_list( $args['include'] );
	}

	if ( ! empty( $include ) ) {
		// Sanitized earlier.
		$ids         = implode( ',', $include );
		$query_where .= " AND $wpdb->posts.ID IN ($ids)";
	} elseif ( ! empty( $args['exclude'] ) ) {
		$ids         = implode( ',', wp_parse_id_list( $args['exclude'] ) );
		$query_where .= " AND $wpdb->posts.ID NOT IN ($ids)";
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
			$searches[] = $wpdb->prepare( "$col LIKE %s", $like );
		}

		$query_where .= ' AND (' . implode( ' OR ', $searches ) . ')';
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
			$searches[] = $wpdb->prepare( "$col LIKE %s", $like );
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

	//fields
	if ( is_array( $args['fields'] ) ) {
		$args['fields'] = array_unique( $args['fields'] );

		$query_fields = array();
		foreach ( $args['fields'] as $field ) {
			$field          = 'id' === $field ? 'id' : sanitize_key( $field );
			$query_fields[] = "$wpdb->posts.$field";
		}
		$query_fields = implode( ',', $query_fields );
	} elseif ( 'all' == $args['fields'] ) {
		$query_fields = "$wpdb->posts.*";
	} else {
		$query_fields = "$wpdb->posts.ID";
	}

	if ( $count ) {
		return $wpdb->get_var( "SELECT count(id) $query_from $query_where" );
	}

	$request = "SELECT $query_fields $query_from $query_where $query_orderby $query_limit";

	return $wpdb->get_results( $request );
}

/**
 * since 1.0.0
 * @param $product_id
 */
function wc_serial_numbers_product_enable_serial_number( $product_id ) {
	update_post_meta( $product_id, '_is_serial_number', 'true' );
}

/**
 * since 1.0.0
 * @param $product_id
 *
 * @return bool
 */
function wc_serial_numbers_product_support_serial_number( $product_id ) {
	return 'yes' == get_post_meta( $product_id, '_is_serial_number', true );
}


