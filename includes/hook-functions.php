<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Disable all expired serial numbers
 *
 * since 1.0.0
 */
function wcsn_check_expired_serial_numbers() {
	global $wpdb;
	$wpdb->query( "update {$wpdb->prefix}wcsn_serial_numbers set status='expired' where expire_date != '0000-00-00 00:00:00' AND expire_date < NOW()" );
	$wpdb->query( "update {$wpdb->prefix}wcsn_serial_numbers set status='expired' where validity !='0' AND (order_date + INTERVAL validity DAY ) < NOW()" );
}

add_action( 'wcsn_hourly_event', 'wcsn_check_expired_serial_numbers' );

/**
 * Show serial number details on order details table
 *
 * @since 1.0.0
 *
 * @param $order
 */

function wcsn_order_table_serial_number_details( $order ) {

	if ( 'completed' != $order->get_status() ) {
		return;
	}

	$serial_numbers = wcsn_get_serial_numbers( [ 'order_id' => $order->get_id() ] );

	if ( empty( $serial_numbers ) ) {
		return;
	}

	wc_get_template( '/html-order-details-table.php', array( 'serial_numbers' => $serial_numbers ), '', WC_SERIAL_NUMBERS_TEMPLATES );
}

add_action( 'woocommerce_order_details_after_order_table', 'wcsn_order_table_serial_number_details' );

/**
 * Auto Complete Order
 *
 * @since 1.0.0
 *
 * @param $order
 */
function wcsn_auto_complete_order( $payment_complete_status, $order_id, $order ) {
	if ( 'yes' !== wcsn_get_settings( 'wsn_auto_complete_order', '', 'wsn_delivery_settings' ) ) {
		return;
	}

	$current_status = $order->get_status();
	// We only want to update the status to 'completed' if it's coming from one of the following statuses:
	$allowed_current_statuses = array( 'on-hold', 'pending', 'failed' );
	if ( 'processing' === $payment_complete_status && in_array( $current_status, $allowed_current_statuses ) ) {
		$items = $order->get_items();
		foreach ( $items as $item_data ) {
			$product                  = $item_data->get_product();
			$product_id               = $product->get_id();
			$is_serial_number_enabled = get_post_meta( $product_id, '_is_serial_number', true ); //Check if the serial number enabled for this product.

			if ( 'yes' == $is_serial_number_enabled ) {
				$order->update_status( 'completed' );
			}
		}
	}


}

add_action( 'woocommerce_payment_complete_order_status', 'wcsn_auto_complete_order', 10, 3 );

/**
 * Register Post types
 *
 * @since 1.0.0
 */

function wcsn_register_post_types() {
	register_post_type( 'wcsn_notification', array(
		'labels'              => false,
		'hierarchical'        => false,
		'supports'            => false,
		'public'              => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'query_var'           => false,
		'can_export'          => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'capabilities'        => array(
			'create_posts' => 'do_not_allow',
		),
		'map_meta_cap'        => true,
	) );
}

add_action( 'init', 'wcsn_register_post_types' );


/**
 * Show Admin Bar Notification Label
 *
 * @since 1.0.0
 *
 * @return bool|string
 */

function wcsn_admin_bar_notification_label() {

	global $wpdb;

	$show_notification = wcsn_get_settings( 'wsn_admin_bar_notification', 'on', 'wsn_notification_settings' );

	if ( 'off' == $show_notification ) {
		return false;
	}

	if ( wcsn_get_notifications( array(), true ) > 0 ) {
		return '<span class="wsn_admin_bar_notification"></span>';
	}

	return false;
}

add_filter( 'wcsn_admin_bar_notification_label', 'wcsn_admin_bar_notification_label' );

/**
 * Render admin bar notification list
 *
 * @param $html
 * @param $email_notification
 *
 * @return bool|false|string
 */

function wcsn_render_notification_list( $html, $email_notification = false ) {

	$show_notification        = wcsn_get_settings( 'wsn_admin_bar_notification', 'on', 'wsn_notification_settings' );
	$show_notification_number = wcsn_get_settings( 'wsn_admin_bar_notification_number', '5', 'wsn_notification_settings' );

	if ( 'on' != $show_notification ) {
		return false;
	}

	$ids = wcsn_get_notifications();

	if ( ! empty( $ids ) ) {

		$message = '';

		ob_start();
		wc_get_template( 'notification-list.php', array( 'ids' => $ids ), '', WC_SERIAL_NUMBERS_INCLUDES . '/admin/notification/' );
		$html = ob_get_clean();
	}

	if ( $email_notification ) {
		return $message;
	}

	return $html;
}

add_filter( 'wcsn_admin_bar_notification_list', 'wcsn_render_notification_list', 10, 2 );

/**
 * Update Notification on serial number created and update
 *
 * @since 1.0.0
 *
 * @param $serial_id
 * @param $product_id
 */

function wcsn_update_notification_on_list( $serial_id, $product_id ) {

	$available_numbers = wcsn_get_serial_numbers( array( 'status' => 'new', 'product_id' => $product_id ), true );

	$show_number = wcsn_get_settings( 'wsn_admin_bar_notification_number', 5, 'wsn_notification_settings' );

	$is_exists = get_page_by_title( $product_id, OBJECT, 'wcsn_notification' );

	if ( $available_numbers >= $show_number ) {

		if ( $is_exists ) {
			wp_update_post( array(
				'ID'             => $is_exists->ID,
				'post_content'   => $available_numbers,
				'post_status'    => 'draft',
				'comment_status' => 'disable',
			) );
		}

		return;
	}

	if ( $is_exists ) {
		wp_update_post( array(
			'ID'             => $is_exists->ID,
			'post_content'   => $available_numbers,
			'post_status'    => 'publish',
			'comment_status' => 'enable',
		) );

		return;
	}

	wp_insert_post( array(
		'post_type'      => 'wcsn_notification',
		'post_title'     => $product_id,
		'post_content'   => $available_numbers,
		'post_status'    => 'publish',
		'comment_status' => 'enable',
	) );

	return;
}

add_action( 'wcsn_serial_number_created', 'wcsn_update_notification_on_list', 10, 2 );
add_action( 'wcsn_serial_number_deleted', 'wcsn_update_notification_on_list', 10, 2 );



