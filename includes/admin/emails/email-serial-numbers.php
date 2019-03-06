<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<h2 class="woocommerce-order-downloads__title"><?php esc_html_e( 'Serial Numbers', 'wc-serial-numbers' ); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;" border="1">
	<thead>
	<tr>
		<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></th>
		<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Serial Number', 'wc-serial-numbers' ); ?></th>
	</tr>
	</thead>

	<?php foreach ( $serial_numbers as $serial_number ) : ?>
		<tr>
			<td class="td" style="text-align:text-align:left;">
				<a href="<?php echo esc_url( get_permalink( $serial_number->product_id ) ); ?>"><?php echo wp_kses_post( get_the_title( $serial_number->product_id ) ); ?></a>
			</td>
			<td class="td" style="text-align:text-align:left;">
				<ul>
					<li><strong><?php _e( 'Serial Email:', 'wc-serial-numbers' ); ?></strong> <br><?php echo esc_html( $serial_number->activation_email ); ?></li>
					<li><strong><?php _e( 'Serial Key:', 'wc-serial-numbers' ); ?></strong> <br><?php echo sanitize_textarea_field( $serial_number->serial_key ); ?></li>
					<li><strong><?php _e( 'Validity:', 'wc-serial-numbers' ); ?></strong> <br><?php echo wcsn_get_serial_expiration_date( $serial_number ); ?></li>
					<li><strong><?php _e( 'Activation Limit:', 'wc-serial-numbers' ); ?></strong> <br><?php echo empty( $serial_number->activation_limit ) ? __( 'Unlimited', 'wc-serial-numbers' ) : intval( $serial_number->activation_limit ); ?></li>
				</ul>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
