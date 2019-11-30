<?php
defined( 'ABSPATH' ) || exit();
$serial_numbers = wcsn_get_serial_numbers( [
	'order_id' => $order->ID
] );

if ( empty( $serial_numbers ) ) {
	sprintf( '<p>%s</p>', __( 'No serial numbers associated with the order', 'wc-serial-numbers' ) );
}

if ( ! empty( $serial_numbers ) ):?>
	<table class="widefat fixed" cellspacing="0">
		<thead>
		<tr>
			<th><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></th>
			<th><?php esc_html_e( 'Serial Key', 'wc-serial-numbers' ); ?></th>
			<th><?php esc_html_e( 'Activation Limit', 'wc-serial-numbers' ); ?></th>
			<th><?php esc_html_e( 'Expire Date', 'wc-serial-numbers' ); ?></th>
			<th><?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?></th>
			<th><?php esc_html_e( 'Actions', 'wc-serial-numbers' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php $i = 0; ?>
		<?php foreach ( $serial_numbers as $serial_number ) : ?>
			<tr class="<?php echo ( $i % 2 == 0 ) ? 'alternate' : '' ?>">
				<?php
				echo sprintf( '<td><a href="%s" target="_blank">#%d - %s</a></td>', get_edit_post_link( $serial_number->product_id ), $serial_number->product_id, get_the_title( $serial_number->product_id ) );
				echo sprintf( '<td>%s</td>', $serial_number->serial_key );
				echo sprintf( '<td>%s</td>', $serial_number->activation_limit );
				echo sprintf( '<td>%s</td>', wcsn_get_serial_expiration_date($serial_number) );
				echo sprintf( '<td>%s</td>', $serial_number->status );
				echo sprintf( '<td>%s</td>', '&mdash;' );
				?>
			</tr>
			<?php $i ++; ?>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif;
