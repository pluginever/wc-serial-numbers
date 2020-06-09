<?php

function wcsn_update_1_2_0() {
	//cron update
	wp_clear_scheduled_hook( 'wcsn_per_minute_event' );
	wp_clear_scheduled_hook( 'wcsn_daily_event' );
	wp_clear_scheduled_hook( 'wcsn_hourly_event' );

	if ( ! wp_next_scheduled( 'wcsn_hourly_event' ) ) {
		wp_schedule_event( time(), 'hourly', 'wcsn_hourly_event' );
	}

	if ( ! wp_next_scheduled( 'wcsn_daily_event' ) ) {
		wp_schedule_event( time(), 'daily', 'wcsn_daily_event' );
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


	//settings update

	$updated_general_settings = array(
		'autocomplete_order'       => 'yes' == wc_serial_numbers()->get_settings( 'wsn_auto_complete_order', 'no', 'wsn_delivery_settings' ) ? 'on' : 'off',
		'reuse_serial_number'      => 'yes' == wc_serial_numbers()->get_settings( 'wsn_re_use_serial', 'no', 'wsn_delivery_settings' ) ? 'on' : 'off',
		'disable_software_support' => 'off',
		'revoke_statuses'          => wc_serial_numbers()->get_settings( 'wsn_revoke_serial_number', [], 'wsn_delivery_settings' ),
		'enable_backorder'         => wc_serial_numbers()->get_settings( 'wsn_allow_checkout', 'off', 'wsn_general_settings' ),
		'allow_duplicate'          => 'off',
		'manual_delivery'          => 'off',
		'hide_serial_number'       => wc_serial_numbers()->get_settings( 'wsn_hide_serial_key', 'on', 'wsn_general_settings' ),
	);
	update_option('wsn_general_settings', $updated_general_settings);
	$heading_text          = wc_serial_numbers()->get_settings( 'heading_text', 'Serial Numbers', 'wsn_delivery_settings' );
	$serial_col_heading    = wc_serial_numbers()->get_settings( 'table_column_heading', 'Serial Number', 'wsn_delivery_settings' );
	$serial_key_label      = wc_serial_numbers()->get_settings( 'serial_key_label', 'Serial Key', 'wsn_delivery_settings' );
	$serial_email_label    = wc_serial_numbers()->get_settings( 'serial_email_label', 'Activation Email', 'wsn_delivery_settings' );
	$show_validity         = 'yes' == wc_serial_numbers()->get_settings( 'show_validity', 'yes', 'wsn_delivery_settings' );
	$show_activation_limit = 'yes' == wc_serial_numbers()->get_settings( 'show_activation_limit', 'yes', 'wsn_delivery_settings' );
	update_option( 'wcsn_tmpl_heading', $heading_text );
	update_option( 'wcsn_tmpl_serial_col_heading', $serial_col_heading );
	$serial_col_content = sprintf( '<strong>%s:</strong>{serial_number}<br/>', $serial_key_label );
	$serial_col_content .= sprintf( '<strong>%s:</strong>{activation_email}<br/>', $serial_email_label );
	if ( $show_validity ) {
		$serial_col_content .= '<strong>Expire At:</strong>{expired_at}<br/>';
	}
	if ( $show_activation_limit ) {
		$serial_col_content .= '<strong>Activation Limit:</strong>{activation_limit}';
	}

	update_option('wcsn_tmpl_serial_col_content', $serial_col_content);


	$activations = $wpdb->get_col( "select serial_id, count(id) as active_count from  {$wpdb->prefix}wc_serial_numbers_activations where active='1' GROUP BY serial_id" );
	foreach ( $activations as $activation ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wc_serial_numbers SET activation_count = %d WHERE id=%d", intval( $activation->active_count ), intval( $activation->serial_id ) ) );
	}

}

wcsn_update_1_2_0();
