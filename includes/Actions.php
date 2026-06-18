<?php

namespace WooCommerceSerialNumbers;

use WooCommerceSerialNumbers\Models\Activation;
use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Actions class.
 *
 * A class that handles common actions and filters.
 *
 * @since 1.5.6
 * @package WooCommerceSerialNumbers
 */
class Actions {

	/**
	 * Actions constructor.
	 *
	 * @since 1.5.6
	 */
	public function __construct() {
		add_action( 'wc_serial_numbers_key_inserted', array( __CLASS__, 'enable_product' ) );
		add_action( 'wc_serial_numbers_key_deleted', array( __CLASS__, 'delete_activations' ) );
		add_action( 'wc_serial_numbers_activation_inserted', array( __CLASS__, 'update_activation_count' ) );
		add_action( 'wc_serial_numbers_activation_deleted', array( __CLASS__, 'update_activation_count' ) );
	}

	/**
	 * Enable serial numbers on the product when a key is inserted.
	 *
	 * @param Key $key The inserted key object.
	 *
	 * @since 1.4.6
	 */
	public static function enable_product( $key ) {
		if ( $key && $key->product_id ) {
			update_post_meta( $key->product_id, '_is_serial_number', 'yes' );
		}
	}

	/**
	 * Delete activations.
	 *
	 * @param Key $key The key object.
	 *
	 * @since 1.4.6
	 */
	public static function delete_activations( $key ) {
		$activations = $key->activations;
		if ( $activations ) {
			foreach ( $activations as $activation ) {
				$activation->delete();
			}
		}
	}

	/**
	 * Revoke order item keys.
	 *
	 * @param bool $revoke The revoke flag.
	 *
	 * @since 1.4.6
	 */
	public static function revoke_order_item_keys( $revoke ) {
		if ( 'yes' !== get_option( 'wc_serial_numbers_revoke_keys', 'yes' ) ) {
			$revoke = false;
		}

		return $revoke;
	}


	/**
	 * Update activation count.
	 *
	 * @param Activation $activation The activation object.
	 *
	 * @since 1.0.0
	 */
	public static function update_activation_count( $activation ) {
		$key = Key::find( $activation->serial_id );
		if ( $key ) {
			$key->recount_remaining_activation();
		}
	}
}
