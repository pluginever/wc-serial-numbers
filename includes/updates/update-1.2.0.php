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
}

wcsn_update_1_2_0();
