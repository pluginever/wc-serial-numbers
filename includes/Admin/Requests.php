<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Models\Generator;
use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Class Requests.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Requests {

	/**
	 * Requests constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_post_wcsn_edit_key', array( __CLASS__, 'handle_edit_key' ) );
		add_action( 'admin_post_wcsn_edit_generator', array( $this, 'edit_generator' ) );
		// Ajax Search.
		add_action( 'admin_post_wcsn_generate_bulk_keys', array( __CLASS__, 'generate_bulk_keys' ) );
		add_action( 'wp_ajax_wc_serial_numbers_search_product', array( __CLASS__, 'search_product' ) );
		add_action( 'wp_ajax_wc_serial_numbers_search_orders', array( __CLASS__, 'search_orders' ) );
		add_action( 'wp_ajax_wc_serial_numbers_search_customers', array( __CLASS__, 'search_customers' ) );
	}

	/**
	 * Handle add/edit key.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_edit_key() {
		check_admin_referer( 'wcsn_edit_key' );

		// Must have WC Serial Numbers manager role to access this endpoint.
		if ( ! current_user_can( wcsn_get_manager_role() ) ) {
			WCSN()->add_notice( __( 'You do not have permission to perform this action.', 'wc-serial-numbers' ), 'error' );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$product_id       = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		$order_id         = isset( $_POST['order_id'] ) ? absint( wp_unslash( $_POST['order_id'] ) ) : 0;
		$id               = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$serial_key       = isset( $_POST['serial_key'] ) ? sanitize_text_field( wp_unslash( $_POST['serial_key'] ) ) : '';
		$activation_limit = isset( $_POST['activation_limit'] ) ? absint( wp_unslash( $_POST['activation_limit'] ) ) : 0;
		$validity         = isset( $_POST['validity'] ) ? absint( wp_unslash( $_POST['validity'] ) ) : 0;
		$status           = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'available';

		$data = array(
			'id'               => $id,
			'product_id'       => $product_id,
			'order_id'         => $order_id,
			'serial_key'       => $serial_key,
			'activation_limit' => $activation_limit,
			'validity'         => $validity,
			'status'           => $status,
		);

		$key = Key::insert( $data );
		if ( is_wp_error( $key ) ) {
			WCSN()->add_notice( $key->get_error_message(), 'error' );
			// redirect to referrer.
			wp_safe_redirect( wp_get_referer() );
			exit();
		}
		$add = empty( $data['id'] ) ? true : false;
		if ( $add ) {
			// Adding manually so let's enable to product and set the source.
			$product_id = $key->get_product_id();
			update_post_meta( $product_id, '_is_serial_number', 'yes' );
			update_post_meta( $product_id, '_serial_key_source', 'preset' );

			WCSN()->add_notice( __( 'Key added successfully.', 'wc-serial-numbers' ) );
		} else {
			WCSN()->add_notice( __( 'Key updated successfully.', 'wc-serial-numbers' ) );
		}

		$redirect_to = admin_url( 'admin.php?page=wc-serial-numbers&edit=' . $key->get_id() );
		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Edit generator.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function edit_generator() {
		check_admin_referer( 'wc_serial_numbers_edit_generator' );

		if ( function_exists( 'wcsn_get_manager_role' ) && ! current_user_can( wcsn_get_manager_role() ) ) {
			WCSN()->add_notice( __( 'Error: Sorry, you are not allowed to do this.', 'wc-serial-numbers-pro', 'wc-serial-numbers' ), 'error' );
			wp_safe_redirect( wp_get_referer() );
			exit;
		}

		$referer          = wp_get_referer();
		$id               = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$name             = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$pattern          = isset( $_POST['pattern'] ) ? sanitize_text_field( wp_unslash( $_POST['pattern'] ) ) : '';
		$charset          = isset( $_POST['charset'] ) ? sanitize_text_field( wp_unslash( $_POST['charset'] ) ) : '';
		$valid_for        = isset( $_POST['valid_for'] ) ? absint( $_POST['valid_for'] ) : 0;
		$activation_limit = isset( $_POST['activation_limit'] ) ? absint( $_POST['activation_limit'] ) : 0;
		$status           = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'active';

		$generator = Generator::make(
			array(
				'id'               => $id,
				'name'             => $name,
				'pattern'          => $pattern,
				'charset'          => $charset,
				'valid_for'        => $valid_for,
				'activation_limit' => $activation_limit,
				'status'           => $status,
			)
		);

		$generator = $generator->save();
		if ( is_wp_error( $generator ) ) {
			WCSN()->add_notice( $generator->get_error_message(), 'error' );
			wp_safe_redirect( $referer );
			exit;
		}

		WCSN()->add_notice( __( 'Generator has been saved successfully.', 'wc-serial-numbers-pro', 'wc-serial-numbers' ) );
		// Remove 'add' query arg from the URL.
		$referer = remove_query_arg( 'add', $referer );
		wp_safe_redirect( add_query_arg( array( 'edit' => $generator->id ), $referer ) );
		exit;
	}

	/**
	 * Generate serial numbers.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function generate_bulk_keys() {
		check_admin_referer( 'wcsn_generate_bulk_keys' );

		if ( function_exists( 'wcsn_get_manager_role' ) && ! current_user_can( wcsn_get_manager_role() ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed, to use this.', 'wc-serial-numbers-pro', 'wc-serial-numbers' ) ) );
		}

		$referer      = wp_get_referer();
		$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$generator_id = isset( $_POST['generator_id'] ) ? absint( $_POST['generator_id'] ) : 0;
		$quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;

		// Check if product id is present.
		if ( empty( $product_id ) ) {
			WCSN()->add_notice( __( 'Error: Please select a product.', 'wc-serial-numbers-pro', 'wc-serial-numbers' ), 'error' );
			wp_safe_redirect( $referer );
			exit;
		}

		// Check if quantity is present.
		if ( empty( $quantity ) ) {
			WCSN()->add_notice( __( 'Error: Please enter a quantity.', 'wc-serial-numbers-pro', 'wc-serial-numbers' ), 'error' );
			wp_safe_redirect( $referer );
			exit;
		}

		// key data.
		$data = array(
			'product_id' => $product_id,
			'quantity'   => $quantity,
			'source'     => 'preset',
		);

		// Check if generator id is present then update $data.
		if ( ! empty( $generator_id ) ) {
			$generator_args = array(
				'generator_id' => $generator_id,
				'source'       => 'automatic',
			);

			// parse arguments.
			$data = wp_parse_args( $generator_args, $data );
		}

		// Generate keys.
		$keys = wcsn_generate_keys( $data );

		if ( empty( $keys ) ) {
			WCSN()->add_notice( __( 'Could not generate any keys. Please check the generator settings.', 'wc-serial-numbers-pro', 'wc-serial-numbers' ), 'error' );
			wp_safe_redirect( $referer );
			exit;
		}

		update_post_meta( $product_id, '_is_serial_number', 'yes' );
		update_post_meta( $product_id, '_serial_key_source', 'preset' );

		// Translators: %s: number of keys generated.
		$notice = sprintf( esc_html__( '%s keys have been generated successfully.', 'wc-serial-numbers-pro', 'wc-serial-numbers' ), number_format( count( $keys ) ) );
		WCSN()->add_notice( $notice );
		wp_safe_redirect( $referer );
		exit();
	}

	/**
	 * Search product.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public static function search_product() {
		check_ajax_referer( 'wc_serial_numbers_search_nonce', 'nonce' );

		// Must have WC Serial Numbers manager role to access this endpoint.
		if ( ! current_user_can( wcsn_get_manager_role() ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to access this endpoint.', 'wc-serial-numbers' ) ) );
			wp_die();
		}

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

		// Must have WC Serial Numbers manager role to access this endpoint.
		if ( ! current_user_can( wcsn_get_manager_role() ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to access this endpoint.', 'wc-serial-numbers' ) ) );
			wp_die();
		}

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

		// Must have WC Serial Numbers manager role to access this endpoint.
		if ( ! current_user_can( wcsn_get_manager_role() ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to access this endpoint.', 'wc-serial-numbers' ) ) );
			wp_die();
		}

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
