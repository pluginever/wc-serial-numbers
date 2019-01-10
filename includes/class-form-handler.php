<?php

namespace Pluginever\WCSerialNumberPro;

class FormHandler {

	function __construct() {
		add_action('admin_post_wsn_add_edit_generator_rule', [$this, 'handle_add_edit_generator_rule']);
	}

	/**
	 * Handle add new serial number form
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	function handle_add_edit_generator_rule() {

		if (!wp_verify_nonce($_REQUEST['wsn_add_edit_generator_rule_nonce'], 'wsn_add_edit_generator_rule')) {

			return;

		}

		$action_type = sanitize_text_field($_REQUEST['action_type']);

		$product       = esc_attr($_REQUEST['product']);
		$variation     = esc_attr($_REQUEST['variation']);
		$prefix        = esc_html($_REQUEST['prefix']);
		$chunks_number = esc_attr($_REQUEST['chunks_number']);
		$chunk_length  = esc_attr($_REQUEST['chunk_length']);
		$suffix        = esc_html($_REQUEST['suffix']);
		$deliver_times = esc_attr($_REQUEST['deliver_times']);
		$max_instance  = esc_attr($_REQUEST['max_instance']);
		$validity_type = esc_html($_REQUEST['validity_type']);
		$validity      = esc_attr($_REQUEST['validity']);


		$url = untrailingslashit($_SERVER['HTTP_ORIGIN']) . $_REQUEST['_wp_http_referer'];


		if (empty($product)) {
			wsn_redirect_with_message($url, 'empty_product', 'error');
		}

		$meta_input = array(
			'product'       => $product,
			'variation'     => $variation,
			'prefix'        => $prefix,
			'chunks_number' => $chunks_number,
			'chunk_length'  => $chunk_length,
			'suffix'        => $suffix,
			'deliver_times' => $deliver_times,
			'used'          => 0,
			'max_instance'  => $max_instance,
			'validity_type' => $validity_type,
			'validity'      => $validity,

			'enable_serial_number' => 'enable',
		);

		if ($action_type == 'wsn_add_generator_rule') {

			$post_id = wp_insert_post([
				'post_type'   => 'wsnp_generator_rule',
				'post_status' => 'publish',
				'meta_input'  => $meta_input,
			]);

		} elseif ($action_type == 'wsn_edit_generator_rule') {

			$generator_rule_id = esc_attr($_REQUEST['generator_rule_id']);

			$post_id = wp_update_post([
				'ID'         => $generator_rule_id,
				'meta_input' => $meta_input,
			]);

		}

		wp_redirect(add_query_arg('type', 'automate', WPWSN_ADD_SERIAL_PAGE));

	}

}
