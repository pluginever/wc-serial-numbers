<?php

namespace WooCommerceSerialNumbers\Keys;

use WooCommerceSerialNumbers\B8\Component;
use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Keys.
 *
 * @since 1.5.6
 * @package WooCommerceSerialNumbers\Keys
 */
class Keys extends Component {

	/**
	 * Child components.
	 *
	 * @since 2.4.0
	 * @var array<int|string, class-string>
	 */
	public array $components = array(
		Admin::class,
	);

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'wc_serial_numbers_key_insert', array( $this, 'enable_product' ) );
		add_action( 'wc_serial_numbers_key_deleted', array( $this, 'delete_activations' ) );
		add_action( 'wc_serial_numbers_key_saved', array( $this, 'clear_order_keys_cache' ) );
		add_action( 'wc_serial_numbers_key_deleted', array( $this, 'clear_order_keys_cache' ) );
		add_action( 'wc_serial_numbers_order_remove_keys', array( $this, 'clear_order_keys_cache' ) );
		add_action( 'wc_serial_numbers_order_add_keys', array( $this, 'clear_order_keys_cache' ) );
		add_action( 'wc_serial_numbers_hourly_event', array( $this, 'expire_outdated_serials' ) );
	}

	/**
	 * Clear order keys cache.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function clear_order_keys_cache() {
		delete_transient( 'wcsn_products_stock_count' );
	}

	/**
	 * Disable all expired serial numbers
	 *
	 * since 1.0.0
	 */
	public function expire_outdated_serials() {
		global $wpdb;
		$wpdb->query( "update {$wpdb->prefix}serial_numbers set status='expired' where validity !='0' AND (order_date + INTERVAL validity DAY ) < NOW()" );
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
}
