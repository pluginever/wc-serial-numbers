<?php

namespace WooCommerceSerialNumbers\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Key.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 *
 * @property int    $id Key ID.
 * @property string $serial_key Encrypted serial key.
 * @property int    $product_id Product ID.
 * @property int    $activation_limit Activation limit.
 * @property int    $activation_count Activation count.
 * @property int    $order_id Order ID.
 * @property int    $order_item_id Order item ID.
 * @property int    $vendor_id Vendor ID.
 * @property string $status Key status.
 * @property int    $validity Validity in days.
 * @property string $order_date Order date.
 * @property string $source Key source.
 * @property string $created_date Created date.
 */
class Key extends Model {

	/**
	 * Table name.
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
	 * The table columns.
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
	 * The model's default attributes.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $attributes = array(
		'status' => 'available',
		'source' => 'custom_source',
	);

	/**
	 * The attributes that should be cast.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $casts = array(
		'serial_key'       => 'string',
		'product_id'       => 'integer',
		'activation_limit' => 'integer',
		'activation_count' => 'integer',
		'order_id'         => 'integer',
		'order_item_id'    => 'integer',
		'vendor_id'        => 'integer',
		'status'           => 'string',
		'validity'         => 'integer',
		'order_date'       => 'datetime',
		'source'           => 'string',
		'created_date'     => 'datetime',
	);

	/**
	 * Searchable attributes.
	 * Empty because search requires special handling for encrypted serial_key.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $searchable = array();

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
			add_filter( 'wc_serial_numbers_key_query_clauses', array( __CLASS__, 'filter_query_clauses' ), 10, 3 );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Getters and Setters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get the key ID.
	 *
	 * @since  1.4.6
	 * @return int
	 */
	public function get_id() {
		return $this->get( 'id' );
	}

	/**
	 * Set the key ID.
	 *
	 * @param int $id Key ID.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_id( $id ) {
		$this->set( 'id', absint( $id ) );
	}

	/**
	 * Get the serial key.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return string
	 */
	public function get_serial_key( $context = 'edit' ) {
		$serial_key = $this->get( 'serial_key' );

		// Return decrypted key.
		return wcsn_decrypt_key( $serial_key );
	}

	/**
	 * Get the key.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return string
	 */
	public function get_key( $context = 'edit' ) {
		return $this->get_serial_key( $context );
	}

	/**
	 * Set the serial key.
	 *
	 * @param string $serial_key Serial key.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_serial_key( $serial_key ) {
		$this->set( 'serial_key', sanitize_textarea_field( $serial_key ) );
	}

	/**
	 * Get the product id.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return int
	 */
	public function get_product_id( $context = 'edit' ) {
		return $this->get( 'product_id' );
	}

	/**
	 * Get product.
	 *
	 * @since 1.4.6
	 * @return \WC_Product|null Product object or null if not found.
	 */
	public function get_product() {
		$product_id = $this->get_product_id();

		if ( $product_id ) {
			return wc_get_product( $product_id );
		}

		return null;
	}

	/**
	 * Get product name.
	 *
	 * @since 1.4.6
	 * @return string Product name.
	 */
	public function get_product_title() {
		return wcsn_get_product_title( $this->get_product_id() );
	}

	/**
	 * Set the product id.
	 *
	 * @param int $product_id Product id.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_product_id( $product_id ) {
		$this->set( 'product_id', absint( $product_id ) );
	}

	/**
	 * Get the activation limit.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return int
	 */
	public function get_activation_limit( $context = 'edit' ) {
		return $this->get( 'activation_limit' );
	}

	/**
	 * Set the activation limit.
	 *
	 * @param int $activation_limit Activation limit.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_activation_limit( $activation_limit ) {
		$this->set( 'activation_limit', absint( $activation_limit ) );
	}

	/**
	 * Get the activation count.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return int
	 */
	public function get_activation_count( $context = 'edit' ) {
		return $this->get( 'activation_count' );
	}

	/**
	 * Set the activation count.
	 *
	 * @param int $activation_count Activation count.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_activation_count( $activation_count ) {
		$this->set( 'activation_count', absint( $activation_count ) );
	}

	/**
	 * Get the order id.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return int
	 */
	public function get_order_id( $context = 'edit' ) {
		return $this->get( 'order_id' );
	}

	/**
	 * Get order.
	 *
	 * @since 1.4.6
	 * @return \WC_Order|null Order object or null if not found.
	 */
	public function get_order() {
		$order_id = $this->get_order_id();

		if ( $order_id ) {
			return wc_get_order( $order_id );
		}

		return null;
	}

	/**
	 * Get order title.
	 *
	 * @since 1.4.6
	 * @return string Order title.
	 */
	public function get_order_title() {
		if ( ! $this->get_order() ) {
			return '';
		}

		return sprintf(
			'(#%1$s) %2$s',
			$this->get_order()->get_id(),
			wp_strip_all_tags( $this->get_order()->get_formatted_billing_full_name() )
		);
	}

	/**
	 * Set the order id.
	 *
	 * @param int $order_id Order id.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_order_id( $order_id ) {
		$this->set( 'order_id', absint( $order_id ) );
	}

	/**
	 * Get the order item id.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  2.0.6
	 * @return int
	 */
	public function get_order_item_id( $context = 'edit' ) {
		return $this->get( 'order_item_id' );
	}

	/**
	 * Set the order item id.
	 *
	 * @param int $order_item_id Order item id.
	 *
	 * @since  2.0.6
	 * @return void
	 */
	public function set_order_item_id( $order_item_id ) {
		$this->set( 'order_item_id', absint( $order_item_id ) );
	}

	/**
	 * Get the vendor id.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return int
	 */
	public function get_vendor_id( $context = 'edit' ) {
		return $this->get( 'vendor_id' );
	}

	/**
	 * Get vendor.
	 *
	 * @since 1.4.6
	 * @return \WP_User|null Vendor object or null if not found.
	 */
	public function get_vendor() {
		$vendor_id = $this->get_vendor_id();

		if ( $vendor_id ) {
			return get_user_by( 'id', $vendor_id );
		}

		return null;
	}

	/**
	 * Set the vendor id.
	 *
	 * @param int $vendor_id Vendor id.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_vendor_id( $vendor_id ) {
		$this->set( 'vendor_id', absint( $vendor_id ) );
	}

	/**
	 * Get the status.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return string
	 */
	public function get_status( $context = 'edit' ) {
		return $this->get( 'status' );
	}

	/**
	 * Set the status.
	 *
	 * @param string $status Status.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_status( $status ) {
		if ( array_key_exists( $status, wcsn_get_key_statuses() ) ) {
			$this->set( 'status', $status );
		}
	}

	/**
	 * Get the validity.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return int
	 */
	public function get_validity( $context = 'edit' ) {
		return $this->get( 'validity' );
	}

	/**
	 * Set the validity.
	 *
	 * @param int $validity Validity in days.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_validity( $validity ) {
		$this->set( 'validity', absint( $validity ) );
	}

	/**
	 * Get the order date.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return string
	 */
	public function get_order_date( $context = 'edit' ) {
		return $this->get( 'order_date' );
	}

	/**
	 * Set the order date.
	 *
	 * @param string $order_date Order date.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_order_date( $order_date ) {
		$this->set( 'order_date', $order_date );
	}

	/**
	 * Get the source.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return string
	 */
	public function get_source( $context = 'edit' ) {
		return $this->get( 'source' );
	}

	/**
	 * Set the source.
	 *
	 * @param string $source Source.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_source( $source ) {
		$this->set( 'source', sanitize_text_field( $source ) );
	}

	/**
	 * Get the created date.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 * @return string
	 */
	public function get_created_date( $context = 'edit' ) {
		return $this->get( 'created_date' );
	}

	/**
	 * Set the created date.
	 *
	 * @param string $created_date Created date.
	 *
	 * @since  1.4.6
	 * @return void
	 */
	public function set_created_date( $created_date ) {
		$this->set( 'created_date', $created_date );
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
		if ( ! $this->get_product_id() ) {
			return new \WP_Error( 'missing-required', __( 'Product id is required.', 'wc-serial-numbers' ) );
		}

		if ( empty( wc_get_product( $this->get_product_id() ) ) ) {
			return new \WP_Error( 'invalid-data', __( 'Product id is invalid.', 'wc-serial-numbers' ) );
		}

		if ( ! $this->get_serial_key() ) {
			return new \WP_Error( 'missing-required', __( 'Serial key is required.', 'wc-serial-numbers' ) );
		}

		if ( ! wcsn_is_duplicate_key_allowed() ) {
			$existing = self::find(
				array(
					'serial_key' => $this->get_serial_key(),
				)
			);

			if ( $existing && $existing->get_id() !== $this->get_id() ) {
				return new \WP_Error( 'invalid-data', __( 'Serial key already exists. Duplicate serial keys are not allowed.', 'wc-serial-numbers' ) );
			}
		}

		if ( $this->get_order_id() && empty( wc_get_order( $this->get_order_id() ) ) ) {
			return new \WP_Error( 'invalid-data', __( 'Order id is invalid.', 'wc-serial-numbers' ) );
		}

		if ( 'available' === $this->get_status() ) {
			$this->set_order_id( 0 );
			$this->set_order_date( null );
			$this->set_activation_count( 0 );
		}

		if ( $this->get_order_id() && ! $this->get_order_date() ) {
			$order      = wc_get_order( $this->get_order_id() );
			$order_date = $order->get_date_completed() ? $order->get_date_completed()->date( 'Y-m-d H:i:s' ) : wp_date( 'Y-m-d H:i:s' );
			$this->set( 'order_date', $order_date );
		}

		if ( ! $this->get_created_date() ) {
			$this->set( 'created_date', wp_date( 'Y-m-d H:i:s' ) );
		}

		return parent::save();
	}

	/*
	|--------------------------------------------------------------------------
	| Query Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Find a key by ID or array of conditions.
	 * Encrypts serial_key if present in conditions.
	 *
	 * @param int|array $id Object ID or array of conditions.
	 *
	 * @since 1.0.0
	 * @return static|false Object instance on success, false on failure.
	 */
	public static function find( $id ) {
		if ( is_array( $id ) && array_key_exists( 'serial_key', $id ) ) {
			$id['serial_key'] = wcsn_encrypt_key( $id['serial_key'] );
		}

		return parent::find( $id );
	}

	/**
	 * Filter query clauses for custom query logic.
	 * Handles customer_id filtering and encrypted serial_key search.
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

		// Handle encrypted serial_key search.
		if ( ! empty( $qv['search'] ) ) {
			$search         = $qv['search'];
			$search_columns = ! empty( $qv['search_columns'] ) ? $qv['search_columns'] : array( 'serial_key', 'product_id', 'order_id' );

			/**
			 * Filter the columns to search in.
			 *
			 * @param array $search_columns Array of columns to search in.
			 * @param array $qv Query variables.
			 *
			 * @since 1.0.0
			 */
			$search_columns = apply_filters( 'wc_serial_numbers_key_search_columns', $search_columns, $qv );
			$search_columns = array_filter( array_unique( $search_columns ) );
			$like           = '%' . $wpdb->esc_like( $search ) . '%';

			$search_clauses = array();
			foreach ( $search_columns as $column ) {
				$search_like = $like;
				if ( 'serial_key' === $column ) {
					$search_like = '%' . $wpdb->esc_like( wcsn_encrypt_key( $search ) ) . '%';
				}
				$search_clauses[] = $wpdb->prepare( '`serial_numbers`.`' . $column . '` LIKE %s', $search_like ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}

			if ( ! empty( $search_clauses ) ) {
				$clauses['where'] .= ' AND (' . implode( ' OR ', $search_clauses ) . ')';
			}
		}

		// Handle customer_id filtering via WooCommerce orders.
		if ( ! empty( $qv['customer_id'] ) ) {
			$customer_id = absint( $qv['customer_id'] );
			$order_ids   = wc_get_orders(
				array(
					'customer_id' => $customer_id,
					'limit'       => - 1,
					'return'      => 'ids',
				)
			);

			if ( ! empty( $order_ids ) ) {
				$clauses['where'] .= ' AND `serial_numbers`.`order_id` IN (' . implode( ',', array_map( 'absint', $order_ids ) ) . ')';
			} else {
				$clauses['where'] .= ' AND 0';
			}
		}

		return $clauses;
	}

	/*
	|--------------------------------------------------------------------------
	| Helper Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get status label.
	 *
	 * @since 1.5.6
	 * @return string
	 */
	public function get_status_label() {
		$statuses = wcsn_get_key_statuses();
		if ( array_key_exists( $this->get_status(), $statuses ) ) {
			return $statuses[ $this->get_status() ];
		}

		return '&mdash;';
	}

	/**
	 * Reset activations.
	 *
	 * @since 1.0.0
	 */
	public function reset_activations() {
		$activations = $this->get_activations();
		foreach ( $activations as $activation ) {
			$activation->delete();
		}
	}

	/**
	 * Get customer id.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_customer_id() {
		if ( ! $this->get_order() ) {
			return 0;
		}

		return $this->get_order()->get_customer_id();
	}

	/**
	 * Get customer email.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_customer_email() {
		if ( ! $this->get_order() ) {
			return '';
		}

		return $this->get_order()->get_billing_email();
	}

	/**
	 * Get customer name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_customer_name() {
		if ( ! $this->get_order() ) {
			return '';
		}

		return $this->get_order()->get_formatted_billing_full_name();
	}

	/**
	 * Get the expiry date.
	 *
	 * @since  1.4.6
	 * @return string
	 */
	public function get_expire_date() {
		if ( ! $this->get_order_date() || ! $this->get_validity() ) {
			return '';
		}
		$order_date = $this->get_order_date();
		$validity   = $this->get_validity();

		$expiry_date = strtotime( "+{$validity} days", strtotime( $order_date ) );

		return wp_date( 'Y-m-d H:i:s', $expiry_date );
	}

	/**
	 * Recount activations.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function recount_remaining_activation() {
		$count = $this->get_activations( array( 'count' => true ) );
		$this->set_activation_count( $count );
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
		if ( ! $this->get_order_date() || ! $this->get_validity() ) {
			return false;
		}

		return strtotime( $this->get_expire_date() ) < time();
	}

	/**
	 * Get remaining activations.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_activations_left() {
		$activation_count = $this->get_activation_count();
		$activation_limit = $this->get_activation_limit();
		if ( ! $activation_limit ) {
			return 9999;
		}

		return $activation_limit - $activation_count;
	}

	/**
	 * Get activations.
	 *
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @since 1.0.0
	 * @return array|int Array of activations or count.
	 */
	public function get_activations( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'serial_id' => $this->get_id(),
			)
		);

		return Activation::query( $args );
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
		$key = $this->get_serial_key();
		if ( $masked ) {
			$mask_length = ceil( strlen( $key ) / 3 );
			$mask_start  = ceil( ( strlen( $key ) - $mask_length ) / 2 );
			$mask_end    = $mask_start + $mask_length;
			$masked_key  = substr( $key, 0, $mask_start ) . str_repeat( '*', $mask_length ) . substr( $key, $mask_end );
			$key         = sprintf( '<code class="wcsn-key masked" data-masked="%s" data-unmasked="%s">%s</code>', esc_attr( $masked_key ), esc_attr( $key ), $masked_key );
		} else {
			$key = sprintf( '<code class="wcsn-key" data-unmasked="%s" data-masked="%s">%s</code>', esc_attr( $key ), esc_attr( $key ), $key );
		}

		return apply_filters( 'wc_serial_numbers_key_display_key', $key, $this );
	}
}
