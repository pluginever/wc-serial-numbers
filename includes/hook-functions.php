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

/**
 * since 1.0.0
 * @param $data
 */
function serial_numbers_edit_serial_number( $data ) {
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'wcsn_edit_serial_number' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'wc-serial-numbers' ), __( 'Error', 'wc-serial-numbers' ), array( 'response' => 403 ) );
	}

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( __( 'You do not have permission to update account', 'wc-serial-numbers' ), __( 'Error', 'wc-serial-numbers' ), array( 'response' => 403 ) );
	}
	$id               = empty( $data['id'] ) ? '' : absint( $data['id'] );
	$product_id       = empty( $data['product_id'] ) ? '' : absint( $data['product_id'] );
	$serial_key       = empty( $data['serial_key'] ) ? '' : sanitize_textarea_field( $data['serial_key'] );
	$activation_limit = empty( $data['activation_limit'] ) ? '' : absint( $data['activation_limit'] );
	$expire_date      = empty( $data['expire_date'] ) ? '' : sanitize_text_field( $data['expire_date'] );
	$serial_id        = Pluginever\SerialNumbers\SerialNumber::insert( [
		'id'               => $id,
		'product_id'       => $product_id,
		'serial_key'       => serial_numbers_encrypt( $serial_key ),
		'activation_limit' => $activation_limit,
		'expire_date'      => $expire_date,
	] );

	if ( is_wp_error( $serial_id ) ) {
		serial_numbers_admin_notice( $serial_id->get_error_message(), 'error' );
		wp_redirect( admin_url( 'admin.php?page=wc-serial-numbers&serial_numbers_action=add_serial_number' ) );
		exit();
	}

	$message = '';
	if ( $id == $serial_id ) {
		$message = __( 'Serial Number has updated successfully' );
	} else {
		$message = __( 'Serial Number has inserted successfully' );
	}
	serial_numbers_admin_notice( $message );
	wp_redirect( add_query_arg( [
		'wcsn-action' => 'edit_serial_number',
		'serial'      => $serial_id,
	], admin_url( 'admin.php?page=wc-serial-numbers' ) ) );
	exit();
}

add_action( 'serial_numbers_admin_post_edit_serial_number', 'serial_numbers_edit_serial_number' );
