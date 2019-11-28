<?php

class WCSN_Ajax {

	/**
	 * Ajax constructor.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */

	function __construct() {
		add_action( 'wp_ajax_wcsn_show_serial_key', [ $this, 'show_serial_key' ] );
	}

	public function show_serial_key() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wcsn_show_serial_key' ) ) {
			wp_send_json_error( [ 'message' => 'No, cheating' ] );
		}


		if ( empty( $_POST['serial_id'] ) ) {
			wp_send_json_error( [ 'message' => 'Serial id required' ] );
		}
		$serial_id     = $_POST['serial_id'];
		$serial_number = wc_serial_numbers()->serial_number->get_by( 'id', $serial_id );
		if ( empty( $serial_number ) ) {
			wp_send_json_error( [ 'message' => 'Serial Key not found' ] );
		}
		wp_send_json_success( [ 'message' => wcsn_decrypt( $serial_number->serial_key ) ] );
	}
}

new WCSN_Ajax();
