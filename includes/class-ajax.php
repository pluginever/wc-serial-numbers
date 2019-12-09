<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Ajax {
	/**
	 * WC_Serial_Numbers_Ajax constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_serial_numbers_product_search', array( $this, 'search_product' ) );
		add_action( 'wp_ajax_serial_numbers_get_decrypted_key', array( $this, 'decrypted_key' ) );
	}

	/**
	 * @since 1.0.0
	 */
	public function search_product() {
		$this->verify_nonce( 'serial_numbers_search_dropdown', 'nonce' );
		$this->check_permission();
		$search   = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$page     = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$products = wc_serial_numbers_get_products( [
			'page'   => $page,
			'search' => $search,
			'fields' => 'id',
		] );
		$total    = wc_serial_numbers_get_products( [
			'page'   => $page,
			'search' => $search,
			'fields' => 'id',
		], true );

		$more = false;
		if ( $total > ( 20 * $page ) ) {
			$more = true;
		}


		$results = array();
		foreach ( $products as $product_id ) {
			/** @var \WC_Product $product */
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			$text = sprintf(
				'(#%1$s) %2$s',
				$product->get_id(),
				html_entity_decode($product->get_formatted_name())
			);

			$results[] = array(
				'id'   => $product->get_id(),
				'text' => $text
			);

		}

		wp_send_json(
			array(
				'page'       => $page,
				'results'    => $results,
				'pagination' => array(
					'more' => $more
				)
			)
		);

	}

	public function decrypted_key(){
		$this->verify_nonce( 'wcsn_show_serial_key', 'nonce' );
		$this->check_permission();
		$serial_id   = isset( $_REQUEST['serial_id'] ) ? sanitize_text_field( $_REQUEST['serial_id'] ) : '';
		if(empty($serial_id)){
			wp_send_json_error([]);
		}

		$serial_number = wc_serial_numbers_get_serial_number($serial_id);
		if(empty($serial_number)){
			wp_send_json_error([]);
		}
		wp_send_json_success([
			'key' => wc_serial_numbers_decrypt_serial_number($serial_number->serial_key)
		]);
	}


	/**
	 * Check permission
	 *
	 * since 1.0.0
	 */
	public function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->send_error( __( 'Error: You are not allowed to do this.', 'wc-serial-numbers' ) );
		}
	}

	/**
	 * Verify nonce request
	 * since 1.0.0
	 *
	 * @param $action
	 */
	public function verify_nonce( $action, $field = '_wpnonce' ) {
		if ( ! isset( $_REQUEST[ $field ] ) || ! wp_verify_nonce( $_REQUEST[ $field ], $action ) ) {
			$this->send_error( __( 'Error: Nonce verification failed', 'wc-serial-numbers' ) );
		}
	}

	/**
	 * Wrapper function for sending success response
	 * since 1.0.0
	 *
	 * @param null $data
	 */
	public function send_success( $data = null ) {
		wp_send_json_success( $data );
	}

	/**
	 * Wrapper function for sending error
	 * since 1.0.0
	 *
	 * @param null $data
	 */
	public function send_error( $data = null ) {
		wp_send_json_error( $data );
	}
}

new WC_Serial_Numbers_Ajax();
