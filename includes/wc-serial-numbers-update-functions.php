<?php
/**
 * WooCommerce Serial Numbers Updates
 *
 * Functions for updating data.
 *
 * @package WC_Serial_Numbers/Functions
 * @version 1.2.8
 */

defined( 'ABSPATH' ) || exit;

function wcsn_update_1_0_1() {
	WCSN_Install::activate();

	$serial_numbers = get_posts( array(
		'post_type' => 'wsn_serial_number',
		'nopaging'  => true,
	) );

	foreach ( $serial_numbers as $post ) {
		$validity = get_post_meta( $post->ID, 'validity', true );
		$order    = get_post_meta( $post->ID, 'order', true );
		$data     = array(
			'serial_key'       => $post->post_title,
			'license_image'    => ! empty( $_POST['license_image'] ) ? sanitize_text_field( $_POST['license_image'] ) : '',
			'product_id'       => get_post_meta( $post->ID, 'product', true ),
			'activation_limit' => get_post_meta( $post->ID, 'max_instance', true ),
			'validity'         => is_numeric( $validity ) ? $validity : 0,
			'expire_date'      => is_string( $validity ) && ( strtotime( $validity ) > strtotime( '2019-01-01' ) ) ? date( 'Y-m-d', strtotime( $validity ) ) : 0,
			'status'           => empty( intval( $order ) ) ? 'new' : 'active',
			'order_id'         => intval( $order ),
		);
		wcsn()->serial_number->insert( $data );
	}
}

function wcsn_update_1_0_6() {
	global $wpdb;

	WCSN_Install::activate();
	WCSN_Install::create_cron();

	$collate = '';
	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty( $wpdb->charset ) ) {
			$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$collate .= " COLLATE $wpdb->collate";
		}
	}

	$tables = [
		"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wcsn_tmp_serial_numbers(
        id bigint(20) NOT NULL AUTO_INCREMENT,
        serial_key longtext DEFAULT NULL,
        serial_image varchar(200) DEFAULT NULL,
        product_id bigint(20) NOT NULL,
        activation_limit int(9) NULL,
        order_id bigint(20) NOT NULL DEFAULT 0,
        activation_email varchar(200) DEFAULT NULL,
        status varchar(50) DEFAULT 'available',
        validity varchar(200) DEFAULT NULL,
        expire_date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
        order_date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
        created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
        ) $collate;",
	];

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	foreach ( $tables as $table ) {
		dbDelta( $table );
	}


	$serial_numbers = wcsn_get_serial_numbers( array(
		'number' => - 1,
	) );
	foreach ( $serial_numbers as $serial_number ) {
		wcsn()->tmp_serial_number->insert( $serial_number );
	}
	foreach ( $serial_numbers as $serial_number ) {
		$serial_number = (array) $serial_number;

		if ( ! empty( $serial_number['serial_key'] ) ) {
			$serial_number['serial_key'] = wcsn_encrypt( $serial_number['serial_key'] );
		}

		wcsn()->serial_number->update( $serial_number['id'], $serial_number );
	}
}

function wcsn_update_1_0_8() {
	$delivery_settings                          = get_option( 'wsn_delivery_settings' );
	$delivery_settings['heading_text']          = empty( $delivery_settings['wsnp_email_label'] ) ? 'Serial Numbers' : $delivery_settings['wsnp_email_label'];
	$delivery_settings['table_column_heading']  = empty( $delivery_settings['wsnp_email_tabel_label'] ) ? 'Serial Number' : $delivery_settings['wsnp_email_tabel_label'];
	$delivery_settings['serial_key_label']      = empty( $delivery_settings['wsnp_email_serial_key_email_label'] ) ? 'Serial Key' : $delivery_settings['wsnp_email_serial_key_email_label'];
	$delivery_settings['serial_email_label']    = empty( $delivery_settings['wsnp_email_serial_key_label'] ) ? 'Serial Email' : $delivery_settings['wsnp_email_serial_key_label'];
	$delivery_settings['show_validity']         = empty( $delivery_settings['wsnp_show_validity_on_email'] ) ? 'yes' : $delivery_settings['wsnp_show_validity_on_email'];
	$delivery_settings['show_activation_limit'] = empty( $delivery_settings['wsnp_show_activation_limit_on_email'] ) ? 'yes' : $delivery_settings['wsnp_show_activation_limit_on_email'];

	update_option( 'wsn_delivery_settings', $delivery_settings );
}


function wcsn_update_1_1_2() {
	global $wpdb;
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers ADD KEY product_id(`product_id`)" );
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers ADD KEY order_id (`order_id`)" );
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers ADD KEY status (`status`)" );

	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers CHANGE expire_date expire_date DATETIME DEFAULT NULL" );
	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_serial_numbers CHANGE order_date order_date DATETIME DEFAULT NULL" );
	$wpdb->query( "UPDATE {$wpdb->prefix}wcsn_serial_numbers  set expire_date=NULL WHERE expire_date='0000-00-00 00:00:00'" );
	$wpdb->query( "UPDATE {$wpdb->prefix}wcsn_serial_numbers  set order_date=NULL WHERE order_date='0000-00-00 00:00:00'" );

	$wpdb->query( "ALTER TABLE {$wpdb->prefix}wcsn_activations ADD KEY serial_id (`serial_id`)" );

}

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
	$wpdb->query( "RENAME TABLE `{$prefix}wcsn_serial_numbers` TO `{$prefix}serial_numbers`" );
	$wpdb->query( "RENAME TABLE `{$prefix}wcsn_activations` TO `{$prefix}serial_numbers_activations`" );

	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers DROP COLUMN `serial_image`;" );
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers DROP COLUMN `activation_email`;" );
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers CHANGE `created` `created_date` DATETIME NULL DEFAULT NULL;" );
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD vendor_id bigint(20) NOT NULL DEFAULT 0" );
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD activation_count int(9) NOT NULL  DEFAULT 0" );
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD KEY vendor_id(`vendor_id`)" );
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers ADD source varchar(200) NOT NULL default 'custom_source'" );
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers_activations CHANGE platform platform varchar(200) DEFAULT NULL" );
	//status update
	$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}serial_numbers set status=%s WHERE status=%s AND order_id=0", 'available', 'new' ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}serial_numbers set status=%s WHERE status=%s AND order_id != 0", 'sold', 'active' ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}serial_numbers set status=%s WHERE status=%s", 'cancelled', 'pending' ) );
	$wpdb->query( $wpdb->prepare( "UPDATE {$prefix}serial_numbers set status=%s WHERE status=%s", 'cancelled', 'rejected' ) );
	global $current_user;
	if ( ! empty( $current_user->ID ) && current_user_can( 'manage_options' ) ) {
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}serial_numbers set vendor_id=%d", $current_user->ID ) );
	}

	$activations = $wpdb->get_results( "select serial_id, count(id) as active_count from  {$wpdb->prefix}serial_numbers_activations where active='1' GROUP BY serial_id" );
	foreach ( $activations as $activation ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}serial_numbers SET activation_count = %d WHERE id=%d", intval( $activation->active_count ), intval( $activation->serial_id ) ) );
	}

	$wpdb->query( "UPDATE {$wpdb->prefix}serial_numbers set status='available', order_date='0000-00-00 00:00:00', order_id='0' WHERE status !='available' AND order_id='0' AND expire_date='0000-00-00 00:00:00'" );

	//settings update
	$heading_text          = wcsn_update_1_2_0_get_option( 'heading_text', 'Serial Numbers', 'wsn_delivery_settings' );
	$serial_col_heading    = wcsn_update_1_2_0_get_option( 'table_column_heading', 'Serial Number', 'wsn_delivery_settings' );
	$serial_key_label      = wcsn_update_1_2_0_get_option( 'serial_key_label', 'Serial Number', 'wsn_delivery_settings' );
	$serial_email_label    = wcsn_update_1_2_0_get_option( 'serial_email_label', 'Activation Email', 'wsn_delivery_settings' );
	$show_validity         = 'yes' == wcsn_update_1_2_0_get_option( 'show_validity', 'yes', 'wsn_delivery_settings' );
	$show_activation_limit = 'yes' == wcsn_update_1_2_0_get_option( 'show_activation_limit', 'yes', 'wsn_delivery_settings' );
	$license               = get_option( 'woocommerce_serial_numbers_pro_pluginever_license' );
	$options               = [
		'wc_serial_numbers_autocomplete_order'            => wcsn_update_1_2_0_get_option( 'wsn_auto_complete_order', 'yes', 'wsn_delivery_settings' ),
		'wc_serial_numbers_reuse_serial_number'           => wcsn_update_1_2_0_get_option( 'wsn_re_use_serial', 'no', 'wsn_delivery_settings' ),
		'wc_serial_numbers_disable_software_support'      => 'no',
		'wc_serial_numbers_manual_delivery'               => 'no',
		'wc_serial_numbers_hide_serial_number'            => 'yes',
		'wc_serial_numbers_revoke_status_cancelled'       => in_array( 'cancelled', wcsn_update_1_2_0_get_option( 'wsn_revoke_serial_number', [], 'wsn_delivery_settings' ) ) ? 'yes' : 'no',
		'wc_serial_numbers_revoke_status_refunded'        => in_array( 'refunded', wcsn_update_1_2_0_get_option( 'wsn_revoke_serial_number', [], 'wsn_delivery_settings' ) ) ? 'yes' : 'no',
		'wc_serial_numbers_revoke_status_failed'          => in_array( 'failed', wcsn_update_1_2_0_get_option( 'wsn_revoke_serial_number', [], 'wsn_delivery_settings' ) ) ? 'yes' : 'no',
		'wc_serial_numbers_enable_stock_notification'     => wcsn_update_1_2_0_get_option( 'wsn_admin_bar_notification_send_email', 'yes', 'wsn_notification_settings' ),
		'wc_serial_numbers_stock_threshold'               => wcsn_update_1_2_0_get_option( 'wsn_admin_bar_notification_number', '5', 'wsn_notification_settings' ),
		'wc_serial_numbers_notification_recipient'        => wcsn_update_1_2_0_get_option( 'wsn_admin_bar_notification_email', get_option( 'admin_email' ), 'wsn_notification_settings' ),
		'wc_serial_numbers_order_table_heading'           => $heading_text,
		'wc_serial_numbers_order_table_col_product_label' => 'Product',
		'wc_serial_numbers_order_table_col_key_label'     => $serial_key_label,
		'wc_serial_numbers_order_table_col_email_label'   => $serial_email_label,
		'wc_serial_numbers_order_table_col_limit_label'   => 'Activation Limit',
		'wc_serial_numbers_order_table_col_expires_label' => 'Expire Date',
		'wc_serial_numbers_order_table_col_product'       => 'yes',
		'wc_serial_numbers_order_table_col_key'           => 'yes',
		'wc_serial_numbers_order_table_col_email'         => 'no',
		'wc_serial_numbers_order_table_col_limit'         => $show_activation_limit ? 'yes' : 'no',
		'wc_serial_numbers_order_table_col_expires'       => $show_validity ? 'yes' : 'no',
		'wc_serial_numbers_install_time'                  => get_option( 'woocommerceserialnumbers_install_time' ),
		'woocommerce-serial-numbers-pro_license_key'      => array_key_exists( 'key', $license ) ? $license['key'] : '',
		'woocommerce-serial-numbers-pro_license_status'   => array_key_exists( 'license', $license ) ? $license['license'] : '',
	];
	foreach ( $options as $key => $option ) {
		update_option( $key, $option );
	}

}

function wcsn_update_1_2_0_get_option( $key, $default = '', $section = 'serial_numbers_settings' ) {
	$settings = get_option( $section, [] );

	return ! empty( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

function wcsn_update_1_2_1() {
	global $wpdb;
	$prefix = $wpdb->prefix;
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers CHANGE order_id order_id bigint(20) DEFAULT NULL" );
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers CHANGE vendor_id vendor_id bigint(20) DEFAULT NULL" );
}
