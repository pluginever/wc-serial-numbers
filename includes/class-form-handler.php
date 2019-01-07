<?php

namespace Pluginever\WCSerialNumbers;

class FormHandler
{
	function __construct()
	{
		add_action('admin_post_wsn_add_edit_serial_number', [$this, 'handle_add_edit_serial_number_form']);
		add_action('admin_post_wsn_edit_serial_number', [$this, 'handle_edit_serial_number_form']);
		add_action('init', [$this, 'handle_serial_numbers_table']);
	}

	/**
	 * Handle add new serial number form
	 */

	function handle_add_edit_serial_number_form()
	{

		if (!wp_verify_nonce($_REQUEST['wsn_add_edit_serial_numbers_nonce'], 'wsn_add_edit_serial_numbers')) {
			return;
		}

		$action_type = sanitize_text_field($_REQUEST['action_type']);

		$serial_number = sanitize_textarea_field($_REQUEST['serial_number']);
		$product       = esc_attr($_REQUEST['product']);
		$image_license = esc_url($_REQUEST['image_license']);
		$deliver_times = esc_attr($_REQUEST['deliver_times']);
		$max_instance  = esc_attr($_REQUEST['max_instance']);
		$expires_on    = esc_attr($_REQUEST['expires_on']);
		$validity      = esc_attr($_REQUEST['validity']);

		$url = untrailingslashit($_SERVER['HTTP_ORIGIN']) . $_REQUEST['_wp_http_referer'];

		if (empty($serial_number) and empty($image_license)) {
			wsn_redirect_with_message($url, 'empty_serial_number', 'error');
		}
		if (empty($product)) {
			wsn_redirect_with_message($url, 'empty_product', 'error');
		}
		if ($action_type == 'wsn_add_serial_number') {

			$post_id = wp_insert_post([
				'post_title'  => $serial_number,
				'post_type'   => 'wsn_serial_number',
				'post_status' => 'publish',
			]);

		} elseif ($action_type == 'wsn_edit_serial_number') {

			$serial_number_id = esc_attr($_REQUEST['serial_number_id']);

			$post_id = wp_update_post([
				'ID'         => $serial_number_id,
				'post_title' => $serial_number,
			]);

		}

		update_post_meta($post_id, 'product', $product);
		update_post_meta($post_id, 'image_license', $image_license);
		update_post_meta($post_id, 'deliver_times', $deliver_times);
		update_post_meta($post_id, 'remain_deliver_times', $deliver_times);
		update_post_meta($post_id, 'max_instance', $max_instance);
		update_post_meta($post_id, 'expires_on', $expires_on);
		update_post_meta($post_id, 'validity', $validity);

		update_post_meta($product, 'enable_serial_number', true); //Enable serial number system for the product

		wp_redirect(WPWSN_SERIAL_INDEX_PAGE);

	}


	/**
	 * Handle serial number table actions
	 * @return bool|void
	 */

	function handle_serial_numbers_table()
	{

		if (!isset($_REQUEST['wsn-serial-numbers-table-action'])) {
			return;
		}

		if (!isset($_REQUEST['action'])) {
			return;
		}

		if (!wp_verify_nonce($_REQUEST['wsn-serial-numbers-table-nonce'], 'wsn-serial-numbers-table')) {
			wp_die('No Cheating!');
		}

		$bulk_deletes = $_REQUEST['bulk-delete'];

		if (isset($bulk_deletes)) {
			foreach ($bulk_deletes as $bulk_delete) {
				wp_delete_post($bulk_delete);
			}
		}

		return wp_redirect(admin_url('admin.php?page=serial-numbers'));

	}

}
