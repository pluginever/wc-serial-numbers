<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Serial_Numbers_Settings_General' ) ) :
	/**
	 * WC_Serial_Numbers_Settings_General
	 */
	class WC_Serial_Numbers_Settings_General extends WC_Settings_Page {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'general';
			$this->label = __( 'General', 'wc-serial-numbers' );

			add_filter( 'wc_serial_numbers_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'wc_serial_numbers_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'wc_serial_numbers_settings_save_' . $this->id, array( $this, 'save' ) );
		}

		/**
		 * Get settings array
		 *
		 * @return array
		 */
		public function get_settings() {
			global $woocommerce, $wp_roles;
			$settings = array(
				[
					'title' => __( 'Serial Number Settings.', 'wc-serial-numbers' ),
					'type'  => 'title',
					'desc'  => __( 'The following options affects how the serial numbers will work.', 'wc-serial-numbers' ),
					'id'    => 'section_serial_numbers'
				],
				[
					'title'   => __( 'Auto Complete Order', 'wc-serial-numbers' ),
					'id'      => 'wc_serial_numbers_autocomplete_order',
					'desc'    => __( 'This will automatically complete an order after successfull payment.', 'wc-serial-numbers' ),
					'type'    => 'checkbox',
					'default' => 'no',
				],
				[
					'title' => __( 'Reuse Serial Number', 'wc-serial-numbers' ),
					'id'    => 'wc_serial_numbers_reuse_serial_number',
					'desc'  => __( 'This will recover failed, refunded serial number for selling again.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				],
				[
					'title'           => __( 'Revoke statuses', 'wc-serial-numbers' ),
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
					'title'   => __( 'Hide Serial Number', 'wc-serial-numbers' ),
					'id'      => 'wc_serial_numbers_hide_serial_number',
					'desc'    => __( 'All serial numbers will be hidden and only displayed when the "Show" button is clicked.', 'wc-serial-numbers' ),
					'default' => 'yes',
					'type'    => 'checkbox',
				],
				[
					'title'   => __( 'Disable Software Support', 'wc-serial-numbers' ),
					'id'      => 'wc_serial_numbers_disable_software_support',
					'desc'    => __( 'This will disable Software Licensing support & API functionalities..', 'wc-serial-numbers' ),
					'default' => 'yes',
					'type'    => 'checkbox',
				],
				[
					'type' => 'sectionend',
					'id'   => 'section_serial_numbers'
				],
				[
					'title' => __( 'Stock notification.', 'wc-serial-numbers' ),
					'type'  => 'title',
					'desc'  => __( 'The following options affects how stock notification will work.', 'wc-serial-numbers' ),
					'id'    => 'stock_section'
				],
				[
					'title'             => __( 'Stock Notification Email', 'wc-serial-numbers' ),
					'id'                => 'wc_serial_numbers_enable_stock_notification',
					'desc'              => __( 'This will send you notification email when product stock is low.', 'wc-serial-numbers' ),
					'type'              => 'checkbox',
					'sanitize_callback' => 'intval',
				],
				array(
					'title'   => __( 'Stock Threshold', 'wc-serial-numbers' ),
					'id'    => 'wc_serial_numbers_stock_threshold',
					'desc'    => __( 'When stock goes below the above number, it will send notification email.', 'wc-serial-numbers' ),
					'type'    => 'number',
					'default' => '5',
				),
				array(
					'title'   => __( 'Notification Recipient Email', 'wc-serial-numbers' ),
					'id'    => 'wc_serial_numbers_notification_recipient',
					'desc'    => __( 'The email address to be used for sending the email notification.', 'wc-serial-numbers' ),
					'type'    => 'text',
					'default' => get_option( 'admin_email' ),
				),
				[
					'type' => 'sectionend',
					'id'   => 'stock_section'
				],
			);

			return apply_filters( 'wc_serial_numbers_general_settings_fields', $settings );
		}

		/**
		 * Save settings
		 */
		public function save() {
			$settings = $this->get_settings();
			WC_Serial_Numbers_Admin_Settings::save_fields( $settings );
		}
	}

endif;

return new WC_Serial_Numbers_Settings_General();
