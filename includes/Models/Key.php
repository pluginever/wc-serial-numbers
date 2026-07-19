<?php

namespace WooCommerceSerialNumbers\Models;

use WooCommerceSerialNumbers\B8\Models\Relations\HasMany;
use WooCommerceSerialNumbers\B8\Models\Utilities\DateUtil;

defined( 'ABSPATH' ) || exit;

/**
 * Class Key.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 *
 * @property int            $id               Key ID.
 * @property string         $serial_key       Serial key (decrypted on read, encrypted at rest).
 * @property int            $product_id       Product ID.
 * @property int            $activation_limit Maximum number of activations.
 * @property int            $activation_count Number of activations used.
 * @property int            $order_id         Order ID.
 * @property int            $order_item_id    Order item ID.
 * @property int            $vendor_id        Vendor user ID.
 * @property string         $status           Key status.
 * @property int            $validity         Validity in days.
 * @property \DateTime|null $order_date       Date the key was attached to an order.
 * @property string         $source           Source the key was created from.
 * @property \DateTime|null $created_date     Date the key was created.
 *
 * @property-read string            $key              Alias of $serial_key.
 * @property-read \WC_Product|null  $product          Product object.
 * @property-read string            $product_title    Product title.
 * @property-read \WC_Order|null    $order            Order object.
 * @property-read string            $order_title      Formatted order title.
 * @property-read \WP_User|false    $vendor           Vendor user object.
 * @property-read string            $status_label     Translated status label.
 * @property-read string            $expire_date      Calculated expiry date.
 * @property-read int               $activations_left Remaining activations.
 * @property-read int               $customer_id      Customer ID from the order.
 * @property-read string            $customer_email   Customer email from the order.
 * @property-read string            $customer_name    Customer name from the order.
 * @property-read Activation[]      $activations      Activations for the key.
 */
class Key extends Model {
	/**
	 * The table associated with the model.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table = 'serial_numbers';

	/**
	 * Object type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $object_type = 'key';

	/**
	 * The cache group for the object type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $cache_group = 'serial_numbers';

	/**
	 * The table columns of the model.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $columns = array(
		'id',
		'serial_key',
		'product_id',
		'activation_limit',
		'activation_count',
		'order_id',
		'order_item_id',
		'vendor_id',
		'status',
		'validity',
		'order_date',
		'source',
		'created_date',
	);

	/**
	 * The model's attributes with their default values.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $attributes = array(
		'serial_key'       => '',
		'product_id'       => 0,
		'activation_limit' => 0,
		'activation_count' => 0,
		'order_id'         => 0,
		'order_item_id'    => 0,
		'vendor_id'        => 0,
		'status'           => 'available',
		'validity'         => 0,
		'order_date'       => '',
		'source'           => 'custom_source',
		'created_date'     => '',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'id'               => 'int',
		'product_id'       => 'int',
		'activation_limit' => 'int',
		'activation_count' => 'int',
		'order_id'         => 'int',
		'order_item_id'    => 'int',
		'vendor_id'        => 'int',
		'validity'         => 'int',
		'serial_key'       => 'textarea',
		'source'           => 'string',
		'order_date'       => 'datetime',
		'created_date'     => 'datetime',
	);

	/**
	 * Validation rules applied before the model is saved.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $rules = array(
		'product_id' => 'required|integer|min:1',
		'serial_key' => 'required',
	);

	/**
	 * The searchable attributes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $searchable = array(
		'id',
		'serial_key',
		'product_id',
		'activation_limit',
		'activation_count',
		'order_id',
		'order_item_id',
		'vendor_id',
		'status',
		'validity',
		'order_date',
		'source',
		'created_date',
	);

	/*
	|--------------------------------------------------------------------------
	| Static Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Find a key by its primary key or column conditions.
	 *
	 * The serial key is stored encrypted, so a serial_key condition is
	 * encrypted before it is matched against the database.
	 *
	 * @param mixed $id The ID or array of column conditions.
	 *
	 * @since 1.0.0
	 * @return static|null The model instance, or null if not found.
	 */
	public static function find( $id ) {
		if ( is_array( $id ) && ! empty( $id['serial_key'] ) ) {
			$id['serial_key'] = wcsn_encrypt_key( $id['serial_key'] );
		}

		return parent::find( $id );
	}

	/**
	 * Prepare the search query var.
	 *
	 * The serial key column holds encrypted values, so the search term is
	 * encrypted when compared against that column.
	 *
	 * @param array                                     $args Query arguments.
	 * @param \WooCommerceSerialNumbers\B8\Models\Query $query Query object.
	 *
	 * @since 1.0.0
	 * @return array Query arguments.
	 */
	public static function prepare_search( $args, $query ) {
		if ( empty( $args['search'] ) ) {
			return $args;
		}

		$search  = $args['search'];
		$columns = ! empty( $args['search_columns'] ) ? wp_parse_list( $args['search_columns'] ) : ( new static() )->get_searchable();
		$columns = array_filter( array_unique( (array) $columns ) );

		if ( ! empty( $columns ) ) {
			$query->where(
				function ( $q ) use ( $columns, $search ) {
					foreach ( $columns as $column ) {
						$term = 'serial_key' === $column ? wcsn_encrypt_key( $search ) : $search;
						$q->where( $column, 'LIKE', $term );
					}
				},
				'OR'
			);
		}

		$args['search']         = '';
		$args['search_columns'] = array();

		return $args;
	}

	/**
	 * Bootstrap the model.
	 *
	 * Wires the encryption boundary into the native read and query hooks:
	 * the serial key is decrypted when read from the database and search
	 * terms are encrypted to match the value at rest.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function bootstrap() {
		parent::bootstrap();
		$this->on_filter( 'serial_key', 'wcsn_decrypt_key' );
		$this->on_filter( 'query_args', array( static::class, 'prepare_search' ), 10, 2 );
	}

	/*
	|--------------------------------------------------------------------------
	| Accessors & Mutators
	|--------------------------------------------------------------------------
	*/

	/**
	 * Only store known statuses; unknown values are ignored.
	 *
	 * @param string $value Status value.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function set_status_attribute( $value ) {
		if ( array_key_exists( $value, wcsn_get_key_statuses() ) ) {
			$this->attributes['status'] = $value;
		}
	}

	/**
	 * Serial key alias.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_key_attribute() {
		return $this->serial_key;
	}

	/**
	 * Product object.
	 *
	 * @since 1.0.0
	 * @return \WC_Product|null
	 */
	protected function get_product_attribute() {
		return $this->product_id ? wc_get_product( $this->product_id ) : null;
	}

	/**
	 * Product title.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_product_title_attribute() {
		return wcsn_get_product_title( $this->product_id );
	}

	/**
	 * Order object.
	 *
	 * @since 1.0.0
	 * @return \WC_Order|null
	 */
	protected function get_order_attribute() {
		return $this->order_id ? wc_get_order( $this->order_id ) : null;
	}

	/**
	 * Formatted order title.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_order_title_attribute() {
		$order = $this->order;
		if ( ! $order ) {
			return '';
		}

		return sprintf(
			'(#%1$s) %2$s',
			$order->get_id(),
			wp_strip_all_tags( $order->get_formatted_billing_full_name() )
		);
	}

	/**
	 * Vendor user object.
	 *
	 * @since 1.0.0
	 * @return \WP_User|false
	 */
	protected function get_vendor_attribute() {
		return $this->vendor_id ? get_user_by( 'id', $this->vendor_id ) : false;
	}

	/**
	 * Translated status label.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_status_label_attribute() {
		$statuses = wcsn_get_key_statuses();

		return array_key_exists( $this->status, $statuses ) ? $statuses[ $this->status ] : '&mdash;';
	}

	/**
	 * Calculated expiry date.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_expire_date_attribute() {
		$order_timestamp = DateUtil::strtotime( $this->order_date );
		if ( ! $order_timestamp || ! $this->validity ) {
			return '';
		}

		return wp_date( 'Y-m-d H:i:s', strtotime( "+{$this->validity} days", $order_timestamp ) );
	}

	/**
	 * Remaining activations.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	protected function get_activations_left_attribute() {
		if ( ! $this->activation_limit ) {
			return 9999;
		}

		return $this->activation_limit - $this->activation_count;
	}

	/**
	 * Customer ID from the order.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	protected function get_customer_id_attribute() {
		return $this->order ? $this->order->get_customer_id() : 0;
	}

	/**
	 * Customer email from the order.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_customer_email_attribute() {
		return $this->order ? $this->order->get_billing_email() : '';
	}

	/**
	 * Customer name from the order.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_customer_name_attribute() {
		return $this->order ? $this->order->get_formatted_billing_full_name() : '';
	}

	/*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/

	/**
	 * Activations for the key.
	 *
	 * @since 1.0.0
	 * @return HasMany
	 */
	public function activations(): HasMany {
		return $this->has_many( Activation::class, 'serial_id' );
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD methods
	|--------------------------------------------------------------------------
	|
	| Methods which create, read, update and delete keys from the database.
	|
	*/
	/**
	 * Saves an object in the database.
	 *
	 * @since 1.0.0
	 * @return static|\WP_Error Object instance on success, WP_Error on failure.
	 */
	public function save() {
		// Required product_id and serial_key are enforced by $rules in parent::save().
		// Duplicate serial key is not allowed.
		if ( $this->serial_key && ! wcsn_is_duplicate_key_allowed() ) {
			$existing = static::find( array( 'serial_key' => $this->serial_key ) );
			if ( $existing && $existing->id !== $this->id ) {
				return new \WP_Error( 'invalid-data', __( 'Serial key already exists. Duplicate serial keys are not allowed.', 'wc-serial-numbers' ) );
			}
		}

		// If status is available, order fields should be cleared.
		if ( 'available' === $this->status ) {
			$this->order_id         = 0;
			$this->order_date       = null;
			$this->activation_count = 0;
		}

		// If order is set without an order date, derive it from the order.
		if ( $this->order_id && ! $this->order_date ) {
			$order = wc_get_order( $this->order_id );
			if ( $order ) {
				$this->order_date = $order->get_date_completed() ? $order->get_date_completed() : wp_date( 'Y-m-d H:i:s' );
			}
		}

		// If key is not created yet, set created date.
		if ( ! $this->created_date ) {
			$this->created_date = wp_date( 'Y-m-d H:i:s' );
		}

		return parent::save();
	}

	/**
	 * Create an item in the database.
	 *
	 * The serial key is encrypted at rest before it is written.
	 *
	 * @param array $data Data to be inserted.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|int The ID of the inserted item, or WP_Error on failure.
	 */
	protected function perform_create( $data ) {
		if ( ! empty( $data['serial_key'] ) ) {
			$data['serial_key'] = wcsn_encrypt_key( $data['serial_key'] );
		}

		return parent::perform_create( $data );
	}

	/**
	 * Update an item in the database.
	 *
	 * The serial key is encrypted at rest before it is written.
	 *
	 * @param int   $id ID of the item to be updated.
	 * @param array $data Data to be updated.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|bool True on success, or WP_Error on failure.
	 */
	protected function perform_update( $id, $data ) {
		if ( ! empty( $data['serial_key'] ) ) {
			$data['serial_key'] = wcsn_encrypt_key( $data['serial_key'] );
		}

		return parent::perform_update( $id, $data );
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers Methods
	|--------------------------------------------------------------------------
	|
	| Common methods used by the class.
	|
	*/

	/**
	 * Reset activations.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function reset_activations() {
		foreach ( $this->activations as $activation ) {
			$activation->delete();
		}
	}

	/**
	 * Recount activations and persist the remaining count.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function recount_remaining_activation() {
		$count                  = $this->activations()->count();
		$this->activation_count = $count;
		$this->save();

		return $count;
	}

	/**
	 * Check if the key is expired.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_expired() {
		$expire_date = $this->expire_date;

		return ! empty( $expire_date ) && strtotime( $expire_date ) < time();
	}

	/**
	 * Display the serial key.
	 *
	 * @param bool $masked Whether to mask the key or not.
	 *
	 * @since 1.5.0
	 * @return string
	 */
	public function print_key( $masked = false ) {
		$key = $this->serial_key;
		if ( $masked ) {
			// Divide the length of the key by 3 and round up. Then mask the middle part of the key.
			$mask_length = ceil( strlen( $key ) / 3 );
			$mask_start  = ceil( ( strlen( $key ) - $mask_length ) / 2 );
			$mask_end    = $mask_start + $mask_length;
			$masked_key  = substr( $key, 0, $mask_start ) . str_repeat( '*', $mask_length ) . substr( $key, $mask_end );
			$key         = sprintf( '<code class="wcsn-key masked" data-masked="%s" data-unmasked="%s">%s</code>', esc_attr( $masked_key ), esc_attr( $key ), $masked_key );
		} else {
			$key = sprintf( '<code class="wcsn-key" data-unmasked="%s" data-masked="%s">%s</code>', esc_attr( $key ), esc_attr( $key ), $key );
		}

		return $this->apply_filters( 'display_key', $key, $this );
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated Methods
	|--------------------------------------------------------------------------
	|
	| Old getter/setter API kept so existing integrations (and Pro) keep
	| working. New code should use property access (e.g. $key->product_id).
	| Grouped here so the whole block can be removed in a future release.
	|
	*/

	/**
	 * Get the key ID.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->id.
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the key ID.
	 *
	 * @param int $id Key ID.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->id = $id.
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get the serial key.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->serial_key.
	 * @return string
	 */
	public function get_serial_key( $context = 'edit' ) {
		return $this->serial_key;
	}

	/**
	 * Get the serial key (alias).
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->serial_key.
	 * @return string
	 */
	public function get_key( $context = 'edit' ) {
		return $this->serial_key;
	}

	/**
	 * Set the serial key.
	 *
	 * @param string $serial_key Serial key.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->serial_key = $serial_key.
	 * @return void
	 */
	public function set_serial_key( $serial_key ) {
		$this->serial_key = $serial_key;
	}

	/**
	 * Get the product ID.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->product_id.
	 * @return int
	 */
	public function get_product_id( $context = 'edit' ) {
		return $this->product_id;
	}

	/**
	 * Set the product ID.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->product_id = $product_id.
	 * @return void
	 */
	public function set_product_id( $product_id ) {
		$this->product_id = $product_id;
	}

	/**
	 * Get the product object.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->product.
	 * @return \WC_Product|null
	 */
	public function get_product() {
		return $this->product;
	}

	/**
	 * Get the product title.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->product_title.
	 * @return string
	 */
	public function get_product_title() {
		return $this->product_title;
	}

	/**
	 * Get the activation limit.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->activation_limit.
	 * @return int
	 */
	public function get_activation_limit( $context = 'edit' ) {
		return $this->activation_limit;
	}

	/**
	 * Set the activation limit.
	 *
	 * @param int $activation_limit Activation limit.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->activation_limit = $activation_limit.
	 * @return void
	 */
	public function set_activation_limit( $activation_limit ) {
		$this->activation_limit = $activation_limit;
	}

	/**
	 * Get the activation count.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->activation_count.
	 * @return int
	 */
	public function get_activation_count( $context = 'edit' ) {
		return $this->activation_count;
	}

	/**
	 * Set the activation count.
	 *
	 * @param int $activation_count Activation count.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->activation_count = $activation_count.
	 * @return void
	 */
	public function set_activation_count( $activation_count ) {
		$this->activation_count = $activation_count;
	}

	/**
	 * Get the order ID.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->order_id.
	 * @return int
	 */
	public function get_order_id( $context = 'edit' ) {
		return $this->order_id;
	}

	/**
	 * Set the order ID.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->order_id = $order_id.
	 * @return void
	 */
	public function set_order_id( $order_id ) {
		$this->order_id = $order_id;
	}

	/**
	 * Get the order object.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->order.
	 * @return \WC_Order|null
	 */
	public function get_order() {
		return $this->order;
	}

	/**
	 * Get the formatted order title.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->order_title.
	 * @return string
	 */
	public function get_order_title() {
		return $this->order_title;
	}

	/**
	 * Get the order item ID.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      2.0.6
	 * @deprecated 2.3.5 Use $key->order_item_id.
	 * @return int
	 */
	public function get_order_item_id( $context = 'edit' ) {
		return $this->order_item_id;
	}

	/**
	 * Set the order item ID.
	 *
	 * @param int $order_item_id Order item ID.
	 *
	 * @since      2.0.6
	 * @deprecated 2.3.5 Use $key->order_item_id = $order_item_id.
	 * @return void
	 */
	public function set_order_item_id( $order_item_id ) {
		$this->order_item_id = $order_item_id;
	}

	/**
	 * Get the vendor ID.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->vendor_id.
	 * @return int
	 */
	public function get_vendor_id( $context = 'edit' ) {
		return $this->vendor_id;
	}

	/**
	 * Set the vendor ID.
	 *
	 * @param int $vendor_id Vendor ID.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->vendor_id = $vendor_id.
	 * @return void
	 */
	public function set_vendor_id( $vendor_id ) {
		$this->vendor_id = $vendor_id;
	}

	/**
	 * Get the vendor object.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->vendor.
	 * @return \WP_User|false
	 */
	public function get_vendor() {
		return $this->vendor;
	}

	/**
	 * Get the status.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->status.
	 * @return string
	 */
	public function get_status( $context = 'edit' ) {
		return $this->status;
	}

	/**
	 * Set the status.
	 *
	 * @param string $status Status.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->status = $status.
	 * @return void
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * Get the validity.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->validity.
	 * @return int
	 */
	public function get_validity( $context = 'edit' ) {
		return $this->validity;
	}

	/**
	 * Set the validity.
	 *
	 * @param int $validity Validity.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->validity = $validity.
	 * @return void
	 */
	public function set_validity( $validity ) {
		$this->validity = $validity;
	}

	/**
	 * Get the order date.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->order_date.
	 * @return string
	 */
	public function get_order_date( $context = 'edit' ) {
		return $this->order_date instanceof \DateTime ? $this->order_date->format( 'Y-m-d H:i:s' ) : '';
	}

	/**
	 * Set the order date.
	 *
	 * @param string $order_date Order date.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->order_date = $order_date.
	 * @return void
	 */
	public function set_order_date( $order_date ) {
		$this->order_date = $order_date;
	}

	/**
	 * Get the source.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->source.
	 * @return string
	 */
	public function get_source( $context = 'edit' ) {
		return $this->source;
	}

	/**
	 * Set the source.
	 *
	 * @param string $source Source.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->source = $source.
	 * @return void
	 */
	public function set_source( $source ) {
		$this->source = $source;
	}

	/**
	 * Get the created date.
	 *
	 * @param string $context Unused. Kept for backward compatibility.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->created_date.
	 * @return string
	 */
	public function get_created_date( $context = 'edit' ) {
		return $this->created_date instanceof \DateTime ? $this->created_date->format( 'Y-m-d H:i:s' ) : '';
	}

	/**
	 * Set the created date.
	 *
	 * @param string $created_date Created date.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->created_date = $created_date.
	 * @return void
	 */
	public function set_created_date( $created_date ) {
		$this->created_date = $created_date;
	}

	/**
	 * Get the translated status label.
	 *
	 * @since      1.5.6
	 * @deprecated 2.3.5 Use $key->status_label.
	 * @return string
	 */
	public function get_status_label() {
		return $this->status_label;
	}

	/**
	 * Get the customer ID.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use $key->customer_id.
	 * @return int
	 */
	public function get_customer_id() {
		return $this->customer_id;
	}

	/**
	 * Get the customer email.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use $key->customer_email.
	 * @return string
	 */
	public function get_customer_email() {
		return $this->customer_email;
	}

	/**
	 * Get the customer name.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use $key->customer_name.
	 * @return string
	 */
	public function get_customer_name() {
		return $this->customer_name;
	}

	/**
	 * Get the calculated expiry date.
	 *
	 * @since      1.4.6
	 * @deprecated 2.3.5 Use $key->expire_date.
	 * @return string
	 */
	public function get_expire_date() {
		return $this->expire_date;
	}

	/**
	 * Get remaining activations.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use $key->activations_left.
	 * @return int
	 */
	public function get_activations_left() {
		return $this->activations_left;
	}

	/**
	 * Get activations for the key.
	 *
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @since      1.0.0
	 * @deprecated 2.3.5 Use $key->activations or $key->activations()->count().
	 * @return array|int Array of activations or count.
	 */
	public function get_activations( $args = array() ) {
		$output = isset( $args['output'] ) ? $args['output'] : '';
		unset( $args['output'] );

		$args    = wp_parse_args( $args, array( 'serial_id' => $this->id ) );
		$results = Activation::query( $args );

		if ( ARRAY_A === $output && is_array( $results ) ) {
			return array_map(
				fn( $item ) => $item instanceof Activation ? $item->to_array() : $item,
				$results
			);
		}

		return $results;
	}
}
