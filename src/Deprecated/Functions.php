<?php
/**
 * Deprecated functions.
 *
 * @since 1.4.6
 * @package WooCommerceSerialNumbers/Functions
 */

defined( 'ABSPATH' ) || exit;

require_once dirname( __FILE__ ) . '/class-encryption.php';
require_once dirname( __FILE__ ) . '/class-query.php';

/**
 * Get manager role.
 *
 * @since 1.4.2
 * @return string
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_manager_role() {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_get_manager_role' );

	return wcsn_get_manager_role();
}

/**
 * Get serial number's statuses.
 *
 * since 1.2.0
 * @return array
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_serial_number_statuses() {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_get_key_statuses' );

	return wcsn_get_key_statuses();
}

/**
 * Get product title.
 *
 * @param $product
 *
 * @since 1.2.0
 * @return string
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_product_title( $product ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_get_product_title' );

	return wcsn_get_product_title( $product );
}

/**
 * Check if product enabled for selling serial numbers.
 *
 * @param $product_id
 *
 * @since 1.2.0
 * @return bool
 * @deprecated 1.4.6
 */
function wc_serial_numbers_product_serial_enabled( $product_id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_product_serial_enabled' );

	return wcsn_is_product_enabled( $product_id );
}

/**
 * Connect serial numbers with order.
 *
 * @param $order_id
 *
 * @since 1.2.0
 * @return void
 * @deprecated 1.4.6
 */
function wc_serial_numbers_order_connect_serial_numbers( $order_id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_order_connect_serial_numbers' );

	wcsn_order_update_keys( $order_id );
}

/**
 * Check if serial number is reusing.
 *
 * @since 1.2.0
 * @return bool
 * @deprecated 1.4.6
 */
function wc_serial_numbers_reuse_serial_numbers() {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_reuse_keys' );

	return wcsn_is_reusing_keys();
}

/**
 * Disconnect serial numbers from order.
 *
 * @param $order_id
 *
 * @since 1.2.0
 * @return void
 * @deprecated 1.4.6
 */
function wc_serial_numbers_order_disconnect_serial_numbers( $order_id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_order_disconnect_serial_numbers' );

	wcsn_order_update_keys( $order_id );
}

/**
 * Insert serial number.
 *
 * @param $args
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_insert_serial_number( $args ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_insert_key' );

	return wcsn_insert_key( $args );
}

/**
 * @param $args
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_update_serial_number( $args ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_insert_key' );

	return wcsn_insert_key( $args );
}

/**
 * Update status.
 *
 * @param $id
 * @param $status
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_update_serial_number_status( $id, $status ) {
	$key = wcsn_get_key( $id );
	if ( ! $key ) {
		return new WP_Error( 'invalid_data', __( 'Serial number not found.', 'wc-serial-numbers' ) );
	}
	$key->set_status( $status );

	return $key->save();
}

/**
 * Delete serial number.
 *
 * @param $id
 *
 * @since 1.2.0
 * @return bool
 * @deprecated 1.4.6
 */
function wc_serial_numbers_delete_serial_number( $id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_delete_key' );

	return wcsn_delete_key( $id );
}

/**
 * @param $id
 *
 * @since 1.2.0
 * @return mixed
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_serial_number( $id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_get_key' );

	return wcsn_get_key( $id );
}

/**
 * Get activation
 *
 * @param $args
 *
 * @since 1.2.0
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_activation( $activation_id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_get_activation' );

	return wcsn_get_activation( $activation_id );
}

/**
 * @param $args
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_insert_activation( $args ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_insert_activation' );

	return wcsn_insert_activation( $args );
}

/**
 * @param $args
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_update_activation( $args ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_insert_activation' );

	return wcsn_insert_activation( $args );
}

/**
 * @param $id
 *
 * @since 1.2.0
 * @return bool
 * @deprecated 1.4.6
 */
function wc_serial_numbers_delete_activation( $id ) {
	wc_deprecated_function( __FUNCTION__, '1.4.6', 'wcsn_delete_activation' );

	return wcsn_delete_activation( $id );
}

/**
 * @param $id
 * @param int $status
 *
 * @since 1.2.0
 * @return int|WP_Error
 * @deprecated 1.4.6
 */
function wc_serial_numbers_update_activation_status( $id, $status = 1 ) {
	// Do nothing.
}

/**
 * Encrypt serial number.
 *
 * @param string $key Serial number.
 *
 * @since 1.2.0
 * @return false|string
 * @deprecated 1.4.6
 */
function wc_serial_numbers_encrypt_key( $key ) {
	return wcsn_encrypt_key( $key );
}

/**
 * Decrypt number.
 *
 * @param string $key Serial number.
 *
 * @since 1.2.0
 * @return false|string
 * @deprecated 1.4.6
 */
function wc_serial_numbers_decrypt_key( $key ) {
	return wcsn_decrypt_key( $key );
}

/**
 * Get Low stock products.
 *
 * @param int $stock
 *
 * @since 1.0.0
 * @return array
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_low_stock_products( $force = false, $stock = 10 ) {
	return wcsn_get_stocks_count( $stock, $force );
}

/**
 * Check if software disabled.
 * @since 1.2.0
 * @return bool
 * @deprecated 1.4.6
 */
function wc_serial_numbers_software_support_disabled() {
	return ! wcsn_is_software_support_enabled();
}

/**
 * Get refund statuses.
 * @since 1.2.0
 * @return array|bool|mixed
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_revoke_statuses() {
	return wcsn_get_revoke_statuses();
}

/**
 * Get serial number user role.
 * @since 1.2.0
 * @return mixed|void
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_user_role() {
	return wcsn_get_manager_role();
}

/**
 * Get key sources.
 *
 * @since 1.2.0
 * @return mixed|void
 * @deprecated 1.4.6
 */
function wc_serial_numbers_get_key_sources() {
	return wcsn_get_key_sources();
}

/**
 * Check if order contains serial numbers.
 *
 * @param $order
 *
 * @since 1.2.0
 * @return bool|int
 * @deprecated 1.4.6
 */
function wc_serial_numbers_order_has_serial_numbers( $order ) {
	return wcsn_order_has_products( $order );
}

/**
 * Serial number order table get columns.
 *
 * @since 1.2.0
 * @return mixed|void
 */
function wc_serial_numbers_get_order_table_columns() {
	$columns = array(
		'product'          => __( 'Product', 'wc-serial-numbers' ),
		'serial_key'       => __( 'Serial Number', 'wc-serial-numbers' ),
		'activation_email' => __( 'Email', 'wc-serial-numbers' ),
		'activation_limit' => __( 'Activation Limit', 'wc-serial-numbers' ),
		'expire_date'      => __( 'Expires', 'wc-serial-numbers' ),
	);

	return apply_filters( 'wc_serial_numbers_order_table_columns', $columns );
}

/**
 * Get product stock
 *
 * @param $product_id
 *
 * @since 1.2.0
 * @deprecated 1.4.6
 * @return int
 */
function wc_serial_numbers_get_stock_quantity( $product_id ) {
	$source = get_post_meta( $product_id, '_serial_key_source', true );
	if ( 'custom_source' == get_post_meta( $product_id, '_serial_key_source', true ) || empty( $source ) ) {
		$stocks = wcsn_get_stocks_count();
		if ( isset( $stocks[ $product_id ] ) ) {
			return absint( $stocks[ $product_id ] );
		}

		return 0;
	}

	return 9999;
}


/**
 * Get order table.
 *
 * @since 1.2.0
 *
 * @param bool $return
 *
 * @param      $order
 *
 * @return false|string|void
 */
function wc_serial_numbers_get_order_table( $order, $return = false ) {
	$order_id = $order->get_id();
	if ( 'completed' !== $order->get_status( 'edit' ) ) {
		return;
	}

	//no serial numbers ordered so bail @since 1.2.1
	$total_ordered_serial_numbers = wc_serial_numbers_order_has_serial_numbers( $order );

	if ( empty( $total_ordered_serial_numbers ) ) {
		return;
	}

	$serial_numbers = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( 'order_id', intval( $order_id ) )->get();

	echo sprintf( '<h2 class="woocommerce-order-downloads__title">%s</h2>', apply_filters( 'wc_serial_numbers_order_table_heading', esc_html__( "Serial Numbers", 'wc-serial-numbers' ) ) );
	if ( empty( $serial_numbers ) ) {
		echo sprintf( '<p>%s</p>', apply_filters( 'wc_serial_numbers_pending_notice', __( 'Order is waiting for serial numbers to be assigned.', 'wc-serial-numbers' ) ) );

		return;
	}

	ob_start();
	$columns = wc_serial_numbers_get_order_table_columns();
	?>
	<table
		class="woocommerce-table woocommerce-table--order-details shop_table order_details wc-serial-numbers-order-items"
		style="width: 100%; margin-bottom: 40px;"
		cellspacing="0" cellpadding="6" border="1">
		<thead>
		<tr>
			<?php foreach ( $columns as $key => $label ) {
				echo sprintf( '<th class="td %s" scope="col" style="text-align:left;">%s</th>', sanitize_html_class( $key ), $label );
			} ?>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $serial_numbers as $serial_number ) {
			echo '<tr>';
			foreach ( $columns as $key => $label ) {
				echo '<td class="td" style="text-align:left;">';
				switch ( $key ) {
					case 'product':
						echo sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $serial_number->product_id ) ), get_the_title( $serial_number->product_id ) );
						break;
					case 'serial_key':
						echo wc_serial_numbers_decrypt_key( $serial_number->serial_key );
						break;
					case 'activation_email':
						echo $order->get_billing_email();
						break;
					case 'activation_limit':
						if ( empty( $serial_number->activation_limit ) ) {
							echo __( 'Unlimited', 'wc-serial-numbers' );
						} else {
							echo $serial_number->activation_limit;
						}
						break;
					case 'expire_date':
						if ( empty( $serial_number->validity ) ) {
							echo __( 'Lifetime', 'wc-serial-numbers' );
						} else {
							echo date( 'Y-m-d', strtotime( $serial_number->order_date . ' + ' . $serial_number->validity . ' Day ' ) );
						}
						break;

					default:
						do_action( 'wc_serial_numbers_order_table_cell_content', $key, $serial_number, $order_id );
				}
				echo '</td>';
			}
			echo '</tr>';
		} ?>

		</tbody>
	</table>
	<?php
	$output = ob_get_contents();
	ob_get_clean();
	if ( $return ) {
		return $output;
	}

	echo $output;
}

/**
 * Control software related columns
 *
 * @param $columns
 *
 * @since 1.2.0
 * @return mixed
 */
function wc_serial_numbers_control_order_table_columns( $columns ) {
	if ( wc_serial_numbers_software_support_disabled() ) {
		$software_columns = [ 'activation_email', 'activation_limit', 'expire_date' ];
		foreach ( $columns as $key => $label ) {
			if ( in_array( $key, $software_columns ) ) {
				unset( $columns[ $key ] );
			}
		}
	}

	return $columns;
}

add_filter( 'wc_serial_numbers_order_table_columns', 'wc_serial_numbers_control_order_table_columns', 99 );


/**
 * Sanitize boolean
 *
 * @param $string
 *
 * @since 1.2.0
 * @return mixed
 */
function wc_serial_numbers_validate_boolean( $string ) {
	return filter_var( $string, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
}
