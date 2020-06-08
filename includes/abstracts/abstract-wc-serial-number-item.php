<?php
defined( 'ABSPATH' ) || exit();

abstract class WC_Serial_Number_Item {

	/**
	 * Name of the table.
	 *
	 * @since 1.1.6
	 * @var string
	 */
	protected $table;

	/**
	 * Name of the object.
	 *
	 * @since 1.1.6
	 * @var string
	 */
	protected $object_name;

	/**
	 * ID for this object.
	 *
	 * @since 1.1.6
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.1.6
	 * @var array
	 */
	protected $data = array();


	/**
	 * Core data changes for this object.
	 *
	 * @since 1.1.6
	 * @var array
	 */
	protected $changes = array();


	/**
	 * Returns the unique ID for this object.
	 *
	 * @return int
	 * @since  1.1.6
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Delete an object, set the ID to 0, and return result.
	 *
	 * @return bool result
	 * @since  1.1.6
	 */
	public function delete() {
		$deleted = false;
		if ( ! empty( $this->table ) && ! empty( $this->get_id() ) ) {
			global $wpdb;
			$deleted = $wpdb->delete( $wpdb->prefix . $this->table, [ 'id' => $this->get_id() ] );
			$this->set_id( 0 );
		}

		return $deleted;
	}

	/**
	 * Save should create or update based on object existence.
	 *
	 * @return int
	 * @since  1.1.6
	 */
	public function save() {
		global $wpdb;

		if ( ! $this->data ) {
			return $this->get_id();
		}

		if ( $this->get_id() ) {
			$wpdb->update( $wpdb->prefix . $this->table, $this->data, [ 'id' => $this->get_id() ] );
		} else {
			if ( false === $wpdb->insert( $wpdb->prefix . $this->table, $this->data ) ) {
				$this->set_id( 0 );
			};
		}

		return $this->get_id();
	}

	/**
	 * Change data to JSON format.
	 *
	 * @return string Data in JSON format.
	 * @since  1.1.6
	 */
	public function __toString() {
		return wp_json_encode( $this->get_data() );
	}

	/**
	 * Returns all data for this object.
	 *
	 * @return array
	 * @since  1.1.6
	 */
	public function get_data() {
		return array_merge( array( 'id' => $this->get_id() ), $this->data );
	}

	/**
	 * Returns array of expected data keys for this object.
	 *
	 * @return array
	 * @since   3.0.0
	 */
	public function get_data_keys() {
		return array_keys( $this->data );
	}

	/**
	 * Set ID.
	 *
	 * @param int $id ID.
	 *
	 * @since 3.0.0
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
	}

	/**
	 * Set a collection of props in one go, collect any errors, and return the result.
	 * Only sets using public methods.
	 *
	 * @param array $props Key value pairs to set. Key is the prop and should map to a setter function name.
	 * @param string $context In what context to run this.
	 *
	 * @return bool|WP_Error
	 * @since  3.0.0
	 *
	 */
	public function set_props( $props, $context = 'set' ) {
		$errors = false;

		foreach ( $props as $prop => $value ) {
			try {
				/**
				 * Checks if the prop being set is allowed, and the value is not null.
				 */
				if ( is_null( $value ) || in_array( $prop, array( 'prop', 'date_prop', 'meta_data' ), true ) ) {
					continue;
				}
				$setter = "set_$prop";

				if ( is_callable( array( $this, $setter ) ) ) {
					$this->{$setter}( $value );
				}
			} catch ( WC_Data_Exception $e ) {
				if ( ! $errors ) {
					$errors = new WP_Error();
				}
				$errors->add( $e->getErrorCode(), $e->getMessage() );
			}
		}

		return $errors && count( $errors->get_error_codes() ) ? $errors : true;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * This stores changes in a special array so we can track what needs saving
	 * the the DB later.
	 *
	 * @param string $prop Name of prop to set.
	 * @param mixed $value Value of the prop.
	 *
	 * @since 3.0.0
	 */
	protected function set_prop( $prop, $value ) {
		if ( array_key_exists( $prop, $this->data ) ) {
			if ( $value !== $this->data[ $prop ] || array_key_exists( $prop, $this->changes ) ) {
				$this->changes[ $prop ] = $value;
			} else {
				$this->data[ $prop ] = $value;
			}
		}
	}

	/**
	 * Return data changes only.
	 *
	 * @return array
	 * @since 3.0.0
	 */
	public function get_changes() {
		return $this->changes;
	}

	/**
	 * Merge changes with data and clear.
	 *
	 * @since 3.0.0
	 */
	public function apply_changes() {
		$this->data    = array_replace_recursive( $this->data, $this->changes ); // @codingStandardsIgnoreLine
		$this->changes = array();
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * Gets the value from either current pending changes, or the data itself.
	 * Context controls what happens to the value before it's returned.
	 *
	 * @param string $prop Name of prop to get.
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return mixed
	 * @since  3.0.0
	 */
	protected function get_prop( $prop, $context = 'view' ) {
		$value = null;

		if ( array_key_exists( $prop, $this->data ) ) {
			$value = array_key_exists( $prop, $this->changes ) ? $this->changes[ $prop ] : $this->data[ $prop ];
			if ( 'view' === $context ) {
				$value = apply_filters( $this->get_hook_prefix() . $prop, $value, $this );
			}
		}

		return $value;
	}

	/**
	 * When invalid data is found, throw an exception unless reading from the DB.
	 *
	 * @param string $code Error code.
	 * @param string $message Error message.
	 * @param int $http_status_code HTTP status code.
	 * @param array $data Extra error data.
	 *
	 * @throws WC_Data_Exception Data Exception.
	 * @since 3.0.0
	 */
	protected function error( $code, $message, $http_status_code = 400, $data = array() ) {
		throw new WC_Data_Exception( $code, $message, $http_status_code, $data );
	}

}
