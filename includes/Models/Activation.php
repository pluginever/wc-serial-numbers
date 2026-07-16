<?php

namespace WooCommerceSerialNumbers\Models;

use WooCommerceSerialNumbers\B8\Models\Relations\BelongsTo;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activation.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 *
 * @property int    $id              Activation ID.
 * @property int    $serial_id       Serial key ID this activation belongs to.
 * @property string $instance        Instance identifier the key was activated for.
 * @property string $platform        Platform the key was activated on.
 * @property string $activation_time Time the activation was recorded.
 *
 * @property-read Key|null $key           Related serial key object.
 * @property-read int|null $product_id    Product ID via the related key.
 * @property-read string   $product_title Product title via the related key.
 */
class Activation extends Model {
	/**
	 * The table associated with the model.
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
		'id'              => 'int',
		'serial_id'       => 'int',
		'instance'        => 'string',
		'platform'        => 'string',
		'activation_time' => 'string',
	);

	/**
	 * Validation rules applied before the model is saved.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $rules = array(
		'serial_id' => 'required|integer|min:1',
		'instance'  => 'required',
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
	| Accessors & Mutators
	|--------------------------------------------------------------------------
	*/

	/**
	 * Product ID via the related key.
	 *
	 * @since 1.0.0
	 * @return int|null
	 */
	protected function get_product_id_attribute() {
		return $this->key ? $this->key->product_id : null;
	}

	/**
	 * Product title via the related key.
	 *
	 * @since 1.0.0
	 * @return string|null
	 */
	protected function get_product_title_attribute() {
		return $this->key ? $this->key->product_title : null;
	}

	/*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/

	/**
	 * The serial key this activation belongs to.
	 *
	 * @since 1.0.0
	 * @return BelongsTo
	 */
	public function key(): BelongsTo {
		return $this->belongs_to( Key::class, 'serial_id' );
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD methods
	|--------------------------------------------------------------------------
	|
	| Methods which create, read, update and delete activations from the database.
	|
	*/
	/**
	 * Saves an object in the database.
	 *
	 * @since 1.0.0
	 * @return static|\WP_Error Object instance on success, WP_Error on failure.
	 */
	public function save() {
		// Required serial_id and instance are enforced by $rules in parent::save().
		// If the activation time is empty, set it to now.
		if ( empty( $this->activation_time ) ) {
			$this->activation_time = current_time( 'mysql' );
		}

		return parent::save();
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated Methods
	|--------------------------------------------------------------------------
	|
	| Old getter/setter API kept so existing integrations (and Pro) keep
	| working. New code should use property access (e.g. $activation->instance).
	| Grouped here so the whole block can be removed in a future release.
	|
	*/

	/**
	 * Get the activation ID.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->id.
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the activation ID.
	 *
	 * @param int $id Activation ID.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->id = $id.
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get the serial key ID.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->serial_id.
	 * @return int
	 */
	public function get_serial_id( $context = 'view' ) {
		return $this->serial_id;
	}

	/**
	 * Set the serial key ID.
	 *
	 * @param int $serial_id The serial key ID.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->serial_id = $serial_id.
	 * @return void
	 */
	public function set_serial_id( $serial_id ) {
		$this->serial_id = $serial_id;
	}

	/**
	 * Get the related serial key object.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->key.
	 * @return Key|null
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * Get the product ID via the related key.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->product_id.
	 * @return int|null
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * Get the product title via the related key.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->product_title.
	 * @return string|null
	 */
	public function get_product_title() {
		return $this->product_title;
	}

	/**
	 * Get the instance.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->instance.
	 * @return string
	 */
	public function get_instance( $context = 'view' ) {
		return $this->instance;
	}

	/**
	 * Set the instance.
	 *
	 * @param string $instance The instance.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->instance = $instance.
	 * @return void
	 */
	public function set_instance( $instance ) {
		$this->instance = $instance;
	}

	/**
	 * Get the platform.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->platform.
	 * @return string
	 */
	public function get_platform( $context = 'view' ) {
		return $this->platform;
	}

	/**
	 * Set the platform.
	 *
	 * @param string $platform The platform.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->platform = $platform.
	 * @return void
	 */
	public function set_platform( $platform ) {
		$this->platform = $platform;
	}

	/**
	 * Get the activation time.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->activation_time.
	 * @return string
	 */
	public function get_activation_time( $context = 'view' ) {
		return $this->activation_time;
	}

	/**
	 * Set the activation time.
	 *
	 * @param string $activation_time The activation time.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $activation->activation_time = $activation_time.
	 * @return void
	 */
	public function set_activation_time( $activation_time ) {
		$this->activation_time = $activation_time;
	}
}
