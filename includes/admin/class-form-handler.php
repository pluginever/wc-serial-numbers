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
		add_action( 'admin_post_unlink_serial_number', array( $this, 'unlink_serial_number' ) );
	}

	/**
	 * Create serial number
	 *
	 * since 1.0.0
	 */
	public function create_serial_number() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wcsn_create_serial_number' ) ) {
			wp_die( 'No, Cheating!' );
		}

		$id            = ! empty( $_POST['serial_number_id'] ) ? intval( $_POST['serial_number_id'] ) : '';
		$product_id    = ! empty( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : '';
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
			'activation_limit' => ! empty( $_POST['activation_limit'] ) ? intval( $_POST['activation_limit'] ) : '',
			'validity'         => ! empty( $_POST['validity'] ) ? intval( $_POST['validity'] ) : '',
			'expire_date'      => ! empty( $_POST['expire_date'] ) ? sanitize_text_field( $_POST['expire_date'] ) : '',
			//'status'           => ! empty( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '',
			//'order_id'         => ! empty( $_POST['order_id'] ) ? intval( $_POST['status'] ) : '',
		);


		if ( empty( $posted['serial_key'] ) && empty( $posted['license_image'] ) ) {
			wc_serial_numbers()->add_notice( 'error', __( 'The Serial Number is empty. Please enter a serial number and try again', 'wc-serial-numbers' ) );
			wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
			exit();
		}


		if ( empty( $id ) ) {
			$inserted = wc_serial_numbers()->serial_number->insert( $posted );

			if ( ! empty( $inserted ) ) {
				wc_serial_numbers()->add_notice( 'success', __( 'Serial Number created successfully', 'wc-serial-numbers' ) );
			} else {
				wc_serial_numbers()->add_notice( 'error', __( 'Could not create serial number', 'wc-serial-numbers' ) );
				wp_safe_redirect( add_query_arg( $redirect_args, admin_url( 'admin.php' ) ) );
				exit();
			}
		} else {
			wc_serial_numbers()->serial_number->update( $id, $posted );
			wc_serial_numbers()->add_notice( 'success', __( 'Serial Number updated successfully', 'wc-serial-numbers' ) );
		}

		update_post_meta( $product_id, '_is_serial_number', 'yes' );

		do_action( 'wcsn_serial_number_created', $id, $product_id );

		wp_safe_redirect( add_query_arg( array( 'page' => $redirect_args['page'] ), admin_url( 'admin.php' ) ) );

		exit();

	}

	/**
	 * Delete serial number
	 *
	 * since 1.0.0
	 */
	public function delete_wc_serial_number() {

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'delete_wc_serial_number' ) ) {
			wp_die( 'No, Cheating!' );
		}

		$id = ! empty( $_REQUEST['serial_id'] ) ? intval( $_REQUEST['serial_id'] ) : '';

		$serial_number = array_pop( wcsn_get_serial_numbers( array( 'id' => $id ) ) );

		if ( ! empty( $id ) ) {
			wc_serial_numbers()->serial_number->delete( $id );
			wc_serial_numbers()->add_notice( 'success', __( 'Serial Number deleted successfully', 'wc-serial-numbers' ) );
		}

		do_action( 'wcsn_serial_number_deleted', $id, $serial_number->product_id );

		if ( ! empty( $_REQUEST['order_id'] ) ) {
			wp_safe_redirect( get_edit_post_link( intval( $_REQUEST['order_id'] ) ) );
			exit();
		}

		wp_safe_redirect( add_query_arg( array(
			'page' => 'wc-serial-numbers',
		), admin_url( 'admin.php' ) ) );

		exit();

	}

	/**
	 * Unlink serial number
	 *
	 * @since 1.0.0
	 */

	public function unlink_serial_number() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'unlink_serial_number' ) ) {
			wp_die( 'No, Cheating!' );
		}

		if ( empty( $_REQUEST['serial_id'] ) ) {
			return;
		} else {
			$serial_id  = intval( $_REQUEST['serial_id'] );
			$product_id = reset( wcsn_get_serial_numbers( array( 'id' => $serial_id ) ) )->product_id;
		}

		$data['order_date']       = '';
		$data['order_id']         = '';
		$data['order_date']       = '';
		$data['activation_email'] = '';
		$data['status']           = 'new';

		if ( wc_serial_numbers()->serial_number->update( $serial_id, $data ) ) {
			wc_serial_numbers()->add_notice( 'success', __( 'Serial Number successfully Unlinked from the order', 'wc-serial-numbers' ) );
		}

		do_action( 'wcsn_serial_number_unlinked', $serial_id, $product_id);

		wp_safe_redirect( site_url( $_REQUEST['_wp_http_referer'] ) );
	}
}

new WCSN_Form_Handler();
