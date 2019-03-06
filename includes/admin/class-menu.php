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
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 );
	}

	/**
	 * Adds page to admin menu
	 */
	function admin_menu() {
		add_menu_page( __( 'WC Serial Numbers', 'wc-serial-numbers' ), __( 'Serial Numbers', 'wc-serial-numbers' ), 'manage_woocommerce', 'wc-serial-numbers', array( $this, 'serial_numbers_page' ), 'dashicons-admin-network' );
		add_submenu_page( 'wc-serial-numbers', __( 'Serial Numbers', 'wc-serial-numbers' ), __( 'Serial Numbers', 'wc-serial-numbers' ), 'manage_woocommerce', 'wc-serial-numbers', array( $this, 'serial_numbers_page' ) );
		add_submenu_page( 'wc-serial-numbers', __( 'API Doc', 'wc-serial-numbers' ), __( 'API Doc', 'wc-serial-numbers' ), 'manage_woocommerce', 'wcsn-api-doc', array( $this, 'api_doc_page' ) );
	}

	/**
	 * add serial make contents
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

	/**
	 * add admin bar menu item
	 *
	 * @since 1.0.0
	 */
	function admin_bar_menu() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu( array(
			'id'    => 'wsn-wc-serial-numbers',
			'title' => __( 'WC Serial Numbers', 'wc-serial-numbers' ) . apply_filters( 'wcsn_admin_bar_notification_label', false ),
			'href'  => admin_url( 'admin.php?page=wc-serial-numbers' ),
			'meta'  => array(
				'html' => apply_filters( 'wcsn_admin_bar_notification_list', '' ),
			),
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wsn-serial-numbers',
			'title'  => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'href'   => admin_url( 'admin.php?page=wc-serial-numbers' ),
			'parent' => 'wsn-wc-serial-numbers',

		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wsn-add-serial-number',
			'title'  => __( 'Add Serial Number', 'wc-serial-numbers' ),
			'href'   => admin_url( 'admin.php?page=wc-serial-numbers&action_type=add_serial_number' ),
			'parent' => 'wsn-wc-serial-numbers',
		) );
//
//		$wp_admin_bar->add_menu( array(
//			'id'     => 'wsn-generate-serial-number',
//			'title'  => __( 'Generate Serial Number', 'wc-serial-numbers' ),
//			'href'   => WPWSN_GENERATE_SERIAL_PAGE,
//			'parent' => 'wsn-wc-serial-numbers',
//		) );
//
//		$wp_admin_bar->add_menu( array(
//			'id'     => 'wsn-settings',
//			'title'  => __( 'Settings', 'wc-serial-numbers' ),
//			'href'   => WPWSN_SETTINGS_PAGE,
//			'parent' => 'wsn-wc-serial-numbers',
//		) );

	}

	public function api_doc_page() {
		include( dirname( __FILE__ ) . '/views/html-api-doc.php' );
	}


}

new WC_Serial_Numbers_Menu();
