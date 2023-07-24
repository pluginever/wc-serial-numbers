<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Actions.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Actions {

	/**
	 * Actions constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_post_wc_serial_numbers_edit_key', array( __CLASS__, 'handle_edit_key' ) );
	}

	/**
	 * Handle edit key.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_edit_key() {
		check_admin_referer( 'wc_serial_numbers_edit_key' );
		$data = wc_clean( wp_unslash( $_POST ) );
		$key  = Key::insert( $data );
		if ( is_wp_error( $key ) ) {
			WCSN()->add_notice( $key->get_error_message(), 'error' );
			// redirect to referrer.
			wp_safe_redirect( wp_get_referer() );
			exit();
		}
		$add = empty( $data['id'] ) ? true : false;
		if ( $add ) {
			// Adding manually so let's enable to product and set the source.
			$product_id = $key->get_product_id();
			update_post_meta( $product_id, '_is_serial_number', 'yes' );
			update_post_meta( $product_id, '_serial_key_source', 'custom_source' );

			WCSN()->add_notice( __( 'Key added successfully.', 'wc-serial-numbers' ) );
		} else {
			WCSN()->add_notice( __( 'Key updated successfully.', 'wc-serial-numbers' ) );
		}

		$redirect_to = admin_url( 'admin.php?page=wc-serial-numbers&edit=' . $key->get_id() );
		wp_safe_redirect( $redirect_to );
		exit;
	}
}
