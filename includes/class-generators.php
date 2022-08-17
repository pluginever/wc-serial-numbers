<?php

namespace PluginEver\WooCommerceSerialNumbers;

use PluginEver\WooCommerceSerialNumbers\Generator;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

class Generators {
	/**
	 * Get generator object.
	 *
	 * @param int $id Generator id
	 * @param string $output The required return type. One of OBJECT, ARRAY_A, or ARRAY_N. Default OBJECT.
	 *
	 * @since 1.3.0
	 */
	public static function get( $id, $output = OBJECT ) {
		if ( empty( $id ) ) {
			return null;
		}

		if ( $id instanceof Generator ) {
			$serial = $id;
		} else {
			$serial = new Generator( $id );
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
	 * Insert generator in the database.
	 *
	 * @param array|object $data Generator Data
	 *
	 * @since 1.3.0
	 * @return object|\WP_Error
	 */
	public static function insert( $data ) {
		if ( $data instanceof Generator ) {
			$data = $data->get_data();
		} elseif ( is_object( $data ) ) {
			$data = get_object_vars( $data );
		}

		if ( empty( $data ) || ! is_array( $data ) ) {
			return new \WP_Error( 'invalid_data', __( 'Generator could not be saved.', 'wc-serial-numbers' ) );
		}

		$data       = wp_parse_args( $data, array( 'id' => null ) );
		$serial_key = new Generator( (int) $data['id'] );
		$serial_key->set_props( $data );
		$is_error = $serial_key->save();
		if ( is_wp_error( $is_error ) ) {
			return $is_error;
		}

		return $serial_key;
	}

	/**
	 * Delete generator.
	 *
	 * @param int $id Generator id
	 *
	 * @since 1.3.0
	 * @return object|bool
	 */
	public static function delete( $id ) {
		if ( $id instanceof Generator ) {
			$id = $id->get_id();
		}

		if ( empty( $id ) ) {
			return false;
		}

		$serial_key = new Generator( (int) $id );
		if ( ! $serial_key->exists() ) {
			return false;
		}

		return $serial_key->delete();
	}

	/**
	 * Get all generators
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
		$cache_group  = Generator::get_cache_group();
		$table        = $wpdb->prefix . Generator::get_table_name();
		$columns      = Generator::get_columns();
		$key          = md5( serialize( $args ) );
		$last_changed = wp_cache_get_last_changed( $cache_group );
		$cache_key    = "$cache_group:$key:$last_changed";
		$cache        = wp_cache_get( $cache_key, $cache_group );
		$fields       = '';
		$join         = '';
		$where        = '';
		$groupby      = '';
		$having       = '';
		$limit        = '';

		$args = (array) wp_parse_args( $args, array(
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

		) );

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
		if ( ! empty ( $args['search'] ) ) {
			$allowed_fields = array( 'name', 'pattern' );
			$search_fields  = ! empty( $args['search_field'] ) ? $args['search_field'] : $allowed_fields;
			$search_fields  = array_intersect( $search_fields, $allowed_fields );
			$searches       = array();
			foreach ( $search_fields as $field ) {
				$searches[] = $wpdb->prepare( $field . ' LIKE %s', '%' . $wpdb->esc_like( $args['search'] ) . '%' );
			}

			$where .= ' AND (' . implode( ' OR ', $searches ) . ')';
		}

		// Parse date params
		if ( ! empty ( $args['date'] ) ) {
			$args['date_from'] = $args['date'];
			$args['date_to']   = $args['date'];
		}

		if ( ! empty( $args['date_from'] ) ) {
			$date  = get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $args['date_from'] . ' 00:00:00' ) ) );
			$where .= $wpdb->prepare( " AND DATE($table.date_created) >= %s", $date );
		}

		if ( ! empty( $args['date_to'] ) ) {
			$date  = get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $args['date_to'] . ' 23:59:59' ) ) );
			$where .= $wpdb->prepare( " AND DATE($table.date_created) <= %s", $date );
		}

		if ( ! empty( $args['date_after'] ) ) {
			$date  = get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $args['date_after'] ) ) );
			$where .= $wpdb->prepare( " AND DATE($table.date_created) > %s", $date );
		}

		if ( ! empty( $args['date_before'] ) ) {
			$date  = get_gmt_from_date( gmdate( 'Y-m-d H:i:s', strtotime( $args['date_before'] ) ) );
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

		//Parse pagination
		$page     = absint( $args['paged'] );
		$per_page = (int) $args['per_page'];
		if ( $per_page >= 0 ) {
			$offset = absint( ( $page - 1 ) * $per_page );
			$limit  = " LIMIT {$offset}, {$per_page}";
		}

		//Parse order.
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

		//Add all param.
		if ( null === $results ) {
			$request = "SELECT {$fields} {$from} {$join} WHERE 1=1 {$where} {$groupby} {$having} {$orderby} {$limit}";

			if ( is_array( $args['fields'] ) || 'all' === $args['fields'] ) {
				$results = $wpdb->get_results( $request );
			} else {
				$results = $wpdb->get_col( $request );
			}

			if ( ! $args['no_count'] ) {
				$total = (int) $wpdb->get_var( "SELECT FOUND_ROWS()" );
			}

			if ( 'all' === $args['fields'] ) {
				foreach ( $results as $key => $row ) {
					wp_cache_add( $row->id, $row, $cache_group );
					$item = new Generator();
					$item->set_props( $row );
					$item->set_object_read( true );
					$results[ $key ] = $item;
				}
			}

			$cache          = new \StdClass;
			$cache->results = $results;
			$cache->total   = $total;

			wp_cache_add( $cache_key, $cache, $cache_group );
		}

		return $count ? $total : $results;
	}

	/**
	 * Generate serial keys.
	 *
	 * @param string $pattern Serial number pattern.
	 * @param int $quantity Quantity to generate.
	 * @param bool $is_sequential Is sequential or not.
	 * @param int $start Sequential start number.
	 *
	 * @return array
	 */
	public static function generate_keys( $pattern = 'SERIAL-#####################', $quantity = 5, $is_sequential = false, $start = 0 ) {
		$pattern              = empty( $pattern ) ? str_pad( $pattern, 32, '#' ) : trim( $pattern );
		$start                = absint( $start );
		$pattern_length       = strlen( $pattern );
		$pattern_mask_length  = substr_count( $pattern, '#' );
		$required_mask_length = strlen( $quantity + $start );
		if ( $pattern_mask_length < $required_mask_length ) {
			$static              = $pattern_length - $pattern_mask_length;
			$pad_length          = $static + $required_mask_length;
			$pattern_mask_length = $required_mask_length;
			$pattern             = str_pad( $pattern, $pad_length, '#' );
		}

		$serial_keys = array();
		for ( $i = 1; $i <= $quantity; $i ++ ) {
			$serial_key = $pattern;
			if ( $is_sequential ) {
				$new_serial_key = str_pad( $start + $i, $pattern_mask_length, '0', STR_PAD_LEFT );
			} else {
				$new_serial_key = strtolower( wp_generate_password( $pattern_mask_length, false ) );
			}


			$new_key_parts = str_split( $new_serial_key );
			for ( $j = 0; $j <= count( $new_key_parts ) - 1; $j ++ ) {
				if ( strpos( $serial_key, '#' ) !== false ) {
					$occurrence = strpos( $serial_key, '#' );
					$serial_key = substr_replace( $serial_key, $new_key_parts[ $j ], $occurrence, 1 );
				}
			}

			$serial_keys[] = apply_filters( 'wc_serial_numbers_generated_key', $serial_key, $pattern, $quantity, $is_sequential, $start );
		}

		return $serial_keys;
	}

	/**
	 * Get dropdown options.
	 *
	 *
	 * @since #.#.#
	 * @return array
	 */
	public static function get_dropdown_options() {
		$options = self::query( [
			'fields'   => [ 'id', 'name' ],
			'per_page' => - 1,
		] );

		return wp_list_pluck( $options, 'name', 'id' );
	}

	public static function get_products_counts(){
		global $wpdb;
	}
}
