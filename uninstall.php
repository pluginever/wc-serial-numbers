<?php
/**
 * Serial Numbers Uninstall
 *
 * Uninstalling Serial Numbers deletes user roles, pages, tables, and options.
 *
 * @package     WooCommerceSerialNumbers
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// remove all the options starting with wc_serial_numbers.
$delete_all_options = get_option( 'wc_serial_numbers_delete_data' );
if ( empty( $delete_all_options ) ) {
	return;
}
// Delete all the options.
global $wpdb;
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wc_serial_numbers%';" );

