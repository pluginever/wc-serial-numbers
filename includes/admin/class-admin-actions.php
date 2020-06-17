<?php

namespace PluginEver\SerialNumbers\Admin;

use PluginEver\SerialNumbers\Sanitization;

defined( 'ABSPATH' ) || exit();

class Admin_Actions {
	/**
	 * @since 1.2.0
	 */
	public static function init() {
		add_action( 'admin_post_add_serial_number', array( __CLASS__, 'add_serial_number' ) );
	}

	/**
	 * Save update serial number.
	 *
	 * @since 1.2.0
	 */
	public static function add_serial_number() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'add_serial_number' ) ) {
			wp_die( 'No, Cheating!' );
		}

		$posted = array(
			'id'          => ! empty( $_POST['id'] ) ? intval( $_POST['id'] ) : '',
			'serial_key'  => ! empty( $_POST['serial_key'] ) ? Sanitization::sanitize_key( $_POST['serial_key'] ) : '',
			'product_id'  => ! empty( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : '',
			'order_id'    => ! empty( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : '',
			'expire_date' => ! empty( $_POST['expire_date'] ) ? Sanitization::sanitize_date( $_POST['expire_date'] ) : '',
			'status'      => ! empty( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'available',
		);

		if ( !wc_serial_numbers()->get_settings('disable_software_support', false, true ) ) {
			$posted['activation_limit'] = ! empty( $_POST['activation_limit'] ) ? intval( $_POST['activation_limit'] ) : '';
			$posted['validity']         = ! empty( $_POST['validity'] ) ? intval( $_POST['validity'] ) : '';
		}

		$redirect_args = array(
			'page'   => 'serial-numbers',
			'action' => empty( $posted['id'] ) ? 'add' : 'edit',
		);

		if ( ! empty( $posted['id'] ) ) {
			$redirect_args['id'] = $posted['id'];
		}

		if ( empty( $posted['product_id'] ) ) {
			Notice::add_notice( __( 'You must select a product to add serial number.', 'wc-serial-numbers' ), [ 'type' => 'error' ] );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}

		if ( empty( $posted['serial_key'] ) && empty( $posted['license_image'] ) ) {
			Notice::add_notice( __( 'The Serial Number is empty. Please enter a serial number and try again', 'wc-serial-numbers' ), [ 'type' => 'error' ] );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}

		$inserted = wc_serial_numbers_insert_serial( $posted );
		if ( is_wp_error( $inserted ) ) {
			Notice::add_notice( $inserted->get_error_message(), [ 'type' => 'error' ] );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}

		Notice::add_notice( __( 'Serial Number saved successfully', 'wc-serial-numbers' ), [ 'type' => 'success' ] );

		wp_safe_redirect( add_query_arg( array( 'page' => $redirect_args['page'] ), admin_url( 'admin.php' ) ) );

		exit();

	}


}

Admin_Actions::init();
