<?php
defined( 'ABSPATH' ) || exit();

class WCSN_API {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @return self Main instance.
	 * @since  1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * WCSN_API constructor.
	 */
	public function __construct() {

	}


	public static function is_enabled() {

	}


}

WCSN_API::instance();
