<?php

namespace Pluginever\WCSerialNumberPro;

class FormHandler {

	function __construct() {
		add_action( 'admin_post_wsn_add_edit_generator_rule', array( $this, 'handle_add_edit_generator_rule' ) );
	}

	/**
	 * Handle add new serial number form
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	function handle_add_edit_generator_rule() {

		if ( ! wp_verify_nonce( $_REQUEST['_nonce'], 'wsn_add_edit_generator_rule' ) ) {
			wp_die( __( 'No, Cheating', 'wc-serial-number-pro' ) );
		}

		$action_type   = ! empty( $_REQUEST['action_type'] ) ? sanitize_key( $_REQUEST['action_type'] ) : '';
		$product       = ! empty( $_REQUEST['product'] ) ? intval( $_REQUEST['product'] ) : '';
		$variation     = ! empty( $_REQUEST['variation'] ) ? intval( $_REQUEST['variation'] ) : '';
		$prefix        = ! empty( $_REQUEST['prefix'] ) ? sanitize_text_field( $_REQUEST['prefix'] ) : '';
		$chunks_number = ! empty( $_REQUEST['chunks_number'] ) ? intval( $_REQUEST['chunks_number'] ) : '';
		$chunk_length  = ! empty( $_REQUEST['chunk_length'] ) ? intval( $_REQUEST['chunk_length'] ) : '';
		$suffix        = ! empty( $_REQUEST['suffix'] ) ? sanitize_text_field( $_REQUEST['suffix'] ) : '';
		$deliver_times = ! empty( $_REQUEST['deliver_times'] ) ? intval( $_REQUEST['deliver_times'] ) : '';
		$max_instance  = ! empty( $_REQUEST['max_instance'] ) ? intval( $_REQUEST['max_instance'] ) : '';
		$validity_type = ! empty( $_REQUEST['validity_type'] ) ? sanitize_key( $_REQUEST['validity_type'] ) : '';
		$validity      = ! empty( $_REQUEST['validity'] ) ? sanitize_key( $_REQUEST['validity'] ) : '';

		$url = admin_url( 'admin.php?page=add-wc-generator-rule' );

		if ( empty( $product ) ) {

			if ( empty( $product ) ) {
				wc_serial_numbers()->add_notice( __( 'The product is empty. Please select a product and try again', 'wc-serial-number-pro' ) );
				wp_safe_redirect( $url );
				exit();
			}

		}

		$meta_input = array(
			'product'              => $product,
			'variation'            => $variation,
			'prefix'               => $prefix,
			'chunks_number'        => $chunks_number,
			'chunk_length'         => $chunk_length,
			'suffix'               => $suffix,
			'deliver_times'        => $deliver_times,
			'used'                 => 0,
			'max_instance'         => $max_instance,
			'validity_type'        => $validity_type,
			'validity'             => $validity,
			'enable_serial_number' => 'enable',
		);

		if ( $action_type == 'add' ) {

			wp_insert_post( [
				'post_type'   => 'wsnp_generator_rule',
				'post_status' => 'publish',
				'meta_input'  => $meta_input,
			] );

		} elseif ( $action_type == 'edit' ) {

			$generator_rule_id = !empty($_REQUEST['generator_rule_id']) ? intval( $_REQUEST['generator_rule_id'] ) : '';

			if( current_user_can('publish_posts') && get_post_status($generator_rule_id) ) {

				wp_update_post( [
					'ID'         => $generator_rule_id,
					'meta_input' => $meta_input,
				] );

			}

		}

		wp_safe_redirect( add_query_arg( 'type', 'automate', WPWSN_ADD_SERIAL_PAGE ) );

	}

}
