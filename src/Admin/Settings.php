<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Lib;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Settings extends Lib\Settings {

	/**
	 * Get settings tabs.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_tabs() {
		$tabs = array(
			'general' => __( 'General', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_settings_tabs', $tabs );
	}

	/**
	 * Get settings.
	 *
	 * @param string $tab Current tab.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings( $tab ) {
		$settings = array();

		switch ( $tab ) {
			case 'general':
				$settings = array(
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
				break;
		}
		/**
		 * Filter the settings for the plugin.
		 *
		 * @param array $settings The settings.
		 *
		 * @deprecated 1.4.1
		 */
		$settings = apply_filters( 'wc_serial_numbers_' . $tab . '_settings_fields', $settings );

		return apply_filters( 'wc_serial_numbers_get_settings_' . $tab, $settings );
	}

	/**
	 * Output premium widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_premium_widget() {
		if ( WCSN()->is_premium_active() ) {
			return;
		}
		$features = array(
			__( 'Create and assign keys for WooCommerce variable products.', 'wc-serial-numbers' ),
			__( 'Generate bulk keys with your custom key generator rule.', 'wc-serial-numbers' ),
			__( 'Random & sequential key order for the generator rules.', 'wc-serial-numbers' ),
			__( 'Automatic key generator to auto-create & assign keys with orders.', 'wc-serial-numbers' ),
			__( 'License key management option from the order page with required actions.', 'wc-serial-numbers' ),
			__( 'Support for bulk import/export of keys from/to CSV.', 'wc-serial-numbers' ),
			__( 'Send keys via SMS with Twilio.', 'wc-serial-numbers' ),
			__( 'Option to sell keys even if there are no available keys in the stock.', 'wc-serial-numbers' ),
			__( 'Custom deliverable quantity to deliver multiple keys with a single product.', 'wc-serial-numbers' ),
			__( 'Manual delivery option to manually deliver license keys instead of automatic.', 'wc-serial-numbers' ),
			__( 'Email template to easily and quickly customize the order confirmation & low stock alert email.', 'wc-serial-numbers' ),
			__( 'Many more ...', 'wc-serial-numbers' ),
		);
		?>
		<div class="pev-panel promo-panel">
			<h3><?php esc_html_e( 'Want More?', 'wc-serial-numbers' ); ?></h3>
			<p><?php esc_attr_e( 'This plugin offers a premium version which comes with the following features:', 'wc-serial-numbers' ); ?></p>
			<ul>
				<?php foreach ( $features as $feature ) : ?>
					<li>- <?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
			<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=plugin-settings&utm_medium=banner&utm_campaign=upgrade&utm_id=wc-serial-numbers" class="button" target="_blank"><?php esc_html_e( 'Upgrade to PRO', 'wc-serial-numbers' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Output tabs.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_tabs( $tabs ) {
		parent::output_tabs( $tabs );
		if ( WCSN()->get_docs_url() ) {
			printf( '<a href="%s" class="nav-tab" target="_blank">%s</a>', esc_url( WCSN()->get_docs_url() ), esc_html__( 'Documentation', 'wc-serial-numbers' ) );
		}
	}
}
