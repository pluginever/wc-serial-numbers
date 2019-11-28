<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_MetaBox{
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
	 * WC_Serial_Numbers_MetaBox constructor.
	 */
	public function __construct() {
	}


}

WC_Serial_Numbers_MetaBox::instance();
