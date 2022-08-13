<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Frontend class.
 *
 * @since 1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Frontend {

	/**
	 * Frontend constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( __CLASS__, 'add_endpoint' ) );
		add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ), 0 );
		add_filter( 'the_title', array( __CLASS__, 'endpoint_title' ) );
		add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'my_account_menu_item' ) );
		add_action( 'woocommerce_account_' . self::get_endpoint() . '_endpoint', array( __CLASS__, 'endpoint_content' ) );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'order_print_items' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'order_print_items' ), - 1 );
	}

	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @since #.#.#
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public static function add_endpoint() {
		add_rewrite_endpoint( self::get_endpoint(), EP_PAGES );
	}

	/**
	 * Return the my-account page endpoint.
	 *
	 * @since #.#.#
	 * @return string
	 */
	public static function get_endpoint() {
		return apply_filters( 'wc_serial_numbers_account_endpoint', 'serial-numbers' );
	}


	/**
	 * Add new query var.
	 *
	 * @since #.#.#
	 *
	 * @param array $vars Query vars.
	 *
	 * @return string[]
	 */
	public static function add_query_vars( $vars ) {
		$vars[] = self::get_endpoint();

		return $vars;
	}

	/**
	 * Change endpoint title.
	 *
	 * @since #.#.#
	 *
	 * @param string $title Page title.
	 *
	 * @return string
	 */
	public static function endpoint_title( $title ) {
		global $wp_query;
		$is_endpoint = isset( $wp_query->query_vars[ self::get_endpoint() ] );

		if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
			$title = __( 'Serial Numbers', 'wc-serial-numbers' );
			remove_filter( 'the_title', array( __CLASS__, 'endpoint_title' ) );
		}

		return $title;
	}

	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @since #.#.#
	 *
	 * @param array $items Menu items.
	 *
	 * @return array
	 */
	public static function my_account_menu_item( $items ) {
		// Remove logout menu item.
		if ( array_key_exists( 'customer-logout', $items ) ) {
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );
		}

		// Add serial numbers menu item.
		$items[ self::get_endpoint() ] = __( 'Serial Numbers', 'wc-serial-numbers' );

		// Add back the logout item.
		if ( isset( $logout ) ) {
			$items['customer-logout'] = $logout;
		}

		return $items;
	}


	/**
	 * Print ordered serials
	 *
	 * @param int $order_id Order id.
	 *
	 * @since #.#.#
	 */
	public static function order_print_items( $order_id ) {
		$order = Helper::get_order_object( $order_id );
		if ( 'completed' !== $order->get_status( 'edit' ) ) {
			return;
		}
		$line_items = Helper::get_order_line_items( $order );
		if ( empty( $line_items ) ) {
			return;
		}

		$table_columns = apply_filters(
			'wc_serial_numbers_order_table_columns',
			[
				'product'       => esc_html__( 'Product', 'wc-serial-numbers' ),
				'serial_number' => esc_html__( 'Serial Number', 'wc-serial-numbers' ),
			]
		);

		$keys = Keys::query(
			[
				'order_id__in' => $order->get_id(),
				'per_page'     => - 1,
			]
		);

		wc_get_template(
			'/order/serial-numbers-display.php',
			array(
				'order_id'      => $order_id,
				'table_columns' => $table_columns,
				'keys'          => $keys,
			),
			'wc-serial-numbers/',
			Plugin::instance()->get_templates_path()
		);
	}

	/**
	 * Endpoint HTML content.
	 *
	 * @param int $current_page Per page.
	 *
	 * @since    #.#.#
	 */
	public static function endpoint_content( $current_page ) {
		$user_id      = get_current_user_id();
		$current_page = empty( $current_page ) ? 1 : absint( $current_page );

		// wc_get_template(
		// 'myaccount/serial numbers.php',
		// apply_filters( 'woocommerce_serial numbers_my_serial numbers_template_args', array(
		// 'tables'            => apply_filters( 'woocommerce_serial numbers_account_tables', $tables ),
		// 'page'              => $current_page,
		// 'serial numbers_per_page' => 20,
		// ) ),
		// 'wc-serial-numbers/', WC_BOOKINGS_TEMPLATE_PATH );
	}
}
