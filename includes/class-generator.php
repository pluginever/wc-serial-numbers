<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Generator object data class.
 *
 * @since 1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Generator extends Abstracts\Data {

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
		'activation_limit' => '',
		'valid_for'        => '',
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
			case 'name':
			case 'pattern':
				$value = sanitize_text_field( $value );
				break;
			case 'activation_limit':
				$value = absint( $value );
				break;
			case 'valid_for':
				$value = absint( $value );
				$value = $value > 0 ? $value : '';
				break;

			case 'date_created':
				$value = $this->is_date_valid( $value ) ? $value : '';
				break;
		}


		parent::set_prop( $prop, $value );
	}


	/**
	 * Saves an object in the database.
	 *
	 * @since 1.0.0
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
				return new \WP_Error(
					'missing_required_params',
					sprintf(
					/* translator %s generator rule name */
						__( 'Generator %s is required.', 'wc-serial-numbers' ),
						$required
					)
				);
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
