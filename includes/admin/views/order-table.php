<?php defined( 'ABSPATH' ) || exit(); ?>
<?php global $serial_numbers; ?>
<?php echo sprintf( '<h2 class="woocommerce-order-downloads__title">%s</h2>', esc_html( $heading ) ); ?>

<table class="wcsn-order-table" cellspacing="0"  style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;" border="1">
	<thead>
	<tr>
		<?php echo sprintf( '<th class="td" scope="col" style="text-align:left;">%s</h2>', esc_html( $product_column ) ); ?>
		<?php echo sprintf( '<th class="td" scope="col" style="text-align:left;">%s</h2>', esc_html( $content_column ) ); ?>
	</tr>
	</thead>

	<tbody>
	<?php foreach ( $serial_numbers as $serial_number ): ?>
		<tr>
			<td class="td" style="text-align:text-align:left;">
				<a href="<?php echo esc_url( get_permalink( $serial_number->product_id ) ); ?>"><?php echo wp_kses_post( get_the_title( $serial_number->product_id ) ); ?></a>
			</td>

			<td class="td" style="text-align:text-align:left;">
				<?php
				$serial_column_content = str_replace('{serial_number}', wc_serial_numbers_decrypt_key($serial_number->serial_key), $serial_column_content);
				$serial_column_content = str_replace('{activation_email}', $order->get_billing_email(), $serial_column_content);
				$serial_column_content = str_replace('{expired_at}', wc_serial_numbers_get_expiration_date($serial_number), $serial_column_content);
				$serial_column_content = str_replace('{activation_limit}', wc_serial_numbers_get_activation_limit($serial_number), $serial_column_content);
				echo wp_kses_post(nl2br($serial_column_content));
				?>
			</td>

		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

