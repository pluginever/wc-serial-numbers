<?php
/**
 * Class Model.
 *
 * Handles generic data interaction which is implemented by
 * the different data object classes.
 *
 * @since 1.0.0
 * @version 1.1.0
 * @package Framework
 */

namespace WooCommerceSerialNumbers\Lib;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Model class.
 *
 * @since 1.0.0
 */
abstract class Model {

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $primary_key = 'id';

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $table_name = '';

	/**
	 * Object type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $object_type = null;

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * Everything in this array will be saved to the core table.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $core_data = array();

	/**
	 * Extra data for this object. Name value pairs (name + default value).
	 * add additional information to an inherited class. Anything that is not
	 * in the columns array will be stored here.
	 *
	 * @since 1.0.0
	 * @var array Extra data.
	 */
	protected $extra_data = array();

	/**
	 * All data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.0.0
	 * @var array All data.
	 */
	protected $data = array();

	/**
	 * This is false until the object is read from the DB.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $object_read = false;

	/**
	 * Meta type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $meta_type = null;

	/**
	 * Meta data for this object. Name value pairs (name + default value).
	 *
	 * For core meta data, use the $metadata array.
	 *
	 * @since 1.0.0
	 * @var array Meta data.
	 */
	protected $metadata = array();

	/**
	 * This is false until the metadata is read from the DB.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $metadata_read = false;

	/**
	 * Model changes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $changes = array();

	/**
	 * Cache group.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $cache_group = null;

	/**
	 * Cache group.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $hook_prefix = 'pluginever_';

	/**
	 * Model constructor.
	 *
	 * @param int|object|array $data Object ID, post object, or array of data.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $data = 0 ) {
		global $wpdb;
		$table_name          = $this->table_name;
		$this->data          = array_merge( $this->core_data, $this->extra_data );
		$wpdb->{$table_name} = $wpdb->prefix . $table_name;
		$wpdb->tables[]      = $table_name;

		// if cache group is not set, use table name.
		if ( ! $this->cache_group ) {
			$this->cache_group = $table_name;
		}

		if ( ! empty( $this->meta_type ) ) {
			$meta_table          = $this->meta_type . 'meta';
			$wpdb->{$meta_table} = $wpdb->prefix . $meta_table;
			$wpdb->tables[]      = $meta_table;
		}

		$this->init( $data );
	}

	/**
	 * Initialize object data from database.
	 *
	 * @param int|object|array $data Object ID, post object, or array of data.
	 *
	 * @since 1.0.0
	 */
	protected function init( $data ) {
		$called_class = get_called_class();
		if ( is_scalar( $data ) ) {
			$this->read( $data );
		} elseif ( $data instanceof $called_class ) {
			$this->set_data( $data->get_data() );
			$this->set_object_read( true );
		} elseif ( is_array( $data ) ) {
			$this->set_data( $data );
			$this->set_object_read( true );
		} elseif ( is_object( $data ) ) {
			$this->set_data( get_object_vars( $data ) );
			$this->set_object_read( true );
		} else {
			$this->set_object_read( true );
		}
	}

	/**
	 * Destroy the object.
	 *
	 * @since 1.0.0
	 */
	public function __destruct() {
		$this->set_defaults();
	}

	/**
	 * Only store the object primary key to avoid serializing the data object instance.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function __sleep() {
		return array( 'data' );
	}

	/**
	 * Re-run the constructor with the object primary key.
	 *
	 * If the object no longer exists, remove the ID.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		try {
			$this->__construct( $this->get_prop( $this->primary_key ) );
		} catch ( \Exception $e ) {
			$this->set_prop( $this->primary_key, null );
			$this->set_object_read( true );
		}
	}

	/**
	 * When the object is cloned, make sure meta is duplicated correctly.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		foreach ( $this->read_metadata() as $key => $meta_datum ) {
			if ( isset( $meta_datum->id ) ) {
				$meta_datum->id = null;
			}
			$this->metadata[ $key ] = clone $meta_datum;
		}

		$this->set_prop( $this->primary_key, 0 );

		return $this;
	}

	/**
	 * Magic method for checking the existence of a certain field.
	 *
	 * @param string $key Field to check if set.
	 *
	 * @return bool Whether the given field is set.
	 * @since 1.0.0
	 */
	public function __isset( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Magic method for unsetting a certain field.
	 *
	 * @param string $key Field to unset.
	 *
	 * @since 1.0.0
	 */
	public function __unset( $key ) {
		$this->__set( $key, null );
	}

	/**
	 * Magic method for setting data fields.
	 *
	 * This method does not update custom fields in the database.
	 *
	 * @param string $key Prop to set.
	 * @param mixed  $value Value to set.
	 *
	 * @since  1.0.0
	 */
	public function __set( $key, $value ) {
		// Check if key have set_ prefix if yes then remove it.
		if ( 0 === strpos( $key, 'set_' ) ) {
			$key = substr( $key, 4 );
		}
		// If there is a setter function for this field, use it.
		$setter = 'set_' . $key;
		if ( method_exists( $this, $setter ) ) {
			$this->$setter( $value );

			return;
		}
		$this->set_prop( $key, $value );
	}

	/**
	 * Magic method for retrieving a property.
	 *
	 * @param string $key Key to get.
	 *
	 * @return mixed|null
	 * @since  1.0.0
	 */
	public function __get( $key ) {
		// Check if key have get_ prefix if yes then remove it.
		if ( 0 === strpos( $key, 'get_' ) ) {
			$key = substr( $key, 4 );
		}
		// Check if we have a helper method for that.
		if ( method_exists( $this, 'get_' . $key ) ) {
			return $this->{'get_' . $key}( 'edit' );
		}

		return $this->get_prop( $key );
	}

	/**
	 * Change data to JSON format.
	 *
	 * @return string Model in JSON format.
	 * @since  1.0.0
	 */
	public function __toString() {
		$json = wp_json_encode( $this->get_data() );

		return ! $json ? '{}' : $json;
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
	 * Retrieve the object from database instance.
	 *
	 * @param int|string $key Unique identifier for the object.
	 *
	 * @return object|false Object, false otherwise.
	 * @since 1.0.0
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 */
	protected function read( $key ) {
		global $wpdb;
		$this->set_defaults();

		// Bail early if no id is set.
		if ( empty( $key ) ) {
			return false;
		}

		$data = $this->get_cache( $key );
		if ( false === $data ) {
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->{$this->table_name}} WHERE {$this->primary_key} = %s LIMIT 1;", esc_sql( $key ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( empty( $data ) ) {
				return false;
			}
			foreach ( $data as $key => $value ) {
				if ( is_serialized( $value ) ) {
					$data->$key = maybe_unserialize( $value );
				}
			}

			$this->set_cache( $data->{$this->primary_key}, $data );
		}

		/**
		 * Filters the data retrieved from the database.
		 *
		 * @param array $data Data retrieved from the database.
		 * @param static $object Model object.
		 *
		 * @since 1.0.0
		 */
		$data = apply_filters( $this->get_hook_prefix() . '_db_data', (array) $data, $this );
		$this->set_data( $data );
		$this->set_object_read( true );

		return $data;
	}

	/**
	 *  Create an item in the database.
	 *
	 * This method is not meant to call publicly instead call save
	 * which will conditionally decide which method to call.
	 *
	 * @return \WP_Error|true True on success, WP_Error on failure.
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 */
	protected function create() {
		global $wpdb;
		$data = wp_unslash( $this->get_core_data() );

		/**
		 * Fires immediately before an item is inserted in the database.
		 *
		 * @param static $object Model object.
		 *
		 * @since 1.0.0
		 */
		do_action( $this->get_hook_prefix() . '_pre_insert', $this );

		foreach ( $data as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				$data[ $key ] = maybe_serialize( $value );
			}
		}

		/**
		 * Filters the data to be inserted into the database.
		 *
		 * @param array $data Data to be inserted.
		 * @param static $object Model object.
		 *
		 * @since 1.0.0
		 */
		$data = apply_filters( $this->get_hook_prefix() . '_insert_data', $data, $this );
		if ( false === $wpdb->insert( $wpdb->{$this->table_name}, $data ) ) {
			// translators: %s: database error message.
			return new \WP_Error( 'db_insert_error', sprintf( __( 'Could not insert item into the database error %s', 'wc-serial-numbers' ), $wpdb->last_error ) );
		}

		$this->set_prop( $this->primary_key, $wpdb->insert_id );
		$this->set_object_read( true );

		/**
		 * Fires immediately after an item is inserted in the database.
		 *
		 * @param static $item Model object.
		 *
		 * @since 1.0.0
		 */
		do_action( $this->get_hook_prefix() . '_inserted', $this );

		return $this->exists();
	}

	/**
	 *  Update an object in the database.
	 *
	 * This method is not meant to call publicly instead call save
	 * which will conditionally decide which method to call.
	 *
	 * @return \WP_Error|true True on success, WP_Error on failure.
	 * @since 1.0.0
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 */
	protected function update() {
		global $wpdb;
		$changes = $this->get_changes();
		// Bail if nothing to save.
		if ( empty( $changes ) ) {
			return true;
		}

		/**
		 * Fires immediately before an existing item is updated in the database.
		 *
		 * @param static $item Model object.
		 * @param array $changes The data will be updated.
		 *
		 * @since 1.0.0
		 */
		do_action( $this->get_hook_prefix() . '_pre_update', $this, $changes );

		$data = wp_array_slice_assoc( $changes, $this->get_core_data_keys() );

		/**
		 * Filters the data to be updated in the database.
		 *
		 * @param array $data Data to be updated.
		 * @param static $object Model object.
		 *
		 * @since 1.0.0
		 */
		$data = apply_filters( $this->get_hook_prefix() . '_update_data', $data, $this );

		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( ! is_scalar( $value ) ) {
					$data[ $key ] = maybe_serialize( $value );
				}
			}
			if ( false === $wpdb->update( $wpdb->{$this->table_name}, $data, array( $this->primary_key => $this->get_prop( $this->primary_key ) ) ) ) {
				return new \WP_Error( 'db_update_error', __( 'Could not update item in the database.', 'wc-serial-numbers' ), $wpdb->last_error );
			}
		}

		/**
		 * Fires immediately after an existing item is updated in the database.
		 *
		 * @param static $item Model object.
		 * @param array $changes The data will be updated.
		 *
		 * @since 1.0.0
		 */
		do_action( $this->get_hook_prefix() . '_updated', $this, $changes );

		return true;
	}

	/**
	 * Deletes the object from database.
	 *
	 * @return array|false true on success, false on failure.
	 * @since 1.0.0
	 */
	public function delete() {
		if ( ! $this->exists() ) {
			return false;
		}

		$data = $this->get_data();

		/**
		 * Filters whether an item delete should take place.
		 *
		 * @param static $item Model object.
		 * @param array $data Model data array.
		 *
		 * @since 1.0.0
		 */
		$check = apply_filters( $this->get_hook_prefix() . '_check_delete', null, $this, $data );
		if ( null !== $check ) {
			return $check;
		}

		/**
		 * Fires before an item is deleted.
		 *
		 * @param static $item Model object.
		 * @param array $data Model data array.
		 *
		 * @since 1.0.0
		 */
		do_action( $this->get_hook_prefix() . '_pre_delete', $this, $data );

		global $wpdb;
		$wpdb->delete(
			$wpdb->{$this->table_name},
			array(
				$this->primary_key => $this->get_prop( $this->primary_key ),
			)
		);

		$this->delete_metadata();
		$this->flush_cache();

		/**
		 * Fires after a item is deleted.
		 *
		 * @param static $item Model object.
		 * @param array $data Model data array.
		 *
		 * @since 1.0.0
		 */
		do_action( $this->get_hook_prefix() . '_deleted', $this, $data );

		$this->set_defaults();

		return $data;
	}

	/**
	 * Saves an object in the database.
	 *
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 * @since 1.0.0
	 */
	public function save() {

		/**
		 * Filters whether an item should be checked.
		 *
		 * @param static $object Model object.
		 *
		 * @since 1.0.0
		 */
		$check = apply_filters( $this->get_hook_prefix() . '_sanitize_data', null, $this );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		/**
		 * Fires immediately before the object is inserted or updated in the database.
		 *
		 * @param static $object The object.
		 *
		 * @since 1.0.0
		 */
		do_action( $this->get_hook_prefix() . '_pre_save', $this );

		if ( ! $this->exists() ) {
			$is_error = $this->create();
		} else {
			$is_error = $this->update();
		}

		if ( is_wp_error( $is_error ) ) {
			return $is_error;
		}

		$this->save_metadata();
		$this->apply_changes();

		// Clear cache.
		$this->flush_cache();

		/**
		 * Fires immediately after a key is inserted or updated in the database.
		 *
		 * @param int $id Key id.
		 * @param static $object The object.
		 *
		 * @since 1.0.0
		 */
		do_action( $this->get_hook_prefix() . '_saved', $this );

		return true;
	}

	/**
	 * Get raw metadata.
	 *
	 * @param bool $force Force to read metadata.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function read_metadata( $force = false ) {
		if ( $this->meta_type && $this->exists() && ( $force || ! $this->metadata_read ) ) {
			global $wpdb;
			$object_id = $this->get_prop( $this->primary_key );
			// replace meta from the end of the meta table name. Then add id.
			$object_id_field = $this->meta_type . '_id';
			$meta_id_field   = 'user' === $this->meta_type ? 'umeta_id' : 'meta_id';
			$metadata        = wp_cache_get( "meta:$object_id", $this->cache_group );

			if ( false === $metadata ) {
				$table    = $wpdb->prefix . $this->meta_type . 'meta';
				$metadata = $wpdb->get_results( $wpdb->prepare( "SELECT $meta_id_field as meta_id, meta_key, meta_value FROM {$table} WHERE $object_id_field = %d ORDER BY $meta_id_field ASC", $object_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				wp_cache_set( "meta:$object_id", $metadata, $this->cache_group );
			}
			/**
			 * Filters the meta data for a specific meta object.
			 *
			 * @param array $metadata Array of metadata for the given object.
			 * @param static $object Object object.
			 *
			 * @since 1.0.0
			 */
			$metadata            = apply_filters( $this->get_hook_prefix() . '_metadata', $metadata, $this );
			$this->metadata_read = true;
			$this->metadata      = array_map(
				function ( $meta ) {
					return (object) array(
						'id'      => $meta->meta_id,
						'key'     => $meta->meta_key,
						'initial' => maybe_unserialize( $meta->meta_value ),
						'value'   => maybe_unserialize( $meta->meta_value ),
					);
				},
				$metadata
			);

		}

		return $this->metadata;
	}

	/**
	 * Update Meta Model in the database.
	 *
	 * @since 1.0.0
	 */
	public function save_metadata() {
		if ( $this->meta_type && $this->exists() ) {
			// Get changed meta data.
			$changed_metadata = array_filter(
				$this->read_metadata(),
				function ( $meta ) {
					return ! $this->is_equal( $meta->initial, $meta->value );
				}
			);
			// If id is not set, we need to add the metadata. if id is set, we need to update the metadata. if value is null, we need to delete the metadata.
			foreach ( $changed_metadata as $key => $meta ) {
				if ( is_null( $meta->id ) && ! empty( $meta->value ) ) {
					$meta_id                         = add_metadata( $this->meta_type, $this->get_prop( $this->primary_key ), $meta->key, $meta->value );
					$this->metadata[ $key ]->id      = $meta_id;
					$this->metadata[ $key ]->initial = $meta->value;
				} elseif ( empty( $meta->value ) && ! is_null( $meta->id ) ) {
					delete_metadata_by_mid( $this->meta_type, $meta->id );
					unset( $this->metadata[ $key ] );
				} elseif ( ! is_null( $meta->id ) && ! $this->is_equal( $meta->initial, $meta->value ) ) {
					update_metadata_by_mid( $this->meta_type, $meta->id, $meta->value );
					$this->metadata[ $key ]->initial = $meta->value;
				}
			}

			// Clear cache.
			$object_id = $this->get_prop( $this->primary_key );
			wp_cache_delete( "meta:$object_id", $this->cache_group );
		}
	}

	/**
	 * Delete all meta data.
	 *
	 * @since 1.0.0
	 */
	protected function delete_metadata() {
		global $wpdb;
		if ( $this->meta_type && $this->exists() ) {
			$object_id       = $this->get_prop( $this->primary_key );
			$object_id_field = $this->meta_type . '_id';
			$table           = $wpdb->prefix . $this->meta_type . 'meta';
			$wpdb->delete(
				$table,
				array(
					$object_id_field => $object_id,
				)
			);

			$this->metadata = array();
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Query Methods
	|--------------------------------------------------------------------------
	|
	| Methods for reading and manipulating the object properties.
	|
	*/
	/**
	 * Retrieve the object instance.
	 *
	 * @param int|array|static $data Object ID or array of arguments.
	 *
	 * @return static|false Object instance on success, false on failure.
	 * @since 1.0.0
	 */
	public static function get( $data ) {
		return ( new static() )->find( $data );
	}

	/**
	 * Insert or update an object in the database.
	 *
	 * @param array|object $data Model to insert or update.
	 * @param boolean      $wp_error Optional. Whether to return a WP_Error on failure. Default false.
	 *
	 * @return static|false|\WP_Error Object instance (success), false (failure), or WP_Error.
	 */
	public static function insert( $data, $wp_error = true ) {
		if ( is_object( $data ) ) {
			$data = get_object_vars( $data );
		}

		if ( ! is_array( $data ) || empty( $data ) ) {
			return false;
		}
		/**
		 * Variable type hints.
		 *
		 * @var $object static The object instance.
		 */
		$primary_key = ( new static() )->get_primary_key();
		$class       = new \ReflectionClass( get_called_class() );
		$id          = isset( $data[ $primary_key ] ) ? $data[ $primary_key ] : 0;
		$object      = $class->newInstance( $id );
		$object->set_data( $data );
		$save = $object->save();

		if ( is_wp_error( $save ) ) {
			if ( $wp_error ) {
				return $save;
			} else {
				return false;
			}
		}

		return $object->exists() ? $object : false;
	}
	/**
	 * Query for objects.
	 *
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return int|static[]|object[]|int[]|string[] Query results.
	 * @since 1.0.0
	 */
	public static function query( $args = array() ) {
		return ( new static() )->all( $args );
	}

	/**
	 * Get count of objects.
	 *
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return int Count of objects.
	 * @since 1.0.0
	 */
	public static function count( $args = array() ) {
		$args['count'] = true;

		return ( new static() )->all( $args );
	}

	/**
	 * Retrieve the object instance.
	 *
	 * @param int|array|static $data Object ID or array of arguments.
	 *
	 * @return static|false Object instance on success, false on failure.
	 * @since 1.0.0
	 */
	public function find( $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		// If It's array, then assume its args.
		if ( is_array( $data ) ) {
			$args['no_count'] = true;
			$args             = $data;
			$items            = $this->all( $args );
			if ( ! empty( $items ) && is_array( $items ) ) {
				return reset( $items );
			}

			return false;
		}

		if ( is_a( $data, __CLASS__ ) ) {
			$data = $data->get_prop( $this->primary_key );
		} elseif ( is_object( $data ) ) {
			$data = get_object_vars( $data );
			$data = ! empty( $data[ $this->primary_key ] ) ? $data[ $this->primary_key ] : null;
		}

		$record = new static( $data );
		if ( $record->exists() ) {
			return $record;
		}

		return false;
	}

	/**
	 * Query for objects.
	 *
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return int|static[]|object[]|int[]|string[] Query results.
	 * @since 1.0.0
	 */
	public function all( $args = array() ) {
		global $wpdb;
		$args     = $this->prepare_query_args( $args );
		$is_count = $this->string_to_bool( $args['count'] );
		$no_count = $this->string_to_bool( $args['no_count'] );
		unset( $args['count'], $args['no_count'] );
		$clauses      = $this->get_query_clauses( $args );
		$clauses      = $this->prepare_query_clauses( $clauses, $args );
		$last_changed = wp_cache_get_last_changed( $this->cache_group );
		$cache_key    = $this->cache_group . ':' . md5( wp_json_encode( $clauses ) ) . ':' . $last_changed;
		$result       = wp_cache_get( $cache_key, $this->cache_group );

		if ( false === $result ) {
			// Go through each clause and add it to the query.
			$clauses = array_map( 'trim', $clauses );
			$fields  = isset( $clauses['select'] ) ? $clauses['select'] : '*';
			$from    = isset( $clauses['from'] ) ? $clauses['from'] : "$wpdb->{$this->table_name} as $this->table_name";
			$join    = isset( $clauses['join'] ) ? $clauses['join'] : '';
			$where   = ! empty( $clauses['where'] ) ? $clauses['where'] : '';
			$groupby = ! empty( $clauses['groupby'] ) ? ' GROUP BY ' . $clauses['groupby'] : '';
			$having  = ! empty( $clauses['having'] ) ? ' HAVING ' . $clauses['having'] : '';
			$orderby = ! empty( $clauses['orderby'] ) ? ' ORDER BY ' . $clauses['orderby'] : '';
			$limit   = ! empty( $clauses['limit'] ) ? ' LIMIT ' . $clauses['limit'] : '';
			if ( ! $no_count ) {
				$fields = ' SQL_CALC_FOUND_ROWS ' . $fields;
			}

			$query = "SELECT $fields FROM $from $join WHERE 1=1 $where $groupby $having $orderby $limit";
			// var dump the query, no carecter limit.
			$total = 0;
			if ( is_array( $args['fields'] ) || 'all' === $args['fields'] ) {
				$items = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			} else {
				$items = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			if ( ! $no_count ) {
				/**
				 * Filter the query result items.
				 *
				 * @param string $sql SQL for finding the item count.
				 * @param array $items Query items.
				 * @param array $args Query arguments.
				 * @param array $clauses Query clauses.
				 *
				 * @since 1.0.0
				 */
				$found_rows = apply_filters( $this->get_hook_prefix() . '_found_rows', 'SELECT FOUND_ROWS()', $items, $args, compact( 'from', 'join', 'where', 'groupby', 'having', 'orderby', 'limit' ) );
				$total      = (int) $wpdb->get_var( $found_rows ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			/**
			 * Filter the query result items.
			 *
			 * @param array $items Query items.
			 * @param array $args Query arguments.
			 *
			 * @since 1.0.0
			 */
			$items = apply_filters( $this->get_hook_prefix() . '_query_items', $items, $args );

			$result = (object) array(
				'items' => $items,
				'total' => $total,
			);

			wp_cache_add( $cache_key, $result, $this->cache_group );
		}

		$items = isset( $result->items ) ? $result->items : array();
		$total = isset( $result->total ) ? absint( $result->total ) : 0;

		if ( in_array( 'all', $args['fields'], true ) ) {
			foreach ( $items as $key => $row ) {
				/**
				 * Filter the query result item.
				 *
				 * @param object $row Query item.
				 * @param array $args Query arguments.
				 *
				 * @since 1.0.0
				 */
				$row = (object) apply_filters( $this->get_hook_prefix() . '_db_data', (array) $row, $this );
				foreach ( $row as $column => $value ) {
					if ( is_serialized( $value ) ) {
						$row->$column = maybe_unserialize( $value );
					}
				}

				$this->set_cache( $row->{$this->primary_key}, $row );
				$item          = new static( $row );
				$items[ $key ] = $item;
			}

			// Based on output prepare the result.
			if ( ARRAY_A === $args['output'] ) {
				$items = wp_list_pluck( $items, 'data' );
			} elseif ( ARRAY_N === $args['output'] ) {
				$items = wp_list_pluck( $items, 'data' );
				foreach ( $items as $key => $data ) {
					$items[ $key ] = array_values( $data );
				}
			} elseif ( OBJECT === $args['output'] ) {
				$items = wp_list_pluck( $items, 'data' );
				foreach ( $items as $key => $data ) {
					$items[ $key ] = (object) $data;
				}
			}
		}

		if ( in_array( 'ids', $args['fields'], true ) ) {
			$items = wp_list_pluck( $items, 'id' );
			$items = array_map(
				function ( $id ) {
					return is_numeric( $id ) ? (int) $id : $id;
				},
				$items
			);
		}

		return $is_count ? $total : $items;
	}

	/**
	 * Parse query args.
	 *
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_query_args( $args = array() ) {
		$default = array(
			'orderby'     => 'id',
			'order'       => 'ASC',
			'search'      => '',
			'include'     => '',
			'exclude'     => '',
			'offset'      => '',
			'per_page'    => 20,
			'paged'       => 1,
			'no_count'    => false,
			'count'       => false,
			'where_query' => array(),
			'meta_query'  => array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'date_query'  => array(),
			'fields'      => 'all',
			'output'      => get_called_class(),
		);

		$args             = wp_parse_args( $args, $default );
		$args['no_count'] = $this->string_to_bool( $args['no_count'] );
		$args['count']    = $this->string_to_bool( $args['count'] );
		$args['fields']   = is_string( $args['fields'] ) ? preg_split( '/[,\s]+/', $args['fields'] ) : $args['fields'];

		if ( ! empty( $args['limit'] ) ) {
			$args['per_page'] = $args['limit'];
			unset( $args['limit'] );
		}

		if ( ! empty( $args['nopaging'] ) ) {
			$args['per_page'] = - 1;
			unset( $args['nopaging'] );
		}

		return $args;
	}

	/**
	 * Get query clauses.
	 *
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function get_query_clauses( $args = array() ) {
		// Query clauses.
		$clauses = array(
			'select'  => '',
			'from'    => '',
			'join'    => '',
			'where'   => '',
			'groupby' => '',
			'orderby' => '',
			'limit'   => '',
		);

		/**
		 * Filter the query clauses before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_query_clauses', $clauses, $args, $this );
	}

	/**
	 * Prepare query clauses.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Query arguments.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_query_clauses( $clauses, $args = array() ) {
		/**
		 * Filter the query clauses before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		$clauses = apply_filters( $this->get_hook_prefix() . '_pre_setup_query_clauses', $clauses, $args, $this );

		$clauses = $this->prepare_select_query( $clauses, $args );
		$clauses = $this->prepare_from_query( $clauses, $args );
		$clauses = $this->prepare_join_query( $clauses, $args );
		$clauses = $this->prepare_where_query( $clauses, $args );
		$clauses = $this->prepare_group_by_query( $clauses, $args );
		$clauses = $this->prepare_having_query( $clauses, $args );
		$clauses = $this->prepare_order_by_query( $clauses, $args );
		$clauses = $this->prepare_limit_query( $clauses, $args );

		/**
		 * Filter the query clauses after setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_query_clauses', $clauses, $args, $this );
	}

	/**
	 * Prepare fields query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_select_query( $clauses, $args = array() ) {
		foreach ( $args['fields'] as $field ) {
			if ( 'all' === $field ) {
				$clauses['select'] .= $this->table_name . '.*';
			} elseif ( 'ids' === $field ) {
				$clauses['select'] .= $this->table_name . '.id';
			} elseif ( in_array( $field, $this->get_core_data_keys(), true ) ) {
				$clauses['select'] .= "{$this->table_name}.{$field}";
			}
		}

		/**
		 * Filter the select clause before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_prepare_select_query', $clauses, $args, $this );
	}

	/**
	 * Prepare from query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_from_query( $clauses, $args = array() ) {
		global $wpdb;
		$clauses['from'] .= "{$wpdb->{$this->table_name}} as {$this->table_name}";

		/**
		 * Filter the from clause before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_prepare_from_query', $clauses, $args, $this );
	}

	/**
	 * Prepare where query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_where_query( $clauses, $args = array() ) {
		global $wpdb;
		/**
		 * Filter the where clause before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @since 1.0.0
		 */
		$clauses = apply_filters( $this->get_hook_prefix() . '_pre_setup_where_query', $clauses, $args, $this );

		$query_where = isset( $args['where_query'] ) ? $args['where_query'] : array();

		// Include clause.
		if ( ! empty( $args['include'] ) ) {
			$query_where[] = array(
				'column'  => "{$this->table_name}.id",
				'value'   => $args['include'],
				'compare' => 'IN',
			);
		}

		// Exclude clause.
		if ( ! empty( $args['exclude'] ) ) {
			$query_where[] = array(
				'column'  => "{$this->table_name}.id",
				'value'   => $args['exclude'],
				'compare' => 'NOT IN',
			);
		}

		foreach ( $this->get_core_data_keys() as $column ) {
			// equals clause.
			if ( ! empty( $args[ $column ] ) ) {
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column ],
					'compare' => false !== strpos( $column, '_ids' ) ? 'FIND_IN_SET' : '=', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				);
			} elseif ( ! empty( $args[ $column . '__in' ] ) ) {
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__in' ],
					'compare' => false !== strpos( $column, '_ids' ) ? 'FIND_IN_SET' : 'IN', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				);
			} elseif ( ! empty( $args[ $column . '__not_in' ] ) ) {
				// __not_in clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__not_in' ],
					'compare' => false !== strpos( $column, '_ids' ) ? 'NOT_IN_SET' : 'NOT IN', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				);
			} elseif ( ! empty( $args[ $column . '__between' ] ) ) {
				// __between clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__between' ],
					'compare' => 'BETWEEN',
				);
			} elseif ( ! empty( $args[ $column . '__not_between' ] ) ) {
				// __not_between clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__not_between' ],
					'compare' => 'NOT BETWEEN',
				);
			} elseif ( ! empty( $args[ $column . '__exists' ] ) ) {
				// __exists clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'compare' => 'EXISTS',
				);
			} elseif ( ! empty( $args[ $column . '__not_exists' ] ) ) {
				// __not_exists clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'compare' => 'NOT EXISTS',
				);
			} elseif ( ! empty( $args[ $column . '__like' ] ) ) {
				// __like clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__like' ],
					'compare' => 'LIKE',
				);
			} elseif ( ! empty( $args[ $column . '__not_like' ] ) ) {
				// __not_like clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__not_like' ],
					'compare' => 'NOT LIKE',
				);
			} elseif ( ! empty( $args[ $column . '__starts_with' ] ) ) {
				// __starts_with clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__starts_with' ],
					'compare' => 'LIKE',
				);
			} elseif ( ! empty( $args[ $column . '__ends_with' ] ) ) {
				// __ends_with clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__ends_with' ],
					'compare' => 'ENDS WITH',
				);
			} elseif ( ! empty( $args[ $column . '__is_null' ] ) ) {
				// __is_null clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'compare' => 'IS NULL',
				);
			} elseif ( ! empty( $args[ $column . '__is_not_null' ] ) ) {
				// __is_not_null clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'compare' => 'IS NOT NULL',
				);
			} elseif ( ! empty( $args[ $column . '__gt' ] ) ) {
				// __gt clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__gt' ],
					'compare' => 'GREATER THAN',
				);
			} elseif ( ! empty( $args[ $column . '__lt' ] ) ) {
				// __lt clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__lt' ],
					'compare' => 'LESS THAN',
				);
			} elseif ( ! empty( $args[ $column . '__find_in_set' ] ) ) {
				// find_in_set clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'compare' => 'FIND_IN_SET',
					'value'   => $args[ $column . '__find_in_set' ],
				);
			} elseif ( ! empty( $args[ $column . '__find_not_in_set' ] ) ) {
				// find_in_set clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'compare' => 'NOT_IN_SET',
					'value'   => $args[ $column . '__find_not_in_set' ],
				);
			} elseif ( ! empty( $args[ $column . '__regexp' ] ) ) {
				// __regexp clause.
				$query_where[] = array(
					'column'  => "{$this->table_name}.{$column}",
					'value'   => $args[ $column . '__regexp' ],
					'compare' => 'REGEXP',
				);
			}
		}

		// Parse each query where clause.
		foreach ( $query_where as $where_key => $where_clause ) {
			if ( ! is_numeric( $where_key ) && is_string( $where_clause ) ) {
				$where_clause = array(
					'column'  => $where_key,
					'value'   => $where_clause,
					'compare' => '=',
				);
			}
			$where_clause = wp_parse_args(
				$where_clause,
				array(
					'column'  => '',
					'value'   => '',
					'compare' => '=',
				)
			);

			$where_column  = $where_clause['column'];
			$where_value   = $where_clause['value'];
			$where_compare = strtoupper( $where_clause['compare'] );
			// Column is not valid or empty. Skip.
			if ( empty( $where_column ) || ! in_array( str_replace( "{$this->table_name}.", '', $where_column ), $this->get_core_data_keys(), true ) ) {
				continue;
			}
			// Validate value.
			if ( ! is_array( $where_value ) && ! is_numeric( $where_value ) && ! is_string( $where_value ) ) {
				continue;
			}
			if ( in_array( $where_compare, array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ), true ) && ! is_array( $where_value ) ) {
				$where_value = preg_split( '/[,\s]+/', $where_value );
			} elseif ( is_string( $where_value ) ) {
				$where_value = trim( $where_value );
			}

			// Make sql query based on compare.
			switch ( $where_compare ) {
				case 'IN':
				case 'NOT IN':
					if ( empty( $where_value ) ) {
						continue 2;
					}
					$placeholders      = array_fill( 0, count( $where_value ), '%s' );
					$format            = "AND (  $where_column $where_compare (" . implode( ', ', $placeholders ) . ') )';
					$clauses['where'] .= $wpdb->prepare( $format, $where_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'BETWEEN':
				case 'NOT BETWEEN':
					if ( empty( $where_value ) || ! is_array( $where_value ) || count( $where_value ) < 2 ) {
						continue 2;
					}
					$placeholder       = '%s';
					$format            = "AND ( $where_column $where_compare $placeholder AND $placeholder )";
					$clauses['where'] .= $wpdb->prepare( $format, $where_value[0], $where_value[1] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'LIKE':
				case 'NOT LIKE':
					$format            = "AND ( $where_column $where_compare %s )";
					$clauses['where'] .= $wpdb->prepare( $format, '%' . $wpdb->esc_like( $where_value ) . '%' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'EXISTS':
				case 'NOT EXISTS':
					$format            = "AND ( $where_compare (SELECT 1 FROM {$this->table_name} WHERE  $where_column = %s) )";
					$clauses['where'] .= $wpdb->prepare( $format, $where_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'RLIKE':
					$format            = "AND (  $where_column REGEXP %s )";
					$clauses['where'] .= $wpdb->prepare( $format, $where_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'ENDS WITH':
					$format            = "AND (  $where_column LIKE %s )";
					$clauses['where'] .= $wpdb->prepare( $format, '%' . $wpdb->esc_like( $where_value ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'STARTS WITH':
					$format            = "AND (  $where_column LIKE %s )";
					$clauses['where'] .= $wpdb->prepare( $format, $wpdb->esc_like( $where_value ) . '%' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'IS NULL':
				case 'IS NOT NULL':
					$format            = "AND ( $where_column $where_compare )";
					$clauses['where'] .= $wpdb->prepare( $format, $where_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'GREATER THAN':
					$placeholder       = is_numeric( $where_value ) ? '%d' : '%s';
					$format            = "AND ( $where_column > $placeholder )";
					$clauses['where'] .= $wpdb->prepare( $format, $where_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'LESS THAN':
					$placeholder       = is_numeric( $where_value ) ? '%d' : '%s';
					$format            = "AND ( $where_column < $placeholder )";
					$clauses['where'] .= $wpdb->prepare( $format, $where_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'FIND_IN_SET':
					$clause            = is_array( $where_value ) ? 'REGEXP' : 'FIND_IN_SET';
					$where_value       = is_array( $where_value ) ? implode( '|', $where_value ) : $where_value;
					$format            = 'REGEXP' === $clause ? "AND ( $where_column REGEXP %s )" : "AND ( FIND_IN_SET( %s, $where_column ) > 0 )";
					$clauses['where'] .= $wpdb->prepare( $format, $where_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'NOT_IN_SET':
					$clause            = is_array( $where_value ) ? 'NOT REGEXP' : 'FIND_IN_SET';
					$where_value       = is_array( $where_value ) ? implode( '|', $where_value ) : $where_value;
					$format            = 'NOT REGEXP' === $clause ? "AND ( $where_column NOT REGEXP %s )" : "AND ( FIND_IN_SET( %s, $where_column ) = 0 )";
					$clauses['where'] .= $wpdb->prepare( $format, $where_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
				case 'REGEXP':
				case 'NOT REGEXP':
				default:
					// Placeholder based on type.
					$placeholder       = is_numeric( $where_value ) ? '%d' : '%s';
					$format            = "AND (  $where_column $where_compare $placeholder )";
					$clauses['where'] .= $wpdb->prepare( $format, $where_value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
			}
		}

		$clauses = $this->prepare_search_query( $clauses, $args );
		$clauses = $this->prepare_date_query( $clauses, $args );
		$clauses = $this->prepare_meta_query( $clauses, $args );

		/**
		 * Filter the where clause before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_where_query', $clauses, $args, $this );
	}

	/**
	 * Prepare meta query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_meta_query( $clauses, $args = array() ) {
		if ( ! $this->meta_type ) {
			return $clauses;
		}

		/**
		 * Filter the meta query before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		$clauses = apply_filters( $this->get_hook_prefix() . '_pre_setup_meta_query', $clauses, $args, $this );

		if ( ! empty( $args['meta_key'] ) || ! empty( $args['meta_query'] ) ) {
			$meta_query = new \WP_Meta_Query();
			$meta_query->parse_query_vars( $args );
			if ( ! empty( $meta_query->queries ) ) {
				$meta_clauses      = $meta_query->get_sql( $this->meta_type, $this->table_name, 'id' );
				$clauses['join']  .= $meta_clauses['join'];
				$clauses['where'] .= $meta_clauses['where'];
				if ( $meta_query->has_or_relation() ) {
					$clauses['groupby'] .= empty( $clauses['groupby'] ) ? $this->table_name . '.id' : ', ' . $this->table_name . '.id';
				}
			}
		}

		/**
		 * Filter the meta query after setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_meta_query', $clauses, $args, $this );
	}

	/**
	 * Prepare date query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_date_query( $clauses, $args = array() ) {
		/**
		 * Filter the date query before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		$clauses = apply_filters( $this->get_hook_prefix() . '_pre_setup_date_query', $clauses, $args, $this );

		if ( ! empty( $args['date_query'] ) ) {
			$wp_columns = array();
			// Whitelist our column.
			add_filter(
				'date_query_valid_columns',
				function ( $cols ) use ( $wp_columns ) {
					$wp_columns = $cols;

					return $this->get_core_data_keys();
				}
			);

			foreach ( $args['date_query'] as $date_query ) {
				$date_query = wp_parse_args(
					$date_query,
					array(
						'column'    => '',
						'after'     => '',
						'before'    => '',
						'inclusive' => true,
					)
				);

				if ( empty( $date_query['column'] ) || ! in_array( $date_query['column'], $this->get_core_data_keys(), true ) ) {
					continue;
				}

				$date_query = new \WP_Date_Query( $date_query );
				if ( ! empty( $date_query->queries ) ) {
					$clauses['where'] .= $date_query->get_sql();
				}
			}

			// Restore the original columns.
			add_filter(
				'date_query_valid_columns',
				function () use ( $wp_columns ) {
					return $wp_columns;
				}
			);
		}

		/**
		 * Filter the date query after setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $object Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_date_query', $clauses, $args, $this );
	}

	/**
	 * Prepare search query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_search_query( $clauses, $args = array() ) {
		global $wpdb;
		/**
		 * Filter the search query before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		$clauses = apply_filters( $this->get_hook_prefix() . '_pre_setup_search_query', $clauses, $args, $this );

		if ( ! empty( $args['search'] ) ) {
			$search = $args['search'];
			if ( ! empty( $args['search_columns'] ) ) {
				$search_columns = wp_parse_list( $args['search_columns'] );
			} else {
				/**
				 * Filter the columns to search in when performing a search query.
				 *
				 * @param array $search_columns Array of columns to search in.
				 * @param array $args Query arguments.
				 * @param static $object Current instance of the class.
				 *
				 * @return array
				 * @since 1.0.0
				 */
				$search_columns = apply_filters( $this->get_hook_prefix() . '_search_columns', $this->get_searchable_keys(), $args, $this );
			}
			$search_columns = array_filter( array_unique( $search_columns ) );
			$like           = '%' . $wpdb->esc_like( $search ) . '%';

			$search_clauses = array();
			foreach ( $search_columns as $column ) {
				$search_clauses[] = $wpdb->prepare( $this->table_name . '.' . $column . ' LIKE %s', $like ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			if ( ! empty( $search_clauses ) ) {
				$clauses['where'] .= 'AND (' . implode( ' OR ', $search_clauses ) . ')';
			}
		}

		/**
		 * Filter the search query after setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_search_query', $clauses, $args, $this );
	}

	/**
	 * Prepare join query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_join_query( $clauses, $args = array() ) {
		/**
		 * Filter the join query before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $object Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */

		$clauses = apply_filters( $this->get_hook_prefix() . '_pre_setup_join_query', $clauses, $args, $this );

		/**
		 * Filter the join query after setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $this Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_join_query', $clauses, $args, $this );
	}

	/**
	 * Prepare group by query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_group_by_query( $clauses, $args = array() ) {
		/**
		 * Filter the group by query before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $object Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		$clauses = apply_filters( $this->get_hook_prefix() . '_pre_setup_group_by_query', $clauses, $args, $this );

		/**
		 * Filter the group by query after setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $object Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_group_by_query', $clauses, $args, $this );
	}

	/**
	 * Prepare having query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_having_query( $clauses, $args = array() ) {
		/**
		 * Filter the having query before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $object Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		$clauses = apply_filters( $this->get_hook_prefix() . '_pre_setup_having_query', $clauses, $args, $this );

		/**
		 * Filter the having query after setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $object Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_having_query', $clauses, $args, $this );
	}

	/**
	 * Prepare order by query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_order_by_query( $clauses, $args = array() ) {
		/**
		 * Filter the order by query before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $object Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		$clauses = apply_filters( $this->get_hook_prefix() . '_pre_setup_order_by_query', $clauses, $args, $this );

		// Check if order is already a sql clause.
		if ( empty( $clauses['orderby'] ) ) {
			if ( 'rand' === $args['orderby'] ) {
				$clauses['orderby'] = 'RAND()';
			} elseif ( ! empty( $args['orderby'] ) ) {
				$orderby = $args['orderby'];
				$order   = strtoupper( $args['order'] );
				if ( ! is_array( $orderby ) ) {
					// convert comma separated string to associative array.
					$orderby = explode( ',', $orderby );
					$orderby = array_map( 'trim', $orderby );
					foreach ( $orderby as $key => $value ) {
						$value                = explode( ' ', $value );
						$orderby[ $value[0] ] = isset( $value[1] ) ? $value[1] : $order;
						unset( $orderby[ $key ] );
					}
				}
				foreach ( $orderby as $key => $value ) {
					if ( ! in_array( strtoupper( $value ), array( 'ASC', 'DESC' ), true ) ) {
						$orderby[ $key ] = 'ASC';
					}
				}
				foreach ( $orderby as $key => $value ) {
					if ( in_array( $key, $this->get_core_data_keys(), true ) ) {
						$clauses['orderby'] .= "{$this->table_name}.$key $value";
					}
				}
			} else {
				$clauses['orderby'] .= "{$this->table_name}.id {$args['order']}";
			}
		}

		/**
		 * Filter the order by query after setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $object Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_order_by_query', $clauses, $args, $this );
	}

	/**
	 * Prepare limit query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected function prepare_limit_query( $clauses, $args = array() ) {
		/**
		 * Filter the limit query before setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $object Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		$clauses = apply_filters( $this->get_hook_prefix() . '_pre_setup_limit_query', $clauses, $args, $this );

		// Limit clause.
		if ( intval( $args['per_page'] ) > 0 ) {
			$page = intval( $args['paged'] );
			if ( ! $page ) {
				$page = 1;
			}
			// If 'offset' is provided, it takes precedence over 'paged'.
			if ( isset( $args['offset'] ) && is_numeric( $args['offset'] ) ) {
				$args['offset'] = absint( $args['offset'] );
				$pgstrt         = $args['offset'] . ', ';
			} else {
				$pgstrt = absint( ( $page - 1 ) * $args['per_page'] ) . ', ';
			}

			$clauses['limit'] = $pgstrt . absint( $args['per_page'] );
		}

		/**
		 * Filter the limit query after setting up the query.
		 *
		 * @param array $clauses Query clauses.
		 * @param array $args Query arguments.
		 * @param static $object Current instance of the class.
		 *
		 * @return array
		 * @since 1.0.0
		 */
		return apply_filters( $this->get_hook_prefix() . '_setup_limit_query', $clauses, $args, $this );
	}


	/*
	|--------------------------------------------------------------------------
	| Getters and Setters
	|--------------------------------------------------------------------------
	| Methods to get and set the model's properties.
	*/

	/**
	 * Gets a prop for a getter method.
	 *
	 * Gets the value from either current pending changes, or the data itself.
	 * Context controls what happens to the value before it's returned.
	 *
	 * @param string $prop Name of prop to get.
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @return mixed
	 * @since  1.0.0
	 */
	protected function get_prop( $prop, $context = 'edit' ) {
		$value = null;
		if ( array_key_exists( $prop, $this->data ) ) {
			$value = isset( $this->changes[ $prop ] ) ? $this->changes[ $prop ] : $this->data[ $prop ];
		}

		if ( 'view' === $context ) {
			$value = apply_filters( $this->get_hook_prefix() . '_get_' . $prop, $value, $this );
		}

		return $value;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * Sets the value to the changes array, and if the model is read, also sets the value to the data array.
	 *
	 * @param string $prop Name of prop to set.
	 * @param mixed  $value Value to set.
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
	 * Set the model's data.
	 *
	 * @param array|object $props Array or object of properties to set.
	 *
	 * @return static
	 * @since 1.0.0
	 */
	public function set_data( $props ) {
		if ( is_object( $props ) ) {
			$props = get_object_vars( $props );
		}
		if ( ! is_array( $props ) ) {
			return $this;
		}

		foreach ( $props as $prop => $value ) {
			$prop = preg_replace( '/^[^a-zA-Z]+/', '', $prop );
			// If the property name is id, then we will skip it.
			// if value is array, call the same function for each item.
			if ( 'metadata' === $prop && is_array( $value ) ) {
				$this->set_data( $value );

				return $this;
			} elseif ( is_callable( array( $this, "set_$prop" ) ) ) {
				$this->{"set_$prop"}( $value );
			} else {
				$this->set_prop( $prop, $value );
			}
		}

		return $this;
	}

	/**
	 * Get the model's data.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_data() {
		$data = array();
		foreach ( $this->data as $key => $value ) {
			if ( is_object( $value ) && method_exists( $value, 'get_data' ) ) {
				$data[ $key ] = $value->get_data();
			} else {
				$data[ $key ] = $this->get_prop( $key, 'edit' );
			}
		}

		return $data;
	}

	/**
	 * Get meta data.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_metadata() {
		$metadata = $this->read_metadata();
		$meta     = array();
		foreach ( $metadata as $meta_value ) {
			if ( ! is_null( $meta_value->value ) ) {
				$meta[ $meta_value->key ][] = $meta_value->value;
			}
		}

		return $meta;
	}

	/**
	 * Get meta data.
	 *
	 * @param string $key Meta key.
	 * @param bool   $single Single.
	 *
	 * @return array|mixed|null
	 * @since 1.0.0
	 */
	public function get_meta( $key, $single = true ) {
		$meta = array_filter(
			$this->read_metadata(),
			function ( $meta ) use ( $key ) {
				return $meta->key === $key;
			}
		);

		if ( $single ) {
			$meta = current( $meta );

			return $meta ? $meta->value : null;
		}

		// get all values without keys.
		return array_values(
			array_map(
				function ( $meta ) {
					return $meta->value;
				},
				$meta
			)
		);
	}

	/**
	 * Set meta data.
	 *
	 * @param string $key Meta key.
	 * @param string $value Meta value.
	 * @param bool   $single Single.
	 *
	 * @since 1.0.0
	 */
	public function set_meta( $key, $value, $single = true ) {
		$meta = array_filter(
			$this->read_metadata(),
			function ( $meta ) use ( $key ) {
				return $meta->key === $key;
			}
		);

		if ( $single ) {
			$meta = array_map(
				function ( $meta ) {
					$meta->value = null;

					return $meta;
				},
				$meta
			);
		}

		if ( ! empty( $meta ) && $single ) {
			$index                 = array_search( reset( $meta ), $this->metadata, true );
			$meta[ $index ]->value = $value;
		} else {
			$this->metadata[] = (object) array(
				'id'      => null,
				'initial' => null,
				'key'     => $key,
				'value'   => $value,
			);
		}
	}

	/**
	 * Delete meta data.
	 *
	 * @param string $key Meta key.
	 * @param string $value Meta value.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function delete_meta( $key, $value = null ) {
		if ( is_null( $value ) ) {
			$this->metadata = array_filter(
				$this->read_metadata(),
				function ( $meta ) use ( $key ) {
					if ( $meta->key === $key ) {
						$meta->value = null;
					}

					return $meta;
				}
			);
		} else {
			$this->metadata = array_filter(
				$this->read_metadata(),
				function ( $meta ) use ( $key, $value ) {
					if ( $meta->key === $key && $this->is_equal( $meta->value, $value ) ) {
						$meta->value = null;
					}

					return $meta;
				}
			);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	| Methods which do not modify class properties but are used by the class.
	*/

	/**
	 * Get primary key.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_primary_key() {
		return $this->primary_key;
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_table_name() {
		return $this->table_name;
	}

	/**
	 * Get meta type.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_meta_type() {
		return $this->meta_type;
	}

	/**
	 * Get object type.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_object_type() {
		return $this->object_type;
	}

	/**
	 * Get cache group.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_cache_group() {
		return $this->cache_group;
	}


	/**
	 * Get searchable keys.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_searchable_keys() {
		return $this->get_core_data_keys();
	}

	/**
	 * Get core data.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_core_data() {
		return wp_array_slice_assoc( $this->get_data(), $this->get_core_data_keys() );
	}

	/**
	 * Get extra data.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_extra_data() {
		return wp_array_slice_assoc( $this->get_data(), array_keys( $this->extra_data ) );
	}

	/**
	 * Get core data keys.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_core_data_keys() {
		return array_keys( $this->core_data );
	}

	/**
	 * Return data changes only.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_changes() {
		return $this->changes;
	}

	/**
	 * Set object read property.
	 *
	 * @param boolean $read Should read?.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function set_object_read( $read = true ) {
		$this->object_read = (bool) $read;
	}

	/**
	 * Get object read property.
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_object_read() {
		return true === $this->object_read;
	}

	/**
	 * Get cache.
	 *
	 * @param int|string $key Primary key.
	 *
	 * @return mixed|false Value on success, false on failure.
	 */
	protected function get_cache( $key ) {
		return wp_cache_get( $key, $this->cache_group );
	}

	/**
	 * Set cache.
	 *
	 * @param string|int $key Key.
	 * @param mixed      $value Value.
	 */
	protected function set_cache( $key, $value ) {
		if ( ! empty( $value ) ) {
			wp_cache_set( $key, $value, $this->cache_group );
		}
	}

	/**
	 * Delete cache.
	 *
	 * @param string|int $key Key.
	 */
	protected function delete_cache( $key ) {
		wp_cache_delete( $key, $this->cache_group );
	}

	/**
	 * Flush cache.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function flush_cache() {
		wp_cache_flush_group( $this->cache_group );
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/**
	 * Get the hook prefix.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_hook_prefix() {
		$hook = $this->hook_prefix . $this->object_type;
		// ensure only one _ at a time and trailing _ is removed.
		$hook = preg_replace( '/_+/', '_', $hook );
		$hook = rtrim( $hook, '_' );

		return $hook;
	}

	/**
	 * Checks if the object is saved in the database
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function exists() {
		return ! empty( $this->get_prop( $this->primary_key ) );
	}

	/**
	 * Merge changes with data and clear.
	 *
	 * @since 1.0.0
	 */
	protected function apply_changes() {
		$this->data    = array_replace_recursive( $this->data, $this->changes );
		$this->changes = array();

		if ( $this->meta_type && ! empty( $this->metadata ) ) {
			$this->metadata = array_map(
				function ( $meta ) {
					$meta->initial = $meta->value;

					return $meta;
				},
				$this->metadata
			);
		}
	}

	/**
	 * Reset data.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function set_defaults() {
		$this->data          = array_merge_recursive( $this->core_data, $this->extra_data );
		$this->changes       = array();
		$this->object_read   = false;
		$this->metadata_read = false;
		$this->metadata      = array();
	}

	/**
	 * Compare if the value of 2 given data is equal. If the data is an array, it will be serialized before comparing.
	 * This is useful for comparing metadata.
	 *
	 * @param mixed $data1 First data to compare.
	 * @param mixed $data2 Second data to compare.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_equal( $data1, $data2 ) {
		$temp = array( $data1, $data2 );
		foreach ( $temp as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$temp[ $key ] = (string) $value;
			} else {
				$temp[ $key ] = maybe_serialize( $value );
			}
		}

		return $temp[0] === $temp[1];
	}


	/**
	 * Convert string to boolean.
	 *
	 * @param string $value Value to convert.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function string_to_bool( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return (bool) $value;
		}

		if ( is_string( $value ) ) {
			$value = strtolower( $value );
			if ( 'true' === $value || 'yes' === $value || '1' === $value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Convert boolean to string.
	 *
	 * @param bool $value Value to convert.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function bool_to_string( $value ) {
		if ( is_string( $value ) ) {
			if ( 'true' === $value || 'yes' === $value || '1' === $value ) {
				return 'yes';
			}

			return 'no';
		}

		if ( is_numeric( $value ) ) {
			return (bool) $value ? 'yes' : 'no';
		}

		if ( is_bool( $value ) ) {
			return $value ? 'yes' : 'no';
		}

		return 'no';
	}

	/**
	 * Convert string to integer.
	 *
	 * @param string $value Value to convert.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function string_to_int( $value ) {
		if ( is_int( $value ) ) {
			return $value;
		}

		if ( is_numeric( $value ) ) {
			return (int) $value;
		}

		if ( is_bool( $value ) ) {
			return $value ? 1 : 0;
		}

		if ( is_string( $value ) ) {
			return $this->string_to_int( $this->string_to_bool( $value ) );
		}

		return 0;
	}

	/**
	 * Checks if a date is valid or not.
	 *
	 * @param string $date Date to check.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function is_date_valid( $date ) {
		if ( empty( preg_replace( '/[^0-9]/', '', $date ) ) ) {
			return false;
		}

		return (bool) strtotime( $date );
	}

	/**
	 * Sanitize date property.
	 * If the date is a valid date, it will be returned to the given format.
	 *
	 * @param string $date Date.
	 *
	 * @return string|null
	 * @since 1.0.0
	 */
	public function sanitize_date( $date ) {
		if ( empty( $date ) || '0000-00-00 00:00:00' === $date || '0000-00-00' === $date ) {
			return null;
		}

		if ( ! $this->is_date_valid( $date ) ) {
			return null;
		}

		// get the date format from the given date.
		$length = strlen( $date );
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

		$d = \DateTime::createFromFormat( $format, $date );

		return $d && $d->format( $format ) === $date ? $d->format( $format ) : null;
	}

	/**
	 * Get date prop
	 *
	 * @param string $prop Name of prop to get.
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 * @param string $format Date format.
	 *
	 * @return string|null
	 * @since 1.0.0
	 */
	public function get_date_prop( $prop, $context = 'edit', $format = 'Y-m-d H:i:s' ) {
		$datetime = $this->sanitize_date( $this->get_prop( $prop ) );

		$value = $datetime ? date( $format, strtotime( $datetime ) ) : null; // @codingStandardsIgnoreLine - date() is ok here.

		if ( 'view' === $context ) {
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
	 */
	public function set_date_prop( $prop, $value, $format = 'Y-m-d H:i:s' ) {
		$date = $this->sanitize_date( $value );
		if ( ! empty( $date ) ) {
			$date = date( $format, strtotime( $date ) ); // @codingStandardsIgnoreLine - date() is ok here.
		}
		$this->set_prop( $prop, $date );
	}

	/**
	 * Get Boolean prop.
	 *
	 * @param string $prop Name of prop to get.
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function get_boolean_prop( $prop, $context = 'edit' ) {
		$value = (bool) $this->string_to_bool( $this->get_prop( $prop ) );

		if ( 'view' === $context ) {
			$value = apply_filters( $this->get_hook_prefix() . '_get_' . $prop, $value, $this );
		}

		return $value;
	}
}
