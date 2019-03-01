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
					<li><?php _e( 'Serial Email:', 'wc-serial-numbers' ); ?> <strong><?php echo esc_html( $serial_number->activation_email ); ?></strong></li>
					<li><?php _e( 'Serial Key:', 'wc-serial-numbers' ); ?> <strong><?php echo esc_html( $serial_number->serial_key ); ?></strong></li>
					<li><?php _e( 'Validity:', 'wc-serial-numbers' ); ?> <strong><?php echo ! empty( $serial_number->validity ) ? sprintf( _n( '%s Day', '%s Days', $serial_number->validity, 'wc-serial-numbers' ), number_format_i18n( $serial_number->validity ) ) : __( 'Never expire', 'wc-serial-numbers' ); ?></strong></li>
					<li><?php _e( 'Activation Limit:', 'wc-serial-numbers' ); ?> <strong><?php echo empty( $serial_number->activation_limit ) ? __( 'Unlimited', 'wc-serial-numbers' ) : intval( $serial_number->activation_limit ); ?></strong></li>
				</ul>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
