<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Activation class.
 *
 * @since 1.0.0
 */
class Activation extends Framework\Data {
	/**
	 * Table name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $table_name = 'serial_numbers_activations';

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $columns = array(
		'serial_id'       => 0,
		'instance'        => 0,
		'active'          => 0,
		'platform'        => '',
		'activation_time' => null,
	);

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	|
	| Methods for getting data from the object.
	|
	*/

	/**
	 * Get serial ID.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_serial_id( $context = 'edit' ) {
		return $this->get_prop( 'serial_id', $context );
	}

	/**
	 * Get instance.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_instance( $context = 'edit' ) {
		return $this->get_prop( 'instance', $context );
	}

	/**
	 * Get active.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_active( $context = 'edit' ) {
		return $this->get_prop( 'active', $context );
	}

	/**
	 * Get platform.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_platform( $context = 'edit' ) {
		return $this->get_prop( 'platform', $context );
	}

	/**
	 * Get activation time.
	 *
	 * @param string $context What the value is for. Valid values are 'view' and 'edit'.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_activation_time( $context = 'edit' ) {
		return $this->get_date_prop( 'activation_time', $context );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	|
	| Functions for setting boll data. These should not update anything in the
	| database itself and should only change what is stored in the class
	| object.
	|
	*/

	/**
	 * Set serial ID.
	 *
	 * @param int $serial_id Serial ID.
	 *
	 * @since 1.0.0
	 */
	public function set_serial_id( $serial_id ) {
		$this->set_prop( 'serial_id', absint( $serial_id ) );
	}

	/**
	 * Set instance.
	 *
	 * @param string $instance Instance.
	 *
	 * @since 1.0.0
	 */
	public function set_instance( $instance ) {
		$this->set_prop( 'instance', sanitize_text_field( $instance ) );
	}

	/**
	 * Set active.
	 *
	 * @param int $active Active.
	 *
	 * @since 1.0.0
	 */
	public function set_active( $active ) {
		$this->set_prop( 'active', absint( $active ) );
	}

	/**
	 * Set platform.
	 *
	 * @param string $platform Platform.
	 *
	 * @since 1.0.0
	 */
	public function set_platform( $platform ) {
		$this->set_prop( 'platform', sanitize_text_field( $platform ) );
	}

	/**
	 * Set activation time.
	 *
	 * @param string $activation_time Activation time.
	 *
	 * @since 1.0.0
	 */
	public function set_activation_time( $activation_time ) {
		$this->set_prop( 'activation_time', sanitize_text_field( $activation_time ) );
	}
}
