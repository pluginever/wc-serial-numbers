<?php

namespace Pluginever\WCSerialNumbers\Admin;
class Admin_Menu {
	/**
	 * Constructor
	 */
	function __construct() {
		add_action('admin_menu', array($this, 'admin_menu'));

		add_action('admin_bar_menu', array($this, 'admin_bar_menu'), 999);
	}

	/**
	 * Adds page to admin menu
	 */
	function admin_menu() {


		add_submenu_page('woocommerce', __('WC Serial Numbers', 'wc-serial-numbers'), __('Serial Numbers', 'wc-serial-numbers'), 'manage_woocommerce', 'serial-numbers', array(
			$this, 'serial_numbers_page'
		));
		add_submenu_page('serial-numbers', __('Add Serial Number', 'wc-serial-numbers'), __('Add Serial Number', 'wc-serial-numbers'), 'manage_woocommerce', 'add-serial-number', array(
			$this, 'add_serial_numbers_page'
		));
		add_submenu_page('serial-numbers', __('Generate Serial Number', 'wc-serial-numbers'), __('Generate Serial Number', 'wc-serial-numbers'), 'manage_woocommerce', 'generate-serial-number', array(
			$this, 'generate_serial_numbers_page'
		));
	}


	function admin_bar_menu() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu(array(
			'id'    => 'serial-numbers',
			'title' => __('Serial Numbers', 'wc-serial-numbers'),
			'href'  => WPWSN_SERIAL_INDEX_PAGE,
		));

		$wp_admin_bar->add_menu(array(
			'id'     => 'add-serial-number',
			'title'  => __('Add Serial Number', 'wc-serial-numbers'),
			'href'   => WPWSN_ADD_SERIAL_PAGE,
			'parent' => 'serial-numbers',
		));

		$wp_admin_bar->add_menu(array(
			'id'     => 'generate-serial-number',
			'title'  => __('Generate Serial Number', 'wc-serial-numbers'),
			'href'   => WPWSN_GENERATE_SERIAL_PAGE,
			'parent' => 'serial-numbers',
		));

	}


	/*
	 * Display The serial numbers information index table
	 *
	 *@since 1.0.0
	 *
	 * @return html
	 * */
	function serial_numbers_page() {
		wsn_get_template_part('serial-numbers-page');
	}


	/**
	 * Display the add serial number manually page
	 *
	 * @since 1.0.0
	 *
	 * return html
	 */

	function add_serial_numbers_page() {
		wsn_get_template_part('add-serial-number-page');
	}

	/**
	 * Display the generate serial number page
	 *
	 * @since 1.0.0
	 *
	 * return html
	 */

	function generate_serial_numbers_page() {
		wsn_get_template_part('generate-serial-number');
	}

}
