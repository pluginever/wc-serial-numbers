<?php

namespace Pluginever\WCSerialNumbers\Admin;
class Settings {
	private $settings_api;

	function __construct() {
		$this->settings_api = new \Ever_Settings_API();
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
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
				'id'    => 'wsn_general_settings',
				'title' => __( 'General Settings', 'wc-serial-numbers' )
			),
			array(
				'id'    => 'wsn_serial_generator_settings',
				'title' => __( 'Serial Numbers Generator', 'wc-serial-numbers' )
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
			'wsn_general_settings' => array(
				array(
					'name'    => 'wsn_rows_per_page',
					'label'   => __( 'Numbers of rows per page', 'wc-serial-numbers' ),
					'desc'    => __( 'Display the serial numbers in the serial table list', 'wc-serial-numbers' ),
					'class'   => 'ever-field-inline',
					'default' => 10,
					'type'    => 'number',
					'min'     => 1,
				),
				array(
					'name'    => 'wsn_allow_checkout',
					'label'   => __( 'Allow to checkout, Even there is no serial number', 'wc-serial-numbers' ),
					'desc'    => __( 'Allow Customers to checkout, Even there is no serial number for a serial activated product', 'wc-serial-numbers' ),
					'default' => 10,
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'wsn_admin_bar_notification',
					'label'   => __( 'Admin bar notification', 'wc-serial-numbers' ),
					'desc'    => __( 'Show addmin bar notification, if there is not enough serial number for any products', 'wc-serial-numbers' ),
					'default' => 10,
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
			),
			'wsn_serial_generator_settings' => array(
				array(
					'name'    => 'wsn_generator_prefix',
					'label'   => __( 'Prefix', 'wc-serial-numbers' ),
					'placeholder'    => __( 'sl-', 'wc-serial-numbers' ),
					'desc'    => __( 'Prefix to added before the serial number. <strong>ex: <em>sl-xxxx-xxxx-xxxx-xxxx</em></strong>', 'wc-serial-numbers' ),
					'class'   => 'ever-field-inline',
					'default' => '',
					'type'    => 'text',
				),
				array(
					'name'    => 'wsn_generator_chunks_number',
					'label'   => __( 'Chunks Number', 'wc-serial-numbers' ),
					'placeholder'    => __( '4', 'wc-serial-numbers' ),
					'desc'    => __( 'The number of chunks for the serial number. <strong>ex: <em>xxxx-xxxx-xxxx-xxxx</em></strong>', 'wc-serial-numbers' ),
					'class'   => 'ever-field-inline',
					'default' => 4,
					'type'    => 'number',
				),
				array(
					'name'    => 'wsn_generator_chunks_length',
					'label'   => __( 'Chunks Length', 'wc-serial-numbers' ),
					'placeholder'    => __( '4', 'wc-serial-numbers' ),
					'desc'    => __( 'The number of chunks length for the serial number. <strong>ex: <em>xxxx-xxxx-xxxx-xxxx</em></strong>', 'wc-serial-numbers' ),
					'class'   => 'ever-field-inline',
					'default' => 4,
					'type'    => 'number',
				),
				array(
					'name'    => 'wsn_generator_suffix',
					'label'   => __( 'Suffix', 'wc-serial-numbers' ),
					'placeholder'    => __( 'suffix-', 'wc-serial-numbers' ),
					'desc'    => __( 'Suffix to added after the serial number. <strong>ex: <em>suffix-xxxx-xxxx-xxxx-xxxx</em></strong>', 'wc-serial-numbers' ),
					'class'   => 'ever-field-inline',
					'default' => '',
					'type'    => 'text',
				),

				array(
					'name'    => 'wsn_generator_instance',
					'label'   => __( 'Instance Number', 'wc-serial-numbers' ),
					'desc'    => __( 'Maximum instance for the serial number.', 'wc-serial-numbers' ),
					'class'   => 'ever-field-inline',
					'default' => 1,
					'type'    => 'number',
				),
				array(
					'name'    => 'wsn_generator_validity',
					'label'   => __( 'Validity', 'wc-serial-numbers' ),
					'desc'    => __( 'Validity days for the serial number. Keep it 0, if the serial number doesn\'t expire', 'wc-serial-numbers' ),
					'class'   => 'ever-field-inline',
					'default' => 0,
					'type'    => 'number',
				),
			),
		);

		return apply_filters( 'wc_serial_numbers_settings_fields', $settings_fields );
	}

	function admin_menu() {
		add_submenu_page( 'serial-numbers', 'WC Serial Numbers Settings', 'WC Serial Numbers Settings', 'manage_options', 'wc_serial_numbers-settings', array(
			$this,
			'settings_page'
		) );
	}

	function settings_page() {
		?><?php
		echo '<div class="wrap">';
		echo sprintf( "<h2>%s</h2>", __( 'WC Serial Numbers Settings', 'wc-serial-numbers' ) );
		$this->settings_api->show_settings();
		echo '</div>';
	}

	/**
	 * Get all the pages
	 *
	 * @return array page names with key value pairs
	 */
	function get_pages() {
		$pages         = get_pages();
		$pages_options = array();
		if ( $pages ) {
			foreach ( $pages as $page ) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}

		return $pages_options;
	}
}
