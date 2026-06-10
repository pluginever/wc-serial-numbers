<?php

namespace WooCommerceSerialNumbers\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Model.
 *
 * Base model built on the b8 models package. It also bridges the legacy
 * framework-model API (getters/setters, query dialect and hooks) so existing
 * call sites and extensions keep working unchanged.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 */
abstract class Model extends \WooCommerceSerialNumbers\B8\Models\Model {

	/**
	 * Hook prefix for the model.
	 *
	 * Hooks are fired as "{hook_prefix}_{object_type}_{hook}", which resolves
	 * to the legacy "wc_serial_numbers_key_*" and "wc_serial_numbers_activation_*" names.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $hook_prefix = 'wc_serial_numbers';

	/**
	 * Default query variables.
	 *
	 * Matches the legacy query defaults (order by id ASC, 20 per page).
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
	| Legacy static entry points kept for backwards compatibility.
	*/

	/**
	 * Retrieve the object instance.
	 *
	 * @param int|array|object|static $data Object ID, array of column conditions, or object.
	 *
	 * @since 1.0.0
	 * @return static|false Object instance on success, false on failure.
	 */
	public static function get( $data ) {
		if ( $data instanceof static ) {
			$data = $data->get_key_value();
		} elseif ( is_object( $data ) ) {
			$vars = get_object_vars( $data );
			$pk   = ( new static() )->get_primary_key();
			$data = ! empty( $vars[ $pk ] ) ? $vars[ $pk ] : null;
		}

		$item = static::find( $data );

		return $item ? $item : false;
	}

	/**
	 * Insert or update an object in the database.
	 *
	 * Accepts both the legacy ( $data, $wp_error ) and the b8
	 * ( $data, $search, $wp_error ) call signatures.
	 *
	 * @param array|object           $data Model data to insert or update.
	 * @param array|string|bool|null $search Search column(s), or the legacy $wp_error flag.
	 * @param bool                   $wp_error Whether to return a WP_Error on failure.
	 *
	 * @since 1.0.0
	 * @return static|false|\WP_Error Object instance on success, false or WP_Error on failure.
	 */
	public static function insert( $data, $search = null, $wp_error = true ) {
		// Legacy signature support: insert( $data, $wp_error ).
		if ( is_bool( $search ) ) {
			$wp_error = $search;
			$search   = null;
		}

		return parent::insert( $data, $search, $wp_error );
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Saves the object in the database.
	 *
	 * @since 1.0.0
	 * @return static|\WP_Error Object instance on success, WP_Error on failure.
	 */
	public function save() {
		/**
		 * Filters whether an item contains valid data before saving.
		 *
		 * Returning a WP_Error prevents the item from being saved.
		 *
		 * @param null|\WP_Error $check Null to allow saving, WP_Error to block.
		 * @param static         $item Model object.
		 *
		 * @since 1.0.0
		 */
		$check = apply_filters( $this->get_hook_prefix() . '_sanitize_data', null, $this );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		return parent::save();
	}

	/**
	 * Deletes the object from the database.
	 *
	 * @since 1.0.0
	 * @return bool|mixed|\WP_Error True on success, false when the object does not exist.
	 */
	public function delete() {
		if ( ! $this->exists() ) {
			return false;
		}

		/**
		 * Filters whether an item delete should take place.
		 *
		 * @param null|mixed $check Null to proceed with the deletion, any other value to short-circuit.
		 * @param static     $item Model object.
		 * @param array      $data Model data array.
		 *
		 * @since 1.0.0
		 */
		$check = apply_filters( $this->get_hook_prefix() . '_check_delete', null, $this, $this->to_array() );
		if ( null !== $check ) {
			return $check;
		}

		return parent::delete();
	}

	/**
	 * Create an item in the database.
	 *
	 * @param array $data Data to be inserted.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|int The ID of the inserted item, or WP_Error on failure.
	 */
	protected function perform_create( $data ) {
		/**
		 * Filters the data to be inserted into the database.
		 *
		 * @param array  $data Data to be inserted.
		 * @param static $item Model object.
		 *
		 * @since 1.0.0
		 */
		$data = apply_filters( $this->get_hook_prefix() . '_insert_data', $data, $this );

		return parent::perform_create( $data );
	}

	/**
	 * Update an item in the database.
	 *
	 * @param int   $id ID of the item to be updated.
	 * @param array $data Data to be updated.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|bool True on success, or WP_Error on failure.
	 */
	protected function perform_update( $id, $data ) {
		/**
		 * Filters the data to be updated in the database.
		 *
		 * @param array  $data Data to be updated.
		 * @param static $item Model object.
		 *
		 * @since 1.0.0
		 */
		$data = apply_filters( $this->get_hook_prefix() . '_update_data', $data, $this );

		return parent::perform_update( $id, $data );
	}

	/*
	|--------------------------------------------------------------------------
	| Query Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get the query object for the model, or run a query when args are given.
	 *
	 * @param array|null $args Optional. The query arguments.
	 *
	 * @since 1.0.0
	 * @return \WooCommerceSerialNumbers\B8\Models\Query|array|int Query object, or the items/count when args are given.
	 */
	public function new_query( $args = null ) {
		if ( is_null( $args ) ) {
			return parent::new_query();
		}

		$args   = (array) $args;
		$output = isset( $args['output'] ) ? $args['output'] : null;
		unset( $args['output'] );

		$items = parent::new_query( $args );

		// Legacy 'output' argument support.
		if ( $output && is_array( $items ) && in_array( $output, array( ARRAY_A, ARRAY_N, OBJECT ), true ) ) {
			foreach ( $items as $index => $item ) {
				if ( ! $item instanceof self ) {
					continue;
				}
				if ( ARRAY_A === $output ) {
					$items[ $index ] = $item->to_array();
				} elseif ( ARRAY_N === $output ) {
					$items[ $index ] = array_values( $item->to_array() );
				} else {
					$items[ $index ] = (object) $item->to_array();
				}
			}
		}

		return $items;
	}

	/**
	 * Apply filters with the automatic hook prefix.
	 *
	 * Intercepts the read and query extension points so legacy behavior
	 * (query-arg dialect, db data filters) applies on every query path.
	 *
	 * @param string $hook The filter name.
	 * @param mixed  $value The value to filter.
	 * @param mixed  ...$args Additional arguments.
	 *
	 * @since 1.0.0
	 * @return mixed The filtered value.
	 */
	public function apply_filters( string $hook, $value, ...$args ) {
		if ( 'query_args' === $hook && isset( $args[0] ) ) {
			$value = $this->prepare_query_args( (array) $value, $args[0] );
		} elseif ( 'data' === $hook && is_array( $value ) ) {
			$value = $this->prepare_db_data( $value );
		}

		return parent::apply_filters( $hook, $value, ...$args );
	}

	/**
	 * Translate legacy query arguments into the b8 query dialect.
	 *
	 * @param array                                     $args Query arguments.
	 * @param \WooCommerceSerialNumbers\B8\Models\Query $query Query object.
	 *
	 * @since 1.0.0
	 * @return array Translated query arguments.
	 */
	protected function prepare_query_args( $args, $query ) {
		// 'paged' is the legacy name of 'page'.
		if ( isset( $args['paged'] ) ) {
			if ( empty( $args['page'] ) ) {
				$args['page'] = max( 1, (int) $args['paged'] );
			}
			unset( $args['paged'] );
		}

		// 'nopaging' and non-positive 'per_page'/'limit' disable the LIMIT clause.
		if ( ! empty( $args['nopaging'] ) ) {
			$args['limit'] = -1;
		}
		unset( $args['nopaging'] );
		if ( isset( $args['per_page'] ) && (int) $args['per_page'] < 1 ) {
			$args['limit'] = -1;
			unset( $args['per_page'] );
		}
		if ( isset( $args['limit'] ) && (int) $args['limit'] < 1 ) {
			$args['limit'] = -1;
		}

		// 'fields' is the legacy name of 'select'.
		if ( isset( $args['fields'] ) ) {
			$fields = is_array( $args['fields'] ) ? $args['fields'] : preg_split( '/[,\s]+/', (string) $args['fields'] );
			if ( in_array( 'ids', (array) $fields, true ) ) {
				$args['select'] = 'ids';
			}
			unset( $args['fields'] );
		}

		// Found rows are now only calculated on demand.
		unset( $args['no_count'] );

		// Legacy multi-column 'orderby' values.
		if ( ! empty( $args['orderby'] ) && is_string( $args['orderby'] ) && preg_match( '/[\s,]/', trim( $args['orderby'] ) ) ) {
			$orderby = array();
			foreach ( array_filter( array_map( 'trim', explode( ',', $args['orderby'] ) ) ) as $part ) {
				$part      = preg_split( '/\s+/', $part );
				$orderby[] = array( $part[0] => isset( $part[1] ) ? $part[1] : '' );
			}
			$args['orderby'] = array_merge( ...$orderby );
		}
		if ( ! empty( $args['orderby'] ) && is_array( $args['orderby'] ) ) {
			$order   = isset( $args['order'] ) && is_string( $args['order'] ) ? strtoupper( $args['order'] ) : 'ASC';
			$orderby = array();
			foreach ( $args['orderby'] as $column => $direction ) {
				if ( is_numeric( $column ) ) {
					$column    = $direction;
					$direction = $order;
				}
				$direction = in_array( strtoupper( (string) $direction ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $direction ) : $order;
				$orderby[] = array(
					'column'    => $column,
					'direction' => $direction,
				);
			}
			$args['orderby'] = $orderby;
		}

		// Legacy 'where_query' clauses.
		if ( ! empty( $args['where_query'] ) && is_array( $args['where_query'] ) ) {
			foreach ( $args['where_query'] as $index => $clause ) {
				if ( ! is_numeric( $index ) && is_string( $clause ) ) {
					$clause = array(
						'column'  => $index,
						'value'   => $clause,
						'compare' => '=',
					);
				}
				$clause = wp_parse_args(
					(array) $clause,
					array(
						'column'  => '',
						'value'   => '',
						'compare' => '=',
					)
				);
				if ( empty( $clause['column'] ) ) {
					continue;
				}
				$query->where( $clause['column'], strtoupper( $clause['compare'] ), $clause['value'] );
			}
		}
		unset( $args['where_query'] );

		return $args;
	}

	/**
	 * Prepare data read from the database before it is hydrated.
	 *
	 * @param array $data Data read from the database.
	 *
	 * @since 1.0.0
	 * @return array Prepared data.
	 */
	protected function prepare_db_data( $data ) {
		/**
		 * Filters the data retrieved from the database.
		 *
		 * @param array  $data Data retrieved from the database.
		 * @param static $item Model object.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_db_data', $data, $this );
	}

	/*
	|--------------------------------------------------------------------------
	| Getters and Setters
	|--------------------------------------------------------------------------
	| Legacy prop accessors used by the concrete models and external code.
	*/

	/**
	 * Get an attribute, routed through the model's getter method when one exists.
	 *
	 * @param string $key The name of the attribute.
	 * @param mixed  $fallback Optional fallback value if the key is not found.
	 *
	 * @since 1.0.0
	 * @return mixed The value of the attribute.
	 */
	public function get_attribute( string $key, $fallback = null ) {
		$getter = "get_{$key}";
		if ( ! method_exists( self::class, $getter ) && method_exists( $this, $getter ) ) {
			return $this->$getter();
		}

		return parent::get_attribute( $key, $fallback );
	}

	/**
	 * Set an attribute, routed through the model's setter method when one exists.
	 *
	 * @param string $key The name of the attribute.
	 * @param mixed  $value The value to set.
	 *
	 * @since 1.0.0
	 * @return static
	 */
	public function set( string $key, $value ) {
		$setter = "set_{$key}";
		if ( ! method_exists( self::class, $setter ) && method_exists( $this, $setter ) ) {
			$this->$setter( $value );

			return $this;
		}

		return parent::set( $key, $value );
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * @param string $prop Name of prop to get.
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	protected function get_prop( $prop, $context = 'edit' ) {
		$value = null;
		if ( array_key_exists( $prop, $this->attributes ) ) {
			$value = $this->attributes[ $prop ];
		} elseif ( array_key_exists( $prop, $this->metadata ) ) {
			$value = $this->metadata[ $prop ];
		}

		if ( 'view' === $context ) {
			/**
			 * Filters the prop value when read for viewing.
			 *
			 * @param mixed  $value Prop value.
			 * @param static $item Model object.
			 *
			 * @since 1.0.0
			 */
			$value = apply_filters( $this->get_hook_prefix() . '_get_' . $prop, $value, $this );
		}

		return $value;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * Only known columns and registered metadata are written, matching the
	 * legacy behavior of ignoring unknown props.
	 *
	 * @param string $prop Name of prop to set.
	 * @param mixed  $value Value to set.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_prop( $prop, $value ) {
		if ( array_key_exists( $prop, $this->attributes ) ) {
			$this->attributes[ $prop ] = $value;
		} elseif ( array_key_exists( $prop, $this->metadata ) ) {
			$this->metadata[ $prop ] = $value;
		}
	}

	/**
	 * Set the model's data.
	 *
	 * @param array|object $props Array or object of properties to set.
	 *
	 * @since 1.0.0
	 * @return static
	 */
	public function set_data( $props ) {
		return $this->fill( $props );
	}

	/**
	 * Get the model's data.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_data() {
		return $this->to_array();
	}

	/**
	 * Get date prop.
	 *
	 * @param string $prop Name of prop to get.
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @param string $format Date format.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function get_date_prop( $prop, $context = 'edit', $format = 'Y-m-d H:i:s' ) {
		$datetime = $this->sanitize_date( $this->get_prop( $prop ) );

		$value = $datetime ? date( $format, strtotime( $datetime ) ) : null; // @codingStandardsIgnoreLine - date() is ok here.

		if ( 'view' === $context ) {
			/**
			 * Filters the date prop value when read for viewing.
			 *
			 * @param mixed  $value Prop value.
			 * @param static $item Model object.
			 *
			 * @since 1.0.0
			 */
			$value = apply_filters( $this->get_hook_prefix() . '_get_' . $prop, $value, $this );
		}

		return $value;
	}

	/**
	 * Sets a date prop whilst handling formatting and datetime objects.
	 *
	 * @param string         $prop Name of prop to set.
	 * @param string|integer $value Value of the prop.
	 * @param string         $format Date format.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function set_date_prop( $prop, $value, $format = 'Y-m-d H:i:s' ) {
		$date = $this->sanitize_date( $value );
		if ( ! empty( $date ) ) {
			$date = date( $format, strtotime( $date ) ); // @codingStandardsIgnoreLine - date() is ok here.
		}
		$this->set_prop( $prop, $date );
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get the hook prefix including the object type.
	 *
	 * Kept for backwards compatibility with the legacy model API, where the
	 * prefix included the object type (e.g. "wc_serial_numbers_key").
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_hook_prefix(): string {
		return $this->hook_prefix . '_' . $this->object_type;
	}

	/**
	 * Get table name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_table_name() {
		return $this->get_table();
	}

	/**
	 * Checks if a date is valid or not.
	 *
	 * @param string $date Date to check.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_date_valid( $date ) {
		if ( empty( preg_replace( '/[^0-9]/', '', (string) $date ) ) ) {
			return false;
		}

		return (bool) strtotime( (string) $date );
	}

	/**
	 * Sanitize date property.
	 * If the date is a valid date, it will be returned to the given format.
	 *
	 * @param string $date Date.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	public function sanitize_date( $date ) {
		if ( empty( $date ) || '0000-00-00 00:00:00' === $date || '0000-00-00' === $date ) {
			return null;
		}

		if ( ! $this->is_date_valid( $date ) ) {
			return null;
		}

		// get the date format from the given date.
		$length = strlen( (string) $date );
		switch ( $length ) {
			case 8:
				$format = 'H:i:s';
				break;
			case 10:
				$format = 'Y-m-d';
				break;
			case 19:
			default:
				$format = 'Y-m-d H:i:s';
				break;
		}

		$d = \DateTime::createFromFormat( $format, (string) $date );

		return $d && $d->format( $format ) === $date ? $d->format( $format ) : null;
	}
}
