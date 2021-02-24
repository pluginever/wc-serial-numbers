<?php foreach ( $serial_numbers as $serial_number ): ?>
	<tr>
		<?php foreach ( $columns as $key => $column ): ?>
			<td class="td" style="text-align:left;">
				<?php
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
						do_action( 'wc_serial_numbers_order_table_cell_content', $key, $serial_number, $order->get_id() );
				}
				?>
			</td>
		<?php endforeach; ?>
		<td>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-serial-numbers&action=edit&id=' . $serial_number->id ) ) ?>" target="_blank"><?php _e( 'Edit', 'wc-serial-numbers' ); ?></a>
		</td>
	</tr>
<?php endforeach; ?>

<?php if ( empty( $serial_numbers ) ) : ?>
	<tr>
		<td colspan="<?php echo esc_attr( $col_span ); ?>">
			<?php esc_html_e( 'No serial numbers associated with the order.', 'wc-serial-numbers' ); ?>
		</td>
	</tr>
<?php endif; ?>
