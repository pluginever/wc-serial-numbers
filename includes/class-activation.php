<?php
namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Activation object data class.
 *
 * @since 1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Activation extends Abstracts\Data {

	/**
	 * This is the name of this object type.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $object_type = 'activation';

	/**
	 * Table name.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $table = 'wcsn_activations';

	/**
	 * Cache group.
	 *
	 * @since 1.3.1
	 * @var string
	 */
	protected $cache_group = 'wcsn_activations';


	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.3.1
	 * @var array
	 */
	protected $core_data = [
		'key_id'          => '',
		'instance'        => '',
		'platform'        => '',
		'is_active'       => '',
		'date_activation' => null,
	];


	/**
	 * Generator constructor.
	 *
	 * @param int|Activation|object|null $activation activation instance.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $activation = 0 ) {
		// Call early so default data is set.
		parent::__construct();

		if ( is_numeric( $activation ) && $activation > 0 ) {
			$this->set_id( $activation );
		} elseif ( $activation instanceof self ) {
			$this->set_id( absint( $activation->get_id() ) );
		} elseif ( ! empty( $activation->id ) ) {
			$this->set_id( absint( $activation->id ) );
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
			case 'platform':
			case 'instance':
				$value = sanitize_text_field( $value );
				break;
			case 'key_id':
			case 'is_active':
				$value = absint( $value );
				break;
			case 'date_activation':
			case 'date_created':
				$value = $this->is_date_valid( $value ) ? $value : '';
				break;
		}


		parent::set_prop( $prop, $value );
	}

	/**
	 * Save activation in the database.
	 *
	 * @return int|\WP_Error
	 */
	public function save() {
		$requires = [ 'key_id', 'instance', 'platform' ];
		foreach ( $requires as $required ) {
			if ( empty( $this->$required ) ) {
				return new \WP_Error( 'missing_required_params', sprintf( __( 'For activation %s is required.', 'wc-serial-numbers' ), $required ) );
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
		 * Fires immediately after an activation is inserted or updated in the database.
		 *
		 * @param int $id Activation id.
		 * @param array $data Activation data array.
		 * @param Activation $activation Activation object.
		 *
		 * @since 1.3.1
		 */
		do_action( 'wc_serial_numbers_saved_' . $this->object_type, $this->get_id(), $this );

		return $this->get_id();
	}
}
