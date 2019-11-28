<?php
defined( 'ABSPATH' ) || exit();

function wcsn_get_serial_number_label() {
	return apply_filters( 'wcsn_serial_number_label', 'Serial Number' );
}

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
 * Get a list of all wc products
 *
 * @return array
 * @since 1.0.0
 */
function wcsn_get_product_list( $only_enabled = false ) {
	global $wpdb;
	$list = [];

	$sql = "SELECT post.ID FROM {$wpdb->prefix}posts post WHERE post.post_status = 'publish' and post.post_type IN ('product_variation', 'product') ORDER BY post.ID ASC";

	if ( $only_enabled ) {
		$sql = "SELECT post.ID FROM {$wpdb->prefix}posts post INNER JOIN {$wpdb->prefix}postmeta postmeta ON postmeta.post_id=post.ID WHERE post.post_status = 'publish' and post.post_type IN ('product_variation', 'product') AND postmeta.meta_key='_is_serial_number' AND postmeta.meta_value='yes' ORDER BY post.ID ASC";
	}


	$posts    = $wpdb->get_results( $sql );
	$products = array_map( 'wc_get_product', $posts );

	$supported_types = apply_filters( 'wcsn_supported_product_types', array( 'simple', 'variation' ) );

	foreach ( $products as $product ) {
		if ( in_array( $product->get_type(), $supported_types ) ) {
			$title                      = $product->get_title();
			$title                      .= "(#{$product->get_id()} {$product->get_sku()} ";
			$title                      .= $product->get_type() == 'variation' ? ', Variation' : '';
			$title                      .= ')';
			$list[ $product->get_id() ] = $title;
		}
	}

	krsort( $list );

	return $list;
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

	//$string = wc_serial_numbers()->encryption->decrypt( $hash, $p_key, 'kcv4tu0FSCB9oJyH' );
	$string = $hash;

	return $string;
}

/**
 * get remaining activation
 *
 * @param $serial_id
 *
 * @return int|mixed
 * @since 1.0.0
 *
 */
function wcsn_get_remaining_activation( $serial_id, $context = 'edit' ) {
	global $wpdb;

	$serial_id = (int) $serial_id;

	if ( ! $serial_id ) {
		return 0;
	}

	$activation_limit = $wpdb->get_var( $wpdb->prepare( "SELECT activation_limit FROM {$wpdb->prefix}wcsn_serial_numbers WHERE id = %s;", $serial_id ) );

	if ( null == $activation_limit || 0 == $activation_limit ) {
		return $context == 'edit' ? 999999999 : __( 'Unlimited', 'wc-serial-numbers' );
	}

	$active_activations = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$wpdb->prefix}wcsn_activations WHERE serial_id = %s AND active = 1;", $serial_id ) );
	$remaining          = max( 0, $activation_limit - $active_activations );

	return $context == 'edit' ? $remaining : ( $remaining > 9999 ? __( 'Unlimited', 'wc-serial-numbers' ) : $remaining );
}
