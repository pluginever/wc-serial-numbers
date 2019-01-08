<?php

namespace Pluginever\WCSerialNumberPro\Admin;


// WP_List_Table is not loaded automatically so we need to load it in our application
if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Generate_Serial_Table extends \WP_List_Table
{

	protected $is_single = false;
	protected $search_query = false;

	/** Class constructor */
	public function __construct($post_id = '')
	{

		parent::__construct([
			'singular' => __('Generate Serial Number', 'wc-serial-number'), //singular name of the listed records
			'plural'   => __('Generate Serial Numbers', 'wc-serial-number'), //plural name of the listed records
			'ajax'     => false //should this table support ajax?

		]);

		$this->is_single = $post_id;

		//Search based on serial number
		empty($_GET['s']) ? false : $this->search_query = $_GET['s'];

	}

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */

	public function prepare_items()
	{
		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
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
	 * @return Array
	 */
	public function get_columns()
	{

		if ($this->is_single) {
			$columns = array();
		} else {
			$columns = array(
				'cb'            => '<input type="checkbox" />',
				'product'       => __('', 'wc-serial-numbers'),
				'variation'     => __('Variation', 'wc-serial-numbers'),
				'prefix'        => __('Prefix. ', 'wc-serial-numbers'),
				'chunks_number' => __('Chunks', 'wc-serial-numbers'),
				'chunks_length' => __('Chunks', 'wc-serial-numbers'),
				'suffix'        => __('Suffix', 'wc-serial-numbers'),
				'instance'      => __('Instance', 'wc-serial-numbers'),
				'validity'      => __('Validity', 'wc-serial-numbers'),
				'generate'      => __('Generate', 'wc-serial-numbers'),
			);
		}

		return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns()
	{
		return array();
	}

	/**
	 * Define the sortable columns
	 *

	 */
	public function get_sortable_columns()
	{
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
	 * @return Array
	 */
	private function table_data()
	{
		$data = array();

		$query = !$this->is_single ? ['s' => $this->search_query] : ['meta_key' => 'product', 'meta_value' => $this->is_single];

		$posts = wsnp_get_generator_rules($query);

		foreach ($posts as $post) {

			setup_postdata($post);

			$product       = get_post_meta($post->ID, 'product', true);
			$variation     = get_post_meta($post->ID, 'variation', true);
			$prefix        = get_post_meta($post->ID, 'prefix', true);
			$chunks_number = get_post_meta($post->ID, 'chunks_number', true);
			$chunks_length = get_post_meta($post->ID, 'chunks_length', true);
			$suffix        = get_post_meta($post->ID, 'suffix', true);
			$instance      = get_post_meta($post->ID, 'instance', true);
			$validity      = get_post_meta($post->ID, 'validity', true);
			$generate      = get_post_meta($post->ID, 'generate', true);

			$data[] = [
				'ID'            => $post->ID,
				'product'       => '<a href="' . get_the_permalink($product) . '">' . get_the_title($product) . '</a>',
				'variation'     => empty($variation) ? '' : $variation ,
				'prefix'        => empty($prefix) ? '' : $prefix ,
				'chunks_number' => empty($chunks_number) ? '' : $chunks_number,
				'chunks_length' => empty($chunks_length) ? '' : $chunks_length,
				'suffix'        => empty($suffix) ? '' : $suffix,
				'instance'      => empty($instance) ? '∞' : $instance,
				'validity'      => empty($instance) ? '∞' : $instance,
				'generate'      => empty($validity) ? '' : $validity,
			];

		}

		return $data;
	}


	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  Array $item Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default($item, $column_name)
	{

		switch ($column_name) {
			case 'ID':
			case 'product':
			case 'variation':
			case 'prefix':
			case 'chunks_number':
			case 'chunks_length':
			case 'suffix':
			case 'instance':
			case 'validity':
			case 'generate':
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

	public function get_bulk_actions()
	{
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
	function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);
	}

	function column_serial_numbers($item)
	{
		$actions = array(
			'edit'   => sprintf('<a href="?page=%s&row_action=%s&serial_number=%s">Edit</a>', $_REQUEST['page'], 'edit', $item['ID']),
			'delete' => sprintf('<a href="?page=%s&row_action=%s&serial_number=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['ID']),
		);

		return sprintf('%1$s %2$s', $item['serial_numbers'], $this->row_actions($actions));
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data($a, $b)
	{
		// Set defaults
		$orderby = 'product';
		$order   = 'asc';

		// If orderby is set, use this as the sort column
		if (!empty($_GET['orderby'])) {
			$orderby = $_GET['orderby'];
		}
		// If order is set use this as the order
		if (!empty($_GET['order'])) {
			$order = $_GET['order'];
		}

		$result = strcmp($a[$orderby], $b[$orderby]);
		if ($order === 'asc') {
			return $result;
		}

		return -$result;
	}


}
