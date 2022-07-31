<?php
// don't call the file directly.
use PluginEver\WooCommerceSerialNumbers\Helper;

defined( 'ABSPATH' ) || exit();
?>
<div class="wrap woocommerce">
	<?php if ( $serial_key->exists() ) : ?>
		<h2><?php esc_html_e( 'Edit Serial Number', 'woocommerce-bookings' ); ?></h2>
		<p><?php // esc_html_e( 'You can create a new booking for a customer here. This form will create a booking for the user, and optionally an associated order. Created orders will be marked as pending payment.', 'woocommerce-bookings' ); ?></p>
	<?php else : ?>
		<h2><?php esc_html_e( 'Add Serial Number', 'woocommerce-bookings' ); ?></h2>
		<p><?php // esc_html_e( 'You can create a new booking for a customer here. This form will create a booking for the user, and optionally an associated order. Created orders will be marked as pending payment.', 'woocommerce-bookings' ); ?></p>
	<?php endif; ?>
	<form method="POST" data-nonce="<?php echo esc_attr( wp_create_nonce( 'edit-serial-key' ) ); ?>">
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row">
					<label for="product_id"><?php esc_html_e( 'Product', 'woocommerce-bookings' ); ?></label>
				</th>
				<td>
					<select name="product_id" id="product_id" class="regular-text wc-product-search" required="required" placeholder="<?php esc_html_e( 'Select Product', 'wc-serial-numbers' ); ?>">
						<?php
						echo sprintf(
							'<option value="%d" selected="selected">%s</option>',
							esc_attr( $serial_key->get_product_id() ),
							esc_html( Helper::get_product_title( $serial_key->get_product_id() ) )
						);
						?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="key">
						<?php esc_html_e( 'Serial Key', 'wc-serial-numbers' ); ?>
						<?php echo wc_help_tip( esc_html__( 'Your secret number, supports multiline. E.g., d555b5ae-d9a6-41cb-ae54-361427357382', 'wc-serial-numbers' ), true ); ?>
					</label>
				</th>

				<td>

					<textarea name="key" id="key" class="regular-text" required="required" placeholder="d555b5ae-d9a6-41cb-ae54-361427357382"><?php echo $serial_key->get_key(); ?></textarea>

				</td>
			</tr>

			<?php if ( Helper::is_software_support_enabled() ) : ?>
				<tr valign="top">
					<th scope="row">

						<label for="activation_limit">
							<?php esc_html_e( 'Activation limit', 'wc-serial-numbers' ); ?>
							<?php echo wc_help_tip( esc_html__( 'Maximum number of times the key can be used to activate the software. If the product is not software, keep it blank.', 'wc-serial-numbers' ), true ); ?>
						</label>
					</th>
					<td>
						<?php echo sprintf( '<input name="activation_limit" id="activation_limit" class="regular-text" type="number" value="%d" autocomplete="off">', $serial_key->get_activation_limit() ); ?>
					</td>
				</tr>
			<?php endif; ?>

			</tbody>
		</table>
	</form>
</div>
