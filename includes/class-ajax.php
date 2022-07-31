<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
use function cli\err;

defined( 'ABSPATH' ) || exit;

/**
 * Class Ajax.
 * Admin and frontend ajax handlers.
 *
 * @since #.#.#
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Ajax {

	/**
	 * Initiate ajax class.
	 *
	 * @since #.#.#
	 */
	public static function init() {
		add_action( 'wp_ajax_wc_serial_numbers_search_products', array( __CLASS__, 'search_products' ) );
	}

	/**
	 * Search products.
	 *
	 * @since 1.1.6
	 */
	public static function search_products() {
		self::verify_nonce( 'wc_serial_numbers_admin_js_nonce', 'nonce' );
		self::check_permission();
		$search   = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$page     = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$page     = absint( $page );
		$per_page = absint( 20 );
		if ( $per_page >= 0 ) {
			$offset = absint( ( $page - 1 ) * $per_page );
			$limit  = " LIMIT {$offset}, {$per_page}";
		}
		global $wpdb;
		$types       = apply_filters( 'wc_serial_numbers_search_product_types', [ 'product' ] );
		$sql_types   = '("' . implode( '","', $types ) . '")';
		$sql = $wpdb->prepare(
			"
SELECT
       ID from {$wpdb->posts}
WHERE
      post_status='publish'
  AND
      post_type in $sql_types
  AND
      ID NOT IN ( SELECT DISTINCT post_parent from {$wpdb->posts} WHERE post_type='product_variation')
 AND
 	post_title LIKE %s $limit
 	",
			'%' . $wpdb->esc_like( $search ) . '%'
		);

		// todo need to add cache.
		$product_ids = $wpdb->get_col(  $sql );

		$more = false;
		if ( count( $product_ids ) > ( 20 * $page ) ) {
			$more = true;
		}
		$results = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			$text = sprintf(
				'(#%1$s) %2$s',
				$product->get_id(),
				wp_strip_all_tags( $product->get_formatted_name() )
			);

			$results[] = array(
				'id'   => $product->get_id(),
				'text' => $text,
			);
		}

		wp_send_json(
			array(
				'page'       => $page,
				'results'    => $results,
				'pagination' => array(
					'more' => $more,
				),
			)
		);
		wp_die();
	}

	/**
	 * Check permission
	 *
	 * since 1.0.0
	 */
	public static function check_permission() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			self::send_error( __( 'Error: You are not allowed to do this.', 'wc-serial-numbers' ) );
		}
	}

	/**
	 * Verify nonce request
	 * since 1.0.0
	 *
	 * @param $action
	 */
	public static function verify_nonce( $action, $field = '_wpnonce' ) {
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
	public static function send_success( $data = null ) {
		wp_send_json_success( $data );
	}

	/**
	 * Wrapper function for sending error
	 * since 1.0.0
	 *
	 * @param null $data
	 */
	public static function send_error( $data = null ) {
		wp_send_json_error( $data );
	}
}


Ajax::init();
