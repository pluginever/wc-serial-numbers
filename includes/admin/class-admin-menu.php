<?php
defined( 'ABSPATH' ) || exit();

class WCSN_Admin_Menu {
	/**
	 * AdminMenu constructor.
	*/
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 999 );
	}

	/**
	 * Adds page to admin menu
	*/
	function admin_menu() {
		add_menu_page( __( 'Serial Numbers', 'wc-serial-numbers' ), __( 'Serial Numbers', 'wc-serial-numbers' ), 'manage_woocommerce', 'wc-serial-numbers', array( $this, 'serial_numbers_page'  ), 'dashicons-admin-network', '55.9' );
		add_submenu_page( 'wc-serial-numbers', __( 'Serial Numbers', 'wc-serial-numbers' ), __( 'Serial Numbers', 'wc-serial-numbers' ), 'manage_woocommerce', 'wc-serial-numbers', array( $this, 'serial_numbers_page' ) );
		if ( ! wcsn_software_disabled() ) {
			add_submenu_page( 'wc-serial-numbers', __( 'Activations', 'wc-serial-numbers' ), __( 'Activations', 'wc-serial-numbers' ), 'manage_woocommerce', 'wc-serial-numbers-activations', array(
				$this,
				'activations_page'
			) );
		}
	}
	public function serial_numbers_page() {
		wcsn_get_views('serial-number-page.php');
	}

	public function activations_page() {
		wcsn_get_views('activations-page.php');
	}

	/**
	 * add admin bar menu item
	 *
	 * @since 1.0.0
	*/
	function admin_bar_menu() {
		global $wp_admin_bar;
		$title = __( 'WC Serial Numbers', 'wc-serial-numbers' );
		if('on' == wcsn_get_settings('low_stock_alert') && current_user_can('manage_woocommerce')){
			$title .= '<span class="wsn_admin_bar_notification"></span>';
		}

		$wp_admin_bar->add_menu( array(
			'id'    => 'wc-serial-numbers',
			'title' => $title,
			'href'  => admin_url( 'admin.php?page=wc-serial-numbers' ),
			'meta'  => array(
				'html' => $this->get_low_stock_list(),
			),
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wsn-serial-numbers',
			'title'  => __( 'Serial Numbers', 'wc-serial-numbers' ),
			'href'   => admin_url( 'admin.php?page=wc-serial-numbers' ),
			'parent' => 'wc-serial-numbers',

		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wsn-add-serial-number',
			'title'  => __( 'Add Serial Number', 'wc-serial-numbers' ),
			'href'   => admin_url( 'admin.php?page=wc-serial-numbers&serial_numbers_action=add_serial_number' ),
			'parent' => 'wc-serial-numbers',
		) );

	}

	public function get_low_stock_list(){

		if('on' !== wcsn_get_settings('low_stock_alert')){
			return '';
		}


		$low_stock_products = wcsn_get_low_stocked_products();
		if(empty($low_stock_products)){
			return '';
		}
		
		ob_start();
		wcsn_get_views('notification-list.php', compact('low_stock_products'));
		$html = ob_get_contents();
		ob_get_clean();
		return $html;
	}


}

new WCSN_Admin_Menu();