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
<div class="wrap pev-wrap woocommerce">
	<h1 class="wp-heading-inline">
		<?php if ( $key->exists() ) : ?>
			<?php esc_html_e( 'Edit Serial Key', 'wc-serial-numbers' ); ?>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wc-serial-numbers&add' ) ); ?>" class="page-title-action">
				<?php esc_html_e( 'Add Another', 'wc-serial-numbers' ); ?>
			</a>
		<?php endif; ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wc-serial-numbers' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Go Back', 'wc-serial-numbers' ); ?>
		</a>
	</h1>

	<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
		<div class="pev-poststuff">
			<div class="column-1">
				<div class="pev-card">
					<div class="pev-card__header">
						<h2 class="pev-card__title"><?php esc_html_e( 'Key Details', 'wc-serial-numbers' ); ?></h2>
					</div>
					<div class="pev-card__body form-inline">

						<div class="pev-form-field">
							<label for="product_id">
								<?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?>
								<abbr title="required"></abbr>
							</label>
							<select name="product_id" id="product_id" class="wcsn_search_product" required="required" placeholder="<?php esc_html_e( 'Select Product', 'wc-serial-numbers' ); ?>">
								<?php
								printf(
									'<option value="%d" selected="selected">%s</option>',
									esc_attr( $key->get_product_id() ),
									esc_html( $key->get_product_title() )
								);
								?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Select the product for which this key is applicable.', 'wc-serial-numbers' ); ?>
							</p>
						</div>

						<div class="pev-form-field">
							<label for="serial_key">
								<?php esc_html_e( 'Serial key', 'wc-serial-numbers' ); ?>
								<abbr title="required"></abbr>
							</label>
							<textarea name="serial_key" id="serial_key" required="required" placeholder="serial-####-####-####"><?php echo wp_kses_post( $key->get_key() ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Enter your serial key, also supports multiline.  For example: 4CE0460D0G-4CE0460D1G-4CE0460D2G', 'wc-serial-numbers' ); ?>
							</p>
						</div>

						<?php if ( wcsn_is_software_support_enabled() ) : ?>
							<div class="pev-form-field">
								<label for="activation_limit"><?php esc_html_e( 'Activation limit', 'wc-serial-numbers' ); ?></label>

								<input type="number" name="activation_limit" id="activation_limit" value="<?php echo esc_attr( $key->get_activation_limit() ); ?>" min="0" step="1"/>
								<p class="description">
									<?php esc_html_e( 'Maximum number of times the key can be used to activate the software. If the product is not software, keep it blank.', 'wc-serial-numbers' ); ?>
								</p>
							</div>

							<div class="pev-form-field">
								<label for="validity">
									<?php esc_html_e( 'Valid for', 'wc-serial-numbers' ); ?>
								</label>
								<div class="pev-form-field__group">
									<input type="number" name="validity" id="validity" value="<?php echo esc_attr( $key->get_validity() ); ?>" min="0" step="1"/>
									<div class="addon">
										<?php esc_html_e( 'Days', 'wc-serial-numbers' ); ?>
									</div>
								</div>
								<p class="description"><?php esc_html_e( 'Number of days the key will be valid from the purchase date. Leave it blank for lifetime validity.', 'wc-serial-numbers' ); ?></p>
							</div>

						<?php endif; ?>

						<div class="pev-form-field">
							<label for="status">
								<?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?>
							</label>
							<select id="status" name="status">
								<?php foreach ( wcsn_get_key_statuses() as $status => $option ) : ?>
									<?php printf( '<option value="%s" %s>%s</option>', esc_attr( $status ), selected( $key->get_status(), $status, false ), esc_html( $option ) ); ?>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Serial key status auto-updates with order status. Avoid manual changes.', 'wc-serial-numbers' ); ?></p>
						</div>

						<div class="pev-form-field">
							<label for="order_id">
								<?php esc_html_e( 'Order ID', 'wc-serial-numbers' ); ?>
							</label>
							<select name="order_id" id="order_id" class="wcsn_search_order" required="required" placeholder="<?php esc_html_e( 'Select Order', 'wc-serial-numbers' ); ?>">
								<?php
								printf(
									'<option value="%d" selected="selected">%s</option>',
									esc_attr( $key->get_order_id() ),
									esc_html( $key->get_order_title() )
								);
								?>
							</select>
							<p class="description"><?php esc_html_e( 'The order to which the serial number will be assigned.', 'wc-serial-numbers' ); ?></p>
						</div>

					</div>
				</div>
				<!--todo add recent activations-->
			</div><!-- .column-1 -->

			<div class="column-2">
				<div class="pev-card">
					<div class="pev-card__header">
						<h2 class="pev-card__title"><?php esc_html_e( 'Actions', 'wc-serial-numbers' ); ?></h2>
					</div>
					<div class="pev-card__footer">
						<?php if ( $key->exists() ) : ?>
							<a class="del" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'delete', admin_url( 'admin.php?page=wc-serial-numbers&id=' . $key->get_id() ) ), 'bulk-keys' ) ); ?>"><?php esc_html_e( 'Delete', 'wc-serial-numbers' ); ?></a>
						<?php endif; ?>
						<button class="button button-primary"><?php esc_html_e( 'Save Key', 'wc-serial-numbers' ); ?></button>
					</div>
				</div>

				<?php if ( $key->get_order() ) : ?>
					<div class="pev-card">
						<div class="pev-card__header">
							<h2 class="pev-card__title"><?php esc_html_e( 'Customer details', 'wc-serial-numbers' ); ?></h2>
						</div>
						<div class="pev-card__body">
							<table class="table-data">
								<tbody>
								<tr>
									<th>
										<?php esc_html_e( 'Name', 'wc-serial-numbers' ); ?>
									</th>
									<td>
										<?php echo esc_html( $key->get_order()->get_formatted_billing_full_name() ); ?>
									</td>
								</tr>
								<tr>
									<th>
										<?php esc_html_e( 'Email', 'wc-serial-numbers' ); ?>
									</th>
									<td>
										<?php echo esc_html( $key->get_order()->get_billing_email() ); ?>
									</td>
								</tr>
								<tr>
									<th>
										<?php esc_html_e( 'Address', 'wc-serial-numbers' ); ?>
									</th>
									<td>
										<?php echo wp_kses_post( $key->get_order()->get_formatted_billing_address() ); ?>
									</td>
								</tr>

								<tr>
									<th>
										<?php esc_html_e( 'Phone', 'wc-serial-numbers' ); ?>
									</th>
									<td>
										<?php echo esc_html( $key->get_order()->get_billing_phone() ); ?>
									</td>
								</tr>
								<tr>
									<th>&nbsp;</th>
									<td>
										<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $key->get_order_id() . '&action=edit' ) ); ?>" class="button">
											<?php esc_html_e( 'View Order', 'wc-serial-numbers' ); ?>
										</a>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				<?php endif; ?>

			</div><!-- .column-2 -->
		</div><!-- .pev-poststuff -->

		<input type="hidden" name="action" value="wcsn_edit_key">
		<input type="hidden" name="id" value="<?php echo esc_attr( $key->get_id() ); ?>">
		<?php wp_nonce_field( 'wcsn_edit_key' ); ?>
	</form>
</div>
