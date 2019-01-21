<?php

namespace Pluginever\WCSerialNumbers\Admin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Admin_Menu {
	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 );
	}

	/**
	 * Adds page to admin menu
	 */
	function admin_menu() {


		add_submenu_page( 'woocommerce', __( 'WC Serial Numbers', 'wc-serial-numbers' ), __( 'WC Serial Numbers', 'wc-serial-numbers' ), 'manage_woocommerce', 'wc-serial-numbers', array(
			$this,
			'serial_numbers_page'
		) );
		add_submenu_page( 'serial-numbers', __( 'Add Serial Number', 'wc-serial-numbers' ), __( 'Add Serial Number', 'wc-serial-numbers' ), 'manage_woocommerce', 'add-wc-serial-number', array(
			$this,
			'add_serial_numbers_page'
		) );
		add_submenu_page( 'serial-numbers', __( 'Generate Serial Number', 'wc-serial-numbers' ), __( 'Generate Serial Number', 'wc-serial-numbers' ), 'manage_woocommerce', 'generate-wc-serial-number', array(
			$this,
			'generate_serial_numbers_page'
		) );
	}


	function admin_bar_menu() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu( array(
			'id'    => 'wsn-serial-numbers',
			'title' => __( 'WC Serial Numbers', 'wc-serial-numbers' ) . apply_filters( 'wsn_admin_bar_notification', false ),
			'href'  => WPWSN_SERIAL_INDEX_PAGE,
			'meta'  => array(
				'html' => apply_filters( 'wsn_admin_bar_notification_list', '' ),
			),
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wsn-add-serial-number',
			'title'  => __( 'Add Serial Number', 'wc-serial-numbers' ),
			'href'   => WPWSN_ADD_SERIAL_PAGE,
			'parent' => 'wsn-serial-numbers',

		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wsn-generate-serial-number',
			'title'  => __( 'Generate Serial Number', 'wc-serial-numbers' ),
			'href'   => WPWSN_GENERATE_SERIAL_PAGE,
			'parent' => 'wsn-serial-numbers',
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wsn-settings',
			'title'  => __( 'Settings', 'wc-serial-numbers' ),
			'href'   => WPWSN_SETTINGS_PAGE,
			'parent' => 'wsn-serial-numbers',
		) );

	}


	/*
	 * Display The serial numbers information index table
	 *
	 *@since 1.0.0
	 *
	 * @return html
	 * */
	function serial_numbers_page() {
		wsn_get_template_part( 'serial-numbers-page' );
	}


	/**
	 * Display the add serial number manually page
	 *
	 * @since 1.0.0
	 *
	 * return html
	 */

	function add_serial_numbers_page() {
		wsn_get_template_part( 'add-serial-number-page' );
	}

	/**
	 * Display the generate serial number page
	 *
	 * @since 1.0.0
	 *
	 * return html
	 */

	function generate_serial_numbers_page() {
		wsn_get_template_part( 'generate-serial-number' );
	}

}
