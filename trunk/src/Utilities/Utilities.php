<?php

namespace WooCommerceSerialNumbers\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Class Utilities.
 *
 * Responsible for providing utility functions.
 *
 * @package WooCommerceSerialNumbers\Utilities
 */
class Utilities {

	/**
	 * Utilities constructor.
	 *
	 * @since 1.5.6
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init.
	 *
	 * @since 1.5.6
	 */
	public function init() {}
}
