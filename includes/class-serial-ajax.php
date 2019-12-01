<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Ajax {
	/**
	 * WC_Serial_Numbers_Ajax constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_wcsn_product_search', array( $this, 'search_product' ) );
	}

	/**
	 * @since 1.0.0
	 */
	public function search_product() {
		$this->verify_nonce( 'wcsn_search_dropdown', 'nonce' );
		$this->check_permission();
		$search   = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$page     = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$products = wcsn_get_products( [
			'page'   => $page,
			'search' => $search,
			'fields' => 'id',
		] );

		$results = array();
		foreach ($products as $product_id) {
			/** @var WC_Product  $product */
			$product = wc_get_product($product_id);

			if (!$product) {
				continue;
			}

			$text = sprintf(
				'(#%1$s) %2$s',
				$product->get_id(),
				$product->get_formatted_name()
			);

			$results[] = array(
				'id' => $product->get_id(),
				'text' => $text
			);

		}

		wp_send_json(
			array(
				'page'       => $page,
				'results'    => $results,
				'pagination' => array(
					'more' => false
				)
			)
		);

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
