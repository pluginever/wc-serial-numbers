<?php
defined( 'ABSPATH' ) || exit();


/**
 * get settings options
 *
 * @param        $key
 * @param string $default
 *
 * @return string|array
 */
function serial_numbers_get_settings( $key, $default = '' ) {
	$option = get_option( 'wc_serial_numbers_settings', [] );

	return ! empty( $option[ $key ] ) ? $option[ $key ] : $default;
}


/**
 * Get admin view
 * since 1.0.0
 *
 * @param $template_name
 * @param array $args
 */
function serial_numbers_get_views( $template_name, $args = [] ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}
	if ( file_exists( WC_SERIAL_NUMBERS_INCLUDES . '/admin/views/' . $template_name ) ) {
		include WC_SERIAL_NUMBERS_INCLUDES . '/admin/views/' . $template_name;
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
function serial_numbers_admin_notice( $notice, $type = 'success', $dismissible = true ) {
	if ( class_exists( 'Pluginever\SerialNumbers\AdminNotice' ) ) {
		$notices = Pluginever\SerialNumbers\Admin\AdminNotice::instance();
		$notices->add( $notice, $type, $dismissible );
	}
}


/**
 * since 1.0.0
 *
 * @param string $key
 * @param bool $plural
 *
 * @return string
 */
function serial_numbers_get_labels( $key = 'serial_number', $plural = false ) {
	$labels = apply_filters( 'wc_serial_number_labels', array(
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
 * Generate Random String
 *
 * @param integer $length
 *
 * @return string
 */
function serial_numbers_generate_random_string( $length = 10 ) {
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
function serial_numbers_get_encrypt_key() {
	$p_key = get_option( 'wcsn_pkey', false );

	if ( false === $p_key || '' === $p_key ) {
		$salt     = serial_numbers_generate_random_string();
		$time     = time();
		$home_url = get_home_url( '/' );
		$salts    = array( $time, $home_url, $salt );

		shuffle( $salts );

		$p_key = hash( 'sha256', implode( '-', $salts ) );

		update_option( 'wcsn_pkey', $p_key );
	}

	return $p_key;
}


function serial_numbers_encrypt( $string ) {

	$p_key      = serial_numbers_get_encrypt_key();
	$encryption = Pluginever\SerialNumbers\Encryption::instance();
	$hash       = $encryption->encrypt( $string, $p_key, 'kcv4tu0FSCB9oJyH' );

	return $hash;
}


function serial_numbers_decrypt( $string ) {

	$p_key      = serial_numbers_get_encrypt_key();
	$encryption = Pluginever\SerialNumbers\Encryption::instance();
	$hash       = $encryption->decrypt( $string, $p_key, 'kcv4tu0FSCB9oJyH' );

	return $hash;
}
