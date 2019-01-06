<h2><?php _e( 'Serial Number', 'wc-serial-numbers' ) ?></h2>

<table class="shop_table order_details" style="width: 100%;">

	<thead>
	<tr>
		<th style="text-align:left;width: 20%;"><strong>Product</strong></th>
		<th style="text-align:left;width: 80%;"><strong>Serial Number</strong></th>
	</tr>
	</thead>

	<tbody>
	<?php

	$items = $order->get_items();

	foreach ( $items as $item_id => $item_data ) {

		$product          = $item_data->get_product();
		$product_id       = $product->get_id();
		$product_name     = $product->get_name();
		$serial_number_id = get_post_meta( $order->get_id(), 'serial_numbers', true )[ $product_id ];
		$serial_number    = get_the_title( $serial_number_id );

		?>

		<tr>
			<td style="text-align:left;width: 20%;"><?php echo $product_name ?></td>
			<td style="text-align:left;width: 80%;"><?php echo $serial_number ?></td>
		</tr>

	<?php } ?>
	</tbody>

</table>
