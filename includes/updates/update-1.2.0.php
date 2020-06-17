<?php

function wcsn_update_1_2_0() {
	wp_clear_scheduled_hook( 'wcsn_per_minute_event' );
	wp_clear_scheduled_hook( 'wcsn_daily_event' );
	wp_clear_scheduled_hook( 'wcsn_hourly_event' );

	if ( ! wp_next_scheduled( 'wc_serial_numbers_hourly_event' ) ) {
		wp_schedule_event( time(), 'hourly', 'wc_serial_numbers_hourly_event' );
	}

	if ( ! wp_next_scheduled( 'wc_serial_numbers_daily_event' ) ) {
		wp_schedule_event( time(), 'daily', 'wc_serial_numbers_daily_event' );
	}

	global $wpdb;
	$prefix = $wpdb->prefix;
	$wpdb->query( "RENAME TABLE `{$prefix}wcsn_serial_numbers` TO `{$prefix}wc_serial_numbers`" );
	$wpdb->query( "RENAME TABLE `{$prefix}wcsn_activations` TO `{$prefix}wc_serial_numbers_activations`" );

	$wpdb->query( "ALTER TABLE {$prefix}wc_serial_numbers DROP COLUMN `serial_image`;" );
	$wpdb->query( "ALTER TABLE {$prefix}wc_serial_numbers DROP COLUMN `activation_email`;" );
	$wpdb->query( "ALTER TABLE {$prefix}wc_serial_numbers CHANGE `created` `created_date` DATETIME NULL DEFAULT NULL;" );
	$wpdb->query( "ALTER TABLE {$prefix}wc_serial_numbers ADD vendor_id bigint(20) NOT NULL DEFAULT 0" );
	$wpdb->query( "ALTER TABLE {$prefix}wc_serial_numbers ADD activation_count int(9) NOT NULL  DEFAULT 0" );
	$wpdb->query( "ALTER TABLE {$prefix}wc_serial_numbers ADD KEY vendor_id(`vendor_id`)" );
	$wpdb->query( "ALTER TABLE {$prefix}wc_serial_numbers ADD source varchar(200) NOT NULL default 'custom_source'" );
	$wpdb->query( "ALTER TABLE {$prefix}wc_serial_numbers_activations CHANGE platform platform varchar(200) DEFAULT NULL" );
//status update
	$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}wc_serial_numbers set status=%s WHERE status=%s AND order_id=0", 'available', 'new' ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}wc_serial_numbers set status=%s WHERE status=%s AND order_id != 0", 'sold', 'active' ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}wc_serial_numbers set status=%s WHERE status=%s", 'cancelled', 'pending' ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}wc_serial_numbers set status=%s WHERE status=%s", 'cancelled', 'rejected' ) );
	global $current_user;
	if ( ! empty( $current_user->ID ) && current_user_can( 'manage_options' ) ) {
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wc_serial_numbers set vendor_id=%d", $current_user->ID ) );
	}

	$activations = $wpdb->get_col( "select serial_id, count(id) as active_count from  {$wpdb->prefix}wc_serial_numbers_activations where active='1' GROUP BY serial_id" );
	foreach ( $activations as $activation ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wc_serial_numbers SET activation_count = %d WHERE id=%d", intval( $activation->active_count ), intval( $activation->serial_id ) ) );
	}

	$wpdb->query( "UPDATE {$wpdb->prefix}wc_serial_numbers set status='available', order_date='0000-00-00 00:00:00', order_id='0' WHERE status !='available' AND order_id='0' AND expire_date='0000-00-00 00:00:00'" );

	//settings update
	$heading_text          = wc_serial_numbers()->get_settings( 'heading_text', 'Serial Numbers', 'wsn_delivery_settings' );
	$serial_col_heading    = wc_serial_numbers()->get_settings( 'table_column_heading', 'Serial Number', 'wsn_delivery_settings' );
	$serial_key_label      = wc_serial_numbers()->get_settings( 'serial_key_label', 'Serial Number', 'wsn_delivery_settings' );
	$serial_email_label    = wc_serial_numbers()->get_settings( 'serial_email_label', 'Activation Email', 'wsn_delivery_settings' );
	$show_validity         = 'yes' == wc_serial_numbers()->get_settings( 'show_validity', 'yes', 'wsn_delivery_settings' );
	$show_activation_limit = 'yes' == wc_serial_numbers()->get_settings( 'show_activation_limit', 'yes', 'wsn_delivery_settings' );

	$settings = array(
		'autocomplete_order'       => 'yes' == wc_serial_numbers()->get_settings( 'wsn_auto_complete_order', 'no', 'wsn_delivery_settings' ) ? '1' : '0',
		'reuse_serial_number'      => 'yes' == wc_serial_numbers()->get_settings( 'wsn_re_use_serial', 'no', 'wsn_delivery_settings' ) ? '1' : '0',
		'disable_software_support' => '0',
		'revoke_statuses'          => wc_serial_numbers()->get_settings( 'wsn_revoke_serial_number', [], 'wsn_delivery_settings' ),
		'enable_backorder'         => 'on' == wc_serial_numbers()->get_settings( 'wsn_allow_checkout', 'off', 'wsn_general_settings' ) ? '1' : '0',
		'stock_notification'       => 'on' == wc_serial_numbers()->get_settings( 'wsn_admin_bar_notification_send_email', 'off', 'wsn_notification_settings' ) ? '1' : '0',
		'enable_duplicate'         => '0',
		'manual_delivery'          => '0',
		'hide_serial_number'       => '1',
		'template_heading'         => $heading_text,
		'product_cell_heading'     => __( 'Product', 'wc-serial-numbers' ),
		'serial_cell_heading'      => $serial_col_heading,
		'product_cell_content'     => '<a href="{product_url}">{product_title}</a>',
	);

	$serial_col_content = sprintf( '<li><strong>%s:</strong>{serial_number}</li>', $serial_key_label );
	$serial_col_content .= sprintf( '<li><strong>%s:</strong>{activation_email}</li>', $serial_email_label );
	if ( $show_validity ) {
		$serial_col_content .= '<li><strong>Expire At:</strong>{expired_at}</li>';
	}
	if ( $show_activation_limit ) {
		$serial_col_content .= '<li><strong>Activation Limit:</strong>{activation_limit}</li>';
	}
	$serial_col_content = '<ul>' . $serial_col_content . '</ul>';

	$settings['serial_cell_content'] = $serial_col_content;

	update_option( 'serial_numbers_settings', $settings );

}

wcsn_update_1_2_0();
