<?php
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

wcsn_update_1_1_2();
