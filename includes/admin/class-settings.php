<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Framework;

defined( 'ABSPATH' ) || exit();

/**
 * Settings class.
 *
 * @since 1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Settings extends Framework\Settings {
	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 55 );
		add_action( 'wc_serial_numbers_settings_tabs', array( $this, 'add_extra_tabs' ), 20 );
		add_action( 'wc_serial_numbers_activated', array( $this, 'save_defaults' ) );
		add_action( 'wc_serial_numbers_settings_sidebar', array( $this, 'output_upgrade_widget' ) );
		add_action( 'wc_serial_numbers_settings_sidebar', array( $this, 'output_about_widget' ) );
		add_action( 'wc_serial_numbers_settings_sidebar', array( $this, 'output_help_widget' ) );
		add_action( 'wc_serial_numbers_settings_sidebar', array( $this, 'output_recommended_widget' ) );
	}

	/**
	 * Admin menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		$load = add_submenu_page(
			'wc-serial-numbers',
			__( 'Settings', 'wc-serial-numbers' ),
			__( 'Settings', 'wc-serial-numbers' ),
			'manage_options',
			'wc-serial-numbers-settings',
			array( $this, 'output' )
		);
		add_action( 'load-' . $load, array( $this, 'save_settings' ) );
	}

	/**
	 * Add extra tabs.
	 *
	 * @since 1.0.0
	 */
	public function add_extra_tabs() {
		if ( $this->get_plugin()->get_doc_url() ) {
			echo '<a href="' . esc_url( $this->get_plugin()->get_doc_url() ) . '" target="_blank" class="nav-tab">' . esc_html__( 'Documentation', 'wc-serial-numbers' ) . '</a>';
		}
	}

	/**
	 * Get tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings_tabs() {
		$tabs = [
			'general'  => __( 'General', 'wc-serial-numbers' ),
			'advanced' => __( 'Advanced', 'wc-serial-numbers' ),
		];

		return apply_filters( 'wc_serial_numbers_settings_tabs_array', $tabs );
	}

	/**
	 * Get general settings.
	 *
	 * @since 1.0.0
	 * @return array General settings.
	 */
	public function get_general_tab_settings() {
		return array(
			[
				'title' => __( 'General settings', 'wc-serial-numbers' ),
				'type'  => 'title',
				'desc'  => __( 'The following options affect how the serial numbers will work.', 'wc-serial-numbers' ),
				'id'    => 'section_serial_numbers',
			],
			[
				'title'   => __( 'Auto-complete order', 'wc-serial-numbers' ),
				'id'      => 'wc_serial_numbers_autocomplete_order',
				'desc'    => __( 'Automatically completes orders  after successfull payments.', 'wc-serial-numbers' ),
				'type'    => 'checkbox',
				'default' => 'no',
			],
			[
				'title' => __( 'Reuse serial number', 'wc-serial-numbers' ),
				'id'    => 'wc_serial_numbers_reuse_serial_number',
				'desc'  => __( 'Recover failed, refunded serial numbers for selling again.', 'wc-serial-numbers' ),
				'type'  => 'checkbox',
			],
			[
				'title'           => __( 'Revoke status', 'wc-serial-numbers' ),
				'desc'            => __( 'Cancelled', 'wc-serial-numbers' ),
				'id'              => 'wc_serial_numbers_revoke_status_cancelled',
				'default'         => 'yes',
				'type'            => 'checkbox',
				'checkboxgroup'   => 'start',
				'show_if_checked' => 'option',
			],
			[
				'desc'          => __( 'Refunded', 'wc-serial-numbers' ),
				'id'            => 'wc_serial_numbers_revoke_status_refunded',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => '',

			],
			[
				'desc'          => __( 'Failed', 'wc-serial-numbers' ),
				'id'            => 'wc_serial_numbers_revoke_status_failed',
				'default'       => 'yes',
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',

			],
			[
				'title'   => __( 'Hide serial number', 'wc-serial-numbers' ),
				'id'      => 'wc_serial_numbers_hide_serial_number',
				'desc'    => __( 'All serial numbers will be hidden and only displayed when the "Show" button is clicked.', 'wc-serial-numbers' ),
				'default' => 'yes',
				'type'    => 'checkbox',
			],
			[
				'title'   => __( 'Disable software support', 'wc-serial-numbers' ),
				'id'      => 'wc_serial_numbers_disable_software_support',
				'desc'    => __( 'Disable Software Licensing support & API functionalities.', 'wc-serial-numbers' ),
				'default' => 'yes',
				'type'    => 'checkbox',
			],
			[
				'type' => 'sectionend',
				'id'   => 'section_serial_numbers',
			],
			[
				'title' => __( 'Stock notification', 'wc-serial-numbers' ),
				'type'  => 'title',
				'desc'  => __( 'The following options affects how stock notification will work.', 'wc-serial-numbers' ),
				'id'    => 'stock_section',
			],
			[
				'title'             => __( 'Stock notification email', 'wc-serial-numbers' ),
				'id'                => 'wc_serial_numbers_enable_stock_notification',
				'desc'              => __( 'Sends notification emails when product stock is low.', 'wc-serial-numbers' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval',
			],
			array(
				'title'   => __( 'Stock threshold', 'wc-serial-numbers' ),
				'id'      => 'wc_serial_numbers_stock_threshold',
				'desc'    => __( 'When stock goes below the above number, it will send notification email.', 'wc-serial-numbers' ),
				'type'    => 'number',
				'default' => '5',
			),
			array(
				'title'   => __( 'Notification recipient email', 'wc-serial-numbers' ),
				'id'      => 'wc_serial_numbers_notification_recipient',
				'desc'    => __( 'The email address to be used for sending the email notifications.', 'wc-serial-numbers' ),
				'type'    => 'text',
				'default' => get_option( 'admin_email' ),
			),
			[
				'type' => 'sectionend',
				'id'   => 'stock_section',
			],
		);
	}
}
