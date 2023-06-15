<?php
/**
 * The template for editing a key.
 *
 * @package WooCommerceSerialNumbers/Admin/Views
 * @version 1.4.6
 * @var \WooCommerceSerialNumbers\Models\Key $key
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wrap woocommerce">
	<?php if ( $key->exists() ) : ?>
		<h2>
			<?php esc_html_e( 'Edit serial key', 'wc-serial-numbers' ); ?>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wc-serial-numbers&add' ) ); ?>" class="add-serial-title page-title-action">
				<?php esc_html_e( 'Add New', 'wc-serial-numbers' ); ?>
			</a>
		</h2>
	<?php else : ?>
		<h2><?php esc_html_e( 'Add serial key', 'wc-serial-numbers' ); ?></h2>
	<?php endif; ?>
	<hr class="wp-header-end">

	<?php if ( ! wc_serial_numbers()->is_premium_active() ) : ?>
		<div class="notice notice-warning">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
					/* translators: %s: link to the pro version */
						__( 'You are using the free version of WooCommerce Serial Numbers. <a href="%s" target="_blank">Upgrade to Pro</a> to get more features.', 'wc-serial-numbers' ),
						esc_url( wc_serial_numbers()->get_premium_url() . '?utm_source=create_serial_page&utm_medium=button&utm_campaign=wc-serial-numbers&utm_content=View%20Details' )
					)
				);
				?>
			</p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row">
					<label for="product_id"><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></label>
				</th>
				<td>
					<select name="product_id" id="product_id" class="regular-text wcsn_search_product" required="required" placeholder="<?php esc_html_e( 'Select Product', 'wc-serial-numbers' ); ?>">
						<?php
						echo sprintf(
							'<option value="%d" selected="selected">%s</option>',
							esc_attr( $key->get_product_id() ),
							esc_html( $key->get_product_title() )
						);
						?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select the product for which this key is applicable.', 'wc-serial-numbers' ); ?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="serial_key">
						<?php esc_html_e( 'Serial key', 'wc-serial-numbers' ); ?>
					</label>
				</th>

				<td>
					<textarea name="serial_key" id="serial_key" class="regular-text" required="required" placeholder="######-######-######-######"><?php echo wp_kses_post( $key->get_key() ); ?></textarea>
					<p class="description">
						<?php esc_html_e( 'Enter secret key, supports multiline.  For example: 4CE0460D0G-4CE0460D1G-4CE0460D2G', 'wc-serial-numbers' ); ?>
					</p>
				</td>
			</tr>
			<?php if ( wcsn_is_software_support_enabled() ) : ?>
				<tr valign="top">
					<th scope="row">
						<label for="activation_limit">
							<?php esc_html_e( 'Activation limit', 'wc-serial-numbers' ); ?>
							<?php echo wc_help_tip( esc_html__( 'Maximum number of times the key can be used to activate the software. If the product is not software, keep it blank.', 'wc-serial-numbers' ), true ); ?>
						</label>
					</th>
					<td>
						<?php echo sprintf( '<input name="activation_limit" id="activation_limit" class="regular-text" type="number" value="%d" autocomplete="off">', $key->get_activation_limit() ); ?>
						<p class="description">
							<?php esc_html_e( 'Maximum number of times the key can be used to activate the software. If the product is not software, keep it blank.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="validity">
							<?php esc_html_e( 'Valid for (days)', 'wc-serial-numbers' ); ?>
						</label>
					</th>
					<td>
						<?php echo sprintf( '<input name="validity" id="validity" class="regular-text" type="number" value="%d">', esc_attr( $key->get_validity() ) ); ?>
						<p class="description">
							<?php esc_html_e( 'Number of days the key will be valid from the purchase date. Leave blank for lifetime validity.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>
			<?php endif; ?>
			<?php if ( $key->exists() ) : ?>
				<!-- status -->
				<tr>
					<th scope="row">
						<label for="status">
							<?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?>
						</label>
					</th>
					<td>
						<select id="status" name="status" class="regular-text">
							<?php foreach ( wcsn_get_key_statuses() as $status => $option ) : ?>
								<?php echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $status ), selected( $key->get_status(), $status, false ), esc_html( $option ) ); ?>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Status of the serial number.', 'wc-serial-numbers' ); ?></p>
					</td>
				</tr>
				<!-- order -->
				<tr>
					<th scope="row">
						<label for="order_id">
							<?php esc_html_e( 'Order ID', 'wc-serial-numbers' ); ?>
						</label>
					</th>
					<td>
						<select name="order_id" id="order_id" class="regular-text wcsn_search_order" required="required" placeholder="<?php esc_html_e( 'Select Order', 'wc-serial-numbers' ); ?>">
							<?php
							echo sprintf(
								'<option value="%d" selected="selected">%s</option>',
								esc_attr( $key->get_order_id() ),
								esc_html( $key->get_order_title() )
							);
							?>
						<p class="description"><?php esc_html_e( 'The order to which the serial number will be assigned.', 'wc-serial-numbers' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="2">
					<input type="hidden" name="action" value="wc_serial_numbers_edit_key">
					<input type="hidden" name="id" value="<?php echo esc_attr( $key->get_id() ); ?>">
					<?php wp_nonce_field( 'wc_serial_numbers_edit_key' ); ?>
					<?php submit_button( $key->exists() ? esc_html__( 'Update', 'wc-serial-numbers' ) : esc_html__( 'Create', 'wc-serial-numbers' ), 'primary' ); ?>
				</td>
			</tr>
			</tfoot>
		</table>
	</form>

</div>
