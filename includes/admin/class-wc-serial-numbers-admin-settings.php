<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin_Settings {

	/**
	 * WC_Serial_Numbers_MetaBoxes constructor.
	 */
	public static function init() {
		add_action( 'updated_option', array( __CLASS__, 'save_view_template' ) );
		add_filter( 'wcsn_setting_sections', array( __CLASS__, 'get_setting_sections' ) );
		add_filter( 'wcsn_setting_fields', array( __CLASS__, 'get_setting_fields' ) );
	}

	public static function save_view_template() {
		if ( ! isset( $_REQUEST['option_page'] ) ) {
			return;
		}

		update_option( 'wcsn_tmpl_heading', isset( $_REQUEST['wcsn_tmpl_heading'] ) ? sanitize_text_field( $_REQUEST['wcsn_tmpl_heading'] ) : '' );
		update_option( 'wcsn_tmpl_product_col_heading', isset( $_REQUEST['wcsn_tmpl_product_col_heading'] ) ? sanitize_text_field( $_REQUEST['wcsn_tmpl_product_col_heading'] ) : '' );
		update_option( 'wcsn_tmpl_serial_col_heading', isset( $_REQUEST['wcsn_tmpl_serial_col_heading'] ) ? sanitize_text_field( $_REQUEST['wcsn_tmpl_serial_col_heading'] ) : '' );
		update_option( 'wcsn_tmpl_product_col_content', isset( $_REQUEST['wcsn_tmpl_product_col_content'] ) ? sanitize_textarea_field( $_REQUEST['wcsn_tmpl_product_col_content'] ) : '' );
		update_option( 'wcsn_tmpl_serial_col_content', isset( $_REQUEST['wcsn_tmpl_serial_col_content'] ) ? sanitize_textarea_field( $_REQUEST['wcsn_tmpl_serial_col_content'] ) : '' );

	}

	public static function get_setting_sections( $sections ) {
		$new_sections = array(
			array(
				'id'    => 'wcsn_general_settings',
				'title' => __( 'General Settings', 'wc-serial-numbers' )
			),
			array(
				'id'    => 'wcsn_notification_settings',
				'title' => __( 'Notification Settings', 'wc-serial-numbers' )
			),
			array(
				'id'    => 'wcsn_display_settings',
				'title' => __( 'Display Settings', 'wc-serial-numbers' )
			),
		);

		return array_merge( $sections, $new_sections );
	}


	public static function get_setting_fields( $fields ) {
		$email_body = '<strong>Serial Numbers:</strong>{serial_numbers}<br/><strong>Activation Email:</strong>{activation_email}<br/><strong>Expire At:</strong>{expired_at}<br/><strong>Activation Limit:</strong>{activation_limit}<br/>';


		$new_fields = array(
			'wcsn_general_settings'      => array(
				array(
					'name'  => 'autocomplete_order',
					'label' => __( 'Auto Complete Order', 'wc-serial-numbers' ),
					'desc'  => __( 'This will automatically complete an order after successfull payment.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				),
				array(
					'name'  => 'reuse_serial_number',
					'label' => __( 'Reuse Serial Number', 'wc-serial-numbers' ),
					'desc'  => __( 'This will recover failed, refunded serial number for selling again.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				),
				array(
					'name'  => 'disable_api',
					'label' => __( 'Disable API Support', 'wc-serial-numbers' ),
					'desc'  => __( 'This will disable API support & everything related to software licensing.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				),
				array(
					'name'    => 'revoke_statuses',
					'label'   => __( 'Revoke When', 'wc-serial-numbers' ),
					'desc'    => __( 'Choose order status, when the serial number to be removed from the order detailsChoose order status, when the serial number to be removed from the order details.', 'wc-serial-numbers' ),
					'type'    => 'multicheck',
					'options' => array(
						'cancelled' => __( 'Cancelled', 'wc-serial-numbers' ),
						'refunded'  => __( 'Refunded', 'wc-serial-numbers' ),
						'failed'    => __( 'Failed', 'wc-serial-numbers' ),
					),
				),
				array(
					'name'  => 'hide_serial_number',
					'label' => __( 'Hide Serial Number', 'wc-serial-numbers' ),
					'desc'  => __( 'All serial numbers will be hidden and only displayed when the "Show" button is clicked.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				),
				array(
					'name'  => 'backorder',
					'label' => __( 'Backorder', 'wc-serial-numbers' ),
					'desc'  => __( 'Sell serial numbers even when there no serials in the stock.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
					'class'    => 'pro',
					'disabled' => true,
				),
//				array(
//					'name'     => 'account_tab',
//					'label'    => __( 'My Account Tab', 'wc-serial-numbers' ),
//					'desc'     => __( 'Enable Serial Number Tab on My Account Page.', 'wc-serial-numbers' ),
//					'type'     => 'checkbox',
//					'class'    => 'pro',
//					'disabled' => true,
//				),
//				array(
//					'name'     => 'account_tab_label',
//					'label'    => __( 'Account Tab Label', 'wc-serial-numbers' ),
//					'desc'     => __( 'Customize the label of my account tab.', 'wc-serial-numbers' ),
//					'type'     => 'text',
//					'class'    => 'pro',
//					'default'  => wc_serial_numbers()->get_label(),
//					'disabled' => true,
//				),
				array(
					'name'     => 'allow_duplicate',
					'label'    => __( 'Allow duplicates', 'wc-serial-numbers' ),
					'desc'     => __( 'Enable duplicate serial number, this will force to send billing email with API request.', 'wc-serial-numbers' ),
					'type'     => 'checkbox',
					'class'    => 'pro',
					'disabled' => true,
				),
				array(
					'name'     => 'manual_delivery',
					'label'    => __( 'Manual delivery', 'wc-serial-numbers' ),
					'desc'     => __( 'Manually assign serial numbers with order.', 'wc-serial-numbers' ),
					'type'     => 'checkbox',
					'class'    => 'pro',
					'disabled' => true,
				)
			),
			'wcsn_notification_settings' => array(
				array(
					'name'  => 'notification_bar',
					'label' => __( 'Notification Bar', 'wc-serial-numbers' ),
					'desc'  => __( 'This will show you update of your stock.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				),
				array(
					'name'  => 'stock_notification',
					'label' => __( 'Stock Notification Email', 'wc-serial-numbers' ),
					'desc'  => __( 'This will send you notification email when product stock is low.', 'wc-serial-numbers' ),
					'type'  => 'checkbox',
				),
				array(
					'name'    => 'stock_threshold',
					'label'   => __( 'Stock Threshold', 'wc-serial-numbers' ),
					'desc'    => __( 'When stock goes below the above number, it will send notification email.', 'wc-serial-numbers' ),
					'type'    => 'number',
					'default' => '5',
				),
				array(
					'name'    => 'notification_recipient',
					'label'   => __( 'Notification Recipient Email', 'wc-serial-numbers' ),
					'desc'    => __( 'The email address to be used for sending the email notification.', 'wc-serial-numbers' ),
					'type'    => 'text',
					'default' => get_option( 'admin_email' ),
				)
			),
			'wcsn_display_settings'      => array(
				array(
					'name'     => 'low_stock_message',
					'label'    => __( 'Low stock message', 'wc-serial-numbers' ),
					'desc'     => __( 'When "Sell From Stock" enabled and there is not enough items in <br/>stock the message will appear on checkout page. Supported tags {product_title}, {stock_quantity}', 'wc-serial-numbers' ),
					'type'     => 'textarea',
					'default'  => __( 'Sorry, There is not enough Serial Numbers available for {product_title}, Please remove this item or lower the quantity, For now we have {stock_quantity} Serial Number for this product.', 'wc-serial-numbers' ),
					'class'    => 'pro',
					'disabled' => true,
				),
				array(
					'name'     => 'view_template',
					'label'    => __( 'Template', 'wc-serial-numbers' ),
					'callback' => 'wc_serial_number_view_template_settings',
					'class'    => 'pro',
					'disabled' => true,
				),
			)
		);

		return array_merge( $fields, $new_fields );
	}
}

WC_Serial_Numbers_Admin_Settings::init();
