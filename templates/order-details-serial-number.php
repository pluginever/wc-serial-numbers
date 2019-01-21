<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<h2><?php _e( 'Serial Number', 'wc-serial-numbers' ) ?></h2>

<table class="shop_table order_details" style="width: 100%;">

	<thead>
	<tr>
		<th style="text-align:left;width: 20%;"><strong><?php _e( 'Product', 'wc-serial-numbers' ) ?></strong></th>
		<th style="text-align:left;width: 80%;"><strong><?php _e( 'Serial Number', 'wc-serial-numbers' ) ?></strong>
		</th>
	</tr>
	</thead>

	<tbody>
	<?php

	$items = $order->get_items();

	foreach ( $items as $item_id => $item_data ) {

		$product           = $item_data->get_product();
		$product_id        = $product->get_id();
		$product_name      = $product->get_name();
		$serial_number_ids = get_post_meta( $order->get_id(), 'serial_numbers', true )[ $product_id ];

		foreach ( $serial_number_ids as $serial_number_id ) {

			$serial_number = get_the_title( $serial_number_id );
			$max_instance  = get_post_meta( $serial_number_id, 'max_instance', true );
			$validity_type = get_post_meta( $serial_number_id, 'validity_type', true );
			$validity      = get_post_meta( $serial_number_id, 'validity', true );

			?>

			<tr>
				<td style="text-align:left;width: 20%;"><?php echo $product_name ?></td>
				<td style="text-align:left;width: 80%;">
					<?php echo $serial_number ?>

					<br>

					<?php

					if ( ! empty( $max_instance ) && $max_instance > 0 ) {
						echo sprintf( __( 'Can be used: %d times', 'wc-serial-numbers' ), $max_instance );
					}

					?>

					<?php

					if ( ! empty( $validity_type ) && ! empty( $validity ) && in_array( $validity_type, array(
							'days',
							'date'
						) ) ) {

						echo '<br>';

						if ( $validity_type == 'days' ) {
							echo sprintf( __( 'Validity: %d (Days)', 'wc-serial-numbers' ), $validity );
						} elseif ( $validity_type == 'date' ) {
							echo sprintf( __( 'Validity: until %s', 'wc-serial-numbers' ), $validity );
						}

					}

					?>

				</td>
			</tr>

		<?php }

	} ?>
	</tbody>

</table>
