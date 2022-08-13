<?php


namespace PluginEver\WooCommerceSerialNumbers\Abstracts;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

abstract class Data {
	/**
	 * id for this object.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $id = 0;

	/**
	 * All data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $data = array();

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $core_data = array();

	/**
	 * Extra data for this object. Name value pairs (name + default value).
	 * Used as a standard way for subclasses (like product types) to add
	 * additional information to an inherited class. Anything that is not
	 * core data may store here,
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $extra_data = array();

	/**
	 * Set to data on construct, so we can track and reset data if needed.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $default_data = array();

	/**
	 * Core data changes for this object.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $changes = array();

	/**
	 * This is false until the object is read from the DB.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $object_read = false;

	/**
	 * Table name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = '';

	/**
	 * Core table columns.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array();

	/**
	 * Data constructor.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $cache_group = '';

	/**
	 * Default constructor.
	 *
	 * @param int|object|array $read ID to load from the DB (optional) or already queried data.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $read = 0 ) {
		$this->data         = array_merge( $this->core_data, $this->extra_data );
		$this->columns      = array_keys( $this->core_data );
		$this->default_data = $this->data;
	}

	/**
	 * Only store the object ID to avoid serializing the data object instance.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function __sleep() {
		return array( 'id' );
	}

	/**
	 * Re-run the constructor with the object ID.
	 *
	 * If the object no longer exists, remove the ID.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		try {
			$this->__construct( absint( $this->id ) ); //phpcs:ignore
		} catch ( \Exception $e ) {
			$this->set_id( 0 );
			$this->set_object_read( true );
		}
	}

	/**
	 * When the object is cloned, make sure meta is duplicated correctly.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
	}

	/**
	 * Magic method for checking the existence of a certain field.
	 *
	 * @param string $key Field to check if set.
	 *
	 * @since 1.0.0
	 * @return bool Whether the given field is set.
	 */
	public function __isset( $key ) {
		return ! empty( $this->get_prop( $key ) );
	}

	/**
	 * Magic method for unsetting a certain field.
	 *
	 * @param string $key Field to unset.
	 *
	 * @since 1.0.0
	 */
	public function __unset( $key ) {
		$this->set_prop( $key, '' );
	}

	/**
	 * Magic method for setting data fields.
	 *
	 * This method does not update custom fields in the database.
	 *
	 * @since  1.0.0
	 */
	public function __set( $key, $value ) {

		if ( 'id' === strtolower( $key ) ) {
			$this->set_id( $value );
		}

		if ( method_exists( $this, "set_$key" ) ) {
			call_user_func( array( $this, "set_$key" ), $value );
		} else {
			$this->set_prop( $key, $value );
		}

	}

	/**
	 * Magic method for retrieving a property.
	 *
	 * @param $key
	 *
	 * @since  1.0.0
	 * @return mixed|null
	 */
	public function __get( $key ) {
		// Check if we have a helper method for that.
		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		}

		return $this->get_prop( $key );
	}

	/**
	 * Change data to JSON format.
	 *
	 * @since  1.0.0
	 * @return string Data in JSON format.
	 */
	public function __toString() {
		return wp_json_encode( $this->get_data() );
	}

	/**
	 * This method will be called when somebody will try to invoke a method in object
	 * context, which does not exist, like:
	 *
	 * $plugin->method($arg, $arg1);
	 *
	 * @param string $method Method name.
	 * @param array $arguments Array of arguments passed to the method.
	 */
	public function __call( $method, $arguments ) {
		$sub_method = substr( $method, 0, 3 );
		// Drop method name.
		$property_name = substr( $method, 4 );
		switch ( $sub_method ) {
			case "get":
				return $this->get_prop( $property_name );
			case "set":
				$this->set_prop( $property_name, $arguments[0] );
				break;
			case "has":
				return $this->get_prop( $property_name ) !== null;
			default:
				throw new \BadMethodCallException( "Undefined method $method" );
		}

		return null;
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Methods for getting data from the bill object.
	|
	*/
	/**
	 * Get the table columns.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_columns() {
		return array_merge( [ 'id' ], ( new static() )->columns );
	}

	/**
	 * Get the table name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_table_name() {
		return ( new static() )->table;
	}

	/**
	 * Get cache group.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_cache_group() {
		return ( new static() )->cache_group;
	}

	/**
	 * Returns the unique ID for this object.
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * Gets the value from either current pending changes, or the data itself.
	 * Context controls what happens to the value before it's returned.
	 *
	 * @param string $prop Name of prop to get.
	 *
	 * @since  1.0.0
	 * @return mixed
	 */
	protected function get_prop( $prop ) {
		$value = null;

		if ( array_key_exists( $prop, $this->data ) ) {
			$value = isset( $this->changes[ $prop ] ) ? $this->changes[ $prop ] : $this->data[ $prop ];
		}

		return $value;
	}

	/**
	 * Returns all data for this object.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_data() {
		return array_merge( array( 'id' => $this->get_id() ), array_replace_recursive( $this->data, $this->changes ) );
	}

	/**
	 * Returns array of expected data keys for this object.
	 *
	 * @since   1.0.0
	 * @return array
	 */
	public function get_data_keys() {
		return array_keys( $this->data );
	}

	/**
	 * Get core data.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_core_data() {
		return wp_array_slice_assoc( $this->get_data(), array_keys( $this->core_data ) );
	}

	/**
	 * Returns all "core" data keys for an object.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_core_data_keys() {
		return array_keys( $this->core_data );
	}

	/**
	 * Get Extra data.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_extra_data() {
		return wp_array_slice_assoc( $this->get_data(), array_keys( $this->extra_data ) );
	}

	/**
	 * Returns all "extra" data keys for an object (for sub objects like product types).
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_extra_data_keys() {
		return array_keys( $this->extra_data );
	}

	/**
	 * Return data changes only.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_changes() {
		return $this->changes;
	}



	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting boll data. These should not update anything in the
	| database itself and should only change what is stored in the class
	| object.
	|
	*/
	/**
	 * Set ID.
	 *
	 * @param int $id ID.
	 *
	 * @since 1.0.0
	 */
	protected function set_id( $id ) {
		$this->id = absint( $id );
	}

	/**
	 * Set object read property.
	 *
	 * @param boolean $read Should read?.
	 *
	 * @since 1.0.0
	 */
	public function set_object_read( $read = true ) {
		$this->object_read = (bool) $read;
	}

	/**
	 * Set all props to default values.
	 *
	 * @since 1.0.0
	 */
	public function set_defaults() {
		$this->data    = $this->default_data;
		$this->changes = array();
		$this->set_object_read( false );
	}

	/**
	 * Set a collection of props in one go, collect any errors, and return the result.
	 * Only sets using public methods.
	 *
	 * @param array|object $props Key value pairs to set. Key is the prop and should map to a setter function name.
	 *
	 * @since  1.0.0
	 */
	public function set_props( $props ) {
		if ( is_object( $props ) ) {
			$props = get_object_vars( $props );
		}
		if ( ! is_array( $props ) ) {
			return $this;
		}

		foreach ( $props as $prop => $value ) {
			/**
			 * Checks if the prop being set is allowed, and the value is not null.
			 */
			if ( is_null( $value ) || in_array( $prop, array( 'prop', 'date_prop' ), true ) ) {
				continue;
			}

			$setter = "set_$prop";
			if ( is_callable( array( $this, $setter ) ) ) {
				$this->{$setter}( $value );
			} else {
				$this->set_prop( $prop, $value );
			}
		}

		return $this;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * This stores changes in a special array, so we can track what needs saving
	 * the DB later.
	 *
	 * @param string $prop Name of prop to set.
	 * @param mixed $value Value of the prop.
	 *
	 * @since 1.0.0
	 */
	protected function set_prop( $prop, $value ) {
		if ( array_key_exists( $prop, $this->data ) ) {
			if ( true === $this->object_read ) {
				if ( $value !== $this->data[ $prop ] || array_key_exists( $prop, $this->changes ) ) {
					$this->changes[ $prop ] = $value;
				}
			} else {
				$this->data[ $prop ] = $value;
			}
		}
	}

	/**
	 * Sets a date prop whilst handling formatting and datetime objects.
	 *
	 * @param string $prop Name of prop to set.
	 * @param string|integer $value Value of the prop.
	 * @param string $format Date format.
	 *
	 * @since 1.0.0
	 */
	protected function set_date_prop( $prop, $value, $format = 'Y-m-d H:i:s' ) {
		if ( empty( $value ) || '0000-00-00 00:00:00' === $value || '0000-00-00' === $value ) {
			$this->set_prop( $prop, null );

			return;
		}

		if ( ! $format ) {
			$format = 'Y-m-d H:i:s';
		}

		if ( ! is_numeric( $value ) ) {
			$value = (int) strtotime( $value );
		}

		$value = date_i18n( $format, $value );

		$this->set_prop( $prop, $value );
	}

	/*
	|--------------------------------------------------------------------------
	| Conditionals
	|--------------------------------------------------------------------------
	|
	| Checks if a condition is true or false.
	|
	*/
	/**
	 * Get object read property.
	 *
	 * @since  1.0.0
	 * @return boolean
	 */
	public function is_object_read() {
		return (bool) $this->object_read;
	}

	/**
	 * Checks if the object is saved in the database
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function exists() {
		$id = $this->get_id();

		return ! empty( $id );
	}

	/*
	|--------------------------------------------------------------------------
	| Helper
	|--------------------------------------------------------------------------
	|
	| Helper methods.
	|
	*/

	/**
	 * Merge changes with data and clear.
	 *
	 * @since 1.0.0
	 */
	public function apply_changes() {
		$this->data    = array_replace_recursive( $this->data, $this->changes );
		$this->changes = array();
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
		if ( empty( preg_replace( '/[^0-9]/', '', $date ) ) ) {
			return false;
		}

		return (bool) strtotime( $date );
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD methods
	|--------------------------------------------------------------------------
	|
	| Methods which create, read, update and delete documents from the database.
	| Written in abstract fashion so that the way documents are stored can be
	| changed more easily in the future.
	|
	| A save method is included for convenience (chooses update or create based
	| on if the order exists yet).
	|
	*/

	/**
	 *  Create an item in the database.
	 *
	 * This method is not meant to call publicly instead call save
	 * which will conditionally decide which method to call.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|true True on success, WP_Error on failure.
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 */
	protected function create() {
		global $wpdb;

		$data = wp_unslash( $this->get_core_data() );

		/**
		 * Fires immediately before an item is inserted in the database.
		 *
		 * @param array $data Data data to be inserted.
		 * @param string $data_arr Sanitized item data.
		 * @param self $item Data object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_serial_numbers_pre_insert_' . $this->object_type, $data, $this->get_data(), $this );

		if ( false === $wpdb->insert( $wpdb->prefix . $this->table, $data, array() ) ) {
			return new \WP_Error( 'db_insert_error', __( 'Could not insert item into the database.', 'wc-serial-numbers' ), $wpdb->last_error );
		}

		$this->set_id( $wpdb->insert_id );
		$this->apply_changes();

		/**
		 * Fires immediately after an item is inserted in the database.
		 *
		 * @param array $data Data data to be inserted.
		 * @param string $data_arr Sanitized item data.
		 * @param self $item Data object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_serial_numbers_insert_' . $this->object_type, $this->get_id(), $data, $this->get_data(), $this );

		return $this->exists();
	}

	/**
	 * Retrieve the object from database instance.
	 *
	 * @since 1.0.0
	 *
	 * @return object|false Object, false otherwise.
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 */
	protected function read() {
		global $wpdb;
		$this->set_defaults();
		// Bail early if no id is set
		if ( ! $this->get_id() ) {
			return false;
		}

		$data = wp_cache_get( $this->get_id(), $this->cache_group );
		if ( false === $data ) {
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$this->table} WHERE id = %d LIMIT 1;", $this->get_id() ) ); // WPCS: cache ok, DB call ok.
			wp_cache_add( $this->get_id(), $data, $this->cache_group );
		}

		if ( ! $data ) {
			$this->set_id( 0 );

			return false;
		}

		$this->set_props( $data );
		$this->set_object_read( true );
		do_action( 'wc_serial_numbers_read_' . $this->object_type . '_item', $this->get_id(), $this );

		return $data;
	}

	/**
	 *  Update an object in the database.
	 *
	 * This method is not meant to call publicly instead call save
	 * which will conditionally decide which method to call.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|true True on success, WP_Error on failure.
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 */
	protected function update() {
		global $wpdb;
		$changes = $this->get_changes();

		// Bail if nothing to save
		if ( empty( $changes ) ) {
			return true;
		}

		/**
		 * Fires immediately before an existing item is updated in the database.
		 *
		 * @param int $id Data id.
		 * @param array $data Data data.
		 * @param array $changes The data will be updated.
		 * @param self $item Data object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_serial_numbers_pre_update_' . $this->object_type, $this->get_id(), $this->get_data(), $changes, $this );

		$this->date_updated = current_time( 'mysql' );
		$data               = wp_unslash( $this->get_core_data() );
		if ( false === $wpdb->update( $wpdb->prefix . $this->table, $data, [ 'id' => $this->get_id() ], array(), [ 'id' => '%d' ] ) ) {
			return new \WP_Error( 'db_update_error', __( 'Could not update item in the database.', 'wc-serial-numbers' ), $wpdb->last_error );
		}

		/**
		 * Fires immediately after an existing item is updated in the database.
		 *
		 * @param int $id Data id.
		 * @param array $data Data data.
		 * @param array $changes The data will be updated.
		 * @param self $item Data object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_serial_numbers_update_' . $this->object_type, $this->get_id(), $this->get_data(), $changes, $this );

		return true;
	}

	/**
	 * Deletes the object from database.
	 *
	 * @param array $args Array of args to pass to the delete method.
	 *
	 * @since 1.0.0
	 * @return array|false true on success, false on failure.
	 */
	public function delete( $args = array() ) {
		if ( ! $this->exists() ) {
			return false;
		}

		$data = $this->get_data();

		/**
		 * Filters whether an item delete should take place.
		 *
		 * @param bool|null $delete Whether to go forward with deletion.
		 * @param int $id Data id.
		 * @param array $data Data data array.
		 * @param self $item Data object.
		 *
		 * @since 1.0.0
		 */
		$check = apply_filters( 'wc_serial_numbers_check_delete_' . $this->object_type, null, $this->get_id(), $data, $this );
		if ( null !== $check ) {
			return $check;
		}

		/**
		 * Fires before an item is deleted.
		 *
		 * @param int $id Data id.
		 * @param array $data Data data array.
		 * @param self $item Data object.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_serial_numbers_pre_delete_' . $this->object_type, $this->get_id(), $data, $this );

		global $wpdb;

		$wpdb->delete(
			$wpdb->prefix . $this->table,
			array(
				'id' => $this->get_id(),
			),
			array( '%d' )
		);

		/**
		 * Fires after a item is deleted.
		 *
		 * @param int $id Data id.
		 * @param array $data Data data array.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_serial_numbers_delete_' . $this->object_type, $this->get_id(), $data );

		wp_cache_delete( $this->get_id(), $this->cache_group );
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
		$this->set_defaults();

		return $data;
	}

	/**
	 * Saves an object in the database.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|int id on success, WP_Error on failure.
	 */
	abstract public function save();
}
