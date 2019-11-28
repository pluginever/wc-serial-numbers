<?php
defined( 'ABSPATH' ) || exit();


/**
 * Get admin view
 * since 1.0.0
 * @param $template_name
 * @param array $args
 */
function wcsn_get_views( $template_name, $args = [] ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	if ( file_exists( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/views/' . $template_name ) ) {
		include WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/views/' . $template_name;
	}
}



/**
 * Add admin notice
 * since 1.0.0
 * @param $notice
 * @param string $type
 * @param bool $dismissible
 */
function wcsn_admin_notice( $notice, $type = 'success', $dismissible = true ) {
	$notices = WC_Serial_Numbers_Admin_Notices::instance();
	$notices->add($notice, $type, $dismissible);
}
