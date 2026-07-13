<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\B8\SettingsUI;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Settings extends SettingsUI {

	/**
	 * Capability required to manage the settings.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $capability = 'manage_woocommerce';

	/**
	 * Get the settings instance.
	 *
	 * @since 1.0.0
	 * @return static
	 */
	public static function instance() {
		return WCSN()->make( static::class );
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		$this->app->on_filter( 'settings', array( $this, 'register_settings' ) );
		$this->app->on_filter( 'settings_wrap_classes', array( $this, 'wrap_classes' ) );
		$this->app->on_filter( 'settings_nav_extras', array( $this, 'nav_extras' ) );
	}

	/**
	 * Add the WooCommerce class to the settings page wrapper.
	 *
	 * @since 1.0.0
	 * @param array<int, string> $classes Wrapper class names.
	 * @return array<int, string>
	 */
	public function wrap_classes( array $classes ): array {
		$classes[] = 'woocommerce';

		return $classes;
	}

	/**
	 * Add the documentation link to the settings navigation.
	 *
	 * @since 1.0.0
	 * @param string $extras Extra navigation markup.
	 * @return void
	 */
	public function nav_extras( $extras ) {
		if ( $this->app->docs_url ) {
			printf(
				'<a href="%s" class="nav-tab" target="_blank">%s</a>',
				esc_url( (string) $this->app->docs_url ),
				esc_html__( 'Documentation', 'wc-serial-numbers' )
			);
		}
	}

	/**
	 * Register the plugin settings.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Settings definition keyed by tab.
	 * @return array<string, mixed>
	 */
	public function register_settings( array $settings ): array {
		$tabs = apply_filters(
			'wc_serial_numbers_settings_tabs',
			array(
				'general' => __( 'General', 'wc-serial-numbers' ),
			)
		);

		foreach ( $tabs as $tab => $title ) {
			$fields = 'general' === $tab ? $this->get_general_settings() : array();

			/**
			 * Filter the settings for the plugin.
			 *
			 * @param array $fields The settings.
			 *
			 * @deprecated 1.4.1
			 */
			$fields = apply_filters( 'wc_serial_numbers_' . $tab . '_settings_fields', $fields );

			$settings[ $tab ] = array(
				'title'  => $title,
				'fields' => apply_filters( 'wc_serial_numbers_get_settings_' . $tab, $fields ),
			);
		}

		return $settings;
	}

	/**
	 * Get general settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_general_settings() {
		return array(
			array(
				'title' => __( 'General Settings', 'wc-serial-numbers' ),
				'type'  => 'title',
				'desc'  => __( 'These options determine the behavior and operation of the plugin.', 'wc-serial-numbers' ),
				'id'    => 'section_serial_numbers',
			),
			array(
				'title'   => __( 'Auto-complete orders', 'wc-serial-numbers' ),
				'id'      => 'wc_serial_numbers_autocomplete_order',
				'desc'    => __( 'Automatically completes orders after successful payments.', 'wc-serial-numbers' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'title'    => __( 'Reuse keys', 'wc-serial-numbers' ),
				'id'       => 'wc_serial_numbers_reuse_serial_number',
				'desc'     => __( 'Recover failed, refunded keys for selling again.', 'wc-serial-numbers' ),
				'desc_tip' => __( 'If you enable this option, the keys will be available for selling again if the order is refunded or failed.', 'wc-serial-numbers' ),
				'type'     => 'checkbox',
				'default'  => 'no',
			),
			// Revoke serial keys.
			array(
				'title'    => __( 'Revoke keys', 'wc-serial-numbers' ),
				'id'       => 'wc_serial_numbers_revoke_keys',
				'desc'     => __( 'Revoke keys when the order status changes to cancelled or refunded.', 'wc-serial-numbers' ),
				'desc_tip' => __( 'If you enable this option, the keys will be revoked when the order status changes to cancelled or refunded.', 'wc-serial-numbers' ),
				'type'     => 'checkbox',
				'default'  => 'no',
			),
			array(
				'title'   => __( 'Hide keys', 'wc-serial-numbers' ),
				'id'      => 'wc_serial_numbers_hide_serial_number',
				'desc'    => __( 'Keys will be masked in the list table.', 'wc-serial-numbers' ),
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'    => __( 'Disable software support', 'wc-serial-numbers' ),
				'id'       => 'wc_serial_numbers_disable_software_support',
				'desc'     => __( 'Disable Software Licensing support & API functionalities.', 'wc-serial-numbers' ),
				'desc_tip' => __( 'If you enable this option, the activation menu and it’s functionality will be turned off.', 'wc-serial-numbers' ),
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			// Enable managing stocks for the key enabled products.
			array(
				'title'    => __( 'Manage Stocks', 'wc-serial-numbers' ),
				'id'       => 'wcsn_manage_stocks',
				'desc'     => __( 'Manage stocks for the key enabled products.', 'wc-serial-numbers' ),
				'desc_tip' => __( 'Enable stock management for key-enabled products. This works only if you select "Manually Added" as the key source and enable stock management for the product. Variable product is not supported.', 'wc-serial-numbers' ),
				'type'     => 'checkbox',
				'default'  => 'no',
			),
			// Enable pdf invoice compatibility.
			array(
				'title'    => __( 'WooCommerce PDF Invoices', 'wc-serial-numbers' ),
				'id'       => 'wcsn_enable_pdf_invoices',
				'desc'     => __( 'Enable WooCommerce PDF Invoices.', 'wc-serial-numbers' ),
				'desc_tip' => sprintf(
					/* translators: %s: documentation link */
					__( 'If you enable this option, the plugin will be compatible with WooCommerce PDF Invoices & Packing Slips plugins and will show the serial keys in the invoice. Check out the <a href="%s" target="_blank">documentation</a> for more details.', 'wc-serial-numbers' ),
					'https://pluginever.com/docs/wc-serial-numbers/woocommerce-pdf-invoices/'
				),
				'type'     => 'checkbox',
				'default'  => 'no',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'section_serial_numbers',
			),
			array(
				'title' => __( 'Stock Notification', 'wc-serial-numbers' ),
				'type'  => 'title',
				'desc'  => __( 'These options determine the operation of the key\'s stock notification.', 'wc-serial-numbers' ),
				'id'    => 'stock_section',
			),
			array(
				'title'             => __( 'Stock notification email', 'wc-serial-numbers' ),
				'id'                => 'wc_serial_numbers_enable_stock_notification',
				'desc'              => __( 'Sends notification emails when key stock is low.', 'wc-serial-numbers' ),
				'type'              => 'checkbox',
				'sanitize_callback' => 'intval',
				'default'           => 'yes',
			),
			array(
				'title'   => __( 'Stock threshold', 'wc-serial-numbers' ),
				'id'      => 'wc_serial_numbers_stock_threshold',
				'desc'    => __( 'An email notification will be sent when the key stock falls below the specified number.', 'wc-serial-numbers' ),
				'type'    => 'number',
				'default' => '5',
			),
			array(
				'title'   => __( 'Notification recipient email', 'wc-serial-numbers' ),
				'id'      => 'wc_serial_numbers_notification_recipient',
				'desc'    => __( 'The email address which will be used to send email notifications.', 'wc-serial-numbers' ),
				'type'    => 'text',
				'default' => get_option( 'admin_email' ),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'stock_section',
			),
		);
	}

	/**
	 * Save the default value of the registered settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_defaults() {
		foreach ( $this->app->settings->get_settings() as $group ) {
			$fields = isset( $group['fields'] ) ? $group['fields'] : array();
			foreach ( $fields as $field ) {
				if ( ! empty( $field['id'] ) && isset( $field['default'] ) ) {
					add_option( $field['id'], $field['default'] );
				}
			}
		}
	}

	/**
	 * Output the settings fields.
	 *
	 * @since 1.0.0
	 * @param array<int, array<string, mixed>> $fields Prepared field declarations.
	 * @return void
	 */
	protected function render_fields( array $fields ): void {
		if ( function_exists( 'woocommerce_admin_fields' ) ) {
			woocommerce_admin_fields( $fields );
			return;
		}

		parent::render_fields( $fields );
	}

	/**
	 * Persist the submitted settings fields.
	 *
	 * @since 1.0.0
	 * @param array<int, array<string, mixed>> $fields Field declarations for the current tab.
	 * @param array<string, mixed>             $data   Unslashed request data.
	 * @return bool True when the fields were saved.
	 */
	protected function save_fields( array $fields, array $data ): bool {
		if ( ! function_exists( 'woocommerce_update_options' ) ) {
			return false;
		}

		woocommerce_update_options( $fields );

		return true;
	}
}
