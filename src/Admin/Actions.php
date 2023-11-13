<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Actions.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Actions {

	/**
	 * Actions constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_wcsn_ajax_search', array( __CLASS__, 'handle_ajax_search' ) );
		add_action( 'admin_post_wcsn_add_key', array( __CLASS__, 'handle_add_key' ) );
		add_action( 'admin_post_wcsn_edit_key', array( __CLASS__, 'handle_edit_key' ) );
	}

	/**
	 * Handle ajax search.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_ajax_search() {
		check_ajax_referer( 'wcsn_ajax_search' );
		$type    = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$term    = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
		$limit   = isset( $_POST['limit'] ) ? absint( $_POST['limit'] ) : 20;
		$page    = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$results = array();
		$total   = 0;
		$offset  = ( $page - 1 ) * $limit;

		switch ( $type ) {
			case 'product':
				$args = array_merge(
					wcsn_get_products_query_args(),
					array(
						'paged'          => $page,
						'posts_per_page' => $limit,
						's'              => $term,
						'fields'         => 'ids',
					)
				);
				// if the term is numeric then search by product id.
				if ( is_numeric( $term ) ) {
					$args['post__in'] = array( $term );
					unset( $args['s'] );
				}

				$the_query   = new \WP_Query( $args );
				$product_ids = $the_query->get_posts();
				$total       = $the_query->found_posts;
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
				break;
			case 'order':
				$args = array(
					'paged'          => $page,
					'posts_per_page' => $limit,
					's'              => $term,
					'fields'         => 'ids',
					'post_type'      => 'shop_order',
					'post_status'    => array_keys( wc_get_order_statuses() ),
				);
				// if the term is numeric then search by order id.
				if ( is_numeric( $term ) ) {
					$args['post__in'] = array( $term );
					unset( $args['s'] );
				}

				$the_query = new \WP_Query( $args );
				$order_ids = $the_query->get_posts();
				$total     = $the_query->found_posts;
				foreach ( $order_ids as $order_id ) {
					$order = wc_get_order( $order_id );

					if ( ! $order ) {
						continue;
					}

					$text = sprintf(
						'(#%1$s) %2$s - %3$s',
						$order->get_id(),
						wp_strip_all_tags( $order->get_formatted_billing_full_name() ),
						wp_strip_all_tags( wc_format_datetime( $order->get_date_created() ) )
					);

					$results[] = array(
						'id'   => $order->get_id(),
						'text' => $text,
					);
				}
				break;

			case 'customer':
			case 'user':
				// query wp users.
				$args = array(
					'paged'          => $page,
					'number'         => $limit,
					'search'         => '*' . $term . '*',
					'search_columns' => array( 'user_login', 'user_email', 'user_nicename' ),
					'fields'         => 'ID',
				);
				// if the term is numeric then search by user id.
				if ( is_numeric( $term ) ) {
					$args['include'] = array( $term );
					unset( $args['search'] );
				}

				$user_query = new \WP_User_Query( $args );
				$user_ids   = $user_query->get_results();
				$total      = $user_query->get_total();

				foreach ( $user_ids as $user_id ) {
					$user = get_user_by( 'id', $user_id );

					if ( ! $user ) {
						continue;
					}

					$text = sprintf(
						'(#%1$s) %2$s - %3$s',
						$user->ID,
						wp_strip_all_tags( $user->display_name ),
						wp_strip_all_tags( $user->user_email )
					);

					$results[] = array(
						'id'   => $user->ID,
						'text' => $text,
					);
				}

				break;
		}

		wp_send_json(
			array(
				'page'       => $page,
				'results'    => $results,
				'pagination' => array(
					'more' => $total > ( $offset + $limit ),
				),
			)
		);
	}

	/**
	 * Handle add key.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_add_key() {
		check_admin_referer( 'wcsn_add_key' );
		$data = wc_clean( wp_unslash( $_POST ) );
		$key  = Key::insert( $data );
		if ( is_wp_error( $key ) ) {
			WCSN()->add_notice( $key->get_error_message(), 'error' );
			// redirect to referrer.
			wp_safe_redirect( wp_get_referer() );
			exit();
		}
		// Adding manually so let's enable to product and set the source.
		$product_id = $key->get_product_id();
		update_post_meta( $product_id, '_is_serial_number', 'yes' );
		update_post_meta( $product_id, '_serial_key_source', 'custom_source' );
		$status = isset( $data['status'] ) ? $data['status'] : '';
	}

	/**
	 * Handle edit key.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function handle_edit_key() {
		check_admin_referer( 'wcsn_edit_key' );
		$data = wc_clean( wp_unslash( $_POST ) );
		$key  = Key::insert( $data );
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
			update_post_meta( $product_id, '_serial_key_source', 'custom_source' );

			WCSN()->add_notice( __( 'Key added successfully.', 'wc-serial-numbers' ) );
		} else {
			WCSN()->add_notice( __( 'Key updated successfully.', 'wc-serial-numbers' ) );
		}

		$redirect_to = admin_url( 'admin.php?page=wc-serial-numbers&edit=' . $key->get_id() );
		wp_safe_redirect( $redirect_to );
		exit;
	}
}
