<?php

namespace WooCommerceSerialNumbers;

use WooCommerceSerialNumbers\B8\Component;
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
class Actions extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'wc_serial_numbers_key_db_data', array( $this, 'decrypt_key' ) );
		add_action( 'wc_serial_numbers_key_insert_data', array( $this, 'encrypt_key' ) );
		add_action( 'wc_serial_numbers_key_update_data', array( $this, 'encrypt_key' ) );
		add_action( 'wc_serial_numbers_key_insert', array( $this, 'enable_product' ) );
		add_action( 'wc_serial_numbers_key_deleted', array( $this, 'delete_activations' ) );
		add_action( 'wc_serial_numbers_activation_inserted', array( $this, 'update_activation_count' ) );
		add_action( 'wc_serial_numbers_activation_deleted', array( $this, 'update_activation_count' ) );
	}

	/**
	 * Decrypt key.
	 *
	 * @param array $data The key data.
	 *
	 * @since 1.4.6
	 */
	public function decrypt_key( $data ) {
		if ( ! empty( $data['serial_key'] ) ) {
			$data['serial_key'] = wcsn_decrypt_key( $data['serial_key'] );
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
	public function encrypt_key( $data ) {
		if ( ! empty( $data['serial_key'] ) ) {
			$data['serial_key'] = wcsn_encrypt_key( $data['serial_key'] );
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
	public function enable_product( $key_id ) {
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
	public function delete_activations( $key ) {
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
	public function revoke_order_item_keys( $revoke ) {
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
	public function update_activation_count( $activation ) {
		$key = Key::get( $activation->get_serial_id() );
		if ( $key ) {
			$key->recount_remaining_activation();
		}
	}
}
