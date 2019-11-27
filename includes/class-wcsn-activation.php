<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Serial_Number_Activation {
	/**
	 * @var
	 */
	protected $id;
	/**
	 * @var
	 */
	protected $serial_id;
	/**
	 * @var
	 */
	protected $instance;
	/**
	 * @var
	 */
	protected $active;
	/**
	 * @var
	 */
	protected $platform;
	/**
	 * @var
	 */
	protected $activation_time;

	/**
	 * @var null
	 */
	protected $activation = null;

	/**
	 * WC_Serial_Number_Activation constructor.
	 *
	 * @param int $serial
	 */
	public function __construct( $activation = 0 ) {
		$this->init( $activation );
	}

	/**
	 * Init/load the activation object. Called from the constructor.
	 *
	 * @param $activation
	 *
	 * @since 1.0.0
	 */
	protected function init( $activation ) {
		if ( is_numeric( $activation ) ) {
			$this->id         = absint( $activation );
			$this->activation = $this->get_activation( $activation );
		} elseif ( $activation instanceof WC_Serial_Number_Activation ) {
			$this->id     = absint( $activation->id );
			$this->populate( $activation );
		} elseif ( isset( $activation->id ) ) {
			$this->activation = $activation;
			$this->populate( $activation );
		}
	}


	/**
	 * @param int $id
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	protected function get_activation( $id = 0 ) {
		if ( ! $id ) {
			return false;
		}
		global $wpdb;

		if ( $activation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcsn_activation WHRE id=%d", $id ) ) ) {
			$this->populate( $activation );

			return true;
		}

		return false;
	}


	/**
	 * @param $activation
	 *
	 * @since 1.0.0
	 */
	public function populate( $activation ) {
		$this->id      = $activation->id;
		$this->user_id = $activation->user_id;
		foreach ( $activation as $key => $value ) {
			$this->$key = $value;
		}
	}


}
