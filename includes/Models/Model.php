<?php

namespace WooCommerceSerialNumbers\Models;

use WooCommerceSerialNumbers\B8\Models\Utilities\DateUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class Model.
 *
 * Base model built on the b8 models package.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 */
abstract class Model extends \WooCommerceSerialNumbers\B8\Models\Model {

	/**
	 * Hook prefix for the model.
	 *
	 * Hooks are fired as "{hook_prefix}_{object_type}_{hook}", which resolves
	 * to the "wc_serial_numbers_key_*" and "wc_serial_numbers_activation_*" names.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $hook_prefix = 'wc_serial_numbers';

	/**
	 * Default query variables.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $query_vars = array(
		'orderby' => 'id',
		'order'   => 'ASC',
		'limit'   => 20,
	);

	/*
	|--------------------------------------------------------------------------
	| Static Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Translate the legacy `where_query` argument into native query conditions.
	 *
	 * The old framework model accepted a `where_query` array of
	 * `array( 'column' => ..., 'compare' => ..., 'value' => ... )` clauses.
	 * The b8 Query class does not read that argument, so released integrations
	 * (e.g. the Pro CSV export date range) would silently lose their filters.
	 * This rewrites those clauses into `$query->where()` calls.
	 *
	 * @param array                                     $args  Query arguments.
	 * @param \WooCommerceSerialNumbers\B8\Models\Query $query Query object.
	 *
	 * @since 1.0.0
	 * @return array Query arguments with `where_query` consumed.
	 */
	public static function prepare_where_query( $args, $query ) {
		if ( empty( $args['where_query'] ) || ! is_array( $args['where_query'] ) ) {
			return $args;
		}

		// Verbose operators the old model accepted but b8 names differently.
		$operator_map = array(
			'GREATER THAN' => '>',
			'LESS THAN'    => '<',
			'FIND_IN_SET'  => 'FIND IN SET',
			'NOT_IN_SET'   => 'NOT FIND IN SET',
		);

		foreach ( $args['where_query'] as $key => $clause ) {
			// Support the `'column' => 'value'` shorthand the old model also allowed.
			if ( ! is_array( $clause ) && ! is_numeric( $key ) ) {
				$clause = array(
					'column'  => $key,
					'value'   => $clause,
					'compare' => '=',
				);
			}

			if ( empty( $clause['column'] ) ) {
				continue;
			}

			$operator = strtoupper( $clause['operator'] ?? ( $clause['compare'] ?? '=' ) );
			$operator = $operator_map[ $operator ] ?? $operator;
			$value    = $clause['value'] ?? null;

			$query->where( $clause['column'], $operator, $value );
		}

		unset( $args['where_query'] );

		return $args;
	}

	/**
	 * Bootstrap the model.
	 *
	 * Registers the legacy `where_query` translation on top of the native b8
	 * setup so query arguments built for the old framework model keep working.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function bootstrap() {
		parent::bootstrap();
		$this->on_filter( 'query_args', array( static::class, 'prepare_where_query' ), 5, 2 );
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated Methods
	|--------------------------------------------------------------------------
	|
	| Backward-compatibility shims for the old framework model public API. New
	| code should use the b8 equivalents. They carry an @deprecated tag (no
	| runtime notice, to avoid flooding logs from hot call paths) and are grouped
	| here so the whole block can be removed in a future release.
	|
	| Note: the old static `get( $id )` is not shimmable. The b8 base defines
	| `get()` as an instance attribute getter, so use `find()` instead.
	|
	*/

	/**
	 * Get the object data as an array.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use get_attributes().
	 * @return array
	 */
	public function get_data() {
		return $this->get_attributes();
	}

	/**
	 * Set a collection of props from an array.
	 *
	 * @param array $props Key value pairs to set.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use fill().
	 * @return static
	 */
	public function set_data( $props ) {
		return $this->fill( $props );
	}

	/**
	 * Get a prop value.
	 *
	 * @param string $prop    Name of prop to get.
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use property access, e.g. $model->prop.
	 * @return mixed
	 */
	public function get_prop( $prop, $context = 'edit' ) {
		return $this->get( $prop );
	}

	/**
	 * Set a prop value.
	 *
	 * @param string $prop  Name of prop to set.
	 * @param mixed  $value Value to set.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use property access, e.g. $model->prop = $value.
	 * @return void
	 */
	public function set_prop( $prop, $value ) {
		$this->set( $prop, $value );
	}

	/**
	 * Get a date prop formatted as a string.
	 *
	 * @param string $prop    Name of prop to get.
	 * @param string $context Unused. Kept for backward compatibility.
	 * @param string $format  Date format.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use property access; date columns are cast to DateTime.
	 * @return string|null
	 */
	public function get_date_prop( $prop, $context = 'edit', $format = 'Y-m-d H:i:s' ) {
		$value = $this->get( $prop );
		if ( $value instanceof \DateTime ) {
			return $value->format( $format );
		}

		$timestamp = DateUtil::strtotime( $value );

		return $timestamp ? wp_date( $format, $timestamp ) : null;
	}

	/**
	 * Set a date prop.
	 *
	 * @param string         $prop   Name of prop to set.
	 * @param string|integer $value  Value of the prop.
	 * @param string         $format Unused. Kept for backward compatibility.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use property access; date columns are cast on write.
	 * @return void
	 */
	public function set_date_prop( $prop, $value, $format = 'Y-m-d H:i:s' ) {
		$this->set( $prop, $value );
	}

	/**
	 * Get the database table name.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use get_table().
	 * @return string
	 */
	public function get_table_name() {
		return $this->get_table();
	}

	/**
	 * Get the core (column-backed) data.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use get_attributes().
	 * @return array
	 */
	public function get_core_data() {
		return wp_array_slice_assoc( $this->get_attributes(), $this->get_columns() );
	}

	/**
	 * Get the core data keys (column names).
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use get_columns().
	 * @return array
	 */
	public function get_core_data_keys() {
		return $this->get_columns();
	}

	/**
	 * Whether the object has been read from the database.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use exists().
	 * @return bool
	 */
	public function get_object_read() {
		return $this->exists();
	}

	/**
	 * Whether the object has been read from the database.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use exists().
	 * @return bool
	 */
	public function is_object_read() {
		return $this->exists();
	}

	/**
	 * Mark the object as read from (existing in) the database.
	 *
	 * @param bool $read Whether the object exists in the database.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 The b8 model manages existence automatically.
	 * @return void
	 */
	public function set_object_read( $read = true ) {
		$this->exists = (bool) $read;
	}
}
