<?php

namespace WooCommerceSerialNumbers\Controllers;

use WooCommerceSerialNumbers\Models\Activation;
use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activation.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Controllers
 */
class Activations extends \WooCommerceSerialNumbers\Lib\Singleton {

	/**
	 * Activation constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'wc_serial_numbers_activation_inserted', array( __CLASS__, 'update_activation_count' ) );
		add_action( 'wc_serial_numbers_activation_deleted', array( __CLASS__, 'update_activation_count' ) );
	}

	/**
	 * Update activation count.
	 *
	 * @param Activation $activation The activation object.
	 *
	 * @since 1.0.0
	 */
	public static function update_activation_count( $activation ) {
		$key = Key::get( $activation->serial_id );
		if ( $key ) {
			$key->recount_remaining_activation();
		}
	}
}
