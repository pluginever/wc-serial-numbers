<?php
function wcsn_update_1_2_1() {
	global $wpdb;
	$prefix = $wpdb->prefix;
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers CHANGE order_id order_id bigint(20) DEFAULT NULL" );
	$wpdb->query( "ALTER TABLE {$prefix}serial_numbers CHANGE vendor_id vendor_id bigint(20) DEFAULT NULL" );
}

wcsn_update_1_2_1();
