<?php

namespace Pluginever\WCSerialNumbers\Admin;
class Admin_Menu{
	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Adds page to admin menu
	 */
	function admin_menu() {
		add_submenu_page( 'woocommerce', __( 'WC Serial Numbers', 'wc-serial-numbers' ), __( 'Serial Numbers', 'wc-serial-numbers' ), 'manage_woocommerce', 'serial-numbers', array(
			$this, 'serial_numbers_page' ) );
		add_submenu_page( 'serial-numbers', __( 'Add Serial Number', 'wc-serial-numbers' ), __( 'Add Serial Number', 'wc-serial-numbers' ), 'manage_woocommerce', 'add-serial-number', array(
			$this, 'generate_serial_numbers_page' ) );
	}

	/*
	 * Display The serial numbers information
	 * */

	function serial_numbers_page(){
		wsn_get_template_part('serial-numbers-page');
	}

	function generate_serial_numbers_page(){
		wsn_get_template_part('add-serial-number');
	}

}
