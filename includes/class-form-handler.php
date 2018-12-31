<?php

namespace Pluginever\WCSerialNumbers;

class FormHandler {
	function __construct() {
		add_action( 'admin_post_wsn_generate_serial_numbers', [ $this, 'handle_generate_serial_numbers_form' ] );
	}

	function handle_generate_serial_numbers_form( $post ) {

		echo '<pre>';
		//print_r($_POST);
		echo '</pre>';
		//die();
		if ( ! wp_verify_nonce( $_POST['wsn_generate_serial_numbers_nonce'], 'wsn_generate_serial_numbers' ) ) {
			return;
		}


		$serial_number = sanitize_text_field( $_POST['serial_number'] );
		$product       = esc_attr( $_POST['product'] );
		$usage_limit   = esc_attr( $_POST['usage_limit'] );
		$expired_date  = esc_attr( $_POST['expired_date'] );

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
		update_post_meta( $post_id, 'expired_date', $expired_date );

		wp_redirect( admin_url( 'admin.php?page=serial-numbers' ) );

	}


}
