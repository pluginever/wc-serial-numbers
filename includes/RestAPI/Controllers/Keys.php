<?php

namespace PluginEver\SerialNumbers\RestAPI\Controllers;

use PluginEver\SerialNumbers\Models\Key;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Keys REST API controller.
 *
 * @since 2.4.0
 * @package PluginEver\SerialNumbers\RestAPI\Controllers
 */
class Keys extends Controller {

	/**
	 * Retrieves a collection of keys.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function get_items( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce capability.
			return new WP_Error( 'rest_forbidden', __( 'Not allowed.', 'wc-serial-numbers' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$query = Key::query();

		foreach ( $request->get_params() as $key => $value ) {
			$query->set_var( $key, $value );
		}

		$query = $this->apply_query_filters( $query, $request, 'keys' );

		/**
		 * Filters the key query before execution.
		 *
		 * @since 2.4.0
		 *
		 * @param mixed           $query   Query object.
		 * @param WP_REST_Request $request Request object.
		 */
		$query = WCSN()->apply_filters( 'rest_key_query', $query, $request );

		$items    = $query->get();
		$total    = $query->count();
		$page     = (int) $query->get_var( 'page', 1 );
		$per_page = (int) $query->get_var( 'per_page', 20 );

		$results = array();
		foreach ( $items as $item ) {
			$data      = $this->prepare_item_for_response( $item, $request );
			$results[] = $this->prepare_response_for_collection( $data );
		}

		$response  = rest_ensure_response( $results );
		$max_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 0;

		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$base = add_query_arg( urlencode_deep( $request->get_query_params() ), rest_url( $request->get_route() ) );

		if ( $page > 1 ) {
			$prev_page = min( $page - 1, $max_pages );
			$response->link_header( 'prev', add_query_arg( 'page', $prev_page, $base ) );
		}

		if ( $max_pages > $page ) {
			$response->link_header( 'next', add_query_arg( 'page', $page + 1, $base ) );
		}

		return $response;
	}

	/**
	 * Retrieves one key.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function get_item( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce capability.
			return new WP_Error( 'rest_forbidden', __( 'Not allowed.', 'wc-serial-numbers' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$item = Key::find( $request->get_param( 'id' ) );

		if ( ! $item ) {
			return new WP_Error( 'rest_key_not_found', __( 'Key not found.', 'wc-serial-numbers' ), array( 'status' => 404 ) );
		}

		return $this->prepare_item_for_response( $item, $request );
	}

	/**
	 * Creates one key.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function create_item( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce capability.
			return new WP_Error( 'rest_forbidden', __( 'Not allowed.', 'wc-serial-numbers' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( ! empty( $request->get_param( 'id' ) ) ) {
			return new WP_Error( 'rest_key_exists', __( 'Cannot create existing key.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		$item = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $item ) ) {
			return $item;
		}

		/**
		 * Filters the key object before saving.
		 *
		 * @since 2.4.0
		 *
		 * @param Key             $item    Key object.
		 * @param WP_REST_Request $request Request object.
		 */
		$item = WCSN()->apply_filters( 'rest_pre_insert_key', $item, $request );
		$item = $item->save();

		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$response = $this->prepare_item_for_response( $item, $request );
		$response->set_status( 201 );
		$response->header( 'Location', rest_url( $request->get_route() . '/' . $item->id ) );

		return $response;
	}

	/**
	 * Updates one key.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function update_item( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce capability.
			return new WP_Error( 'rest_forbidden', __( 'Not allowed.', 'wc-serial-numbers' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$item = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $item ) ) {
			return $item;
		}

		if ( ! $item->exists() ) {
			return new WP_Error( 'rest_key_not_found', __( 'Key not found.', 'wc-serial-numbers' ), array( 'status' => 404 ) );
		}

		/**
		 * Filters the key object before saving.
		 *
		 * @since 2.4.0
		 *
		 * @param Key             $item    Key object.
		 * @param WP_REST_Request $request Request object.
		 */
		$item = WCSN()->apply_filters( 'rest_pre_insert_key', $item, $request );
		$item = $item->save();

		if ( is_wp_error( $item ) ) {
			return $item;
		}

		return $this->prepare_item_for_response( $item, $request );
	}

	/**
	 * Deletes one key.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function delete_item( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce capability.
			return new WP_Error( 'rest_forbidden', __( 'Not allowed.', 'wc-serial-numbers' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$item = Key::find( $request->get_param( 'id' ) );

		if ( ! $item ) {
			return new WP_Error( 'rest_key_not_found', __( 'Key not found.', 'wc-serial-numbers' ), array( 'status' => 404 ) );
		}

		$response = $this->prepare_item_for_response( $item, $request );
		$previous = $response->get_data();
		$deleted  = $item->delete();

		if ( is_wp_error( $deleted ) ) {
			return $deleted;
		}

		return rest_ensure_response(
			array(
				'deleted'  => true,
				'previous' => $previous,
			)
		);
	}

	/**
	 * Retrieves form options for keys.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function get_options( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce capability.
			return new WP_Error( 'rest_forbidden', __( 'Not allowed.', 'wc-serial-numbers' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$options = array(
			'statuses'       => $this->prepare_options( wcsn_get_key_statuses() ),
			'sources'        => $this->prepare_options( wcsn_get_key_sources() ),
			'bulk_actions'   => array(
				array(
					'value' => 'delete',
					'label' => __( 'Delete', 'wc-serial-numbers' ),
				),
			),
			'import_fields'  => array(),
			'export_columns' => array(),
		);

		/**
		 * Filters the key form options.
		 *
		 * @since 2.4.0
		 *
		 * @param array           $options Options data.
		 * @param WP_REST_Request $request Request object.
		 */
		return rest_ensure_response( WCSN()->apply_filters( 'rest_key_options', $options, $request ) );
	}

	/**
	 * Batch operations.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function batch_items( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown -- WooCommerce capability.
			return new WP_Error( 'rest_forbidden', __( 'Not allowed.', 'wc-serial-numbers' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return $this->process_batch( $request );
	}

	/**
	 * Import keys from CSV.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function import_items( $request ) {
		return new WP_Error( 'rest_not_implemented', __( 'Import not yet implemented.', 'wc-serial-numbers' ), array( 'status' => 501 ) );
	}

	/**
	 * Export keys to CSV.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	public function export_items( $request ) {
		return new WP_Error( 'rest_not_implemented', __( 'Export not yet implemented.', 'wc-serial-numbers' ), array( 'status' => 501 ) );
	}

	/**
	 * Prepare key for response.
	 *
	 * @since 2.4.0
	 *
	 * @param Key             $item    Key object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function prepare_item_for_response( $item, $request ): WP_REST_Response {
		$raw  = $item->to_array();
		$data = array();

		foreach ( $raw as $key => $value ) {
			switch ( $key ) {
				case 'order_date':
				case 'created_date':
					$data[ $key ] = $this->prepare_date_response( $value );
					break;

				default:
					$data[ $key ] = $value;
					break;
			}
		}

		/**
		 * Filters the key data before creating the response.
		 *
		 * @since 2.4.0
		 *
		 * @param WP_REST_Response $response Response object.
		 * @param Key              $item     Key object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return WCSN()->apply_filters( 'rest_prepare_key', rest_ensure_response( $data ), $item, $request );
	}

	/**
	 * Prepare key for database.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return Key|WP_Error Key object or error.
	 */
	protected function prepare_item_for_database( $request ) {
		$id   = $request->get_param( 'id' );
		$item = $id ? Key::find( $id ) : new Key();

		if ( $id && ! $item ) {
			return new WP_Error( 'rest_key_not_found', __( 'Key not found.', 'wc-serial-numbers' ), array( 'status' => 404 ) );
		}

		foreach ( $request->get_params() as $key => $value ) {
			switch ( $key ) {
				case 'order_date':
				case 'created_date':
					$item->$key = $this->prepare_date_for_database( $value );
					break;

				default:
					$item->$key = $value;
					break;
			}
		}

		return $item;
	}
}
