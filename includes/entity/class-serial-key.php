<?php

namespace PluginEver\WooCommerceSerialNumbers\Entity;

use PluginEver\WooCommerceSerialNumbers\Serial_Keys;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

class Serial_Key extends Data {
	/**
	 * This is the name of this object type.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $object_type = 'serial_key';

	/**
	 * Table name.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $table = 'wcsn_keys';

	/**
	 * Cache group.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $cache_group = 'wcsn_keys';


	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.3.1
	 * @var array
	 */
	protected $core_data = [
		'key'              => '',
		'product_id'       => '',
		'order_id'         => '',
		'order_item_id'    => '',
		'vendor_id'        => '',
		'activation_limit' => '',
		'activation_count' => '',
		'status'           => 'available',
		'validity'         => '',
		'hash'             => '',
		'date_expire'      => '',
		'date_ordered'     => '',
		'date_created'     => null,
	];

	/**
	 * Hold status transition.
	 *
	 * @since 1.3.1
	 *
	 * @var array
	 */
	protected $status_transition = array();

	/**
	 * Serial key constructor.
	 *
	 * @param int|Serial_key|object|null $serial_key serial_key instance.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $serial_key = 0 ) {
		// Call early so default data is set.
		parent::__construct();

		if ( is_numeric( $serial_key ) && $serial_key > 0 ) {
			$this->set_id( $serial_key );
		} elseif ( $serial_key instanceof self ) {
			$this->set_id( absint( $serial_key->get_id() ) );
		} elseif ( ! empty( $serial_key->id ) ) {
			$this->set_id( absint( $serial_key->id ) );
		} else {
			$this->set_object_read( true );
		}

		$this->read();
	}


	/**
	 * Set product id.
	 *
	 * @param int $product_id Product id.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_product_id( $product_id ) {
		$this->set_prop( 'product_id', absint( $product_id ) );
	}

	/**
	 * Set order id.
	 *
	 * @param int $order_id Order id.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_order_id( $order_id ) {
		$this->set_prop( 'order_id', absint( $order_id ) );
	}

	/**
	 * Set item id.
	 *
	 * @param int $order_item_id Order item id.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_order_item_id( $order_item_id ) {
		$this->set_prop( 'order_item_id', absint( $order_item_id ) );
	}

	/**
	 * Set vendor id.
	 *
	 * @param int $vendor_id Order id.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_vendor_id( $vendor_id ) {
		$this->set_prop( 'vendor_id', absint( $vendor_id ) );
	}

	/**
	 * Set activation limit.
	 *
	 * @param int $activation_limit Activation limit.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_activation_limit( $activation_limit ) {
		$this->set_prop( 'activation_limit', absint( $activation_limit ) );
	}

	/**
	 * Set activation count.
	 *
	 * @param int $activation_count Activation count.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_activation_count( $activation_count ) {
		$this->set_prop( 'activation_count', absint( $activation_count ) );
	}

	/**
	 * Set status.
	 *
	 * @param string $status Serial number status.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_status( $status ) {
		$old_status = $this->get_status();
		// If setting the status, ensure it's set to a valid status.
		if ( true === $this->object_read ) {
			if ( ! array_key_exists( $status, Serial_Keys::get_statuses() ) ) {
				$status = 'available';
			}
			// Only allow valid new status.

			// If the old status is set but unknown (e.g. available) assume It's pending for action usage.
			if ( $old_status && ! array_key_exists( $old_status, Serial_Keys::get_statuses() ) ) {
				$old_status = 'available';
			}
		}

		$this->set_prop( 'status', $status );

		$this->status_transition = array(
			'from' => $old_status,
			'to'   => $status,
		);
	}

	/**
	 * Set expire date.
	 *
	 * @param string $date_expire Serial number date_expire.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_date_expire( $date_expire ) {
		$this->set_date_prop( 'date_expire', $date_expire );
	}

	/**
	 * Set ordered date.
	 *
	 * @param string $date_ordered Serial number date_ordered.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_date_ordered( $date_ordered ) {
		$this->set_date_prop( 'date_ordered', $date_ordered );
	}

	/**
	 * Set created date.
	 *
	 * @param string $date_created Serial number date_created.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_date_created( $date_created ) {
		$this->set_date_prop( 'date_created', $date_created );
	}

	/**
	 * @return int|\WP_Error
	 */
	public function save() {
		// check if anything missing before save.
		if ( ! $this->is_date_valid( $this->date_created ) ) {
			$this->date_created = current_time( 'mysql' );
		}

		$requires = [ 'key', 'status', 'product_id' ];
		foreach ( $requires as $required ) {
			if ( empty( $this->$required ) ) {
				return new \WP_Error( 'missing_required_params', sprintf( __( '%s is required.', 'wc-serial-numbers' ), $required ) );
			}
		}

		if ( 'available' !== $this->status ) {
			$requires = [ 'order_id', 'order_item_id', 'product_id', 'date_ordered' ];
			foreach ( $requires as $required ) {
				if ( empty( $this->$required ) ) {
					return new \WP_Error( 'missing_required_params', sprintf( __( '%s is required.', 'wc-serial-numbers' ), $required ) );
				}
			}
		}

		if ( 'available' === $this->status ) {
			$this->set_prop( 'order_id', 0 );
			$this->set_prop( 'order_item_id', 0 );
			$this->set_prop( 'activation_count', 0 );
		}

		if ( ! $this->exists() ) {
			$is_error = $this->create();
		} else {
			$is_error = $this->update();
		}

		if ( is_wp_error( $is_error ) ) {
			return $is_error;
		}

		$this->apply_changes();

		// Clear cache.
		wp_cache_delete( $this->get_id(), $this->cache_group );
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );

		/**
		 * Fires immediately after a generator is inserted or updated in the database.
		 *
		 * @param int $id Generator id.
		 * @param array $data Generator data array.
		 * @param Generator $account Generator object.
		 *
		 * @since 1.3.1
		 */
		do_action( 'wc_serial_numbers_saved_' . $this->object_type, $this->get_id(), $this );

		return $this->get_id();
	}

	/*
	|--------------------------------------------------------------------------
	| Status handling.
	|--------------------------------------------------------------------------
	*/
	public function set_status_available() {
		if ( 'available' === $this->status ) {
			$this->set_prop( 'order_id', 0 );
			$this->set_prop( 'order_item_id', 0 );
		}
	}
}
