<?php

namespace Pluginever\WCSerialNumbers;

class FormHandler {
	function __construct() {
		add_action('admin_post_wsn_add_edit_serial_number', [$this, 'handle_add_edit_serial_number_form']);
		add_action('admin_post_wsn_edit_serial_number', [$this, 'handle_edit_serial_number_form']);
		add_action('init', [$this, 'handle_serial_numbers_table']);
	}

	/**
	 * Handle add new serial number form
	 *
	 * @since 1.0.0
	 */

	function handle_add_edit_serial_number_form() {

		if (!wp_verify_nonce($_REQUEST['wsn_add_edit_serial_numbers_nonce'], 'wsn_add_edit_serial_numbers')) {
			return;
		}

		$action_type = sanitize_text_field($_REQUEST['action_type']);

		$serial_number = empty($_REQUEST['serial_number']) ? '' : sanitize_textarea_field($_REQUEST['serial_number']);
		$product       = empty($_REQUEST['product']) ? '' : intval($_REQUEST['product']);
		$variation     = empty($_REQUEST['variation']) ? 0 : intval($_REQUEST['variation']);
		$image_license = empty($_REQUEST['image_license']) ? '' : esc_url($_REQUEST['image_license']);
		$deliver_times = empty($_REQUEST['deliver_times']) ? '' : intval($_REQUEST['deliver_times']);
		$max_instance  = empty($_REQUEST['max_instance']) ? '' : intval($_REQUEST['max_instance']);
		$validity_type = empty($_REQUEST['validity_type']) ? '' : esc_attr($_REQUEST['validity_type']);
		$validity      = empty($_REQUEST['validity']) ? '' : esc_attr($_REQUEST['validity']);

		$url = untrailingslashit($_SERVER['HTTP_ORIGIN']) . $_REQUEST['_wp_http_referer'];

		if (empty($serial_number) and empty($image_license)) {
			wsn_redirect_with_message($url, 'empty_serial_number', 'error');
		}
		if (empty($product)) {
			wsn_redirect_with_message($url, 'empty_product', 'error');
		}

		$meta_input = array(
			'product'              => $product,
			'variation'            => $variation,
			'image_license'        => $image_license,
			'deliver_times'        => $deliver_times,
			'max_instance'         => $max_instance,
			'validity_type'        => $validity_type,
			'validity'             => $validity,
			'enable_serial_number' => 'enable',
		);

		if ($action_type == 'wsn_add_serial_number') {

			$meta_input['used'] = 0;

			$post_id = wp_insert_post([
				'post_title'  => $serial_number,
				'post_type'   => 'wsn_serial_number',
				'post_status' => 'publish',
				'meta_input'  => $meta_input,
			]);

		} elseif ($action_type == 'wsn_edit_serial_number') {

			$serial_number_id = esc_attr($_REQUEST['serial_number_id']);

			$post_id = wp_update_post([
				'ID'         => $serial_number_id,
				'post_title' => $serial_number,
				'meta_input' => $meta_input,
			]);

		}

		do_action('wsn_update_notification_on_add_edit', $product);

		wp_redirect(WPWSN_SERIAL_INDEX_PAGE);

	}


	/**
	 * Handle serial number table actions
	 *
	 * @return string|boolean
	 */

	function handle_serial_numbers_table() {

		if (!isset($_REQUEST['wsn-serial-numbers-table-action']) || empty($_REQUEST['wsn-serial-numbers-table-nonce'])) {
			return false;
		}


		if (!wp_verify_nonce($_REQUEST['wsn-serial-numbers-table-nonce'], 'wsn-serial-numbers-table')) {
			wp_die('No Cheating!');
		}

		if (!empty($_REQUEST['wsn-filter-table-serial-numbers'])) {

			$serialnumber = esc_attr($_REQUEST['filter-serialnumber']);
			$product      = esc_attr($_REQUEST['filter-product']);

			return wp_redirect(add_query_arg(
				[
					'serialnumber' => $serialnumber,
					'product'      => $product,
				], WPWSN_SERIAL_INDEX_PAGE));

		} elseif (!empty($_REQUEST['wsn-filter-table-generate'])) {

			$product = esc_attr($_REQUEST['filter-product']);

			return wp_redirect(add_query_arg(
				[
					'product' => $product,
				], WPWSN_GENERATE_SERIAL_PAGE));
		}

		$bulk_deletes = $_REQUEST['bulk-delete'];
		$products     = $_REQUEST['product'];

		if (!empty($bulk_deletes)) {

			foreach ($bulk_deletes as $bulk_delete) {

				$bulk_delete = esc_attr($bulk_delete);

				wp_delete_post($bulk_delete);

				do_action('wsn_update_notification_on_order_delete', $products[$bulk_delete]);
			}

		}

		return wp_redirect(WPWSN_SERIAL_INDEX_PAGE);

	}


}
