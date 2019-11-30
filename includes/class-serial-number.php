<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Number {
	/**
	 * @var
	 */
	protected $id = null;
	/**
	 * @var
	 */
	protected $serial_key = null;
	/**
	 * @var
	 */
	protected $product_id = null;
	/**
	 * @var
	 */
	protected $activation_limit;
	/**
	 * @var
	 */
	protected $order_id;
	/**
	 * @var
	 */
	protected $activation_email;
	/**
	 * @var
	 */
	protected $status;
	/**
	 * @var
	 */
	protected $validity;
	/**
	 * @var
	 */
	protected $expire_date;
	/**
	 * @var
	 */
	protected $order_date;
	/**
	 * @var
	 */
	protected $created;

	/**
	 * @var
	 */
	protected $product;

	/**
	 * @var
	 */
	protected $order;

	/**
	 * @var null
	 */
	protected $serial = null;

	/**
	 * WC_Serial_Number constructor.
	 */
	public function __construct( $serial = 0 ) {
		$this->init( $serial );
	}

	/**
	 * @param $serial
	 *
	 * @since 1.0.0
	 */
	protected function init( $serial ) {
		if ( is_numeric( $serial ) ) {
			$this->id = absint( $serial );
			$this->get_serial_number( $serial );
		} elseif ( isset( $serial->id ) ) {
			$this->serial = $serial;
			$this->id     = absint( $this->serial->id );
			$this->populate( $serial );
		}
	}


	/**
	 * Gets an call from the database.
	 *
	 * @param int $id (default: 0).
	 *
	 * @return bool
	 */
	public function get_serial_number( $id = 0 ) {

		if ( ! $id ) {
			return false;
		}

		if ( $serial = wcsn_get_serial_number( $id ) ) {
			$this->populate( $serial );

			return true;
		}

		return false;
	}


	/**
	 * Populates an call from the loaded post data.
	 *
	 * @param object $serial
	 */
	public function populate( $serial ) {
		$this->id     = $serial->id;
		$this->serial = $serial;
		foreach ( $serial as $key => $value ) {
			$this->$key = $value;
		}

		if ( ! empty( $serial->product_id ) && class_exists( 'WC_Product' ) ) {
			$this->product = new WC_Product( $serial->product_id );
		}

		if ( ! empty( $serial->order_id ) && class_exists( 'WC_Order' ) ) {
			$this->order = new WC_Order( $serial->order_id );
		}
	}


	/**
	 * @param $order_id
	 *
	 * @since 1.0.0
	 */
	public function assign_order( $order_id ) {
		if ( ! empty( $order_id ) && class_exists( 'WC_Order' ) ) {
			$this->order = new WC_Order( $order_id );

			wcsn_insert_serial_number( [
				'id'               => $this->id,
				'order_id'         => $order_id,
				'activation_email' => $this->order->get_billing_email( 'edit' ),
				'status'           => 'active',
				'order_date'       => current_time( 'mysql' )
			] );

		}
	}

	/**
	 * Revoke
	 * @since 1.0.0
	 */
	public function revoke_key() {
		//todo check if reuseable or not
		wcsn_insert_serial_number( [
			'id'               => $this->id,
			'order_id'         => '',
			'activation_email' => '',
			'status'           => 'new',
			'order_date'       => null
		] );
	}







}
