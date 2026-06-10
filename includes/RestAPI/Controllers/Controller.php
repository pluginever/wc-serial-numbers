<?php

namespace PluginEver\SerialNumbers\RestAPI\Controllers;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract REST API controller.
 *
 * Model is the single source of truth. No schema duplication.
 *
 * @since 2.4.0
 * @package PluginEver\SerialNumbers\RestAPI
 */
abstract class Controller extends WP_REST_Controller {

	/**
	 * Retrieves the item's schema, conforming to JSON Schema.
	 *
	 * @since 2.4.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'type'     => 'integer',
					'context'  => array( 'view', 'edit' ),
					'readonly' => true,
				),
			),
		);
	}

	/**
	 * Retrieves the query params for the collection.
	 *
	 * @since 2.4.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['per_page']['default'] = 20;

		return $params;
	}

	/**
	 * Prepare date for response (MySQL to RFC3339).
	 *
	 * @since 2.4.0
	 *
	 * @param string|null $date MySQL datetime.
	 *
	 * @return string|null RFC3339 date.
	 */
	protected function prepare_date_response( ?string $date ): ?string {
		if ( empty( $date ) || '0000-00-00 00:00:00' === $date ) {
			return null;
		}

		return mysql_to_rfc3339( $date );
	}

	/**
	 * Prepare date for database (RFC3339 to MySQL).
	 *
	 * @since 2.4.0
	 *
	 * @param string|null $date RFC3339 datetime.
	 *
	 * @return string|null MySQL datetime.
	 */
	protected function prepare_date_for_database( ?string $date ): ?string {
		if ( empty( $date ) ) {
			return null;
		}

		$timestamp = rest_parse_date( $date );

		if ( ! $timestamp ) {
			return null;
		}

		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}

	/**
	 * Convert items into value/label options array.
	 *
	 * Accepts two formats:
	 * 1. Associative array (key => label): prepare_options( ['available' => 'Available', 'sold' => 'Sold'] )
	 * 2. Array of arrays/objects with pluck keys: prepare_options( $items, 'id', 'name' )
	 *
	 * @since 2.4.0
	 *
	 * @param array  $items     Items to convert.
	 * @param string $value_key Key to pluck for value. Empty = treat as associative key => label.
	 * @param string $label_key Key to pluck for label.
	 *
	 * @return array Options array of [ ['value' => ..., 'label' => ...], ... ].
	 */
	protected function prepare_options( array $items, string $value_key = '', string $label_key = '' ): array {
		if ( empty( $items ) ) {
			return array();
		}

		// Associative array: key => label.
		if ( empty( $value_key ) ) {
			$options = array();
			foreach ( $items as $value => $label ) {
				$options[] = array(
					'value' => $value,
					'label' => $label,
				);
			}
			return $options;
		}

		// Array of arrays/objects: pluck by keys.
		return array_map(
			function ( $item ) use ( $value_key, $label_key ) {
				$item = (array) $item;
				return array(
					'value' => $item[ $value_key ] ?? '',
					'label' => $item[ $label_key ] ?? '',
				);
			},
			array_values( $items )
		);
	}

	/**
	 * Prepare a single related model for response (belongs_to, has_one).
	 *
	 * @since 2.4.0
	 *
	 * @param mixed           $item       Related model instance.
	 * @param string          $controller Controller class name.
	 * @param WP_REST_Request $request    Request object.
	 *
	 * @return array|null Prepared relation data or null.
	 */
	protected function prepare_relation( $item, string $controller, WP_REST_Request $request ): ?array {
		if ( empty( $item ) ) {
			return null;
		}

		$ctrl = new $controller();

		return $ctrl->prepare_response_for_collection( $ctrl->prepare_item_for_response( $item, $request ) );
	}

	/**
	 * Prepare a collection of related models for response (has_many, belongs_to_many).
	 *
	 * @since 2.4.0
	 *
	 * @param array           $items      Array of related model instances.
	 * @param string          $controller Controller class name.
	 * @param WP_REST_Request $request    Request object.
	 *
	 * @return array Prepared relations data.
	 */
	protected function prepare_relations( array $items, string $controller, WP_REST_Request $request ): array {
		if ( empty( $items ) ) {
			return array();
		}

		$ctrl = new $controller();

		return array_map(
			fn( $item ) => $ctrl->prepare_response_for_collection( $ctrl->prepare_item_for_response( $item, $request ) ),
			$items
		);
	}

	/**
	 * Apply query filters from request.
	 *
	 * @since 2.4.0
	 *
	 * @param mixed           $query       The query object.
	 * @param WP_REST_Request $request     The REST API request.
	 * @param string          $object_type Object type for filter hook (e.g., 'keys', 'activations').
	 *
	 * @return mixed The modified query object.
	 */
	protected function apply_query_filters( $query, WP_REST_Request $request, string $object_type = '' ) {
		$filters = $request->get_param( 'filters' );

		if ( ! empty( $filters['filters'] ) && is_array( $filters['filters'] ) ) {
			$boolean = 'any' === ( $filters['match'] ?? 'all' ) ? 'OR' : 'AND';
			$query->where(
				function ( $q ) use ( $filters ) {
					foreach ( $filters['filters'] as $filter ) {
						if ( ! empty( $filter['field'] ) && ! empty( $filter['operator'] ) ) {
							$q->where( $filter['field'], $filter['operator'], $filter['value'] ?? null );
						}
					}
				},
				$boolean
			);
		}

		if ( $object_type ) {
			/**
			 * Filters the query after applying filters.
			 *
			 * @since 2.4.0
			 *
			 * @param mixed           $query   The query object.
			 * @param array           $filters The filters array.
			 * @param WP_REST_Request $request The REST API request.
			 */
			$query = WCSN()->apply_filters( "rest_{$object_type}_apply_query_filters", $query, $filters, $request );
		}

		return $query;
	}

	/**
	 * Process batch operations.
	 *
	 * @since 2.4.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response|WP_Error Response or error.
	 */
	protected function process_batch( $request ) {
		$items    = $request->get_params();
		$response = array();

		/**
		 * Filters the maximum number of items allowed in a batch request.
		 *
		 * @since 2.4.0
		 *
		 * @param int $limit Maximum batch size. Default 100.
		 */
		$limit = WCSN()->apply_filters( 'rest_batch_limit', 100 );
		$total = count( $items['create'] ?? array() ) + count( $items['update'] ?? array() ) + count( $items['delete'] ?? array() );

		if ( $total > $limit ) {
			// translators: %d is the maximum batch size.
			return new WP_Error( 'rest_batch_too_large', sprintf( __( 'Batch limit is %d items.', 'wc-serial-numbers' ), $limit ), array( 'status' => 413 ) );
		}

		foreach ( $items['create'] ?? array() as $data ) {
			$req = new WP_REST_Request( 'POST' );
			$req->set_body_params( $data );
			$res                  = $this->create_item( $req );
			$response['create'][] = is_wp_error( $res ) ? array( 'error' => $res->get_error_message() ) : $res->get_data();
		}

		foreach ( $items['update'] ?? array() as $data ) {
			$req = new WP_REST_Request( 'PUT' );
			$req->set_body_params( $data );
			$res                  = $this->update_item( $req );
			$response['update'][] = is_wp_error( $res ) ? array(
				'id'    => $data['id'] ?? 0,
				'error' => $res->get_error_message(),
			) : $res->get_data();
		}

		foreach ( $items['delete'] ?? array() as $id ) {
			$id  = is_array( $id ) ? ( $id['id'] ?? 0 ) : (int) $id;
			$req = new WP_REST_Request( 'DELETE' );
			$req->set_param( 'id', $id );
			$res                  = $this->delete_item( $req );
			$response['delete'][] = is_wp_error( $res ) ? array(
				'id'    => $id,
				'error' => $res->get_error_message(),
			) : $res->get_data();
		}

		return rest_ensure_response( $response );
	}
}
