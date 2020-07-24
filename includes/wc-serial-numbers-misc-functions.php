<?php
defined( 'ABSPATH' ) || exit();
/**
 * Get product title.
 *
 * @param $product
 *
 * @return string
 * @since 1.2.0
 */
function wc_serial_numbers_get_product_title( $product ) {
	if ( ! empty( $product ) ) {
		$product = wc_get_product( $product );
	}
	if ( $product && ! empty( $product->get_id() ) ) {
		return sprintf(
			'(#%1$s) %2$s',
			$product->get_id(),
			html_entity_decode( $product->get_formatted_name() )
		);
	}

	return '';
}


/**
 * Get Low stock products.
 *
 * @param int $stock
 *
 * @return array
 * @since 1.0.0
 */
function wc_serial_numbers_get_low_stock_products( $force = false, $stock = 10 ) {
	$transient = md5( 'wcsn_low_stock_products' . $stock );
	if ( $force || false == $low_stocks = get_transient( $transient ) ) {
		global $wpdb;
		$product_ids   = $wpdb->get_results( "select post_id, 0 as count from $wpdb->postmeta where meta_key='_is_serial_number' AND meta_value='yes'" );
		$serial_counts = $wpdb->get_results( $wpdb->prepare( "SELECT product_id, count(id) as count FROM {$wpdb->prefix}serial_numbers where status='available' AND product_id IN (select post_id from $wpdb->postmeta where meta_key='_is_serial_number' AND meta_value='yes')
																group by product_id having count < %d order by count asc", $stock ) );
		$serial_counts = wp_list_pluck( $serial_counts, 'count', 'product_id' );

//		$product_ids = wp_list_pluck( $product_ids, 'count', 'post_id' );
//		$low_stocks = array_replace( $product_ids, $serial_counts );
		$low_stocks = $serial_counts;
		set_transient( $transient, $low_stocks, time() + 60 * 20 );
	}

	return $low_stocks;
}

/**
 * Get order table.
 *
 * @param $order
 * @param bool $return
 *
 * @return false|string|void
 * @since 1.2.0
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
		echo sprintf( '<p>%s</p>', apply_filters( 'wc_serial_numbers_pending_notice', __( 'Order waiting for assigning serial numbers.', 'wc-serial-numbers' ) ) );
		return;
	}

	ob_start();
	$columns = wc_serial_numbers_get_order_table_columns();
	?>
	<table
		class="woocommerce-table woocommerce-table--order-details shop_table order_details wc-serial-numbers-order-items"
		style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;"
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
