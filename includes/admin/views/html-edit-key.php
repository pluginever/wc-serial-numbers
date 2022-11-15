<?php
/**
 * Admin view: Admin edit key.
 *
 * @package: WooCommerceSerialNumbers
 * @var Key $key The key object.
 */

use WooCommerceSerialNumbers\Helper;
use WooCommerceSerialNumbers\Key;

defined( 'ABSPATH' ) || exit();
$delete_url = wp_nonce_url(
	add_query_arg(
		array(
			'page'   => 'wc-serial-numbers',
			'action' => 'delete',
			'key_id' => $key->get_id(),
		),
		admin_url( 'admin.php' )
	),
	'wcsn-delete-key'
);
?>

<div class="wrap woocommerce">
	<?php if ( $key->exists() ) : ?>
		<h2><?php esc_html_e( 'Edit serial key', 'wc-serial-numbers' ); ?></h2>
	<?php else : ?>
		<h2><?php esc_html_e( 'Add serial key', 'wc-serial-numbers' ); ?></h2>
	<?php endif; ?>
	<hr class="wp-header-end">
	<form method="post" class="serial-numbers__form-wrap" id="wcsn-key-form">
		<table class="form-table">
			<tbody>
			<tr valign="top" class="form-field">
				<th scope="row">
					<label for="product_id"><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></label>
				</th>
				<td>
					<select name="product_id" id="product_id" class="regular-text wcsn_search_product" required="required" placeholder="<?php esc_html_e( 'Select Product', 'wc-serial-numbers' ); ?>">
						<?php
						if ( $key->get_product_id() ) {
							echo sprintf(
								'<option value="%1$s" selected="selected">(#%1$s) %2$s</option>',
								esc_attr( $key->get_product_id() ),
								esc_html( $key->get_product_title() )
							);
						}
						?>
					</select>
					<?php if ( \WooCommerceSerialNumbers\Plugin::is_pro_active() ) : ?>
						<p class="description"><?php esc_html_e( 'Select a product to add a serial number.', 'wc-serial-numbers' ); ?></p>
					<?php else : ?>
						<p class="description"><?php esc_html_e( 'Select a product to add a serial number. Free version does not support variations & subscription products. Upgrade to premium for more features.', 'wc-serial-numbers' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top" class="form-field">
				<th scope="row">
					<label for="serial_key">
						<?php esc_html_e( 'Serial Key', 'wc-serial-numbers' ); ?>
					</label>
				</th>

				<td>
					<textarea
						name="serial_key"
						id="serial_key"
						class="large-text"
						required="required"
						placeholder="RHY9K-QYWBC-8DTD9-FXJCH-47RTQ"
						rows="5"
						cols="50"><?php echo esc_textarea( $key->get_serial_key( 'view' ) ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Unique key per product. e.g., RHY9K-QYWBC-8DTD9-FXJCH-47RTQ. Supports multiline.', 'wc-serial-numbers' ); ?></p>
				</td>
			</tr>
			<tr valign="top" class="form-field">
				<th scope="row">
					<label for="validity">
						<?php esc_html_e( 'Valid for (days)', 'wc-serial-numbers' ); ?>
					</label>
				</th>
				<td>
					<?php echo sprintf( '<input name="validity" id="validity" class="regular-text" type="number" value="%d">', esc_attr( $key->get_validity() ) ); ?>
					<p class="description"><?php esc_html_e( 'Number of days the key will be valid from the date of purchase.', 'wc-serial-numbers' ); ?></p>
				</td>
			</tr>
			<?php if ( Helper::is_software_support_enabled() ) : ?>

				<tr valign="top" class="form-field">
					<th scope="row">

						<label for="activation_limit">
							<?php esc_html_e( 'Activation limit', 'wc-serial-numbers' ); ?>
						</label>
					</th>
					<td>
						<?php echo sprintf( '<input name="activation_limit" id="activation_limit" class="regular-text" type="number" value="%d" autocomplete="off">', esc_attr( $key->get_activation_limit() ) ); ?>
						<p class="description"><?php esc_html_e( 'Maximum number of times the key can be used to activate the software. If the product is not software, keep it blank.', 'wc-serial-numbers' ); ?></p>
					</td>
				</tr>

			<?php endif; ?>
			<tr valign="top" class="form-field">
				<th scope="row">
					<label for="status">
						<?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?>
					</label>
				</th>
				<td>
					<select id="status" name="status" class="regular-text">
						<?php foreach ( Key::get_statuses() as $status => $option ) : ?>
							<?php echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $status ), selected( $key->get_status(), $status, false ), esc_html( $option ) ); ?>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Serial key status, set as available for new key.', 'wc-serial-numbers' ); ?></p>
				</td>
			</tr>
			<tr valign="top" class="form-field">
				<th scope="row">
					<label for="order_id">
						<?php esc_html_e( 'Order', 'wc-serial-numbers' ); ?>
					</label>
				</th>
				<td>
					<select name="order_id" id="order_id" class="regular-text wcsn_search_order" required="required" disabled="disabled"
							placeholder="<?php esc_html_e( 'Select Order', 'wc-serial-numbers' ); ?>">
						<?php
						if ( $key->get_order_id() ) {
							echo sprintf(
								'<option value="%1$s" selected="selected">%2$s</option>',
								esc_attr( $key->get_order_id() ),
								esc_html( $key->get_order_title() )
							);
						}
						?>
					</select>
					<p class="description"><?php esc_html_e( 'Set key status to sold for assigning with order. If the order does not contains the product key will not be added with the order. ', 'wc-serial-numbers' ); ?></p>
				</td>
			</tr>

			</tbody>
		</table>

		<p class="form-field">
			<?php if ( $key->exists() ) : ?>
				<?php echo sprintf( '<input type="hidden" name="id" value="%d">', esc_attr( $key->get_id() ) ); ?>
				<input type="submit" name="serial_numbers_edit" class="button-primary" value="<?php esc_attr_e( 'Update Key', 'wc-serial-numbers' ); ?>"/>
				<span id="delete-link">
					<a class="delete" href="<?php echo esc_attr( $delete_url ); ?>">
						<?php esc_html_e( 'Delete Key', 'wc-serial-numbers' ); ?>
					</a>
				</span>
			<?php else : ?>
				<input type="submit" name="serial_numbers_edit" class="button-primary" value="<?php esc_attr_e( 'Add Key', 'wc-serial-numbers' ); ?>"/>
			<?php endif ?>
		</p>
	</form>
</div>
