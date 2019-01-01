<?php

namespace Pluginever\WCSerialNumbers;

class FormHandler {
	function __construct() {
		add_action( 'admin_post_wsn_add_serial_number', [ $this, 'handle_add_serial_number_form' ] );
		add_action( 'admin_post_wsn_edit_serial_number', [ $this, 'handle_edit_serial_number_form' ] );
	}

	function handle_add_serial_number_form() {

		if ( ! wp_verify_nonce( $_POST['wsn_generate_serial_numbers_nonce'], 'wsn_generate_serial_numbers' ) ) {
			return;
		}


		$serial_number = sanitize_text_field( $_POST['serial_number'] );
		$product       = esc_attr( $_POST['product'] );
		$usage_limit   = esc_attr( $_POST['usage_limit'] );
		$expires_on    = esc_attr( $_POST['expires_on'] );

		$url = untrailingslashit( site_url( '/' ) ) . $_REQUEST['_wp_http_referer'];

		if ( empty( $serial_number ) ) {
			wsn_redirect_with_message( $url, 'empty_serial_number', 'error' );
		}
		if ( empty( $product ) ) {
			wsn_redirect_with_message( $url, 'empty_product', 'error' );
		}

		if ( empty( $usage_limit ) ) {
			wsn_redirect_with_message( $url, 'empty_usage_limit', 'error' );
		}

		$post_id = wp_insert_post( [
			'post_title'  => $serial_number,
			'post_type'   => 'serial_number',
			'post_status' => 'publish',
		] );

		update_post_meta( $post_id, 'product', $product );
		update_post_meta( $post_id, 'usage_limit', $usage_limit );
		update_post_meta( $post_id, 'expires_on', $expires_on );
		update_post_meta( $product, 'enable_serial_number', true );

		wp_redirect( admin_url( 'admin.php?page=serial-numbers' ) );

	}

	function handle_edit_serial_number_form(){
		echo '<pre>';
		    print_r($_REQUEST);
		echo '</pre>';
		die();
	}


}
