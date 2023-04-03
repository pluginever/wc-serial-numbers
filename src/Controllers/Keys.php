<?php

namespace WooCommerceSerialNumbers\Controllers;

use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Keys.
 *
 * @since   1.4.6
 * @package WooCommerceSerialNumbers\Controllers
 */
class Keys extends \WooCommerceSerialNumbers\Lib\Singleton {

	/**
	 * Keys constructor.
	 *
	 * @since 1.4.6
	 */
	protected function __construct() {
		add_action( 'wc_serial_numbers_key_db_data', array( __CLASS__, 'decrypt_key' ) );
		add_action( 'wc_serial_numbers_key_insert_data', array( __CLASS__, 'encrypt_key' ) );
		add_action( 'wc_serial_numbers_key_update_data', array( __CLASS__, 'encrypt_key' ) );
		add_action( 'wc_serial_numbers_key_insert', array( __CLASS__, 'enable_product' ) );
		add_action( 'wc_serial_numbers_key_deleted', array( __CLASS__, 'delete_activations' ) );
	}

	/**
	 * Decrypt key.
	 *
	 * @param array $data The key data.
	 *
	 * @since 1.4.6
	 */
	public static function decrypt_key( $data ) {
		if ( ! empty( $data['serial_key'] ) ) {
			$data['serial_key'] = wc_serial_numbers_decrypt_key( $data['serial_key'] );
		}

		return $data;
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
			$data['serial_key'] = wc_serial_numbers_encrypt_key( $data['serial_key'] );
		}

		return $data;
	}

	/**
	 * Enable product.
	 *
	 * @param int $key_id The key ID.
	 *
	 * @since 1.4.6
	 */
	public static function enable_product( $key_id ) {
		$key = Key::get( $key_id );

		if ( $key ) {
			$product_id = $key->get_product_id();

			if ( $product_id ) {
				update_post_meta( $product_id, '_is_serial_number', 'yes' );
			}
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
}
