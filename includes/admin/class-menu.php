<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Serial_Numbers_Menu {

	/**
	 * WC_Serial_Numbers_Menu constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Adds page to admin menu
	 */
	function admin_menu() {
		add_menu_page( __( 'WC Serial Numbers', 'wc-serial-numbers' ), __( 'Serial Numbers', 'wc-serial-numbers' ), 'manage_woocommerce', 'wc-serial-numbers', array( $this, 'serial_numbers_page' ), 'dashicons-admin-network' );
		add_submenu_page( 'wc-serial-numbers', __( 'Serial Numbers', 'wc-serial-numbers' ), __( 'Serial Numbers', 'wc-serial-numbers' ), 'manage_woocommerce', 'wc-serial-numbers', array( $this, 'serial_numbers_page' ) );
		//add_submenu_page( 'wc-serial-numbers', __( 'Generated Rules', 'wc-serial-numbers' ), __( 'Generated Rules', 'wc-serial-numbers' ), 'manage_woocommerce', 'wcsn-generated-rules', array( $this, 'generated_rules_page' ) );
	}

	/**
	 *
	 *
	 * @since 1.0.0
	 */
	public function serial_numbers_page() {
		if ( ! empty( $_GET['action_type'] ) && 'add_serial_number' == $_GET['action_type'] ) {
			include( dirname( __FILE__ ) . '/views/html-add-serial-number.php' );
		} else {
			include( dirname( __FILE__ ) . '/views/html-view-serial-numbers.php' );
		}
	}

}

new WC_Serial_Numbers_Menu();
