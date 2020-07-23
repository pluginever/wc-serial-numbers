<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_AJAX {

	/**
	 * WC_Serial_Numbers_AJAX constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_wc_serial_numbers_search_products', array( $this, 'search_products' ) );
		add_action( 'wp_ajax_wc_serial_numbers_decrypt_key', array( $this, 'decrypt_key' ) );
	}

	/**
	 * Search products.
	 *
	 * @since 1.1.6
	 */
	public function search_products() {
		$this->verify_nonce( 'wc_serial_numbers_admin_js_nonce', 'nonce' );
		$this->check_permission();
		$search = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$page   = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$types  = apply_filters( 'wc_serial_numbers_product_types', array( 'product' ) );
		global $wpdb;
		$query = WC_Serial_Numbers_Query::init()->table( 'posts' )
		                                ->where( 'post_status', 'publish' )
		                                ->whereRaw( 'post_type IN ("' . implode( '","', $types ) . '")' )
		                                ->whereRaw( "ID NOT IN  (SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE post_type='product_variation') " )
		                                ->search( sanitize_text_field( $search ), array( 'post_title' ) )
		                                ->page( $page );
		$more  = false;
		if ( $query->count() > ( 20 * $page ) ) {
			$more = true;
		}
		$product_ids = $query->column( 0 );
		$results     = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			$text = sprintf(
				'(#%1$s) %2$s',
				$product->get_id(),
				strip_tags( $product->get_formatted_name() )
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

	/**
	 * Decrypt key
	 * @since 1.2.0
	 */
	public function decrypt_key() {
		$this->verify_nonce( 'wc_serial_numbers_decrypt_key', 'nonce' );
		$this->check_permission();
		$serial_id = isset( $_REQUEST['serial_id'] ) ? sanitize_text_field( $_REQUEST['serial_id'] ) : '';
		if ( empty( $serial_id ) ) {
			$this->send_error( [ 'message' => __( 'Could not detect the serial number to decrypt', 'wc-serial-numbers' ) ] );
		}

		$serial_number = wc_serial_numbers_get_serial_number( $serial_id );
		if ( empty( $serial_number ) ) {
			$this->send_error( [ 'message' => __( 'Could not find the serial number to decrypt', 'wc-serial-numbers' ) ] );
		}

		$this->send_success( [ 'key' => wc_serial_numbers_decrypt_key( $serial_number->serial_key ) ] );

	}

	/**
	 * Check permission
	 *
	 * since 1.0.0
	 */
	public function check_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			self::send_error( __( 'Error: You are not allowed to do this.', 'wc-serial-numbers' ) );
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
			self::send_error( __( 'Error: Nonce verification failed', 'wc-serial-numbers' ) );
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

new WC_Serial_Numbers_AJAX();
