<?php

namespace Pluginever\WCSerialNumberPro\Admin;
class Admin_Menu{

	/**
	 * Admin_Menu constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	function __construct()
	{
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	function admin_menu(){
		add_submenu_page( 'serial-numbers', __( 'Add generator rule', 'wc-serial-number-pro' ), __( 'Add generator rule', 'wc-serial-number-pro' ), 'manage_woocommerce', 'add-generator-rule', array(
			$this, 'add_generator_rule' ) );
	}

	/**
	 * Get the Add generator rule template
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */

	function add_generator_rule(){
		wsn_get_template_part('add-generator-rule', true);
	}




}
