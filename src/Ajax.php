<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class AJAX.
 *
 * @since   1.4.2
 * @package WooCommerceSerialNumbers
 */
class Ajax {

	/**
	 * AJAX constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_wc_serial_numbers_search_product', array( __CLASS__, 'search_product' ) );
		add_action( 'wp_ajax_wc_serial_numbers_search_orders', array( __CLASS__, 'search_orders' ) );
		add_action( 'wp_ajax_wc_serial_numbers_search_customers', array( __CLASS__, 'search_customers' ) );
	}

	/**
	 * Search product.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public static function search_product() {
		check_ajax_referer( 'wc_serial_numbers_search_nonce', 'nonce' );
		$search      = isset( $_REQUEST['search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search'] ) ) : '';
		$page        = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$per_page    = absint( 100 );
		$args        = array_merge(
			wcsn_get_products_query_args(),
			array(
				'posts_per_page' => $per_page,
				's'              => $search,
				'fields'         => 'ids',
			)
		);
		$the_query   = new \WP_Query( $args );
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
		$more = false;
		if ( $the_query->found_posts > ( $per_page * $page ) ) {
			$more = true;
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
	 * Search orders.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public static function search_orders() {
		check_ajax_referer( 'wc_serial_numbers_search_nonce', 'nonce' );
		$search   = isset( $_REQUEST['search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search'] ) ) : '';
		$page     = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$per_page = absint( 100 );

		$ids = array();
		if ( is_numeric( $search ) ) {
			$order = wc_get_order( intval( $search ) );

			// Order does exist.
			if ( $order && 0 !== $order->get_id() ) {
				$ids[] = $order->get_id();
			}
		}

		if ( empty( $ids ) && ! is_numeric( $search ) ) {
			$data_store = \WC_Data_Store::load( 'order' );
			if ( 3 > strlen( $search ) ) {
				$per_page = 20;
			}
			$ids = $data_store->search_orders(
				$search,
				array(
					'limit' => $per_page,
					'page'  => $page,
				)
			);
		}

		$results = array();
		foreach ( $ids as $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				continue;
			}

			$text = sprintf(
				'(#%1$s) %2$s',
				$order->get_id(),
				wp_strip_all_tags( $order->get_formatted_billing_full_name() )
			);

			$results[] = array(
				'id'   => $order->get_id(),
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
	 * Search customers.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public static function search_customers() {
		check_ajax_referer( 'wc_serial_numbers_search_nonce', 'nonce' );
		$search   = isset( $_REQUEST['search'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search'] ) ) : '';
		$page     = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$per_page = absint( 100 );

		$ids = array();
		// Search by ID.
		if ( is_numeric( $search ) ) {
			$customer = new \WC_Customer( intval( $search ) );

			// Customer does not exists.
			if ( $customer && 0 !== $customer->get_id() ) {
				$ids = array( $customer->get_id() );
			}
		}

		// Usernames can be numeric so we first check that no users was found by ID before searching for numeric username, this prevents performance issues with ID lookups.
		if ( empty( $ids ) ) {
			$data_store = \WC_Data_Store::load( 'customer' );

			// If search is smaller than 3 characters, limit result set to avoid
			// too many rows being returned.
			if ( 3 > strlen( $search ) ) {
				$per_page = 20;
			}
			$ids = $data_store->search_customers( $search, $per_page );
		}

		$results = array();
		foreach ( $ids as $id ) {
			$customer = new \WC_Customer( $id );
			$text     = sprintf(
				/* translators: $1: customer name, $2 customer id, $3: customer email */
				esc_html__( '%1$s (#%2$s - %3$s)', 'wc-serial-numbers' ),
				$customer->get_first_name() . ' ' . $customer->get_last_name(),
				$customer->get_id(),
				$customer->get_email()
			);

			$results[] = array(
				'id'   => $id,
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
	}
}
