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
