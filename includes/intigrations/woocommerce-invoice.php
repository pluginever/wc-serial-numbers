<?php
defined('ABSPATH') || exit();

/**
 * WooCommerce PDF Invoices
 *
 * @param $headers
 * @param $order_id
 *
 * @return string
 * @since 1.1.1
 *
 */
function wcsn_woocommerce_invoice( $headers, $order_id ) {
	$serial_numbers = wc_serial_numbers_get_serial_numbers( [ 'order_id' => $order_id, 'number' => - 1 ] );
	if ( empty( $serial_numbers ) ) {
		return $headers;
	}
	ob_start();
	?>
	<table class="shop_table orderdetails" width="100%">
		<thead>
		<tr>
			<th colspan="7" align="left"><h2><?php _e( 'Serial Number', 'wc-serial-numbers' ); ?></h2></th>
		</tr>
		<tr>
			<th class="product"><?php _e( 'Product', 'wc-serial-numbers' ); ?></th>
			<th class="quantity"><?php _e( 'Serial Number', 'wc-serial-numbers' ); ?></th>
			<th class="quantity"><?php _e( 'Activation Limit', 'wc-serial-numbers' ); ?></th>
			<th class="quantity"><?php _e( 'Expire Date', 'wc-serial-numbers' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $serial_numbers as $serial_number ): ?>
			<tr>
				<td><?php echo get_the_title( $serial_number->product_id ); ?></td>
				<td><?php echo wc_serial_numbers_decrypt_serial_number( $serial_number->serial_key ); ?></td>
				<td><?php echo ( $serial_number->activation_limit ) ? $serial_number->activation_limit : __( 'N/A', 'wc-serial-numbers' ); ?></td>
				<td><?php echo wc_serial_numbers_get_serial_expiration_date( $serial_number ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	$content = ob_get_clean();

	return $content . $headers;
}

add_filter( 'pdf_template_table_headings', 'wcsn_woocommerce_invoice', 10, 2 );


/**
 * Support WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels plugin
 *
 * @param $find_replace
 * @param $html
 * @param $template_type
 * @param $order
 * @param $box_packing
 * @param $order_package
 *
 * @return array
 * @since 1.1.1
 *
 */
function wc_serial_numbers_wf_module_add_serial_number_list( $find_replace, $html, $template_type, $order, $box_packing, $order_package ) {
	if ( isset( $find_replace['[wfte_product_table_start]'] ) ) {
		global $post;
		$order_id       = $order->id;
		$serial_numbers = wc_serial_numbers_get_serial_numbers( [ 'order_id' => $order_id, 'number' => - 1 ] );
		if ( empty( $serial_numbers ) ) {
			return $find_replace;
		}
		ob_start();
		?>
		<table class="wfte_product_table wcsn-pdf-table">
			<thead class="wfte_product_table_head wfte_table_head_color wfte_product_table_head_bg wfte_text_center">
			<tr>
				<th class="product"><?php _e( 'Product', 'wc-serial-numbers' ); ?></th>
				<th class="quantity"><?php _e( 'Serial Number', 'wc-serial-numbers' ); ?></th>
				<th class="quantity"><?php _e( 'Activation Limit', 'wc-serial-numbers' ); ?></th>
				<th class="quantity"><?php _e( 'Expire Date', 'wc-serial-numbers' ); ?></th>
			</tr>
			</thead>
			<tbody class="wfte_payment_summary_table_body wfte_table_body_color">
			<?php foreach ( $serial_numbers as $serial_number ): ?>
				<tr>
					<td><?php echo get_the_title( $serial_number->product_id ); ?></td>
					<td><?php echo wc_serial_numbers_decrypt_serial_number( $serial_number->serial_key ); ?></td>
					<td><?php echo ( $serial_number->activation_limit ) ? $serial_number->activation_limit : __( 'N/A', 'wc-serial-numbers' ); ?></td>
					<td><?php echo wc_serial_numbers_get_serial_expiration_date( $serial_number ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<style type="text/css">
			.wfte_product_table.wcsn-pdf-table {
				margin-bottom: 30px;
			}
		</style>
		<?php
		$find_replace['[wfte_product_table_start]'] = ob_get_clean();
	}

	return $find_replace;
}

add_filter( 'wf_module_generate_template_html', 'wc_serial_numbers_wf_module_add_serial_number_list', 10, 6 );



/**
 * Support WooCommerce PDF Invoices & Packing Slips plugin
 *
 * @param $type
 * @param $order
 *
 * @return string
 * @since 1.1.1
 *
 */
function wc_serial_numbers_add_serial_number_list( $type, $order ) {
	global $post;
	$order_id       = $order->get_id();
	$serial_numbers = wc_serial_numbers_get_serial_numbers( [ 'order_id' => $order_id, 'number' => - 1 ] );
	if ( empty( $serial_numbers ) ) {
		return '';
	}
	?>
	<table class="order-details">
		<thead>
		<tr>
			<th class="product"><?php _e( 'Product', 'wc-serial-numbers' ); ?></th>
			<th class="quantity"><?php _e( 'Serial Number', 'wc-serial-numbers' ); ?></th>
			<th class="quantity"><?php _e( 'Activation Limit', 'wc-serial-numbers' ); ?></th>
			<th class="quantity"><?php _e( 'Expire Date', 'wc-serial-numbers' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $serial_numbers as $serial_number ): ?>
			<tr>
				<td><?php echo get_the_title( $serial_number->product_id ); ?></td>
				<td><?php echo wc_serial_numbers_decrypt_serial_number( $serial_number->serial_key ); ?></td>
				<td><?php echo ( $serial_number->activation_limit ) ? $serial_number->activation_limit : __( 'N/A', 'wc-serial-numbers' ); ?></td>
				<td><?php echo wc_serial_numbers_get_serial_expiration_date( $serial_number ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

add_action( 'wpo_wcpdf_before_order_details', 'wc_serial_numbers_add_serial_number_list', 10, 2 );
