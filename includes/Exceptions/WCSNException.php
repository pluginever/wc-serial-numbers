<?php

namespace WCSerialNumbers\Exceptions;

use Exception;

class WCSNException extends Exception {

	/**
	 * Error code
	 *
	 * @since 1.2.8
	 *
	 * @var string
	 */
	protected $error_code = '';

	/**
	 * Class constructor
	 *
	 * @since 1.2.8
	 *
	 * @param string|\WP_Error $error_code  Error code string or WP_Error
	 * @param string           $message
	 * @param int              $status_code
	 */
	public function __construct( $error_code, $message = '', $status_code = 422 ) {
		$this->error_code = $error_code;

		parent::__construct( $message, $status_code );
	}

	/**
	 * Get error code
	 *
	 * @since 1.2.8
	 *
	 * @return string
	 */
	final public function get_error_code() {
		return $this->error_code;
	}

	/**
	 * Get error message
	 *
	 * @since 1.2.8
	 *
	 * @return string
	 */
	final public function get_message() {
		return $this->getMessage();
	}

	/**
	 * Get error status code
	 *
	 * @since 1.2.8
	 *
	 * @return int
	 */
	final public function get_status_code() {
		return $this->getCode();
	}
}
