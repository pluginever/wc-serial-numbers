<?php

defined( 'ABSPATH' ) || exit;

return array(
	'general' => array(
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
			'desc_tip' => __( 'If you enable this option, the activation menu and it\'s functionality will be turned off.', 'wc-serial-numbers' ),
			'default'  => 'no',
			'type'     => 'checkbox',
		),
		array(
			'title'    => __( 'Manage Stocks', 'wc-serial-numbers' ),
			'id'       => 'wcsn_manage_stocks',
			'desc'     => __( 'Manage stocks for the key enabled products.', 'wc-serial-numbers' ),
			'desc_tip' => __( 'Enable stock management for key-enabled products. This works only if you select "Manually Added" as the key source and enable stock management for the product. Variable product is not supported.', 'wc-serial-numbers' ),
			'type'     => 'checkbox',
			'default'  => 'no',
		),
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
	),
);
