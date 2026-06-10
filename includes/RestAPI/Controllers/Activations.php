<?php

namespace PluginEver\SerialNumbers\RestAPI\Controllers;

use PluginEver\SerialNumbers\Models\Activation;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Activations REST API controller.
 *
 * @since 2.4.0
 * @package PluginEver\SerialNumbers\RestAPI\Controllers
 */
class Activations extends Controller {

	/**
	 * Retrieves a collection of activations.
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

		$query = Activation::query();

		foreach ( $request->get_params() as $key => $value ) {
			$query->set_var( $key, $value );
		}

		$query = $this->apply_query_filters( $query, $request, 'activations' );

		/**
		 * Filters the activation query before execution.
		 *
		 * @since 2.4.0
		 *
		 * @param mixed           $query   Query object.
		 * @param WP_REST_Request $request Request object.
		 */
		$query = WCSN()->apply_filters( 'rest_activation_query', $query, $request );

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
	 * Retrieves one activation.
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

		$item = Activation::find( $request->get_param( 'id' ) );

		if ( ! $item ) {
			return new WP_Error( 'rest_activation_not_found', __( 'Activation not found.', 'wc-serial-numbers' ), array( 'status' => 404 ) );
		}

		return $this->prepare_item_for_response( $item, $request );
	}

	/**
	 * Creates one activation.
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
			return new WP_Error( 'rest_activation_exists', __( 'Cannot create existing activation.', 'wc-serial-numbers' ), array( 'status' => 400 ) );
		}

		$item = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $item ) ) {
			return $item;
		}

		/**
		 * Filters the activation object before saving.
		 *
		 * @since 2.4.0
		 *
		 * @param Activation      $item    Activation object.
		 * @param WP_REST_Request $request Request object.
		 */
		$item = WCSN()->apply_filters( 'rest_pre_insert_activation', $item, $request );
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
	 * Updates one activation.
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
			return new WP_Error( 'rest_activation_not_found', __( 'Activation not found.', 'wc-serial-numbers' ), array( 'status' => 404 ) );
		}

		/**
		 * Filters the activation object before saving.
		 *
		 * @since 2.4.0
		 *
		 * @param Activation      $item    Activation object.
		 * @param WP_REST_Request $request Request object.
		 */
		$item = WCSN()->apply_filters( 'rest_pre_insert_activation', $item, $request );
		$item = $item->save();

		if ( is_wp_error( $item ) ) {
			return $item;
		}

		return $this->prepare_item_for_response( $item, $request );
	}

	/**
	 * Deletes one activation.
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

		$item = Activation::find( $request->get_param( 'id' ) );

		if ( ! $item ) {
			return new WP_Error( 'rest_activation_not_found', __( 'Activation not found.', 'wc-serial-numbers' ), array( 'status' => 404 ) );
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
	 * Retrieves form options for activations.
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
		 * Filters the activation form options.
		 *
		 * @since 2.4.0
		 *
		 * @param array           $options Options data.
		 * @param WP_REST_Request $request Request object.
		 */
		return rest_ensure_response( WCSN()->apply_filters( 'rest_activation_options', $options, $request ) );
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
	 * Import activations from CSV.
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
	 * Export activations to CSV.
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
	 * Prepare activation for response.
	 *
	 * @since 2.4.0
	 *
	 * @param Activation      $item    Activation object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function prepare_item_for_response( $item, $request ): WP_REST_Response {
		$raw  = $item->to_array();
		$data = array();

		foreach ( $raw as $key => $value ) {
			switch ( $key ) {
				case 'activation_time':
					$data[ $key ] = $this->prepare_date_response( $value );
					break;

				default:
					$data[ $key ] = $value;
					break;
			}
		}

		/**
		 * Filters the activation data before creating the response.
		 *
		 * @since 2.4.0
		 *
		 * @param WP_REST_Response $response Response object.
		 * @param Activation       $item     Activation object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return WCSN()->apply_filters( 'rest_prepare_activation', rest_ensure_response( $data ), $item, $request );
	}

	/**
	 * Prepare activation for database.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return Activation|WP_Error Activation object or error.
	 */
	protected function prepare_item_for_database( $request ) {
		$id   = $request->get_param( 'id' );
		$item = $id ? Activation::find( $id ) : new Activation();

		if ( $id && ! $item ) {
			return new WP_Error( 'rest_activation_not_found', __( 'Activation not found.', 'wc-serial-numbers' ), array( 'status' => 404 ) );
		}

		foreach ( $request->get_params() as $key => $value ) {
			switch ( $key ) {
				case 'activation_time':
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
