<?php

namespace Pluginever\WCSerialNumberPro;

class FormHandler
{

	function __construct()
	{
		add_action('admin_post_wsn_add_edit_generator_rule', [$this, 'handle_add_edit_generator_rule']);
		//add_action('admin_post_wsn_edit_serial_number', [$this, 'handle_edit_serial_number_form']);
		//add_action('init', [$this, 'handle_serial_numbers_table']);
	}

	/**
	 * Handle add new serial number form
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	function handle_add_edit_generator_rule()
	{

		if (!wp_verify_nonce($_REQUEST['wsn_add_edit_generator_rule_nonce'], 'wsn_add_edit_generator_rule')) {

			return;

		}

		$action_type = sanitize_text_field($_REQUEST['action_type']);

		if ($action_type == 'wsn_edit_generator_rule'){
			echo '<pre>';
			//wp_die(print_r($_REQUEST));
			echo '</pre>';
		}

		$product       = esc_attr($_REQUEST['product']);
		$variation     = esc_attr($_REQUEST['variation']);
		$prefix        = esc_html($_REQUEST['prefix']);
		$chunks_number = esc_attr($_REQUEST['chunks_number']);
		$chunk_length  = esc_attr($_REQUEST['chunk_length']);
		$suffix        = esc_html($_REQUEST['suffix']);
		$max_instance  = esc_attr($_REQUEST['max_instance']);
		$validity_type = esc_html($_REQUEST['validity_type']);
		$validity      = esc_attr($_REQUEST['validity']);


		$url = untrailingslashit($_SERVER['HTTP_ORIGIN']) . $_REQUEST['_wp_http_referer'];


		if (empty($product)) {
			wsn_redirect_with_message($url, 'empty_product', 'error');
		}
		if ($action_type == 'wsn_add_generator_rule') {

			$post_id = wp_insert_post([
				'post_type'   => 'wsnp_generator_rule',
				'post_status' => 'publish',
			]);

		} elseif ($action_type == 'wsn_edit_generator_rule') {

			$generator_rule_id = esc_attr($_REQUEST['generator_rule_id']);

			$post_id = wp_update_post([
				'ID' => $generator_rule_id,
			]);

		}

		update_post_meta($post_id, 'product', $product);
		update_post_meta($post_id, 'variation', $variation);
		update_post_meta($post_id, 'prefix', $prefix);
		update_post_meta($post_id, 'chunks_number', $chunks_number);
		update_post_meta($post_id, 'chunk_length', $chunk_length);
		update_post_meta($post_id, 'suffix', $suffix);
		update_post_meta($post_id, 'max_instance', $max_instance);
		update_post_meta($post_id, 'validity_type', $validity_type);
		update_post_meta($post_id, 'validity', $validity);

		update_post_meta($product, 'enable_serial_number', true); //Enable serial number system for the product

		wp_redirect(add_query_arg('type', 'automate', WPWSN_ADD_SERIAL_PAGE));

	}

}
