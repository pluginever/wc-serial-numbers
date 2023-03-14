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
		$search      = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
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
		$search   = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$page     = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$per_page = absint( 100 );

		$query = new \WP_Query(
			array(
				'post_type'      => 'shop_order',
				'post_status'    => 'any',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
				's'              => $search,
			)
		);

		// todo need to add cache.
		$order_ids = $query->get_posts();
		$more      = false;
		if ( $query->found_posts > ( $per_page * $page ) ) {
			$more = true;
		}
		$results = array();
		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order_id ) {
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
					'more' => $more,
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
		$search   = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$page     = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$per_page = absint( 100 );

		// Search woo customers.
		$customer_query = new \WP_User_Query(
			array(
				'limit'  => $per_page,
				'offset' => ( $page - 1 ) * $per_page,
				'search' => $search,
				'fields' => 'ids',
			)
		);

		$customer_ids = $customer_query->get_results();
		$more         = false;
		if ( $customer_query->get_total() > ( $per_page * $page ) ) {
			$more = true;
		}

		$results = array();
		foreach ( $customer_ids as $customer_id ) {
			$customer = new \WC_Customer( $customer_id );

			if ( ! $customer ) {
				continue;
			}

			$text = sprintf(
				'(#%1$s) %2$s',
				$customer->get_id(),
				wp_strip_all_tags( $customer->get_billing_first_name() . ' ' . $customer->get_billing_last_name() )
			);

			$results[] = array(
				'id'   => $customer->get_id(),
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
	}
}
