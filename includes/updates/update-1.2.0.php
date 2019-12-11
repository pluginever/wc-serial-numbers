<?php
//update settings
//update variable products entry to main products
//serialnumberstatus update
//check for any ref ea, eaccounting
//add insight
//add promotion
//add user id customer id
//alter platform default value
//remove unused cron
//remove cron wcsn_per_minute_event


function wcsn_update_1_2_0() {

	//cron update
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
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers ADD customer_id bigint(20) NOT NULL DEFAULT 0" );
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers ADD vendor_id bigint(20) NOT NULL DEFAULT 0" );
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers ADD KEY customer_id(`customer_id`)" );
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers ADD KEY vendor_id(`vendor_id`)" );
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_activations CHANGE platform platform varchar(200) DEFAULT NULL" );
	global $current_user;
	if ( ! empty( $current_user->ID ) && current_user_can( 'manage_options' ) ) {
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wcsn_serial_numbers set vendor_id=%d", $current_user->ID ) );
	}

	//status update
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wcsn_serial_numbers set status=%s WHERE status=%s", 'available', 'new' ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wcsn_serial_numbers set status=%s WHERE status=%s", 'rejected', 'cancelled' ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}wcsn_serial_numbers set status=%s WHERE status=%s", 'failed', 'pending' ) );


	//settings update
	$updated_settings = [];
	$settings         = get_option( 'wsn_delivery_settings' );
	if ( isset( $settings['wsn_auto_complete_order'] ) ) {
		$updated_settings['autocomplete_order'] = $settings['wsn_auto_complete_order'];
	}
	if ( isset( $settings['wsn_re_use_serial'] ) ) {
		$updated_settings['reuse_serial_numbers'] = $settings['wsn_re_use_serial'];
	}
	if ( isset( $settings['wsn_re_use_serial'] ) ) {
		$updated_settings['reuse_serial_numbers'] = $settings['wsn_re_use_serial'];
	}

	$noty_settings = get_option( 'wsn_notification_settings' );

	if ( isset( $noty_settings['wsn_admin_bar_notification'] ) ) {
		$updated_settings['low_stock_alert'] = $noty_settings['wsn_admin_bar_notification'];
	}
	if ( isset( $noty_settings['wsn_admin_bar_notification_number'] ) ) {
		$updated_settings['low_stock_threshold'] = empty( intval( $noty_settings['wsn_admin_bar_notification'] ) ) ? 10 : intval( $noty_settings['wsn_admin_bar_notification'] );
	}
	if ( isset( $noty_settings['wsn_admin_bar_notification_send_email'] ) ) {
		$updated_settings['low_stock_notification'] = $noty_settings['wsn_admin_bar_notification_send_email'];
	}
	if ( isset( $noty_settings['wsn_admin_bar_notification_email'] ) ) {
		$updated_settings['low_stock_notification_email'] = $noty_settings['wsn_admin_bar_notification_email'];
	}

	$updated_settings = array_merge( array(
		'automatic_delivery'           => 'on',
		'reuse_serial_numbers'         => 'no',
		'allow_duplicate'              => 'no',
		'autocomplete_order'           => 'on',
		'disable_software'             => 'no',
		'low_stock_alert'              => 'on',
		'low_stock_notification'       => 'on',
		'low_stock_threshold'          => '10',
		'low_stock_notification_email' => get_option( 'admin_email' ),
	), $updated_settings );

	update_option( 'wc_serial_numbers_settings', $updated_settings );

}

wcsn_update_1_2_0();
