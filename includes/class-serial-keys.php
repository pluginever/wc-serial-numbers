<?php

namespace PluginEver\WooCommerceSerialNumbers;

use PluginEver\WooCommerceSerialNumbers\Serial_Key;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

class Serial_Keys {

	/**
	 * Get serial number's statuses.
	 *
	 * since 1.2.0
	 *
	 * @return array
	 */
	public static function get_statuses() {
		$statuses = array(
			'available' => __( 'Available', 'wc-serial-numbers' ), // when ready for selling.
			'sold'      => __( 'Sold', 'wc-serial-numbers' ), // when sold for API it should show inactive.
			'active'    => __( 'Active', 'wc-serial-numbers' ), // when sold and API activated.
			'expired'   => __( 'Expired', 'wc-serial-numbers' ), // when expired
		);

		return apply_filters( 'wc_serial_numbers_serial_keys_statuses', $statuses );
	}

	/**
	 * Get serial key.
	 *
	 * @param int    $id serial key id
	 * @param string $output The required return type. One of OBJECT, ARRAY_A, or ARRAY_N. Default OBJECT.
	 *
	 * @since 1.3.0
	 */
	public static function get( $id, $output = OBJECT ) {
		if ( empty( $id ) ) {
			return null;
		}

		if ( $id instanceof Serial_Key ) {
			$serial = $id;
		} else {
			$serial = new Serial_Key( $id );
		}

		if ( ! $serial->exists() ) {
			return null;
		}

		if ( ARRAY_A === $output ) {
			return $serial->get_data();
		}

		if ( ARRAY_N === $output ) {
			return array_values( $serial->get_data() );
		}

		return $serial;
	}

	/**
	 * Get serial key by key
	 *
	 * @param string $key Account Number
	 *
	 * @since 1.3.0
	 * @return Serial_Key|null
	 */
	public static function get_by_key( $key ) {
		global $wpdb;
		$serial_key = wp_cache_get( $key, Serial_Key::get_cache_group() );
		if ( $serial_key === false ) {
			$serial_key = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcsn_serial_keys WHERE number = %s", wc_clean( $key ) ) );
			wp_cache_set( $key, $serial_key, Serial_Key::get_cache_group() );
		}

		return new Serial_Key( $serial_key );
	}

	/**
	 * Insert serial key.
	 *
	 * @param array|object $data Serial key Data
	 *
	 * @since 1.3.0
	 * @return object|\WP_Error
	 */
	public static function insert( $data ) {
		if ( $data instanceof Serial_Key ) {
			$data = $data->get_data();
		} elseif ( is_object( $data ) ) {
			$data = get_object_vars( $data );
		}

		if ( empty( $data ) || ! is_array( $data ) ) {
			return new \WP_Error( 'invalid_data', __( 'Serial key could not be saved.', 'wc-serial-numbers' ) );
		}

		$data       = wp_parse_args( $data, array( 'id' => null ) );
		$serial_key = new Serial_Key( (int) $data['id'] );
		$serial_key->set_props( $data );
		$is_error = $serial_key->save();
		if ( is_wp_error( $is_error ) ) {
			return $is_error;
		}

		return $serial_key;
	}

	/**
	 * Delete Serial key.
	 *
	 * @param int $id Serial key id
	 *
	 * @since 1.3.0
	 * @return object|bool
	 */
	public static function delete( $id ) {
		if ( $id instanceof Serial_Key ) {
			$id = $id->get_id();
		}

		if ( empty( $id ) ) {
			return false;
		}

		$serial_key = new Serial_Key( (int) $id );
		if ( ! $serial_key->exists() ) {
			return false;
		}

		return $serial_key->delete();
	}

	/**
	 * Get all serial keys
	 *
	 * @param array $args Query arguments.
	 *
	 * @since 1.0.0
	 * @return int|object
	 */
	public static function query( $args = array(), $count = false ) {
		global $wpdb;
		$results      = null;
		$total        = 0;
		$cache_group  = Serial_Key::get_cache_group();
		$table        = $wpdb->prefix . Serial_Key::get_table_name();
		$columns      = Serial_Key::get_columns();
		$key          = md5( maybe_serialize( $args ) );
		$last_changed = wp_cache_get_last_changed( $cache_group );
		$cache_key    = "$cache_group:$key:$last_changed";
		$cache        = wp_cache_get( $cache_key, $cache_group );
		$fields       = '';
		$join         = '';
		$where        = '';
		$groupby      = '';
		$having       = '';
		$limit        = '';

		$args = (array) wp_parse_args(
			$args,
			array(
				'orderby'  => 'date_created',
				'order'    => 'ASC',
				'search'   => '',
				'balance'  => '',
				'offset'   => '',
				'per_page' => 20,
				'paged'    => 1,
				'no_count' => false,
				'fields'   => 'all',
				'return'   => 'objects',

			)
		);

		if ( false !== $cache ) {
			return $count ? $cache->total : $cache->results;
		}

		// Fields setup
		if ( is_array( $args['fields'] ) ) {
			$fields .= implode( ',', $args['fields'] );
		} elseif ( 'all' === $args['fields'] ) {
			$fields .= "$table.* ";
		} else {
			$fields .= "$fields.id";
		}

		if ( false === (bool) $args['no_count'] ) {
			$fields = 'SQL_CALC_FOUND_ROWS ' . $fields;
		}

		// Query from.
		$from = "FROM $table";

		// Parse arch params
		if ( ! empty( $args['search'] ) ) {
			$allowed_fields = array( 'key', 'product_id', 'order_id', 'vendor_id' );
			$search_fields  = ! empty( $args['search_field'] ) ? $args['search_field'] : $allowed_fields;
			$search_fields  = array_intersect( $search_fields, $allowed_fields );
			$searches       = array();
			foreach ( $search_fields as $field ) {
				$searches[] = $wpdb->prepare( '`' . $field . '` LIKE %s', '%' . $wpdb->esc_like( $args['search'] ) . '%' );
			}

			$where .= ' AND (' . implode( ' OR ', $searches ) . ')';
		}

		// Parse date params
		if ( ! empty( $args['date'] ) ) {
			$args['date_from'] = $args['date'];
			$args['date_to']   = $args['date'];
		}

		if ( ! empty( $args['date_from'] ) ) {
			$date   = get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $args['date_from'] . ' 00:00:00' ) ) );
			$where .= $wpdb->prepare( " AND DATE($table.date_created) >= %s", $date );
		}

		if ( ! empty( $args['date_to'] ) ) {
			$date   = get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $args['date_to'] . ' 23:59:59' ) ) );
			$where .= $wpdb->prepare( " AND DATE($table.date_created) <= %s", $date );
		}

		if ( ! empty( $args['date_after'] ) ) {
			$date   = get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $args['date_after'] ) ) );
			$where .= $wpdb->prepare( " AND DATE($table.date_created) > %s", $date );
		}

		if ( ! empty( $args['date_before'] ) ) {
			$date   = get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $args['date_before'] ) ) );
			$where .= $wpdb->prepare( " AND DATE($table.date_created) < %s", $date );
		}

		// Parse __in params
		$ins = array();
		foreach ( $args as $arg => $value ) {
			if ( '__in' === substr( $arg, - 4 ) ) {
				$ins[ $arg ] = wp_parse_list( $value );
			}
		}
		if ( ! empty( $ins ) ) {
			foreach ( $ins as $key => $value ) {
				if ( empty( $value ) || ! is_array( $value ) ) {
					continue;
				}

				$field = str_replace( array( 'record_', '__in' ), '', $key );
				$field = empty( $field ) ? 'id' : $field;
				$type  = is_numeric( reset( $value ) ) ? '%d' : '%s';

				if ( ! empty( $value ) ) {
					$format = '(' . implode( ',', array_fill( 0, count( $value ), $type ) ) . ')';

					$where .= $wpdb->prepare( " AND $table.$field IN {$format}", $value ); // @codingStandardsIgnoreLine prepare okay
				}
			}
		}

		// Parse not__in params.
		$not_ins = array();
		foreach ( $args as $arg => $value ) {
			if ( '__not_in' === substr( $arg, - 8 ) ) {
				$not_ins[ $arg ] = $value;
			}
		}
		if ( ! empty( $not_ins ) ) {
			foreach ( $not_ins as $key => $value ) {
				if ( empty( $value ) || ! is_array( $value ) ) {
					continue;
				}

				$field = str_replace( array( 'record_', '__not_in' ), '', $key );
				$field = empty( $field ) ? 'id' : $field;
				$type  = is_numeric( reset( $value ) ) ? '%d' : '%s';

				if ( ! empty( $value ) ) {
					$format = '(' . implode( ',', array_fill( 0, count( $value ), $type ) ) . ')';
					$where  .= $wpdb->prepare( " AND $table.$field NOT IN {$format}", $value ); // @codingStandardsIgnoreLine prepare okay
				}
			}
		}

		// Parse pagination
		$page     = absint( $args['paged'] );
		$per_page = absint( $args['per_page'] );
		if ( $per_page >= 0 ) {
			$offset = absint( ( $page - 1 ) * $per_page );
			$limit  = " LIMIT {$offset}, {$per_page}";
		}

		// Parse order.
		$orderby = "$table.id";
		if ( in_array( $args['orderby'], $columns, true ) ) {
			$orderby = sprintf( '%s.%s', $table, $args['orderby'] );
		}
		// Show the recent records first by default.
		$order = 'DESC';
		if ( 'ASC' === strtoupper( $args['order'] ) ) {
			$order = 'ASC';
		}

		$orderby = sprintf( 'ORDER BY %s %s', $orderby, $order );

		// Add all param.
		if ( null === $results ) {
			$request = "SELECT {$fields} {$from} {$join} WHERE 1=1 {$where} {$groupby} {$having} {$orderby} {$limit}";
			if ( is_array( $args['fields'] ) || 'all' === $args['fields'] ) {
				$results = $wpdb->get_results( $request );
			} else {
				$results = $wpdb->get_col( $request );
			}

			if ( ! $args['no_count'] ) {
				$total = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			}

			if ( 'all' === $args['fields'] ) {
				foreach ( $results as $key => $row ) {
					wp_cache_add( $row->id, $row, $cache_group );
					$item = new Serial_Key();
					$item->set_props( $row );
					$item->set_object_read( true );
					$results[ $key ] = $item;
				}
			}

			$cache          = new \StdClass();
			$cache->results = $results;
			$cache->total   = $total;

			wp_cache_add( $cache_key, $cache, $cache_group );
		}

		return $count ? $total : $results;
	}
}
