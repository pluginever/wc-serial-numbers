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
		add_filter( 'wc_serial_numbers_key_pre_insert_data', array( __CLASS__, 'encrypt_key' ) );
		add_filter( 'wc_serial_numbers_key_pre_update_data', array( __CLASS__, 'encrypt_key' ) );
		add_action( 'wc_serial_numbers_key_inserted', array( __CLASS__, 'enable_product' ) );
		add_action( 'wc_serial_numbers_key_deleted', array( __CLASS__, 'delete_activations' ) );
		add_action( 'wc_serial_numbers_activation_inserted', array( __CLASS__, 'update_activation_count' ) );
		add_action( 'wc_serial_numbers_activation_deleted', array( __CLASS__, 'update_activation_count' ) );
	}

	/**
	 * Encrypt key.
	 *
	 * @param array $data The key data.
	 *
	 * @since 1.4.6
	 */
	public static function encrypt_key( $data ) {
		if ( ! empty( $data['serial_key'] ) ) {
			$data['serial_key'] = wcsn_encrypt_key( $data['serial_key'] );
		}

		return $data;
	}

	/**
	 * Enable product.
	 *
	 * @param Key $key The key object.
	 *
	 * @since 1.4.6
	 */
	public static function enable_product( $key ) {
		if ( ! $key instanceof Key ) {
			return;
		}

		$product_id = $key->get_product_id();
		if ( $product_id ) {
			update_post_meta( $product_id, '_is_serial_number', 'yes' );
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
		$activations = $key->get_activations();
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
		$key = Key::find( $activation->get_serial_id() );
		if ( $key ) {
			$key->recount_remaining_activation();
		}
	}
}
