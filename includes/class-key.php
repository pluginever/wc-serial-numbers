<?php

namespace PluginEver\WooCommerceSerialNumbers;

use PluginEver\WooCommerceSerialNumbers\Abstracts\Data;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Key handler class.
 *
 * @since 1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Key extends Data {
	/**
	 * This is the name of this object type.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $object_type = 'key';

	/**
	 * Table name.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $table = 'wsn_keys';

	/**
	 * Cache group.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $cache_group = 'wsn_keys';


	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.3.1
	 * @var array
	 */
	protected $core_data = [
		'key'              => '',
		'is_encrypted'     => '0',
		'product_id'       => '',
		'order_id'         => '',
		'order_item_id'    => '',
		'vendor_id'        => '',
		'customer_id'      => '',
		'activation_limit' => '',
		'activation_count' => '',
		'status'           => 'available',
		'valid_for'        => '',
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
	 * @param int|Key|object|null $data data instance.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $data = 0 ) {
		// Call early so default data is set.
		parent::__construct();

		if ( is_numeric( $data ) && $data > 0 ) {
			$this->set_id( $data );
		} elseif ( $data instanceof self ) {
			$this->set_id( absint( $data->get_id() ) );
		} elseif ( ! empty( $data->id ) ) {
			$this->set_id( absint( $data->id ) );
		} else {
			$this->set_object_read( true );
		}

		$this->read();
	}

	/**
	 * Get key.
	 *
	 * @since 1.3.1
	 *
	 * @return string
	 */
	public function get_decrypted_key() {
		if ( $this->is_encrypted ) {
			return Encryption::decrypt( $this->key );
		}

		return $this->key;
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
		switch ( $prop ) {
			case 'key':
				$value = sanitize_text_field( $value );
				break;
			case 'status':
				$value = sanitize_key( $value );
				break;
			case 'product_id':
			case 'order_id':
			case 'order_item_id':
			case 'vendor_id':
			case 'customer_id':
			case 'activation_limit':
			case 'activation_count':
			case 'is_encrypted':
				$value = absint( $value );
				break;
			case 'valid_for':
				$value = absint( $value );
				$value = $value > 0 ? $value : '';
				break;
			case 'date_ordered':
			case 'date_expire':
			case 'date_created':
				$value = $this->is_date_valid( $value ) ? $value : '';
				break;
		}


		parent::set_prop( $prop, $value );
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
			if ( ! array_key_exists( $status, Keys::get_statuses() ) ) {
				$status = 'available';
			}
			// Only allow valid new status.

			// If the old status is set but unknown (e.g. available) assume It's pending for action usage.
			if ( $old_status && ! array_key_exists( $old_status, Keys::get_statuses() ) ) {
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
	 * Saves an object in the database.
	 *
	 * @since 1.0.0
	 * @return \WP_Error|int id on success, WP_Error on failure.
	 */
	public function save() {

		do_action( 'wc_serial_numbers_pre_save_' . $this->object_type, $this->get_id(), $this );

		// check if anything missing before save.
		if ( ! $this->is_date_valid( $this->date_created ) ) {
			$this->date_created = current_time( 'mysql' );
		}

		$requires = [ 'key', 'status', 'product_id' ];
		foreach ( $requires as $required ) {
			if ( empty( $this->$required ) ) {
				/* translators: %s required param */
				return new \WP_Error( 'missing_required_params', sprintf( __( '%s is required.', 'wc-serial-numbers' ), $required ) );
			}
		}

		if ( 'available' !== $this->status ) {
			$requires = [ 'order_id', 'order_item_id', 'product_id', 'customer_id', 'date_ordered' ];
			foreach ( $requires as $required ) {
				if ( empty( $this->$required ) ) {
					/* translators: %s required param */
					return new \WP_Error( 'missing_required_params', sprintf( __( '%s is required.', 'wc-serial-numbers' ), $required ) );
				}
			}
		}

		if ( 'available' === $this->status ) {
			$this->set_prop( 'order_id', null );
			$this->set_prop( 'order_item_id', null );
			$this->set_prop( 'customer_id', null );
			$this->set_prop( 'activation_count', null );
		}

		if ( apply_filters( 'wc_serial_numbers_allow_encryption', true ) && ( ! $this->exists() || array_key_exists( 'key', $this->get_changes() ) ) ) {
			$this->set_prop( 'key', Encryption::encrypt( $this->key ) );
			$this->set_prop( 'is_encrypted', 1 );
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
		 * Fires immediately after a key is inserted or updated in the database.
		 *
		 * @param int $id Key id.
		 * @param array $data Key data array.
		 * @param Key $key Key object.
		 *
		 * @since 1.3.1
		 */
		do_action( 'wc_serial_numbers_saved_' . $this->object_type, $this->get_id(), $this );

		return $this->get_id();
	}
}
