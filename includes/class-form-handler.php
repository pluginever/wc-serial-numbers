<?php

namespace Pluginever\WCSerialNumbers;

class FormHandler {
	function __construct() {
		add_action( 'admin_post_wsn_generate_serial_numbers', [ $this, 'handle_generate_serial_numbers_form' ] );
	}

	function handle_generate_serial_numbers_form( $post ) {

		if ( ! isset( $_POST['wsn_generate_serial_numbers'] ) || ! wp_verify_nonce( $_POST['wsn_generate_serial_numbers_nonce'], 'wsn_generate_serial_numbers' ) ) {
			return;
		}

		$product      = $_POST['product'];
		$usage_limit  = $_POST['usage_limit'];
		$expired_date = $_POST['expired_date'];

		$url = untrailingslashit(site_url('/')).$_REQUEST['_wp_http_referer'];

		if(empty($product)){
			wsn_redirect_with_message($url, 'empty_product');
		}

		if(empty($usage_limit)){
			wsn_redirect_with_message($url, 'empty_usage_limit');
		}



	}


}
