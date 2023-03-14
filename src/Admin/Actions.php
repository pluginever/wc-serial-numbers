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
class Actions extends \WooCommerceSerialNumbers\Lib\Singleton {

	/**
	 * Actions constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'admin_post_wcsn_edit_key', array( __CLASS__, 'handle_edit_key' ) );
	}

	/**
	 * Handle edit key.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_edit_key() {
		check_admin_referer( 'wcsn_edit_key' );
		$data = wc_clean( wp_unslash( $_POST ) );
		$key  = Key::insert( $data );
		if ( is_wp_error( $key ) ) {
			wc_serial_numbers()->add_notice( $key->get_error_message(), 'error' );
			// redirect to referrer.
			wp_safe_redirect( wp_get_referer() );
			exit();
		}
		$add = empty( $data['id'] ) ? true : false;
		if ( $add ) {
			wc_serial_numbers()->add_notice( __( 'Key added successfully.', 'wc-serial-numbers' ) );
		} else {
			wc_serial_numbers()->add_notice( __( 'Key updated successfully.', 'wc-serial-numbers' ) );
		}

		$redirect_to = admin_url( 'admin.php?page=wc-serial-numbers&edit=' . $key->get_id() );
		wp_safe_redirect( $redirect_to );
		exit;
	}
}
