<?php

namespace Pluginever\WCSerialNumbers\Admin;


// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Serial_List_Table extends \WP_List_Table {

	protected $is_single = false;
	protected $search_query = false;

	/** Class constructor */
	public function __construct($post_id = '') {

		parent::__construct([
			'singular' => __('Serial Number', 'wc-serial-numbers'), //singular name of the listed records
			'plural'   => __('Serial Numbers', 'wc-serial-numbers'), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

		]);

		$this->is_single = $post_id;

		//Search based on serial number
		empty($_GET['s']) ? false : $this->search_query = esc_attr($_GET['s']);

	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return void
	 */

	public function prepare_items() {

		$columns  = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$data     = $this->table_data();
		usort($data, array(&$this, 'sort_data'));
		$perPage     = 15;
		$currentPage = $this->get_pagenum();
		$totalItems  = count($data);

		$this->set_pagination_args(array(
			'total_items' => $totalItems,
			'per_page'    => $perPage
		));

		$data                  = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items           = $data;

	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return array
	 */
	public function get_columns() {

		if ($this->is_single) {

			$columns = array(
				'serial_numbers' => __('Serial Numbers', 'wc-serial-numbers'),
				'product'        => __('Product', 'wc-serial-numbers'),
				'variation'      => __('Variation', 'wc-serial-numbers'),
				'deliver_times'  => __('Deliver Times', 'wc-serial-numbers'),
				'max_instance'   => __('Max. Instance', 'wc-serial-numbers'),
				'validity'       => __('Validity', 'wc-serial-numbers'),
			);

		} else {

			$columns = array(
				'cb'             => '<input type="checkbox" />',
				'serial_numbers' => __('Serial Numbers', 'wc-serial-numbers'),
				'product'        => __('Product', 'wc-serial-numbers'),
				'variation'      => __('Variation', 'wc-serial-numbers'),
				'deliver_times'  => __('Delivered/ Deliver Times', 'wc-serial-numbers'),
				'max_instance'   => __('Max. Instance', 'wc-serial-numbers'),
				'purchaser'      => __('Purchaser', 'wc-serial-numbers'),
				'order'          => __('Order', 'wc-serial-numbers'),
				'purchased_on'   => __('Purchased On', 'wc-serial-numbers'),
				'validity'       => __('Validity', 'wc-serial-numbers'),
			);

		}

		return $columns;
	}


	/**
	 * Define the sortable columns
	 *

	 */
	public function get_sortable_columns() {
		return [
			'serial_numbers' => array('serial_numbers', false),
			'purchaser'      => array('purchaser', false),
			'order'          => array('order', false),
			'purchased_on'   => array('purchased_on', false),
		];
	}

	/**
	 * Get the table data
	 *
	 * @return array
	 *
	 */
	private function table_data() {

		$data = array();

		$query = !$this->is_single ? ['s' => $this->search_query] : ['meta_key' => 'product', 'meta_value' => $this->is_single];

		$posts = wsn_get_serial_numbers($query);

		foreach ($posts as $post) {

			setup_postdata($post);

			$product            = get_post_meta($post->ID, 'product', true);
			$variation          = get_post_meta($post->ID, 'variation', true);
			$deliver_times      = get_post_meta($post->ID, 'deliver_times', true);
			$used_deliver_times = wsn_used_deliver_times($post->ID);
			$max_instance       = get_post_meta($post->ID, 'max_instance', true);
			$image_license      = get_post_meta($post->ID, 'image_license', true);
			$order              = get_post_meta($post->ID, 'order', true);

			//Order Details
			$order_obj = wc_get_order($order);

			$customer_name  = wsn_get_customer_detail('first_name', $order_obj) . ' ' . wsn_get_customer_detail('last_name', $order_obj);
			$customer_email = wsn_get_customer_detail('email', $order_obj);
			$purchaser      = $customer_name . '<br>' . $customer_email;

			if (is_object($order_obj)) {
				$purchased_on = $order_obj->get_data()['date_created'];
			}

			$validity = get_post_meta($post->ID, 'validity', true);

			$data[] = [
				'ID'             => $post->ID,
				'serial_numbers' => get_the_title($post->ID) . '<br><img src="' . $image_license . '" class="ever-thumbnail-small">',
				'product'        => '<a href="' . get_the_permalink($product) . '">' . get_the_title($product) . '</a>',
				'variation'      => get_the_title($variation),
				'deliver_times'  => empty($deliver_times) ? '∞' : $used_deliver_times . '/' . $deliver_times,
				'max_instance'   => empty($max_instance) ? '∞' : $max_instance,
				'purchaser'      => empty($purchaser) ? '-' : $purchaser,
				'order'          => empty($order) ? '-' : '<a href="' . get_edit_post_link($order) . '">#' . $order . '</a>',
				'purchased_on'   => empty($purchased_on) ? '-' : date('m-d-Y H:i a', strtotime($purchased_on)),
				'validity'       => empty($validity) ? '∞' : $validity,
			];

		}

		return $data;
	}


	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array $item Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default($item, $column_name) {

		switch ($column_name) {
			case 'ID':
			case 'serial_numbers':
			case 'product':
			case 'variation':
			case 'deliver_times':
			case 'max_instance':
			case 'purchaser':
			case 'order':
			case 'purchased_on':
			case 'validity':
				return $item[$column_name];
			default:
				return print_r($item, true);
		}
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */

	public function get_bulk_actions() {

		if (!$this->is_single) {
			$actions = [
				'bulk-delete' => 'Delete'
			];

			return $actions;
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);
	}

	function column_serial_numbers($item) {

		$actions = array(
			'edit'   => '<a href="' . add_query_arg(['type' => 'manual', 'row_action' => 'edit', 'serial_number' => $item['ID']], WPWSN_ADD_SERIAL_PAGE) . '">' . __('Edit', 'wc-serial-numbers') . '</a>',
			'delete' => sprintf('<a href="?page=%s&row_action=%s&serial_number=%s">Delete</a>', !empty($_REQUEST['page']) ? esc_attr($_REQUEST['page']) : '', 'delete', $item['ID']),
		);

		return sprintf('%1$s %2$s', $item['serial_numbers'], $this->row_actions($actions));
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data($a, $b) {
		// Set defaults
		$orderby = 'serial_numbers';
		$order   = 'asc';

		// If orderby is set, use this as the sort column
		if (!empty($_GET['orderby'])) {
			$orderby = esc_attr($_GET['orderby']);
		}
		// If order is set use this as the order
		if (!empty($_GET['order'])) {
			$order = esc_attr($_GET['order']);
		}

		$result = strcmp($a[$orderby], $b[$orderby]);
		if ($order === 'asc') {
			return $result;
		}

		return -$result;
	}


}
