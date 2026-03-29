<?php

namespace WooCommerceSerialNumbers\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activation.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 *
 * @property int    $id Activation ID.
 * @property int    $serial_id Serial number ID.
 * @property string $instance Instance identifier.
 * @property string $platform Platform name.
 * @property string $activation_time Activation timestamp.
 */
class Activation extends Model {

	/**
	 * Table name.
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
	 * The table columns.
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
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'serial_id'       => 'integer',
		'instance'        => 'string',
		'platform'        => 'string',
		'activation_time' => 'datetime',
	);

	/**
	 * Whether query hooks have been registered.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private static $booted = false;

	/**
	 * Constructor.
	 *
	 * @param array $attributes Attributes.
	 */
	public function __construct( $attributes = array() ) {
		parent::__construct( $attributes );
		if ( ! self::$booted ) {
			self::$booted = true;
			add_filter( 'wc_serial_numbers_activation_query_clauses', array( __CLASS__, 'filter_query_clauses' ), 10, 3 );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Getters and Setters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get the activation ID.
	 *
	 * @since  1.4.6
	 * @return int
	 */
	public function get_id() {
		return $this->get( 'id' );
	}

	/**
	 * Set the activation ID.
	 *
	 * @param int $id Activation ID.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_id( $id ) {
		$this->set( 'id', absint( $id ) );
	}

	/**
	 * Get the serial id.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return int
	 */
	public function get_serial_id( $context = 'view' ) {
		return $this->get( 'serial_id' );
	}

	/**
	 * Get the key object.
	 *
	 * @since 1.4.6
	 * @return Key|false
	 */
	public function get_key() {
		if ( empty( $this->get_serial_id() ) ) {
			return null;
		}

		return Key::find( $this->get_serial_id() );
	}

	/**
	 * Get the product id.
	 *
	 * @since 1.4.6
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
	 * @return string|null
	 */
	public function get_product_title() {
		if ( empty( $this->get_key() ) ) {
			return null;
		}

		return $this->get_key()->get_product_title();
	}

	/**
	 * Set the serial id.
	 *
	 * @param int $serial_id The serial id.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_serial_id( $serial_id ) {
		$this->set( 'serial_id', absint( $serial_id ) );
	}

	/**
	 * Get the instance.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return string
	 */
	public function get_instance( $context = 'view' ) {
		return $this->get( 'instance' );
	}

	/**
	 * Set the instance.
	 *
	 * @param string $instance The instance.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_instance( $instance ) {
		$this->set( 'instance', sanitize_text_field( $instance ) );
	}

	/**
	 * Get the platform.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return string
	 */
	public function get_platform( $context = 'view' ) {
		return $this->get( 'platform' );
	}

	/**
	 * Set the platform.
	 *
	 * @param string $platform The platform.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_platform( $platform ) {
		$this->set( 'platform', sanitize_text_field( $platform ) );
	}

	/**
	 * Get the activation time.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return string
	 */
	public function get_activation_time( $context = 'view' ) {
		return $this->get( 'activation_time' );
	}

	/**
	 * Set the activation time.
	 *
	 * @param string $activation_time The activation time.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_activation_time( $activation_time ) {
		$this->set( 'activation_time', sanitize_text_field( $activation_time ) );
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Saves an object in the database.
	 *
	 * @since 1.0.0
	 * @return static|\WP_Error The model on success, WP_Error on failure.
	 */
	public function save() {
		if ( empty( $this->get_serial_id() ) ) {
			return new \WP_Error( 'missing_required', __( 'Serial id is required.', 'wc-serial-numbers' ) );
		}

		if ( empty( $this->get_instance() ) ) {
			return new \WP_Error( 'missing_required', __( 'Instance is required.', 'wc-serial-numbers' ) );
		}

		if ( empty( $this->get_activation_time() ) ) {
			$this->set_activation_time( current_time( 'mysql' ) );
		}

		return parent::save();
	}

	/*
	|--------------------------------------------------------------------------
	| Query Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Filter query clauses for custom query logic.
	 * Handles order_id and product_id filtering via JOIN with keys table.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $qv     Query variables.
	 * @param mixed $query  Query instance.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function filter_query_clauses( $clauses, $qv, $query ) {
		global $wpdb;

		if ( ! empty( $qv['order_id'] ) || ! empty( $qv['product_id'] ) ) {
			$key_table        = ( new Key() )->get_table();
			$clauses['join'] .= " INNER JOIN `{$wpdb->prefix}{$key_table}` AS `{$key_table}` ON `serial_numbers_activations`.`serial_id` = `{$key_table}`.`id`";
		}

		if ( ! empty( $qv['order_id'] ) ) {
			$key_table         = ( new Key() )->get_table();
			$clauses['where'] .= $wpdb->prepare( " AND `{$key_table}`.`order_id` = %d", $qv['order_id'] );
		}

		if ( ! empty( $qv['product_id'] ) ) {
			$key_table         = ( new Key() )->get_table();
			$clauses['where'] .= $wpdb->prepare( " AND `{$key_table}`.`product_id` = %d", $qv['product_id'] );
		}

		return $clauses;
	}
}
