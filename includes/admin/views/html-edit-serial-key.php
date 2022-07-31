<?php

use PluginEver\WooCommerceSerialNumbers\Helper;
use PluginEver\WooCommerceSerialNumbers\Serial_Keys;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();
?>
<div class="wrap woocommerce">
	<?php if ( $serial_key->exists() ) : ?>
		<h2><?php esc_html_e( 'Edit Serial Number', 'wc-serial-numbers' ); ?></h2>
		<?php else : ?>
		<h2><?php esc_html_e( 'Add Serial Number', 'wc-serial-numbers' ); ?></h2>
		<?php endif; ?>

	<?php
	foreach ( $errors as $error ) {
			echo '<div class="error"><p>' . esc_html( $error ) . '</p></div>';
	}
	?>

	<form method="POST">
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row">
					<label for="product_id"><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></label>
				</th>
				<td>
					<select name="product_id" id="product_id" class="regular-text serial-numbers-product-search" required="required" placeholder="<?php esc_html_e( 'Select Product', 'wc-serial-numbers' ); ?>">
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

					<textarea name="serial_key" id="serial_key" class="regular-text" required="required" placeholder="d555b5ae-d9a6-41cb-ae54-361427357382"><?php echo $serial_key->get_key(); ?></textarea>

				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="expire_date"><?php esc_html_e( 'Expires at', 'wc-serial-numbers' ); ?></label>
					<?php echo wc_help_tip( esc_html__( 'After this date, the key will not be assigned with any order. Leave blank for no expiry date.', 'wc-serial-numbers' ), true ); ?>
				</th>
				<td>
					<?php echo sprintf( '<input name="date_expire" id="date_expire" class="regular-text wc-serial-numbers-select-date" type="text" autocomplete="off" value="%s">', $serial_key->get_date_expire() ); ?>
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

				<tr valign="top">
					<th scope="row">
						<label for="validity">
							<?php esc_html_e( 'Validity (days)', 'wc-serial-numbers' ); ?>
							<?php echo wc_help_tip( esc_html__( 'Number of days the key will be valid from the purchase date.', 'wc-serial-numbers' ), true ); ?>
						</label>
					</th>
					<td>
						<?php echo sprintf( '<input name="validity" id="validity" class="regular-text" type="number" value="%d">', $serial_key->get_validity() ); ?>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( ! empty( $serial_key->get_order_id() ) ) : ?>
				<tr valign="top">
					<th scope="row">
						<label for="status">
							<?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?>
							<?php echo wc_help_tip( esc_html__( 'Status of the serial number.', 'wc-serial-numbers' ), true ); ?>
						</label>
					</th>
					<td>
						<select id="status" name="status" class="regular-text serial-numbers-order-search">
							<?php foreach ( Serial_Keys::get_statuses() as $key => $option ) : ?>
								<?php echo sprintf( '<option value="%s" %s>%s</option>', $key, selected( $serial_key->get_status(), $key, false ), $option ); ?>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<!-- order -->
				<tr valign="top">
					<th scope="row">
						<label for="order_id">
							<?php esc_html_e( 'Order ID', 'wc-serial-numbers' ); ?>
							<?php echo wc_help_tip( esc_html__( 'The order to which the serial number will be assigned.', 'wc-serial-numbers' ), true ); ?>
						</label>
					</th>
					<td>
						<select name="order_id" id="order_id" class="regular-text serial-numbers-order-search" required="required" placeholder="<?php esc_html_e( 'Select Order', 'wc-serial-numbers' ); ?>">
							<?php
							echo sprintf(
								'<option value="%d" selected="selected">#%d</option>',
								esc_attr( $serial_key->get_order_id() ),
								esc_attr( $serial_key->get_order_id() ),
							);
							?>
						</select>
					</td>
				</tr>

			<?php endif; ?>

			<tr>
				<td></td>
				<td>
					<p class="submit">
						<input type="hidden" name="action" value="serial_numbers_edit_serial_number">
						<?php wp_nonce_field( 'serial_numbers_edit_key' ); ?>
						<?php if ( $serial_key->exists() ) : ?>
							<?php echo sprintf( '<input type="hidden" name="id" value="%d">', $serial_key->get_id() ); ?>
							<input type="submit" name="serial_numbers_edit" class="button-primary" value="<?php esc_attr_e( 'Update', 'wc-serial-numbers' ); ?>" />
						<?php else : ?>
							<input type="submit" name="serial_numbers_edit" class="button-primary" value="<?php esc_attr_e( 'Submit', 'wc-serial-numbers' ); ?>" />
						<?php endif ?>
					</p>
				</td>
			</tr>

			</tbody>
		</table>
	</form>
</div>
