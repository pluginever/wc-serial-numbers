<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin;

use PluginEver\WooCommerceSerialNumbers\Framework;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Settings page object data class.
 *
 * @since 1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Settings_Page extends Framework\Settings_Page {
	/**
	 * Hook Prefix.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	const HOOK_PREFIX = 'wc_serial_numbers';

	/**
	 * Get settings tabs.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public static function get_tabs() {
		return \apply_filters(
			static::HOOK_PREFIX . '_settings_tabs_array',
			array(
				'general'  => esc_html__( 'General', 'wc-serial-numbers' ),
				'advanced' => esc_html__( 'Advanced', 'wc-serial-numbers' ),
			)
		);
	}

	/**
	 * Get general settings.
	 *
	 * @param string $section_id Section ID.
	 *
	 * @since 1.3.1
	 * @return array
	 */
	public static function get_settings_for_general_tab( $section_id ) {
		// if ( '' !== $section_id ) {
		// return [];
		// }

		$settings = [
			[
				'title' => __( 'General settings', 'wc-serial-numbers' ),
				'type'  => 'title',
				'desc'  => __( 'The following options affect how the serial numbers will work.', 'wc-serial-numbers' ),
				'id'    => 'section_serial_numbers',
			],
			[
				'title'         => __( 'Auto-complete order', 'wc-serial-numbers' ),
				'id'            => 'wc_serial_numbers_autocomplete_order',
				'desc'          => __( 'Automatically completes orders after successfully payments.', 'wc-serial-numbers' ),
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'default'       => 'no',
			],
			[
				'title'         => __( 'Reuse serial numbers', 'wc-serial-numbers' ),
				'id'            => 'wc_serial_numbers_reuse_serial_number',
				'desc'          => __( 'Serial numbers will be reused from the following selected order status.', 'wc-serial-numbers' ),
				'checkboxgroup' => 'start',
				'type'          => 'checkbox',
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
				'title'    => __( 'Stock threshold', 'wc-serial-numbers' ),
				'id'       => 'wc_serial_numbers_stock_threshold',
				'desc'     => __( 'When stock goes below the above number, it will send notification email.', 'wc-serial-numbers' ),
				'type'     => 'number',
				'default'  => '5',
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Notification recipient email', 'wc-serial-numbers' ),
				'id'       => 'wc_serial_numbers_notification_recipient',
				'desc'     => __( 'The email address to be used for sending the email notifications.', 'wc-serial-numbers' ),
				'type'     => 'text',
				'default'  => get_option( 'admin_email' ),
				'desc_tip' => true,
			),
			[
				'type' => 'sectionend',
				'id'   => 'stock_section',
			],

		];

		return $settings;
	}

	/**
	 * Get advanced settings.
	 *
	 * @param string $section_id Section ID.
	 *
	 * @since 1.3.1
	 * @return array
	 */
	public static function get_settings_for_advanced_tab( $section_id ) {
		// if ( '' !== $section_id ) {
		// return [];
		// }

		$settings = [
			[
				'title' => __( 'Advanced settings', 'wc-serial-numbers' ),
				'type'  => 'title',
				'desc'  => __( 'Advanced plugin settings.', 'wc-serial-numbers' ),
				'id'    => 'section_advanced',
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
				'title'   => __( 'Show on my account', 'wc-serial-numbers' ),
				'id'      => 'wc_serial_numbers_show_on_my_account',
				'desc'    => __( 'Show serial numbers on my-account sections', 'wc-serial-numbers' ),
				'default' => 'yes',
				'type'    => 'checkbox',
			],
			[
				'type' => 'sectionend',
				'id'   => 'section_advanced',
			],

		];

		return $settings;
	}

}
