<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Plugin class.
 *
 * @class Plugin
 * @since 1.4.0
 * @package WooCommerceSerialNumbers
 */
class Plugin {
	/**
	 * Include required core files used in admin and on the frontend.
	 * @since 1.2.0
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/includes/deprecated-functions.php';
	}
}
