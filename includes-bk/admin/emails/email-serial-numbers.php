<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<?php
$heading_text         = apply_filters( 'wcsn_heading_text', __( 'Serial Numbers', 'wc-serial-numbers' ) );
$table_column_heading = apply_filters( 'wcsn_table_column_heading', __( 'Serial Number', 'wc-serial-numbers' ) );
$serial_key_label     = apply_filters( 'wcsn_serial_key_label', __( 'Serial Key:', 'wc-serial-numbers' ) );
$serial_email_label   = apply_filters( 'wcsn_serial_email_label', __( 'Serial Email:', 'wc-serial-numbers' ) );

$show_validity         = apply_filters( 'wcsn_show_validity', true );
$show_activation_limit = apply_filters( 'wcsn_show_activation_limit', true );
?>

<h2 class="woocommerce-order-downloads__title"><?php echo esc_html( $heading_text ); ?></h2>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;" border="1">
	<thead>
	<tr>
		<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></th>
		<th class="td" scope="col" style="text-align:left;"><?php echo esc_html( $table_column_heading ); ?></th>
	</tr>
	</thead>

	<?php foreach ( $serial_numbers as $serial_number ) : ?>
		<tr>
			<td class="td" style="text-align:text-align:left;">
				<a href="<?php echo esc_url( get_permalink( $serial_number->product_id ) ); ?>"><?php echo wp_kses_post( get_the_title( $serial_number->product_id ) ); ?></a>
			</td>
			<td class="td" style="text-align:text-align:left;">
				<ul>
					<li><strong><?php echo esc_html( $serial_key_label ); ?></strong> <br><?php echo wcsn_decrypt( sanitize_textarea_field( $serial_number->serial_key ) ); ?></li>
					<li><strong><?php echo esc_html( $serial_email_label ); ?></strong> <br><?php echo esc_html( $serial_number->activation_email ); ?></li>
					<?php if ( $show_validity ): ?>
						<li><strong><?php _e( 'Validity:', 'wc-serial-numbers' ); ?></strong> <br><?php echo wcsn_get_serial_expiration_date( $serial_number ); ?></li>
					<?php endif; ?>
					<?php if ( $show_activation_limit ): ?>
						<li><strong><?php _e( 'Activation Limit:', 'wc-serial-numbers' ); ?></strong> <br><?php echo empty( $serial_number->activation_limit ) ? __( 'Unlimited', 'wc-serial-numbers' ) : intval( $serial_number->activation_limit ); ?></li>
					<?php endif; ?>
				</ul>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
