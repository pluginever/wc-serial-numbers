<?php

namespace WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Admin class.
 *
 * @since 1.3.1
 * @package WooCommerceSerialNumbers
 */
class AJAX {
	/**
	 * AJAX constructor.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_ajax_wc_serial_numbers_search_product', [ __CLASS__, 'search_product' ] );
		add_action( 'wp_ajax_wc_serial_numbers_search_order', [ __CLASS__, 'search_order' ] );
		add_action( 'wp_ajax_wc_serial_numbers_search_customer', [ __CLASS__, 'search_customer' ] );
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
		$per_page    = absint( 20 );
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
	 * Search order.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public static function search_order() {
		check_ajax_referer( 'wc_serial_numbers_search_nonce', 'nonce' );
		$search   = isset( $_REQUEST['search'] ) ? absint( $_REQUEST['search'] ) : '';
		$page     = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$per_page = absint( 20 );
		$args     = array(
			'post_type'      => 'shop_order',
			'posts_per_page' => $per_page,
			'search'         => $search,
		);
		$orders   = wc_get_orders( $args );
		foreach ( $orders as $order ) {
			$text = sprintf(
			/* translators: $1: order id, $2: customer name, $3: customer email */
				'#%1$s %2$s <%3$s>',
				$order->get_order_number(),
				$order->get_formatted_billing_full_name(),
				$order->get_billing_email()
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
	 * Search customer.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public static function search_customer() {
		check_ajax_referer( 'wc_serial_numbers_search_nonce', 'nonce' );
		$search   = isset( $_REQUEST['search'] ) ? sanitize_text_field( $_REQUEST['search'] ) : '';
		$page     = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$per_page = absint( 20 );
		$args     = array(
			'posts_per_page' => $per_page,
			'search'         => '*' . esc_attr( $search ) . '*',
		);

		$customers = new \WP_User_Query( $args );
		/** @var \WP_User $user User object. */
		foreach ( $customers->get_results() as $user ) {
			$results[] = array(
				'id'   => $user->ID,
				'text' => sprintf(
				/* translators: $1: user nicename, $2: user id, $3: user email */
					'%1$s (#%2$d - %3$s)',
					$user->user_nicename,
					$user->ID,
					$user->user_email
				),
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
}
