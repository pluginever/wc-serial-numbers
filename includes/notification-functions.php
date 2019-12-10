<?php
defined( 'ABSPATH' ) || exit();

function wc_serial_numbers_admin_bar_notification_styles() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	?>
	<style>
		#wp-admin-bar-wc-serial-numbers .wsn_admin_bar_notification {
			padding-right: 25px
		}

		#wp-admin-bar-wc-serial-numbers .ever-notification {
			position: absolute;
			right: 3px;
			top: 0
		}

		#wp-admin-bar-wc-serial-numbers .ever-notification > .alert {
			background: #fff;
			padding: 0 5px 0 3px;
			border-radius: 5px;
			color: red;
			cursor: pointer
		}

		#wp-admin-bar-wc-serial-numbers .ever-notification:hover + .ever-notification-list {
			display: -webkit-box;
			display: -webkit-flex;
			display: flex
		}

		#wp-admin-bar-wc-serial-numbers .ever-notification-list {
			position: fixed;
			color: #f0fafe;
			background: #333;
			display: none;
			-webkit-box-orient: vertical;
			-webkit-box-direction: normal;
			-webkit-flex-direction: column;
			flex-direction: column;
			z-index: 999999;
			margin: -1px 0 0 -10px;
			max-height: 100%;
			overflow-y: scroll
		}

		#wp-admin-bar-wc-serial-numbers .ever-notification-list:hover {
			display: -webkit-box;
			display: -webkit-flex;
			display: flex
		}

		#wp-admin-bar-wc-serial-numbers .ever-notification-list.alert > li {
			border-left: 5px solid red;
			padding: 0 15px 0 10px;
			margin: 2px 0;
			font-size: 14px
		}

		#wp-admin-bar-wc-serial-numbers .ever-notification-list.alert > li > a {
			display: inline;
			padding: 0;
			color: #ffffff !important;
		}

	</style>

<?php }

add_action( 'admin_head', 'wc_serial_numbers_admin_bar_notification_styles' );
add_action( 'wp_head', 'wc_serial_numbers_admin_bar_notification_styles' );


/**
 * @param int $stock
 *
 * @return array
 * @since 1.0.0
 */
function wc_serial_numbers_get_low_stocked_products( $force = false, $stock = 10 ) {
	$transient = md5( 'wcsn_low_stocked_products' . $stock );
	if ( $force || false == $low_stocks = get_transient( $transient ) ) {
		global $wpdb;
		$product_ids   = $wpdb->get_results( "select post_id product_id, 0 as count from $wpdb->postmeta where meta_key='_is_serial_number' AND meta_value='yes'" );
		$serial_counts = $wpdb->get_results( $wpdb->prepare( "SELECT product_id, count(id) as count FROM $wpdb->wcsn_serials_numbers where status='available' AND product_id IN (select post_id from $wpdb->postmeta where meta_key='_is_serial_number' AND meta_value='yes') 
																group by product_id having count < %d order by count asc", $stock ) );
		$serial_counts = wp_list_pluck( $serial_counts, 'count', 'product_id' );
		$product_ids   = wp_list_pluck( $product_ids, 'count', 'product_id' );
		$low_stocks    = array_replace( $product_ids, $serial_counts );
		set_transient( $transient, $low_stocks, time() + 60 * 20 );
	}

	return $low_stocks;
}

function wc_serial_numbers_send_low_stock_notification() {
	$notification = wc_serial_numbers_get_settings( 'low_stock_notification', false );
	if ( ! $notification ) {
		return false;
	}
	$stock_threshold = wc_serial_numbers_get_settings( 'low_stock_threshold', 10 );

	$low_stocked_products = wc_serial_numbers_get_low_stocked_products( $stock_threshold );
	if ( empty( $low_stocked_products ) ) {
		return false;
	}
}

add_action( 'wcsn_hourly_event', 'wc_serial_numbers_send_low_stock_notification' );

function wc_serial_numbers_send_stock_email_notification() {
	$notification = wc_serial_numbers_get_settings( 'low_stock_notification', false );
	if ( ! $notification ) {
		return false;
	}

	$stock_threshold    = wc_serial_numbers_get_settings( 'low_stock_threshold', 10 );
	$to = wc_serial_numbers_get_settings( 'low_stock_notification_email', '' );
	if ( empty( $to ) ) {
		return false;
	}

	$low_stock_products = wc_serial_numbers_get_low_stocked_products( $stock_threshold, true );
	if ( empty( $low_stock_products ) ) {
		return false;
	}

	$subject = __( 'Serial Numbers stock running low', 'wc-serial-numbers' );
	/** $woocommerce WooCommerce */
	global $woocommerce;
	$mailer = $woocommerce->mailer();

	ob_start();
	wc_serial_numbers_get_views('email-notification-body.php', compact('low_stock_products'));
	$message = ob_get_contents();
	ob_get_clean();

	$message = $mailer->wrap_message( $subject, $message );
	$headers = apply_filters( 'woocommerce_email_headers', '', 'rewards_message' );
	$mailer->send( $to, $subject, $message, $headers, array() );

	exit();
}

add_action( 'wc_serial_numbers_daily_event', 'wc_serial_numbers_send_stock_email_notification' );



