<?php

namespace Pluginever\WCSerialNumbers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class Ajax {
	/**
	 * Ajax constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	function __construct() {
		add_action( 'wp_ajax_wsn_add_serial_number', array( $this, 'add_serial_number' ) );
		add_action( 'wp_ajax_wsn_enable_serial_number', array( $this, 'enable_serial_number' ) );
		add_action( 'wp_ajax_wsn_load_tab_data', array( $this, 'load_tab_data' ) );
	}

	/**
	 * Add serial number from product edit tab via Ajax Request
	 *
	 * @since 1.0.0
	 *
	 */

	function add_serial_number() {

		if ( ! isset( $_REQUEST['nonce'] ) &&  empty( $_REQUEST['nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wc-serial-numbers' ) ) {
			wp_die( 'No, Cheating!' );
		}

		$serial_number = ! empty( $_REQUEST['serial_number'] ) ? sanitize_textarea_field( $_REQUEST['serial_number'] ) : '';
		$product       = ! empty( $_REQUEST['product'] ) ? intval( $_REQUEST['product'] ) : '';
		$deliver_times = ! empty( $_REQUEST['deliver_times'] ) ? intval( $_REQUEST['deliver_times'] ) : '';
		$max_instance  = ! empty( $_REQUEST['max_instance'] ) ? intval( $_REQUEST['max_instance'] ) : '';
		$validity      = ! empty( $_REQUEST['validity'] ) ? sanitize_key( $_REQUEST['validity'] ) : '';
		$paged_url     = ! empty( $_REQUEST['paged_url'] ) ? esc_url( $_REQUEST['paged_url'] ) : '';

		set_query_var( 'single_list_post_id', $product );
		$is_serial_number_enabled = 'enable';


		if ( ! empty( $paged_url ) ) {

			$url_query = parse_url( $paged_url, PHP_URL_QUERY );
			parse_str( $url_query, $params );
			$paged = $params['paged'];

			set_query_var( 'wsn_product_edit_paged', $paged );

			ob_start();

			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';

			require WPWSN_TEMPLATES_DIR . '/single-serial-numbers.php';

			require WPWSN_TEMPLATES_DIR . '/add-serial-number-page.php';

			$html = ob_get_clean();

			$response = array( 'html' => $html );

		} else {

			if ( empty( $serial_number ) ) {

				$response = array( 'empty_serial' => '<div class="notice notice-error is-dismissible"><p><strong>' . __( 'Please enter a valid serial number', 'wc-serial-numbers' ) . '</strong></p></div>' );

			} else {

				$meta_input = array(
					'product'       => $product,
					'deliver_times' => $deliver_times,
					'used'          => 0,
					'max_instance'  => $max_instance,
					'validity'      => $validity,
				);

				wp_insert_post( [
					'post_title'  => $serial_number,
					'post_type'   => 'wsn_serial_number',
					'post_status' => 'publish',
					'meta_input'  => $meta_input,
				] );

				/*
                 * Update serial number notification posts when a new order added
                 */

				do_action( 'wsn_update_notification_on_add_edit', $product );

				ob_start();

				include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';

				require WPWSN_TEMPLATES_DIR . '/single-serial-numbers.php';

				require WPWSN_TEMPLATES_DIR . '/add-serial-number-page.php';

				$html = ob_get_clean();

				$response = array( 'html' => $html );

			}
		}

		wp_send_json_success( $response );
	}

	/**
	 * Enable number from product edit tab via Ajax Request
	 *
	 * @since 1.0.0
	 *
	 * @return string|void
	 */
	function enable_serial_number() {

		$post_id                  = ! empty( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : '';
		$is_serial_number_enabled = ! empty( $_REQUEST['enable_serial_number'] ) ? sanitize_key( $_REQUEST['enable_serial_number'] ) : '';

		update_post_meta( $post_id, 'enable_serial_number', $is_serial_number_enabled );

		if ( 'enable' == $is_serial_number_enabled ) {

			set_query_var( 'single_list_post_id', $post_id );

			ob_start();

			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';

			require WPWSN_TEMPLATES_DIR . '/single-serial-numbers.php';

			require WPWSN_TEMPLATES_DIR . '/add-serial-number-page.php';

			$html = ob_get_clean();


		} else {
			ob_start();
			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';
			$html = ob_get_clean();
		}

		//Update the wsnp_notification comments status for checking if serial number is enabled
		do_action( 'wsn_update_notification_on_enable_disable', $post_id, $is_serial_number_enabled );

		wp_send_json_success( array( 'html' => $html ) );
	}


	/**
	 * Load product edit tab panel after clicking serial number tab
	 *
	 * @since 1.0.0
	 *
	 */
	function load_tab_data() {

		$post_id = ! empty( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : '';

		set_query_var( 'single_list_post_id', $post_id );

		$is_serial_number_enabled = get_post_meta( $post_id, 'enable_serial_number', true );

		if ( 'enable' == $is_serial_number_enabled ) {

			ob_start();

			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';

			require WPWSN_TEMPLATES_DIR . '/single-serial-numbers.php';

			require WPWSN_TEMPLATES_DIR . '/add-serial-number-page.php';

			$html = ob_get_clean();

		} else {
			ob_start();
			include WPWSN_TEMPLATES_DIR . '/product-tab-enable-serial-number.php';
			$html = ob_get_clean();
		}

		wp_send_json_success( array( 'html' => $html ) );
	}
}
