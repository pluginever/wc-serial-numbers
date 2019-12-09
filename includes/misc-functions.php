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
function wc_serial_numbers_get_settings( $key, $default = '') {

	$option = get_option( 'wc_serial_numbers_settings', [] );

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
function wc_serial_numbers_labels( $key = 'serial_number', $plural = false ) {
	$labels = apply_filters( 'wc_serial_numbers_labels', array(
		'serial_number' => array(
			'singular' => __( 'Serial Number', 'wc-serial-numbers' ),
			'plural'   => __( 'Serial Numbers', 'wc-serial-numbers' ),
		),
		'activation'    => array(
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
function wc_serial_numbers_get_views( $template_name, $args = [] ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}
	if ( file_exists( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/views/' . $template_name ) ) {
		include apply_filters( 'wc_serial_numbers_views', WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/views/' . $template_name, $template_name );
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
function wc_serial_numbers_add_admin_notice( $notice, $type = 'success', $dismissible = true ) {
	if(class_exists('WC_Serial_Numbers_Admin_Notice')){
		$notices = WC_Serial_Numbers_Admin_Notice::instance();
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
function wc_serial_numbers_generate_random_string( $length = 10 ) {
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
function wc_serial_numbers_get_encrypt_key() {
	$p_key = get_option( 'wcsn_pkey', false );

	if ( false === $p_key || '' === $p_key ) {
		$salt     = wc_serial_numbers_generate_random_string();
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
function wc_serial_numbers_is_encrypted_string( $string ) {
	if ( preg_match( '/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/', $string ) ) {
		return true;
	}

	return false;
}

/**
 * since 1.0.0
 * @return mixed|void
 */
function wc_serial_numbers_is_allowed_duplicate_serial_numbers(){
	$allow_duplicate = 'on' == wc_serial_numbers_get_settings('allow_duplicate', '' );
	return apply_filters( 'wc_serial_numbers_allow_duplicate_serial_number', $allow_duplicate );
}

/**
 * @since 1.0.0
 * @return mixed|void
 */
function wc_serial_numbers_is_order_automatically_assign_serial_numbers(){
	$automatic_delivery = 'on' == wc_serial_numbers_get_settings('automatic_delivery', '' );

	return apply_filters( 'wc_serial_numbers_order_automatically_assign_serial_numbers', $automatic_delivery );
}

/**
 * @since 1.0.0
 * @return mixed|void
 */
function wc_serial_numbers_is_reuse_serial_numbers(){
	$reuse = 'on' == wc_serial_numbers_get_settings('reuse_serial_numbers', '' );
	return apply_filters( 'wc_serial_numbers_reuse_serial_numbers', $reuse );
}


/**
 * @since 1.0.0
 * @return mixed|void
 */
function wc_serial_numbers_auto_complete_order(){
	$auto_complete_order = 'on' == wc_serial_numbers_get_settings('auto_complete_order', '' );
	return apply_filters( 'wc_serial_numbers_auto_complete_order', $auto_complete_order );
}

function wc_serial_numbers_software_disabled(){
	$disable_software = 'on' == wc_serial_numbers_get_settings('disable_software', '' );
	return apply_filters( 'wc_serial_numbers_software_disabled', $disable_software );
}
