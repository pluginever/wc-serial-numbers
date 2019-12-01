<?php
defined( 'ABSPATH' ) || exit();

/**
 * get settings options
 *
 * @param        $key
 * @param string $default
 * @param string $section
 *
 * @return string|array
 */
function wcsn_get_settings( $key, $default = '', $section = '' ) {

	$option = get_option( $section, [] );

	return ! empty( $option[ $key ] ) ? $option[ $key ] : $default;
}

/**
 * get expiration date
 *
 * since 1.0.0
 *
 * @param $serial
 *
 * @return string
 */
function wcsn_get_serial_expiration_date( $serial ) {
	if ( empty( $serial->validity ) ) {
		return __( 'Never Expire', 'wc-serial-numbers' );
	}

	return date( 'Y-m-d', strtotime( $serial->order_date . ' + ' . $serial->validity . ' Day ' ) );
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
 * Encrypt String
 *
 * @param string $string
 *
 * @return string
 */
function wcsn_encrypt( $string ) {
	if ( ! function_exists( 'wc_serial_numbers' ) ) {
		return $string;
	}
	$p_key = wcsn_get_encrypt_key();

	$hash = wc_serial_numbers()->encryption->encrypt( $string, $p_key, 'kcv4tu0FSCB9oJyH' );

	return $hash;
}

/**
 * Decrypt hash to string
 *
 * @param string $hash
 *
 * @return string
 */
function wcsn_decrypt( $hash ) {
	if ( ! function_exists( 'wc_serial_numbers' ) ) {
		return $hash;
	}

	$p_key = wcsn_get_encrypt_key();

	$string = wc_serial_numbers()->encryption->decrypt( $hash, $p_key, 'kcv4tu0FSCB9oJyH' );

	return $string;
}

/**
 * Is encrypted
 *
 * @param string $string
 *
 * @return bool
 */
function wcsn_is_encrypted( $string ) {
	if ( preg_match( '/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=|[A-Za-z0-9+\/]{4})$/', $string ) ) {
		return true;
	}

	return false;
}

