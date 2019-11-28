<?php
defined( 'ABSPATH' ) || exit();


/**
 * Get admin view
 * since 1.0.0
 * @param $template_name
 * @param array $args
 */
function serial_numbers_get_views( $template_name, $args = [] ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	if ( file_exists( dirname( __DIR__ ) . '/views/' . $template_name ) ) {
		include dirname( __DIR__ ) . '/views/' . $template_name;
	}
}



/**
 * Add admin notice
 * since 1.0.0
 * @param $notice
 * @param string $type
 * @param bool $dismissible
 */
function eaccounting_admin_notice( $notice, $type = 'success', $dismissible = true ) {
	$notices = EAccounting_Admin_Notices::instance();
	$notices->add($notice, $type, $dismissible);
}
