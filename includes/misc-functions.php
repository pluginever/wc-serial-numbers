<?php
defined( 'ABSPATH' ) || exit();

/**
 * get settings options
 *
 * since 1.0.0
 * @param $key
 * @param string $default
 *
 * @return string
*/
function wcsn_get_settings( $key, $default = '') {

	$option = get_option( 'wcsn_settings', [] );

	return ! empty( $option[ $key ] ) ? $option[ $key ] : $default;
}

/**
 * since 1.0.0
 *
 * @param string $key
 * @param bool $plural
 *
 * @return string
*/
function wcsn_labels( $key = 'serial_number', $plural = false ) {
	$labels = apply_filters( 'wcsn_labels', array(
		'serial_number' => array(
			'singular' => __( 'Serial Number', 'wc-serial-numbers' ),
			'plural'   => __( 'Serial Numbers', 'wc-serial-numbers' ),
		),
		'activation'    => array(
			'singular' => __( 'Activation', 'wc-serial-numbers' ),
			'plural'   => __( 'Activations', 'wc-serial-numbers' ),
		),
		'serial_key'    => array(
			'singular' => __( 'Activation', 'wc-serial-numbers' ),
			'plural'   => __( 'Activations', 'wc-serial-numbers' ),
		),
	) );

	$label_group = array_key_exists( $key, $labels ) ? $labels[ $key ] : $labels['serial_number'];

	$key = $plural ? 'plural' : 'singular';

	return array_key_exists( $key, $label_group ) ? $label_group[ $key ] : $label_group['singular'];
}

/**
 * Get admin view
 * since 1.0.0
 *
 * @param $template_name
 * @param array $args
 */
function wcsn_get_views( $template_name, $args = [] ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	if ( file_exists( WCSN_PATH . '/includes/admin/views/' . $template_name ) ) {
		include apply_filters( 'wcsn_views', WCSN_PATH . '/includes/admin/views/' . $template_name, $template_name );
	}
}

/**
 * Add admin notice
 * since 1.0.0
 *
 * @param $notice
 * @param string $type
 * @param bool $dismissible
 */
function wcsn_add_admin_notice( $notice, $type = 'success', $dismissible = true ) {
	if(class_exists('WCSN_Admin_Notice')){
		$notices = WCSN_Admin_Notice::instance();
		$notices->add($notice, $type, $dismissible);
	}
}

/**
 * Generate Random String
 *
 * @param integer $length
 *
 * @return string
 */
function wcsn_generate_random_string( $length = 10 ) {
	$chars         = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_@$#';
	$chars_length  = strlen( $chars );
	$random_string = '';
	for ( $i = 0; $i < $length; $i ++ ) {
		$random_string .= $chars[ rand( 0, $chars_length - 1 ) ];
	}

	return $random_string;
}

/**
 * Get encrypt private key
 *
 * @return string
*/
function wcsn_get_encrypt_key() {
	$p_key = get_option( 'wcsn_pkey', false );

	if ( false === $p_key || '' === $p_key ) {
		$salt     = wcsn_generate_random_string();
		$time     = time();
		$home_url = get_home_url( '/' );
		$salts    = array( $time, $home_url, $salt );

		shuffle( $salts );

		$p_key = hash( 'sha256', implode( '-', $salts ) );

		update_option( 'wcsn_pkey', $p_key );
	}

	return $p_key;
}

/**
 * Is encrypted
 *
 * @param string $string
 *
 * @return bool
*/
function wcsn_is_encrypted_string( $string ) {
	if ( preg_match( '/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/', $string ) ) {
		return true;
	}

	return false;
}

/**
 * since 1.0.0
 * @return mixed|void
*/
function wcsn_is_allowed_duplicate_serial_numbers(){
	$allow_duplicate = 'on' == wcsn_get_settings('allow_duplicate', '' );
	return apply_filters( 'wcsn_allow_duplicate_serial_number', $allow_duplicate );
}

/**
 * @since 1.0.0
 * @return mixed|void
*/
function wcsn_is_order_automatically_assign_serial_numbers(){
	$automatic_delivery = 'on' == wcsn_get_settings('automatic_delivery', '' );

	return apply_filters( 'wcsn_order_automatically_assign_serial_numbers', $automatic_delivery );
}

/**
 * @since 1.0.0
 * @return mixed|void
*/
function wcsn_is_reuse_serial_numbers(){
	$reuse = 'on' == wcsn_get_settings('reuse_serial_numbers', '' );
	return apply_filters( 'wcsn_reuse_serial_numbers', $reuse );
}

/**
 * @since 1.0.0
 * @return mixed|void
 */
function wcsn_auto_complete_order(){
	$auto_complete_order = 'on' == wcsn_get_settings('autocomplete_order', '' );
	return apply_filters( 'wcsn_auto_complete_order', $auto_complete_order );
}

function wcsn_software_disabled(){
	$disable_software = 'on' == wcsn_get_settings('disable_software', '' );
	return apply_filters( 'wcsn_software_disabled', $disable_software );
}

