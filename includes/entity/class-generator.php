<?php

namespace PluginEver\WooCommerceSerialNumbers\Entity;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

class Generator extends Data {
	/**
	 * This is the name of this object type.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $object_type = 'generator';

	/**
	 * Table name.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $table = 'wcsn_generators';

	/**
	 * Cache group.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $cache_group = 'wcsn_generators';


	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.3.1
	 * @var array
	 */
	protected $core_data = [
		'name'             => '',
		'pattern'          => '',
		'is_sequential'    => '',
		'activation_limit' => '',
		'validity'         => '',
		'date_expire'      => '',
		'date_created'     => '',
	];

	/**
	 * Generator constructor.
	 *
	 * @param int|Generator|object|null $generator generator instance.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $generator = 0 ) {
		// Call early so default data is set.
		parent::__construct();

		if ( is_numeric( $generator ) && $generator > 0 ) {
			$this->set_id( $generator );
		} elseif ( $generator instanceof self ) {
			$this->set_id( absint( $generator->get_id() ) );
		} elseif ( ! empty( $generator->id ) ) {
			$this->set_id( absint( $generator->id ) );
		} else {
			$this->set_object_read( true );
		}

		$this->read();
	}

	/**
	 * Set sequential status.
	 *
	 * @param int|bool $is_sequential Is sequential.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_is_sequential( $is_sequential ) {
		$this->set_prop( 'is_sequential', wc_string_to_bool( $is_sequential ) );
	}

	/**
	 * Set activation limit.
	 *
	 * @param int $count Activation limit count.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_activation_limit( $count ) {
		$this->set_prop( 'activation_limit', absint( $count ) );
	}

	/**
	 * Set validity limit.
	 *
	 * @param int $validity validity limit.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_validity( $validity ) {
		$this->set_prop( 'validity', absint( $validity ) );
	}

	/**
	 * Set expire date.
	 *
	 * @param string $date_expire Expire date.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_date_expire( $date_expire ) {
		$this->set_date_prop( 'date_expire', $date_expire );
	}

	/**
	 * Set date created.
	 *
	 * @param string $date_created Expire date.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function set_date_created( $date_created ) {
		$this->set_date_prop( 'date_created', $date_created );
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
	 * Saves an object in the database.
	 *
	 * @since 1.1.3
	 * @return \WP_Error|int id on success, WP_Error on failure.
	 */
	public function save() {
		// check if anything missing before save.
		if ( ! $this->is_date_valid( $this->date_created ) ) {
			$this->date_created = current_time( 'mysql' );
		}

		$requires = [ 'name', 'pattern' ];
		foreach ( $requires as $required ) {
			if ( empty( $this->$required ) ) {
				return new \WP_Error( 'missing_required_params', sprintf( __( 'Generator %s is required.', 'wc-serial-numbers' ), $required ) );
			}
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
		 * @param Generator $generator Generator object.
		 *
		 * @since 1.3.1
		 */
		do_action( 'wc_serial_numbers_saved_' . $this->object_type, $this->get_id(), $this );

		return $this->get_id();
	}
}
