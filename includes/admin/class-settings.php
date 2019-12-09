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
					'name'    => 'automatic_delivery',
					'label'   => __( 'Automatic delivery', 'wc-serial-numbers' ),
					'desc'    => __( 'Automatically assign serial numbers with completed order', 'wc-serial-numbers' ),
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
					'name'    => 'allow_duplicate',
					'label'   => __( 'Allow Duplicate', 'wc-serial-numbers' ),
					'desc'    => __( 'will create duplicate serial numbers for each products', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'autocomplete_order',
					'label'   => __( 'Autocomplete Order', 'wc-serial-numbers' ),
					'desc'    => __( 'will automatically complete order upon successful payment', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'disable_software',
					'label'   => __( 'Disable Software', 'wc-serial-numbers' ),
					'desc'    => __( 'will disable all the features related to software API', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				)
			),

			'wsn_serial_generator_settings' => array(

				array(
					'name'        => 'wsn_generator_prefix',
					'label'       => __( 'Prefix', 'wc-serial-numbers' ),
					'placeholder' => __( 'sl-', 'wc-serial-numbers' ),
					'desc'        => __( 'Prefix to added before the serial number.', 'wc-serial-numbers' ) . '<br><strong>ex: <em>sl-xxxx-xxxx-xxxx-xxxx</em></strong>',
					'class'       => 'ever-field-inline',
					'default'     => '',
					'type'        => 'text',
				),

				array(
					'name'        => 'wsn_generator_chunks_number',
					'label'       => __( 'Chunks Number', 'wc-serial-numbers' ),
					'placeholder' => __( '4', 'wc-serial-numbers' ),
					'desc'        => __( 'The number of chunks for the serial number.', 'wc-serial-numbers' ) . '<br><strong>ex: <em>xxxx-xxxx-xxxx-xxxx</em></strong>',
					'class'       => 'ever-field-inline',
					'default'     => 4,
					'type'        => 'number',
				),

				array(
					'name'        => 'wsn_generator_chunks_length',
					'label'       => __( 'Chunks Length', 'wc-serial-numbers' ),
					'placeholder' => __( '4', 'wc-serial-numbers' ),
					'desc'        => __( 'The number of chunks length for the serial number.', 'wc-serial-numbers' ) . '<br><strong>ex: <em>xxxx-xxxx-xxxx-xxxx</em></strong>',
					'class'       => 'ever-field-inline',
					'default'     => 4,
					'type'        => 'number',
				),

				array(
					'name'        => 'wsn_generator_suffix',
					'label'       => __( 'Suffix', 'wc-serial-numbers' ),
					'placeholder' => __( '-suffix', 'wc-serial-numbers' ),
					'desc'        => __( 'Suffix to added after the serial number.', 'wc-serial-numbers' ) . '<br><strong>ex: <em>xxxx-xxxx-xxxx-xxxx-suffix</em></strong>',
					'class'       => 'ever-field-inline',
					'default'     => '',
					'type'        => 'text',
				),

				array(
					'name'    => 'wsn_generator_deliver_times',
					'label'   => __( 'Max. Deliver Times', 'wc-serial-numbers' ),
					'desc'    => __( 'The maximum number, the serial number can be delivered..', 'wc-serial-numbers' ),
					'class'   => 'ever-field-inline',
					'default' => 1,
					'type'    => 'number',
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
					'default' => 1,
					'type'    => 'number',
				),


				array(
					'name'    => 'wsn_generate_number',
					'label'   => __( 'Generate Number', 'wc-serial-numbers' ),
					'desc'    => __( 'The default generate number for generating serial number automatically.', 'wc-serial-numbers' ),
					'class'   => 'ever-field-inline',
					'default' => 5,
					'type'    => 'number',
				),

			),

			'wsn_notification_settings' => array(

				array(
					'name'    => 'wsn_admin_bar_notification',
					'label'   => __( 'Admin bar notification', 'wc-serial-numbers' ),
					'desc'    => '<p class="description">' . __( 'Show admin bar notification, if there is not enough serial number for any product', 'wc-serial-numbers' ) . '</p>',
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'        => 'wsn_admin_bar_notification_number',
					'label'       => __( 'Set Limit', 'wc-serial-numbers' ),
					'placeholder' => __( '2', 'wc-serial-numbers' ),
					'desc'        => __( 'Show notifications in the admin panel when, Number of available serial numbers for license able products is under the given number', 'wc-serial-numbers' ),
					'class'       => 'ever-field-inline',
					'default'     => 5,
					'type'        => 'number',
				),

				array(
					'name'    => 'wsn_admin_bar_notification_send_email',
					'label'   => __( 'Send Email', 'wc-serial-numbers' ),
					'desc'    => '<p class="description">' . __( 'Also receive email notification, if there is not enough serial number for any product', 'wc-serial-numbers' ) . '</p>',
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),

				array(
					'name'        => 'wsn_admin_bar_notification_email',
					'label'       => __( 'Email Address', 'wc-serial-numbers' ),
					'placeholder' => __( '', 'wc-serial-numbers' ),
					'desc'        => __( 'The email address to be used for sending the email notification', 'wc-serial-numbers' ),
					'class'       => 'ever-field-inline',
					'default'     => '',
					'type'        => 'text',
				),


			),
			'wsn_delivery_settings'     => array(

				array(
					'name'    => 'wsn_auto_complete_order',
					'label'   => __( 'Auto Complete Order', 'wc-serial-numbers' ),
					'desc'    => '<p class="description">' . __( 'Whether the Order will be auto completed after purchasing.', 'wc-serial-numbers' ) . '</p>',
					'class'   => 'ever-field-inline',
					'type'    => 'select',
					'options' => array(
						'yes' => __( 'Yes', 'wc-serial-numbers' ),
						'no'  => __( 'No', 'wc-serial-numbers' ),
					),
				),

				array(
					'name'    => 'wsn_revoke_serial_number',
					'label'   => __( 'Revoke serial number on', 'wc-serial-numbers' ),
					'desc'    => '<p class="description">' . __( 'Choose order status, when the serial number to be removed from the order details', 'wc-serial-numbers' ) . '</p>',
					'class'   => 'ever-field-inline',
					'type'    => 'multicheck',
					'options' => array(
						'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ),
						'refunded'  => __( 'Refunded', 'wc-serial-numbers' ),
						'failed'    => __( 'Failed', 'wc-serial-numbers' ),
					),
				),
				array(
					'name'    => 'wsn_re_use_serial',
					'label'   => __( 'Reuse Serial Number', 'wc-serial-numbers' ),
					'desc'    => '<p class="description">' . __( 'Enable Serial number reuse, recovered from failed/refunded orders', 'wc-serial-numbers' ) . '</p>',
					'class'   => 'ever-field-inline',
					'type'    => 'select',
					'options' => array(
						'no'  => __( 'No', 'wc-serial-numbers' ),
						'yes' => __( 'Yes', 'wc-serial-numbers' )
					),
				),

			)
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
