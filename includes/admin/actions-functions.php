<?php
defined( 'ABSPATH' ) || exit();


/**
 * since 1.0.0
 *
 * @param $data
 */
function wc_serial_numbers_edit_serial_number( $data ) {
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
	$order_id         = empty( $data['order_id'] ) ? '' : absint( $data['order_id'] );
	$expire_date      = empty( $data['expire_date'] ) ? '' : sanitize_text_field( $data['expire_date'] );
	$order_date       = empty( $data['order_date'] ) ? '' : sanitize_text_field( $data['order_date'] );
	$status           = empty( $data['status'] ) ? 'available' : sanitize_key( $data['status'] );
	$serial_id        = wc_serial_numbers_insert_serial_number( [
		'id'               => $id,
		'product_id'       => $product_id,
		'serial_key'       => wc_serial_numbers_encrypt_serial_number( $serial_key ),
		'activation_limit' => $activation_limit,
		'expire_date'      => $expire_date,
		'status'           => $status,
		'order_id'         => $order_id,
		'order_date'       => $order_date,
	] );

	if ( is_wp_error( $serial_id ) ) {
		wc_serial_numbers_add_admin_notice( $serial_id->get_error_message(), 'error' );
		wp_redirect( admin_url( 'admin.php?page=wc-serial-numbers&serial_numbers_action=add_serial_number' ) );
		exit();
	}

	$message = '';
	if ( $id == $serial_id ) {
		$message = __( 'Serial Number has updated successfully' );
	} else {
		$message = __( 'Serial Number has inserted successfully' );
	}
	wc_serial_numbers_add_admin_notice( $message );
	wp_redirect( admin_url( 'admin.php?page=wc-serial-numbers' ) );
	exit();
}

add_action( 'wc_serial_numbers_admin_post_edit_serial_number', 'wc_serial_numbers_edit_serial_number' );

function wc_serial_numbers_order_assign_serial_number_handler() {
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'assign_serial_numbers' ) ) {
		wp_die( 'cheatin??' );
	}
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'You are not allowed to do this' );
	}
	$order_id = absint( $_REQUEST['order_id'] );
	$redirect = remove_query_arg( [
		'order_id',
		'nonce',
		'serial_numbers_action'
	] );

	if ( empty( $order_id ) ) {
		wp_redirect( $redirect );
		exit();
	}

	$assigned = wc_serial_numbers_order_assign_serial_numbers( $order_id );
	if ( $assigned ) {
		wc_serial_numbers_add_admin_notice( __( 'Serial numbers has been assigned for the order.', 'wc-serial-number' ) );
	}

	wp_redirect( $redirect );
	exit();
}

add_action( 'wc_serial_numbers_admin_get_order_assign_serial_numbers', 'wc_serial_numbers_order_assign_serial_number_handler' );

/**
 * Change status
 * since 1.0.0
 *
 * @param $data
 */
function wc_serial_numbers_inactive_serial_number_handler( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'serial_number_nonce' ) ) {
		wp_die( 'cheatin??' );
	}

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'You are not allowed to do this' );
	}

	$id = absint( $data['serial'] );

	wc_serial_numbers_change_serial_number_status( $id, 'inactive' );
	wp_redirect( admin_url( 'admin.php?page=wc-serial-numbers' ) );
	exit();
}

add_action( 'wc_serial_numbers_admin_get_inactive_serial_number', 'wc_serial_numbers_inactive_serial_number_handler' );


/**
 * Change status
 * since 1.0.0
 *
 * @param $data
 */
function wc_serial_numbers_activate_serial_number_handler( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'serial_number_nonce' ) ) {
		wp_die( 'cheatin??' );
	}

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'You are not allowed to do this' );
	}

	$id = absint( $data['serial'] );

	wc_serial_numbers_change_serial_number_status( $id, 'active' );
	wp_redirect( admin_url( 'admin.php?page=wc-serial-numbers' ) );
	exit();
}

add_action( 'wc_serial_numbers_admin_get_activate_serial_number', 'wc_serial_numbers_activate_serial_number_handler' );

/**
 * @since 1.0.0
 * @param $data
 */
function wc_serial_numbers_delete_serial_number_handler( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'serial_number_nonce' ) ) {
		wp_die( 'cheatin??' );
	}

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'You are not allowed to do this' );
	}

	$id = absint( $data['serial'] );

	wc_serial_numbers_delete_serial_number( $id );
	wp_redirect( admin_url( 'admin.php?page=wc-serial-numbers' ) );
	exit();
}

add_action( 'wc_serial_numbers_admin_get_delete_serial_number', 'wc_serial_numbers_delete_serial_number_handler' );


/**
 * Change status
 * since 1.0.0
 *
 * @param $data
 */
function wc_serial_numbers_activate_activation_handler( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'wcsn_activation_nonce' ) ) {
		wp_die( 'cheatin??' );
	}

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'You are not allowed to do this' );
	}

	$id = absint( $data['activation'] );
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wcsn_activations set active=%d WHERE id=%d", 1, $id ) );
	wp_redirect( admin_url( 'admin.php?page=wc-serial-numbers-activations' ) );
	exit();
}

add_action( 'wc_serial_numbers_admin_get_activate_activation', 'wc_serial_numbers_activate_activation_handler' );

/**
 * Change status
 * since 1.0.0
 *
 * @param $data
 */
function wc_serial_numbers_inactivate_activation_handler( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'wcsn_activation_nonce' ) ) {
		wp_die( 'cheatin??' );
	}

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'You are not allowed to do this' );
	}

	$id = absint( $data['activation'] );
	global $wpdb;

	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->wcsn_activations set active=%d WHERE id=%d", 0, $id ) );
	wp_redirect( admin_url( 'admin.php?page=wc-serial-numbers-activations' ) );
	exit();
}

add_action( 'wc_serial_numbers_admin_get_inactivate_activation', 'wc_serial_numbers_inactivate_activation_handler' );

function wc_serial_numbers_delete_activation_handler($data){
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'wcsn_activation_nonce' ) ) {
		wp_die( 'cheatin??' );
	}

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'You are not allowed to do this' );
	}

	$id = absint( $data['activation'] );
	global $wpdb;

	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->wcsn_activations WHERE id=%d", $id ) );
	wp_redirect( admin_url( 'admin.php?page=wc-serial-numbers-activations' ) );
	exit();
}
add_action( 'wc_serial_numbers_admin_get_delete_activation', 'wc_serial_numbers_delete_activation_handler' );
