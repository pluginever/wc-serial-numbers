<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class AJAX.
 *
 * @since   1.4.2
 * @package WooCommerceSerialNumbers
 */
class AJAX extends Lib\Singleton {

	/**
	 * AJAX constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'wp_ajax_wc_serial_numbers_search_product', [ __CLASS__, 'search_product' ] );
		add_action( 'wp_ajax_wc_serial_numbers_decrypt_key', array( __CLASS__, 'decrypt_key' ) );
	}

	/**
	 * Search product.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public static function search_product() {
		check_ajax_referer( 'wc_serial_numbers_search_nonce', 'nonce' );
		$search      = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$page        = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$per_page    = absint( 100 );
		$args        = array(
			'post_type'      => [ 'product' ],
			'posts_per_page' => $per_page,
			's'              => $search,
			'fields'         => 'ids',
			'tax_query'      => array( // @codingStandardsIgnoreLine
				'relation' => 'OR',
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => [ 'simple' ],
					'operator' => 'IN',
				),
			),
		);
		$the_query   = new \WP_Query( apply_filters( 'wc_serial_numbers_products_query_args', $args ) );
		$product_ids = $the_query->get_posts();
		$results     = array();
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
					'more' => false,
				),
			)
		);
		wp_die();
	}

	/**
	 * Decrypt key
	 *
	 * @since 1.2.0
	 */
	public static function decrypt_key() {
		check_ajax_referer( 'wc_serial_numbers_decrypt_key', 'nonce' );
		$serial_id = isset( $_REQUEST['serial_id'] ) ? sanitize_text_field( $_REQUEST['serial_id'] ) : '';
		if ( empty( $serial_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not detect the serial number to decrypt', 'wc-serial-numbers' ),
				)
			);
		}
		$serial_number = wc_serial_numbers_get_serial_number( $serial_id );
		if ( empty( $serial_number ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not detect the serial number to decrypt', 'wc-serial-numbers' ),
				)
			);
		}
		wp_send_json_success(
			array(
				'key' => wc_serial_numbers_decrypt_key( $serial_number->serial_key ),
			)
		);
	}
}
