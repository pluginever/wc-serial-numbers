<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin_Menu {
	/**
	 * AdminMenu constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Adds page to admin menu
	 */
	function admin_menu() {
		add_menu_page( __( 'Serial Numbers', 'wc-serial-numbers' ), __( 'Serial Numbers', 'wc-serial-numbers' ), 'manage_woocommerce', 'wc-serial-numbers', array( $this, 'serial_numbers_page'  ), 'dashicons-admin-network', '55.9' );
		add_submenu_page( 'wc-serial-numbers', __( 'Serial Numbers', 'wc-serial-numbers' ), __( 'Serial Numbers', 'wc-serial-numbers' ), 'manage_woocommerce', 'wc-serial-numbers', array( $this, 'serial_numbers_page' ) );
		add_submenu_page( 'wc-serial-numbers', __( 'Activations', 'wc-serial-numbers' ), __( 'Activations', 'wc-serial-numbers' ), 'manage_woocommerce', 'wcsn-activations', array( $this, 'activations_page' ) );
	}

	public function serial_numbers_page() {
		wc_serial_numbers_get_views('serial-number-page.php');
	}

	public function activations_page() {
		wc_serial_numbers_get_views('activations-page.php');
	}
}

new WC_Serial_Numbers_Admin_Menu();
