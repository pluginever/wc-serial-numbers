<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Settings {

	private $settings_api;

	function __construct() {

		$this->settings_api = new WC_Serial_Numbers_Settings_API();
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 99 );

	}

	function admin_init() {

		//set the settings
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		//initialize settings
		$this->settings_api->admin_init();
	}

	function get_settings_sections() {

		$sections = array(
			array(
				'id'    => 'wc_serial_numbers_settings',
				'title' => __( 'WC Serial Numbers Settings', 'wc-serial-numbers' )
			),
		);

		return apply_filters( 'wc_serial_numbers_settings_sections', $sections );
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */

	function get_settings_fields() {

		$settings_fields = array(

			'wc_serial_numbers_settings' => array(
				array(
					'name'    => 'enable_api',
					'label'   => __( 'Disable API', 'wc-serial-numbers' ),
					'desc'    => __( 'Will eliminate all features related to API', 'wc-serial-numbers' ),
					'default' => 'on',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'disable_automatic_delivery',
					'label'   => __( 'Disable Automatic delivery', 'wc-serial-numbers' ),
					'desc'    => __( 'Disable automatically sending serial numbers when an order is set to Complete', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'reuse_serial_numbers',
					'label'   => __( 'Reuse Serial Numbers', 'wc-serial-numbers' ),
					'desc'    => __( 'If an order is cancelled serials will be reused', 'wc-serial-numbers' ),
					'default' => 'on',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'hide_serial_number',
					'label'   => __( 'Hide serial key', 'wc-serial-numbers' ),
					'desc'    => __( 'Hide serial key in admin dashboard table', 'wc-serial-numbers' ),
					'default' => 'on',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'auto_complete',
					'label'   => __( 'Autocomplete Order', 'wc-serial-numbers' ),
					'desc'    => __( 'Order will be automatically complete', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'low_stock_notification',
					'label'   => __( 'Low Stock Notification', 'wc-serial-numbers' ),
					'desc'    => __( 'Enable/disable low stock notification ', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'low_stock_threshold',
					'label'   => __( 'Low Stock Threshold', 'wc-serial-numbers' ),
					'desc'    => __( 'Below the above number will trigger low stock email notification', 'wc-serial-numbers' ),
					'default' => '5',
					'class'   => 'ever-field-inline',
					'type'    => 'number',
				),
				array(
					'name'     => 'low_stock_notification_email',
					'label'    => __( 'Low Stock Email', 'wc-serial-numbers' ),
					'desc'     => __( 'The email address to be used for sending the low stock email notification', 'wc-serial-numbers' ),
					'default'  => get_option( 'admin_email' ),
					'class'    => 'ever-field-inline',
					'type'     => 'text',
					'sanitize' => 'sanitize_email',
				),

			),
		);

		return apply_filters( 'wc_serial_numbers_settings_fields', $settings_fields );
	}

	function admin_menu() {
		add_submenu_page( 'wc-serial-numbers', 'WC Serial Numbers Settings', 'Settings', 'manage_woocommerce', 'wc-serial-numbers-settings', array(
			$this,
			'settings_page'
		) );
	}

	function settings_page() {

		echo '<div class="wrap">';
		$this->settings_api->show_settings();
		echo '</div>';

	}

}

new WC_Serial_Numbers_Settings();
