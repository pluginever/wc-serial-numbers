<?php

namespace WooCommerceSerialNumbers\API;

use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Keys.
 *
 * @since   1.5.5
 * @package WooCommerceSerialNumbers\API
 */
class Keys extends Controller {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'keys';

	/**
	 * Checks if a given request has access to read categories.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 * @since 1.5.5
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view keys.', 'wc-serial-numbers' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}


	/**
	 * Checks if a given request has access to create a key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 * @since 1.5.5
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to create key.', 'wc-serial-numbers' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to read a key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 * @since 1.5.5
	 */
	public function get_item_permissions_check( $request ) {
		$item = wcsn_get_key( $request['id'] );

		if ( empty( $item ) || ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to view this key.', 'wc-serial-numbers' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to update a key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 * @since 1.5.5
	 */
	public function update_item_permissions_check( $request ) {
		$item = wcsn_get_key( $request['id'] );

		if ( empty( $item ) || ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to update this key.', 'wc-serial-numbers' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Checks if a given request has access to delete a key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
	 * @since 1.5.5
	 */
	public function delete_item_permissions_check( $request ) {
		$item = wcsn_get_key( $request['id'] );

		if ( empty( $item ) || ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error(
				'rest_forbidden_context',
				__( 'Sorry, you are not allowed to delete this key.', 'wc-serial-numbers' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Retrieves a list of categories.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 1.5.5
	 */
	public function get_items( $request ) {
		$params   = $this->get_collection_params();
		$params[] = 'type';
		foreach ( $params as $key => $value ) {
			if ( isset( $request[ $key ] ) ) {
				$args[ $key ] = $request[ $key ];
			}
		}
		$items     = wcsn_get_keys( $args );
		$total     = wcsn_get_keys( $args, true );
		$page      = isset( $request['page'] ) ? absint( $request['page'] ) : 1;
		$max_pages = ceil( $total / (int) $args['per_page'] );

		// If requesting page is greater than max pages, return empty array.
		if ( $page > $max_pages ) {
			return new \WP_Error(
				'rest_account_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.', 'wc-serial-numbers' ),
				array( 'status' => 400 )
			);
		}

		$results = array();
		foreach ( $items as $item ) {
			$data      = $this->prepare_item_for_response( $item, $request );
			$results[] = $this->prepare_response_for_collection( $data );
		}

		$response = rest_ensure_response( $results );

		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$base           = add_query_arg( urlencode_deep( $request_params ), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Retrieves a single key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 1.5.5
	 */
	public function get_item( $request ) {
		$item = wcsn_get_key( $request['id'] );
		$data = $this->prepare_item_for_response( $item, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Creates a single key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 1.5.5
	 */
	public function create_item( $request ) {
		if ( ! empty( $request['id'] ) ) {
			return new \WP_Error(
				'rest_account_exists',
				__( 'Cannot create existing key.', 'wc-serial-numbers' ),
				array( 'status' => 400 )
			);
		}

		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$item = wcsn_insert_key( $data );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$response = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $item->get_id() ) ) );

		return $response;

	}

	/**
	 * Updates a single key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 1.5.5
	 */
	public function update_item( $request ) {
		$item = wcsn_get_key( $request['id'] );

		$data = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		$data['id'] = $item->get_id();
		$item       = wcsn_insert_key( $data );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$response = $this->prepare_item_for_response( $item, $request );
		$response = rest_ensure_response( $response );

		return $response;
	}

	/**
	 * Deletes a single key.
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 1.5.5
	 */
	public function delete_item( $request ) {
		$item = wcsn_get_key( $request['id'] );
		$request->set_param( 'context', 'edit' );
		$data = $this->prepare_item_for_response( $item, $request );

		if ( ! wcsn_delete_key( $item->get_id() ) ) {
			return new \WP_Error(
				'rest_account_cannot_delete',
				__( 'The key cannot be deleted.', 'wc-serial-numbers' ),
				array( 'status' => 500 )
			);
		}

		$response = new \WP_REST_Response();
		$response->set_data(
			array(
				'deleted'  => true,
				'previous' => $this->prepare_response_for_collection( $data ),
			)
		);

		return $response;
	}

	/**
	 * Prepares a single key output for response.
	 *
	 * @param Key              $item key object.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 * @since 1.5.5
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = [];

		foreach ( array_keys( $this->get_schema_properties() ) as $key ) {
			switch ( $key ) {
				case 'created_at':
				case 'updated_at':
					$value = $this->prepare_date_response( $item->$key );
					break;
				default:
					$value = $item->$key;
					break;
			}

			$data[ $key ] = $value;
		}

		$context  = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, $context );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $item, $request ) );
		return $response;
	}

	/**
	 * Prepares a single key for create or update.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array|\WP_Error key object or WP_Error.
	 * @since 1.5.5
	 */
	protected function prepare_item_for_database( $request ) {
		$schema    = $this->get_item_schema();
		$data_keys = array_keys( array_filter( $schema['properties'], array( $this, 'filter_writable_props' ) ) );
		$props     = [];
		// Handle all writable props.
		foreach ( $data_keys as $key ) {
			$value = $request[ $key ];
			if ( ! is_null( $value ) ) {
				switch ( $key ) {
					default:
						$props[ $key ] = $value;
						break;
				}
			}
		}
		return $props;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param Key              $item Object data.
	 * @param \WP_REST_Request $request Request key.
	 *
	 * @return array Links for the given key.
	 */
	protected function prepare_links( $item, $request ) {
		return array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $item->get_id() ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);
	}

	/**
	 * Retrieves the item's schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 * @since 1.1.2
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => __( 'Key', 'wc-serial-numbers' ),
			'type'       => 'object',
			'properties' => array(
				'id'               => array(
					'description' => __( 'Unique identifier for the key.', 'wc-serial-numbers' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'readonly'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'serial_key'       => array(
					'description' => __( 'Serial key.', 'wc-serial-numbers' ),
					'type'        => 'string',
					'context'     => array( 'view', 'embed', 'edit' ),
					'required'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
				'product_id'       => array(
					'description' => __( 'Product ID.', 'wc-serial-numbers' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'required'    => true,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'activation_limit' => array(
					'description' => __( 'Activation limit.', 'wc-serial-numbers' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'required'    => true,
					'default'     => 0,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'activation_count' => array(
					'description' => __( 'Activation count.', 'wc-serial-numbers' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'required'    => true,
					'default'     => 0,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'order_id'         => array(
					'description' => __( 'Order ID.', 'wc-serial-numbers' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'default'     => 0,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'status'           => array(
					'description' => __( 'Status.', 'wc-serial-numbers' ),
					'type'        => 'string',
					'enum'        => array_keys( wcsn_get_key_statuses() ),
					'context'     => array( 'view', 'embed', 'edit' ),
					'default'     => 'available',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'validity'         => array(
					'description' => __( 'Validity.', 'wc-serial-numbers' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'embed', 'edit' ),
					'default'     => 0,
					'arg_options' => array(
						'sanitize_callback' => 'intval',
					),
				),
				'order_date'       => array(
					'description' => __( 'Order date.', 'wc-serial-numbers' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'embed', 'edit' ),
					'default'     => '',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

				'source'           => array(
					'description' => __( 'Source.', 'wc-serial-numbers' ),
					'type'        => 'string',
					'enum'        => array_keys( wcsn_get_key_sources() ),
					'context'     => array( 'view', 'embed', 'edit' ),
					'default'     => 'manual',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'created_date'     => array(
					'description' => __( 'Created date.', 'wc-serial-numbers' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'embed', 'edit' ),
					'default'     => '',
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),

			),
		);

		return $this->add_additional_fields_schema( $schema );

	}
}
