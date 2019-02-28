<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCSN_Form_Handler {

	/**
	 * WCSN_Form_Handler constructor.
	 */
	public function __construct() {
		add_action( 'admin_post_wcsn_create_serial_number', array( $this, 'create_serial_number' ) );
		add_action( 'admin_post_delete_wc_serial_number', array( $this, 'delete_wc_serial_number' ) );
	}

	public function create_serial_number() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wcsn_create_serial_number' ) ) {
			wp_die( 'No, Cheating!' );
		}


		$id            = ! empty( $_POST['serial_number_id'] ) ? intval( $_POST['serial_number_id'] ) : '';
		$product_id    = ! empty( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : '';
		$variation_id    = ! empty( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : '';
		$redirect_args = array(
			'page'        => 'wc-serial-numbers',
			'action_type' => 'add_serial_number',
			'serial_id'   => '',
		);

		if ( empty( $product_id ) ) {
			wc_serial_numbers()->add_notice( 'error', __( 'You must select a product to add serial number.', 'wc-serial-numbers' ) );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}


		$posted = array(
			'serial_key'       => ! empty( $_POST['serial_key'] ) ? sanitize_textarea_field( $_POST['serial_key'] ) : '',
			'license_image'    => ! empty( $_POST['license_image'] ) ? sanitize_text_field( $_POST['license_image'] ) : '',
			'product_id'       => $product_id,
			'variation_id'     => $variation_id,
			'activation_limit' => ! empty( $_POST['activation_limit'] ) ? intval( $_POST['activation_limit'] ) : '',
			'validity'         => ! empty( $_POST['validity'] ) ? intval( $_POST['validity'] ) : '',
			'expire_date'      => ! empty( $_POST['expire_date'] ) ? sanitize_text_field( $_POST['expire_date'] ) : '',
			'status'           => ! empty( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '',
			'order_id'         => ! empty( $_POST['order_id'] ) ? intval( $_POST['status'] ) : '',
		);

		$posted = array_filter( $posted );

		if ( empty( $posted['serial_key'] ) && empty( $posted['license_image'] ) ) {
			wc_serial_numbers()->add_notice( 'error', __( 'The Serial Number is empty. Please enter a serial number and try again', 'wc-serial-numbers' ) );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}

		$product = wc_get_product( $product_id );

		if ( $product->get_type() == 'variable' && empty( $posted['variation_id'] ) ) {
			wc_serial_numbers()->add_notice( 'error', __( 'You must select a variation for the variable product', 'wc-serial-numbers' ) );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}


		if ( empty( $id ) ) {
			$inserted = wc_serial_numbers()->serial_number->insert( $posted );

			if ( ! empty( $inserted ) ) {
				wc_serial_numbers()->add_notice( 'success', __( 'Serial Number created successfully', 'wc-serial-numbers' ) );
				$redirect_args['serial_id'] = $inserted;
			} else {
				wc_serial_numbers()->add_notice( 'error', __( 'Could not create serial number', 'wc-serial-numbers' ) );
				wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
				exit();
			}
		} else {
			wc_serial_numbers()->serial_number->update( $id, $posted );
			wc_serial_numbers()->add_notice( 'success', __( 'Serial Number updated successfully', 'wc-serial-numbers' ) );
			$redirect_args['serial_id'] = $id;
		}

		if ( ! empty( $variation_id ) ) {
			update_post_meta( $variation_id, '_is_serial_number', 'yes' );
		} else {
			update_post_meta( $product_id, '_is_serial_number', 'yes' );
		}

		do_action( 'created_serial_number', $inserted, $product_id, $variation_id );
		wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
		exit();

	}

	public function delete_wc_serial_number() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'delete_wc_serial_number' ) ) {
			wp_die( 'No, Cheating!' );
		}

		$id = ! empty( $_REQUEST['serial_id'] ) ? intval( $_REQUEST['serial_id'] ) : '';

		if ( ! empty( $id ) ) {
			wc_serial_numbers()->serial_number->delete( $id );
			wc_serial_numbers()->add_notice( 'success', __( 'Serial Number deleted successfully', 'wc-serial-numbers' ) );
		}
		if ( ! empty( $_REQUEST['order_id'] ) ) {
			wp_safe_redirect( get_edit_post_link( intval( $_REQUEST['order_id'] ) ) );
			exit();
		}
		wp_safe_redirect( add_query_arg( array(
			'page' => 'wc-serial-numbers',
		), admin_url( 'admin.php' ) ) );
		exit();

	}
}

new WCSN_Form_Handler();
