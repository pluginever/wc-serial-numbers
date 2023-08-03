<?php
/**
 * Admin View: Add Key
 *
 * @since   1.5.6
 * @package WooCommerce Serial Numbers
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wrap woocommerce pev-wrap">
	<h2>
		<?php esc_html_e( 'Add Serial Key', 'wc-serial-numbers' ); ?>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=wc-serial-numbers' ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Go Back', 'wc-serial-numbers' ); ?>
		</a>
	</h2>

	<p>
		<?php esc_html_e( 'This section allows you to add a new key that can be sold to customers. Additionally, you can choose to associate this key with either a new order or an existing order.', 'wc-serial-numbers' ); ?>
	</p>

	<form id="wcsn-add-key-form" action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" method="post">
		<div class="pev-poststuff">
			<div class="column-1">
				<table class="form-table">
					<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="product_id">
								<?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?>
								<abbr title="required"></abbr>
							</label>
						</th>
						<td>
							<select name="product_id" id="product_id" class="wcsn-select2" data-action="wcsn_ajax_search" data-type="product" required="required" data-placeholder="<?php esc_html_e( 'Select Product', 'wc-serial-numbers' ); ?>">
								<option value=""><?php esc_html_e( 'Select Product', 'wc-serial-numbers' ); ?></option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Choose the product to which this key is applicable. The key will be associated with this product for sale.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<label for="serial_key">
								<?php esc_html_e( 'Serial key', 'wc-serial-numbers' ); ?>
								<abbr title="required"></abbr>
							</label>
						</th>
						<td>
							<textarea name="serial_key" id="serial_key" required="required" placeholder="SERIAL-ABC-DEF-GHI"></textarea>
							<p class="description">
								<?php esc_html_e( 'Enter the unique serial key you want to sell. This key will be sent to the customer after the order status is marked as complete. e.g. 4CE0460D0G-4CE0460D1G-4CE0460D2G', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>

					<?php if ( wcsn_is_software_support_enabled() ) : ?>
						<tr valign="top">
							<th scope="row">
								<label for="activation_limit"><?php esc_html_e( 'Activation limit', 'wc-serial-numbers' ); ?></label>
							</th>
							<td>
								<input type="number" name="activation_limit" id="activation_limit" min="0" step="1" placeholder="<?php esc_attr_e( 'e.g. 5', 'wc-serial-numbers' ); ?>"/>
								<p class="description">
									<?php esc_html_e( 'For software products, specify the maximum number of times the key can be used to activate the software. If the product is not software, you can leave this field blank.', 'wc-serial-numbers' ); ?>
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
								<input type="number" name="validity" id="validity" min="0" step="1" placeholder="<?php esc_attr_e( 'e.g. 365', 'wc-serial-numbers' ); ?>"/>
								<p class="description"><?php esc_html_e( 'For software products, enter the number of days the key will be valid from the purchase date. If the key should have a lifetime validity, leave this field blank.', 'wc-serial-numbers' ); ?></p>
							</td>
						</tr>
					<?php endif; ?>

					<tr valign="top">
						<th scope="row">
							<label for="status">
								<?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?>
								<abbr title="required"></abbr>
							</label>
						</th>
						<td>
							<p>
								<label>
									<input type="radio" name="status" value="new" checked="checked"/>
									<?php esc_html_e( 'Set as available for selling: The key will be available for purchase by customers.', 'wc-serial-numbers' ); ?>
								</label>
							</p>
							<p>
								<label>
									<input type="radio" name="status" value="create_order"/>
									<?php esc_html_e( 'Create a new corresponding order for this key: This option generates a new order specifically for this key.', 'wc-serial-numbers' ); ?>
								</label>
							</p>
							<p>
								<label>
									<input type="radio" name="status" value="existing_order"/>
									<?php esc_html_e( 'Associate this key with an existing order: If the customer has already made a purchase and wants to associate this key with that order, select this option.', 'wc-serial-numbers' ); ?>
								</label>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="order_id">
								<?php esc_html_e( 'Order', 'wc-serial-numbers' ); ?>
								<abbr title="required"></abbr>
							</label>
						</th>
						<td>
							<select name="order_id" id="order_id" class="wcsn-select2" data-action="wcsn_ajax_search" data-type="order" required="required" placeholder="<?php esc_html_e( 'Select Order', 'wc-serial-numbers' ); ?>">
								<option value=""><?php esc_html_e( 'Select Order', 'wc-serial-numbers' ); ?></option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Select the order to which this key should be associated. The key will be sent to the customer after the order status is marked as complete.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>

					<!--customer-->
					<tr>
						<th scope="row">
							<label for="customer_id">
								<?php esc_html_e( 'Customer', 'wc-serial-numbers' ); ?>
								<abbr title="required"></abbr>
							</label>
						</th>
						<td>
							<select name="customer_id" id="customer_id" class="wcsn-select2" data-action="wcsn_ajax_search" data-type="customer" required="required" placeholder="<?php esc_html_e( 'Select Customer', 'wc-serial-numbers' ); ?>">
								<option value=""><?php esc_html_e( 'Select Customer', 'wc-serial-numbers' ); ?></option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Select the customer to which this key should be associated. The key will be sent to the customer after the order status is marked as complete.', 'wc-serial-numbers' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
						</th>
						<td>
							<?php wp_nonce_field( 'wcsn_add_key' ); ?>
							<input type="hidden" name="action" value="wcsn_add_key"/>
							<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Add Key', 'wc-serial-numbers' ); ?>"/>
						</td>

					</tr>

					</tbody>
				</table>
			</div><!-- .column-1 -->
			<div class="column-2">
				<?php if ( WCSN()->is_premium_active() ) : ?>
					<?php
					$features = array(
						__( 'Create and assign license keys for WooCommerce variable products.', 'wc-serial-numbers' ),
						__( 'Generate bulk license keys with your custom key generator rule.', 'wc-serial-numbers' ),
						__( 'Random & sequential key order for the generator rules.', 'wc-serial-numbers' ),
						__( 'Automatic license key generator to auto-create & assign keys with orders.', 'wc-serial-numbers' ),
						__( 'License key management option from the order page with required actions.', 'wc-serial-numbers' ),
						__( 'Support for bulk import/export of license keys from/to CSV.', 'wc-serial-numbers' ),
						__( 'Send Serial Keys via SMS with Twilio.', 'wc-serial-numbers' ),
						__( 'Option to sell license keys even if there are no available keys in the stock.', 'wc-serial-numbers' ),
						__( 'Custom deliverable quantity to deliver multiple keys with a single product.', 'wc-serial-numbers' ),
						__( 'Manual delivery option to manually deliver license keys instead of automatic.', 'wc-serial-numbers' ),
						__( 'Email Template to easily and quickly customize the order confirmation & low stock alert email.', 'wc-serial-numbers' ),
						__( 'Many more ...', 'wc-serial-numbers' ),
					);
					?>
					<div class="pev-panel promo-panel">
						<h3><?php esc_html_e( 'Want More?', 'wc-serial-numbers' ); ?></h3>
						<p><?php esc_attr_e( 'This plugin offers a premium version which comes with the following features:', 'wc-serial-numbers' ); ?></p>
						<ul>
							<?php foreach ( $features as $feature ) : ?>
								<li>- <?php echo esc_html( $feature ); ?></li>
							<?php endforeach; ?>
						</ul>
						<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=plugin-settings&utm_medium=banner&utm_campaign=upgrade&utm_id=wc-serial-numbers" class="button" target="_blank"><?php esc_html_e( 'Upgrade to PRO', 'wc-serial-numbers' ); ?></a>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</form>
</div>
