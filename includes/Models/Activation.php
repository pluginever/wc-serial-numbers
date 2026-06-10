<?php

namespace WooCommerceSerialNumbers\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activation.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 */
class Activation extends Model {
	/**
	 * The table associated with the model.
	 *
	 * This is also used as table alias.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'serial_numbers_activations';

	/**
	 * Object type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $object_type = 'activation';

	/**
	 * The cache group for the object type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $cache_group = 'serial_numbers_activations';

	/**
	 * The table columns of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'serial_id',
		'instance',
		'platform',
		'activation_time',
	);

	/**
	 * The model's attributes with their default values.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $attributes = array(
		'id'              => 0,
		'serial_id'       => 0,
		'instance'        => '',
		'platform'        => '',
		'activation_time' => '',
		// todo add ip address support.
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'id'        => 'int',
		'serial_id' => 'int',
	);

	/**
	 * The searchable attributes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $searchable = array(
		'id',
		'serial_id',
		'instance',
		'platform',
		'activation_time',
	);

	/*
	|--------------------------------------------------------------------------
	| Getters and Setters
	|--------------------------------------------------------------------------
	|
	| Methods for getting and setting data.
	|
	*/
	/**
	 * Get the key.
	 *
	 * @since  1.4.6
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->get_prop( 'id' );
	}

	/**
	 * Set the key.
	 *
	 * @param string $id Key.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_id( $id ) {
		$this->set_prop( 'id', absint( $id ) );
	}

	/**
	 * Get the serial id
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return int
	 */
	public function get_serial_id( $context = 'view' ) {
		return $this->get_prop( 'serial_id', $context );
	}

	/**
	 * Get the key object.
	 *
	 * @since 1.4.6
	 *
	 * @return Key|null
	 */
	public function get_key() {
		if ( empty( $this->get_serial_id() ) ) {
			return null;
		}

		return Key::get( $this->get_serial_id() );
	}

	/**
	 * Get the product id.
	 *
	 * @since 1.4.6
	 *
	 * @return int|null
	 */
	public function get_product_id() {
		if ( empty( $this->get_key() ) ) {
			return null;
		}

		return $this->get_key()->get_product_id();
	}

	/**
	 * Get the product title.
	 *
	 * @since 1.4.6
	 *
	 * @return string|null
	 */
	public function get_product_title() {
		if ( empty( $this->get_key() ) ) {
			return null;
		}

		return $this->get_key()->get_product_title();
	}

	/**
	 * Set the serial id
	 *
	 * @param int $serial_id The serial id.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_serial_id( $serial_id ) {
		$this->set_prop( 'serial_id', absint( $serial_id ) );
	}

	/**
	 * Get the instance
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return string
	 */
	public function get_instance( $context = 'view' ) {
		return $this->get_prop( 'instance', $context );
	}

	/**
	 * Set the instance
	 *
	 * @param string $instance The instance.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_instance( $instance ) {
		$this->set_prop( 'instance', sanitize_text_field( $instance ) );
	}

	/**
	 * Get the platform
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return string
	 */
	public function get_platform( $context = 'view' ) {
		return $this->get_prop( 'platform', $context );
	}

	/**
	 * Set the platform
	 *
	 * @param string $platform The platform.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_platform( $platform ) {
		$this->set_prop( 'platform', sanitize_text_field( $platform ) );
	}

	/**
	 * Get the activation time
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return string
	 */
	public function get_activation_time( $context = 'view' ) {
		return $this->get_prop( 'activation_time', $context );
	}

	/**
	 * Set the activation time
	 *
	 * @param string $activation_time The activation time.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_activation_time( $activation_time ) {
		$this->set_prop( 'activation_time', sanitize_text_field( $activation_time ) );
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD methods
	|--------------------------------------------------------------------------
	|
	| Methods which create, read, update and delete discounts from the database.
	|
	*/
	/**
	 * Saves an object in the database.
	 *
	 * @since 1.0.0
	 * @return static|\WP_Error Object instance on success, WP_Error on failure.
	 */
	public function save() {
		// Serial id is required.
		if ( empty( $this->get_serial_id() ) ) {
			return new \WP_Error( 'missing_required', __( 'Serial id is required.', 'wc-serial-numbers' ) );
		}

		// Instance is required.
		if ( empty( $this->get_instance() ) ) {
			return new \WP_Error( 'missing_required', __( 'Instance is required.', 'wc-serial-numbers' ) );
		}

		// If the activation time is empty, set it to now.
		if ( empty( $this->get_activation_time() ) ) {
			$this->set_activation_time( current_time( 'mysql' ) );
		}

		return parent::save();
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
	 * Translate legacy query arguments into the b8 query dialect.
	 *
	 * If order_id or product_id is given, the query is joined with the keys
	 * table and filtered by those columns.
	 *
	 * @param array                                     $args Query arguments.
	 * @param \WooCommerceSerialNumbers\B8\Models\Query $query Query object.
	 *
	 * @since 1.0.0
	 * @return array Translated query arguments.
	 */
	protected function prepare_query_args( $args, $query ) {
		if ( ! empty( $args['order_id'] ) || ! empty( $args['product_id'] ) ) {
			$key_table = ( new Key() )->get_table_name();
			$query->join( $key_table, "{$this->get_table()}.serial_id", "{$key_table}.id" );

			if ( ! empty( $args['order_id'] ) ) {
				$query->where( "{$key_table}.order_id", absint( $args['order_id'] ) );
			}

			if ( ! empty( $args['product_id'] ) ) {
				$query->where( "{$key_table}.product_id", absint( $args['product_id'] ) );
			}
		}
		unset( $args['order_id'], $args['product_id'] );

		return parent::prepare_query_args( $args, $query );
	}
}
