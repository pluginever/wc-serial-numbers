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
//		add_action( 'init', array( __CLASS__, 'add_endpoint' ) );
//		add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'add_query_vars' ) );
//		add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'account_menu_item' ) );
//		add_filter( 'woocommerce_account_' . self::get_endpoint() . '_title', array( __CLASS__, 'endpoint_title' ), 10, 2 );
//		add_action( 'woocommerce_account_' . self::get_endpoint() . '_endpoint', array( __CLASS__, 'endpoint_content' ) );
//		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'order_print_items' ) );
//		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'order_print_items' ), - 1 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Register new endpoint to use inside My Account page.
	 *
	 * @since #.#.#
	 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
	 */
	public static function add_endpoint() {
		add_rewrite_endpoint( self::get_endpoint(), EP_ROOT | EP_PAGES );
	}

	/**
	 * Return the my-account page endpoint.
	 *
	 * @since #.#.#
	 * @return string
	 */
	public static function get_endpoint() {
		return apply_filters( 'wc_serial_numbers_account_endpoint', 'serials' );
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
	 * @param string $endpoint Page endpoint.
	 *
	 * @return string
	 */
	public static function endpoint_title( $title, $endpoint ) {
		if ( self::get_endpoint() === $endpoint ) {
			$title = __( 'Serial Numbers', 'wc-serial-numbers' );
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
	public static function account_menu_item( $items ) {
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
	 * Gets the URL for an endpoint, which varies depending on permalink settings.
	 *
	 * @param string $endpoint
	 * @param string $value
	 * @param string $permalink
	 *
	 * @since 2.0
	 *
	 * @return string $url
	 */
	public function get_endpoint_url( $url, $endpoint, $value = '', $permalink = '' ) {
		if ( ! empty( $this->query_vars[ $endpoint ] ) ) {
			remove_filter( 'woocommerce_get_endpoint_url', array( $this, 'get_endpoint_url' ) );

			$url = wc_get_endpoint_url( $this->query_vars[ $endpoint ], $value, $permalink );

			add_filter( 'woocommerce_get_endpoint_url', array( $this, 'get_endpoint_url' ), 10, 4 );
		}

		return $url;
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
		var_dump( $user_id );
		var_dump( $current_page );

		// wc_get_template(
		// 'myaccount/serial numbers.php',
		// apply_filters( 'woocommerce_serial numbers_my_serial numbers_template_args', array(
		// 'tables'            => apply_filters( 'woocommerce_serial numbers_account_tables', $tables ),
		// 'page'              => $current_page,
		// 'serial numbers_per_page' => 20,
		// ) ),
		// 'wc-serial-numbers/', WC_BOOKINGS_TEMPLATE_PATH );
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @since #.#.#
	 */
	public static function enqueue_scripts() {
		wp_register_style( 'wc-serial-numbers-frontend', Plugin::instance()->assets_url( '/css/frontend-style.css' ), array(), Plugin::instance()->plugin_version() );
		wp_register_script( 'wc-serial-numbers-frontend', Plugin::instance()->assets_url( '/js/frontend-script.js' ), array( 'jquery' ), Plugin::instance()->plugin_version(), true );
		wp_enqueue_style( 'wc-serial-numbers-frontend' );
		wp_enqueue_script( 'wc-serial-numbers-frontend' );
	}
}
