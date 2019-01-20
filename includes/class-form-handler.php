<?php

namespace Pluginever\WCSerialNumbers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class FormHandler {

	function __construct() {
		add_action( 'admin_post_wsn_add_edit_serial_number', array( $this, 'handle_add_edit_serial_number_form' ) );
		add_action( 'admin_init', array( $this, 'handle_serial_numbers_table' ) );
	}

	/**
	 * Handle add new serial number form
	 *
	 * @since 1.0.0
	 */

	function handle_add_edit_serial_number_form() {

		if ( ! wp_verify_nonce( $_REQUEST['wsn_add_edit_serial_numbers_nonce'], 'wsn_add_edit_serial_numbers' ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You are not allowed to use this', 'wc-serial-numbers' ) );
		}

		$action_type = isset( $_REQUEST['action_type'] ) && ! empty( $_REQUEST['action_type'] ) ? sanitize_key( $_REQUEST['action_type'] ) : '';

		if ( empty( $action_type ) || ! in_array( $action_type, array(
				'add_serial_number',
				'wsn_edit_serial_number'
			) ) ) {
			return false;
		}

		$serial_number = empty( $_REQUEST['serial_number'] ) ? '' : sanitize_textarea_field( $_REQUEST['serial_number'] );
		$product       = empty( $_REQUEST['product'] ) ? '' : intval( $_REQUEST['product'] );
		$variation     = empty( $_REQUEST['variation'] ) ? '' : intval( $_REQUEST['variation'] );
		$deliver_times = empty( $_REQUEST['deliver_times'] ) ? '' : intval( $_REQUEST['deliver_times'] );
		$max_instance  = empty( $_REQUEST['max_instance'] ) ? '' : intval( $_REQUEST['max_instance'] );
		$validity_type = empty( $_REQUEST['validity_type'] ) ? '' : sanitize_key( $_REQUEST['validity_type'] );
		$validity      = empty( $_REQUEST['validity'] ) ? '' : sanitize_key( $_REQUEST['validity'] );

		$url = admin_url( 'admin.php?page=add-wc-serial-number' );

		if ( empty( $serial_number ) ) {
			wc_serial_numbers()->add_notice( __( 'The Serial Number is empty. Please enter a serial number and try again', 'wc-serial-numbers' ), 'error' );
			wp_safe_redirect( $url );
			exit();
		}


		if ( empty( $product ) ) {
			wc_serial_numbers()->add_notice( __( 'The product is empty. Please select a product and try again', 'wc-serial-numbers' ) );
			wp_safe_redirect( $url );
			exit();
		}


		if ( ! current_user_can( 'publish_posts' ) ) {
			return false;
		}

		$meta_input = array(
			'product'              => $product,
			'variation'            => $variation,
			'deliver_times'        => $deliver_times,
			'max_instance'         => $max_instance,
			'validity_type'        => $validity_type,
			'validity'             => $validity,
			'enable_serial_number' => 'enable',
		);

		if ( $action_type == 'add_serial_number' ) {

			$meta_input['used'] = 0;

			wp_insert_post( [
				'post_title'  => $serial_number,
				'post_type'   => 'wsn_serial_number',
				'post_status' => 'publish',
				'meta_input'  => $meta_input,
			] );

			update_post_meta( $product, 'enable_serial_number', 'enable' );

		} elseif ( $action_type == 'wsn_edit_serial_number' ) {

			$serial_number_id = ! empty( $_REQUEST['serial_number_id'] ) ? intval( $_REQUEST['serial_number_id'] ) : '';

			if ( !empty($serial_number_id) && get_post_status( $serial_number_id ) ) {

				wp_update_post( [
					'ID'         => $serial_number_id,
					'post_title' => $serial_number,
					'meta_input' => $meta_input,
				] );

			}
		}

		do_action( 'wsn_update_notification_on_add_edit', $product );

		wp_safe_redirect( WPWSN_SERIAL_INDEX_PAGE );
		exit();
	}


	/**
	 * Handle serial number table actions
	 *
	 * @return string|boolean
	 */

	function handle_serial_numbers_table() {

		if ( ! isset( $_REQUEST['wsn-serial-numbers-table-action'] ) || empty( $_REQUEST['nonce'] ) ) {
			return false;
		}


		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wsn-serial-numbers-table' ) ) {
			wp_die( 'No Cheating!' );
		}

		if ( ! empty( $_REQUEST['wsn-filter-table-serial-numbers'] ) && $_REQUEST['wsn-filter-table-serial-numbers'] == 'Filter' ) {

			$serialnumber = ! empty( $_REQUEST['filter-serialnumber'] ) ? sanitize_key( $_REQUEST['filter-serialnumber'] ) : '';
			$product      = ! empty( $_REQUEST['filter-product'] ) ? intval( $_REQUEST['filter-product'] ) : '';

			return wp_safe_redirect( add_query_arg( array(
				'serialnumber' => $serialnumber,
				'product'      => $product
			), WPWSN_SERIAL_INDEX_PAGE ) );

		} elseif ( ! empty( $_REQUEST['wsn-filter-table-generate'] ) && $_REQUEST['wsn-filter-table-generate'] == 'Filter' ) {

			$product = ! empty( $_REQUEST['filter-product'] ) ? intval( $_REQUEST['filter-product'] ) : '';

			return wp_safe_redirect( add_query_arg( array( 'product' => $product ), WPWSN_GENERATE_SERIAL_PAGE ) );

		}

		$bulk_deletes = ! empty( $_REQUEST['bulk-delete'] ) && is_array( $_REQUEST['bulk-delete'] ) ? array_map( 'intval', $_REQUEST['bulk-delete'] ) : '';
		$products     = ! empty( $_REQUEST['product'] ) ? intval( $_REQUEST['product'] ) : '';

		if ( ! empty( $bulk_deletes ) ) {

			foreach ( $bulk_deletes as $bulk_delete ) {

				$bulk_delete = intval( $bulk_delete );

				if ( current_user_can( 'delete_posts' ) && get_post_status( $bulk_delete ) ) {

					wp_delete_post( $bulk_delete, true );

				}

				do_action( 'wsn_update_notification_on_order_delete', $products[ $bulk_delete ] );
			}

		}

		return wp_safe_redirect( WPWSN_SERIAL_INDEX_PAGE );

	}


}
