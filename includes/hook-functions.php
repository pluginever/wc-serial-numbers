<?php
defined( 'ABSPATH' ) || exit();

/**
 * since 1.0.0
 * @param $data
 */
function serial_numbers_inactive_serial_number( $data ) {
	$serial_id = absint($data['serial']);
	\Pluginever\SerialNumbers\SerialNumber::mark_inactive($serial_id);
	wp_redirect(admin_url('admin.php?page=wc-serial-numbers'));
	exit();
}
add_action( 'serial_numbers_admin_get_inactive_serial_number', 'serial_numbers_inactive_serial_number' );

/**
 * since 1.0.0
 * @param $data
 */
function serial_numbers_activate_serial_number( $data ) {
	$serial_id = absint($data['serial']);
	\Pluginever\SerialNumbers\SerialNumber::mark_active($serial_id);
	wp_redirect(admin_url('admin.php?page=wc-serial-numbers'));
	exit();
}
add_action( 'serial_numbers_admin_get_activate_serial_number', 'serial_numbers_activate_serial_number' );

