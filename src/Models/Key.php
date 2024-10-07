<?php

namespace WooCommerceSerialNumbers\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Class Key.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Models
 */
class Key extends Model {
	/**
	 * Table name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $table_name = 'serial_numbers';

	/**
	 * Object type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $object_type = 'key';

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $core_data = array(
		'id'               => 0,
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
	 * @return string
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
	 * Get the serial key.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return string
	 */
	public function get_serial_key( $context = 'edit' ) {
		return $this->get_prop( 'serial_key', $context );
	}

	/**
	 * Get the key.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
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
	 *
	 * @return void
	 */
	public function set_serial_key( $serial_key ) {
		$this->set_prop( 'serial_key', sanitize_textarea_field( $serial_key ) );
	}

	/**
	 * Get the product id.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return int
	 */
	public function get_product_id( $context = 'edit' ) {
		return $this->get_prop( 'product_id', $context );
	}

	/**
	 * Get product.
	 *
	 * @since 1.4.6
	 *
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
	 *
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
	 *
	 * @return void
	 */
	public function set_product_id( $product_id ) {
		$this->set_prop( 'product_id', absint( $product_id ) );
	}

	/**
	 * Get the activation limit.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return int
	 */
	public function get_activation_limit( $context = 'edit' ) {
		return $this->get_prop( 'activation_limit', $context );
	}

	/**
	 * Set the activation limit.
	 *
	 * @param int $activation_limit Activation limit.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_activation_limit( $activation_limit ) {
		$this->set_prop( 'activation_limit', absint( $activation_limit ) );
	}

	/**
	 * Get the activation count.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return int
	 */
	public function get_activation_count( $context = 'edit' ) {
		return $this->get_prop( 'activation_count', $context );
	}

	/**
	 * Set the activation count.
	 *
	 * @param int $activation_count Activation count.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_activation_count( $activation_count ) {
		$this->set_prop( 'activation_count', absint( $activation_count ) );
	}

	/**
	 * Get the order id.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return int
	 */
	public function get_order_id( $context = 'edit' ) {
		return $this->get_prop( 'order_id', $context );
	}

	/**
	 * Get order.
	 *
	 * @since 1.4.6
	 *
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
	 *
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
	 *
	 * @return void
	 */
	public function set_order_id( $order_id ) {
		$this->set_prop( 'order_id', absint( $order_id ) );
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
		return $this->get_prop( 'order_item_id', $context );
	}

	/**
	 * Set the order item id.
	 *
	 * @param int $order_item_id Order id.
	 *
	 * @since  2.0.6
	 * @return void
	 */
	public function set_order_item_id( $order_item_id ) {
		$this->set_prop( 'order_item_id', absint( $order_item_id ) );
	}

	/**
	 * Get the vendor id.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return int
	 */
	public function get_vendor_id( $context = 'edit' ) {
		return $this->get_prop( 'vendor_id', $context );
	}

	/**
	 * Get vendor.
	 *
	 * @since 1.4.6
	 *
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
	 *
	 * @return void
	 */
	public function set_vendor_id( $vendor_id ) {
		$this->set_prop( 'vendor_id', absint( $vendor_id ) );
	}

	/**
	 * Get the status.
	 * Possible values: 'active', 'inactive', 'expired', 'cancelled', 'pending', 'failed', 'refunded', 'deleted'.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return string
	 */
	public function get_status( $context = 'edit' ) {
		return $this->get_prop( 'status', $context );
	}

	/**
	 * Set the status.
	 * Possible values: 'active', 'inactive', 'expired', 'cancelled', 'pending', 'failed', 'refunded', 'deleted'.
	 *
	 * @param string $status Status.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_status( $status ) {
		if ( array_key_exists( $status, wcsn_get_key_statuses() ) ) {
			$this->set_prop( 'status', $status );
		}
	}

	/**
	 * Get the validity.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return string
	 */
	public function get_validity( $context = 'edit' ) {
		return $this->get_prop( 'validity', $context );
	}

	/**
	 * Set the validity.
	 *
	 * @param string $validity Validity.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_validity( $validity ) {
		$this->set_prop( 'validity', absint( $validity ) );
	}

	/**
	 * Get the order date.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return string
	 */
	public function get_order_date( $context = 'edit' ) {
		return $this->get_prop( 'order_date', $context );
	}

	/**
	 * Set the order date.
	 *
	 * @param string $order_date Order date.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_order_date( $order_date ) {
		$this->set_date_prop( 'order_date', $order_date );
	}

	/**
	 * Get the source.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return string
	 */
	public function get_source( $context = 'edit' ) {
		return $this->get_prop( 'source', $context );
	}

	/**
	 * Set the source.
	 *
	 * @param string $source Source.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_source( $source ) {
		$this->set_prop( 'source', sanitize_text_field( $source ) );
	}

	/**
	 * Get the created date.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since  1.4.6
	 *
	 * @return int
	 */
	public function get_created_date( $context = 'edit' ) {
		return $this->get_prop( 'created_date', $context );
	}

	/**
	 * Set the created date.
	 *
	 * @param string $created_date Created date.
	 *
	 * @since  1.4.6
	 *
	 * @return void
	 */
	public function set_created_date( $created_date ) {
		$this->set_date_prop( 'created_date', $created_date );
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
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public function save() {
		// Product id is required.
		if ( ! $this->get_product_id() ) {
			return new \WP_Error( 'missing-required', __( 'Product id is required.', 'wc-serial-numbers' ) );
		}

		// Check if product id is valid.
		if ( empty( wc_get_product( $this->get_product_id() ) ) ) {
			return new \WP_Error( 'invalid-data', __( 'Product id is invalid.', 'wc-serial-numbers' ) );
		}

		// Serial key is required.
		if ( ! $this->get_serial_key() ) {
			return new \WP_Error( 'missing-required', __( 'Serial key is required.', 'wc-serial-numbers' ) );
		}

		// Duplicate serial key is not allowed.
		if ( ! wcsn_is_duplicate_key_allowed() ) {
			$existing = self::get(
				array(
					'serial_key' => $this->get_serial_key(),
				)
			);

			if ( $existing && $existing->get_id() !== $this->get_id() ) {
				return new \WP_Error( 'invalid-data', __( 'Serial key already exists. Duplicate serial keys are not allowed.', 'wc-serial-numbers' ) );
			}
		}

		// If order id is set, check if it is valid.
		if ( $this->get_order_id() && empty( wc_get_order( $this->get_order_id() ) ) ) {
			return new \WP_Error( 'invalid-data', __( 'Order id is invalid.', 'wc-serial-numbers' ) );
		}

		// If status is available, order date should not be set.
		if ( 'available' === $this->get_status() ) {
			$this->set_order_id( 0 );
			$this->set_order_date( null );
			$this->set_activation_count( 0 );
		}

		// If order is set, order date should be set.
		if ( $this->get_order_id() && ! $this->get_order_date() ) {
			$order = wc_get_order( $this->get_order_id() );
			// Get order confirmed date.
			$order_date = $order->get_date_completed() ? $order->get_date_completed()->getTimestamp() : wp_date( 'Y-m-d H:i:s' );
			$this->set_date_prop( 'order_date', $order_date );
		}

		// If key is not created yet, set created date.
		if ( ! $this->get_created_date() ) {
			$this->set_date_prop( 'created_date', wp_date( 'Y-m-d H:i:s' ) );
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
	 * Retrieve the object instance.
	 *
	 * @param int|array|static $data Object ID or array of arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return static|false Object instance on success, false on failure.
	 */
	public static function get( $data ) {
		// If by is set to serial key, encrypt it.
		if ( is_array( $data ) && array_key_exists( 'serial_key', $data ) ) {
			$data['serial_key'] = wcsn_encrypt_key( $data['serial_key'] );
		}

		return parent::get( $data );
	}

	/**
	 * Prepare where query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function prepare_where_query( $clauses, $args = array() ) {
		global $wpdb;
		$clauses = parent::prepare_where_query( $clauses, $args );

		// If customer id is set, find the orders having that customer id and limit the results to those orders.
		if ( ! empty( $args['customer_id'] ) ) {
			$customer_id = absint( $args['customer_id'] );
			$order_ids   = wc_get_orders(
				array(
					'customer_id' => $customer_id,
					'limit'       => - 1,
					'return'      => 'ids',
				)
			);

			if ( ! empty( $order_ids ) ) {
				$clauses['where'] .= " AND {$this->table_name}.order_id IN (" . implode( ',', $order_ids ) . ')';
			} else {
				$clauses['where'] .= ' AND 0';
			}
		}

		return $clauses;
	}

	/**
	 * Prepare search query.
	 *
	 * @param array $clauses Query clauses.
	 * @param array $args Array of args to pass to the query method.
	 *
	 * @since 1.0.0
	 * @return array
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
				if ( 'serial_key' === $column ) {
					$like = '%' . $wpdb->esc_like( wcsn_encrypt_key( $search ) ) . '%';
				}
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

	/*
	|--------------------------------------------------------------------------
	| Helpers Methods
	|--------------------------------------------------------------------------
	|
	| Common methods used by the class.
	|
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
	 *
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
	 *
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
	 *
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
	 *
	 * @return string
	 */
	public function get_expire_date() {
		// If order date is not set or validity is not set, return empty string.
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
	 *
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
	 *
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
			// Divide the length of the key by 3 and round up. Then mask the middle part of the key.
			$mask_length = ceil( strlen( $key ) / 3 );
			$mask_start  = ceil( ( strlen( $key ) - $mask_length ) / 2 );
			$mask_end    = $mask_start + $mask_length;
			$masked_key  = substr( $key, 0, $mask_start ) . str_repeat( '*', $mask_length ) . substr( $key, $mask_end );
			$key         = sprintf( '<code class="wcsn-key masked" data-masked="%s" data-unmasked="%s">%s</code>', esc_attr( $masked_key ), esc_attr( $key ), $masked_key );
		} else {
			$key = sprintf( '<code class="wcsn-key" data-unmasked="%s" data-masked="%s">%s</code>', esc_attr( $key ), esc_attr( $key ), $key );
		}

		return apply_filters( $this->get_hook_prefix() . '_display_key', $key, $this );
	}
}
