<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin_Actions {
	public static function init() {
		add_action( 'admin_post_wc_serial_numbers_edit_serial_number', array( __CLASS__, 'edit_serial_number' ) );
	}

	public static function edit_serial_number() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edit_serial_number' ) ) {
			wp_die( 'No, Cheating!' );
		}

		$id     = ! empty( $_POST['id'] ) ? intval( $_POST['id'] ) : null;
		$posted = array(
			'id'          => ! empty( $_POST['id'] ) ? intval( $_POST['id'] ) : '',
			'serial_key'  => ! empty( $_POST['serial_key'] ) ? sanitize_textarea_field( $_POST['serial_key'] ) : '',
			'product_id'  => ! empty( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : '',
			'order_id'    => ! empty( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : '',
			'expire_date' => ! empty( $_POST['expire_date'] ) ? sanitize_text_field( $_POST['expire_date'] ) : '',
			'status'      => ! empty( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : 'available',
		);

		if ( ! wc_serial_numbers_software_support_disabled() ) {
			$posted['activation_limit'] = ! empty( $_POST['activation_limit'] ) ? intval( $_POST['activation_limit'] ) : '';
			$posted['validity'] = ! empty( $_POST['validity'] ) ? intval( $_POST['validity'] ) : '';
		}

		$created = wc_serial_numbers_insert_serial_number($posted);

		$redirect_args = array(
			'page'   => 'wc-serial-numbers',
			'action' => empty( $id ) ? 'add' : 'edit',
		);

		if ( ! empty( $id ) ) {
			$redirect_args['id'] = $id;
		}

		if ( is_wp_error( $created ) ) {
			WC_Serial_Numbers_Admin_Notice::add_notice( $created->get_error_message(), [ 'type' => 'error' ] );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}


		WC_Serial_Numbers_Admin_Notice::add_notice( __( 'Serial Number saved successfully', 'wc-serial-numbers' ), [ 'type' => 'success' ] );
		wp_safe_redirect( add_query_arg( array( 'page' => $redirect_args['page'] ), admin_url( 'admin.php' ) ) );
		exit();
	}
}

WC_Serial_Numbers_Admin_Actions::init();
