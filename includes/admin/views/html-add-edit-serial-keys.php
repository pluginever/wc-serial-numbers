<?php

use PluginEver\WooCommerceSerialNumbers\Serial_Keys;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

$action = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list'; //phpcs:ignore
$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore
if ( empty( $id ) && 'edit' === $action ) {
	wp_safe_redirect(
		add_query_arg(
			[ 'action' => 'add' ],
			remove_query_arg(
				array(
					'_wp_http_referer',
					'_wpnonce',
					'id',
				),
				wp_unslash( $_SERVER['REQUEST_URI'] )
			)
		)
	);
	exit;
}

$update = false;
$item   = array(
	'id'               => '',
	'key'              => '',
	'product_id'       => '',
	'order_id'         => '',
	'order_item_id'    => '',
	'activation_limit' => '',
	'status'           => 'available',
	'validity'         => '',
	'date_expire'      => '',
);

if ( ! empty( $id ) && $serial = Serial_Keys::get( $id ) ) { //phpcs:ignore
	$update = true;
	$item   = wp_parse_args( $serial->get_data(), $item );
	// $item['serial_key'] = wc_serial_numbers_decrypt_key( $item['key'] );
}

?>
	<div class="wrap wc-serial-numbers-table-wrap">
		<h1 class="wp-heading-inline">
			<?php if ( $update ) : ?>
				<?php esc_html_e( 'Update Serial Number', 'wc-serial-numbers' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'Add Serial Number', 'wc-serial-numbers' ); ?>
			<?php endif ?>
		</h1>
		<a href="<?php echo esc_url ( remove_query_arg( array( 'action', 'id' ) ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Back', 'wc-serial-numbers' ); ?>
		</a>
		<hr class="wp-header-end">

		<?php
		if ( ! is_plugin_active( 'wc-serial-numbers-pro/wc-serial-numbers-pro.php' ) ) {
			echo sprintf( '<p class="wc-serial-numbers-upgrade-box" style="background-color: #fff;">%s <a href="%s" target="_blank" class="button">%s</a></p>', esc_html__( 'Checkout the full features of WooCommerce Serial Numbers Pro.', 'wc-serial-numbers' ), 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=create_serial_page&utm_medium=button&utm_campaign=wc-serial-numbers&utm_content=View%20Details', esc_html__( 'View Details', 'wc-serial-numbers' ) );
		}
		?>

		<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
			<table class="form-table">

				<tr valign="top">
					<th scope="row">
						<label for="product_id">
							<?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?>
							<?php echo wc_help_tip( esc_html__( 'Select a product to add a serial number. Note: Free version does not support variations & subscription products.', 'wc-serial-numbers' ), true );?>
						</label>
					</th>

					<td>
						<select name="product_id" id="product_id"
						        class="regular-text wc-serial-numbers-select-product" required="required"
						        placeholder="<?php esc_html_e( 'Select Product', 'wc-serial-numbers' ); ?>">
							<?php echo sprintf( '<option value="%d" selected="selected">%s</option>', $item['product_id'], get_the_title( $item['product_id'] ) ); ?>
						</select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="key">
							<?php esc_html_e( 'Serial number', 'wc-serial-numbers' ); ?>
							<?php echo wc_help_tip( esc_html__( 'Your secret number, supports multiline. Will be encrypted before saving it. E.g., d555b5ae-d9a6-41cb-ae54-361427357382', 'wc-serial-numbers'), true );?>
						</label>
					</th>

					<td>
						<textarea name="key" id="key" class="regular-text" required="required" placeholder="d555b5ae-d9a6-41cb-ae54-361427357382"><?php echo $item['key']; ?></textarea>

					</td>
				</tr>

				<?php if ( 'yes' !== get_option( 'wc_serial_numbers_disable_software_support' ) ) : ?>
					<tr valign="top">
						<th scope="row">

							<label for="activation_limit">
								<?php esc_html_e( 'Activation limit', 'wc-serial-numbers' ); ?>
								<?php echo wc_help_tip( esc_html__( 'Maximum number of times the key can be used to activate the software. If the product is not software, keep it blank.', 'wc-serial-numbers' ), true ); ?>
							</label>
						</th>
						<td>
							<?php echo sprintf( '<input name="activation_limit" id="activation_limit" class="regular-text" type="number" value="%d" autocomplete="off">', $item['activation_limit'] ); ?>
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
							<?php echo sprintf( '<input name="validity" id="validity" class="regular-text" type="number" value="%d">', $item['validity'] ); ?>
						</td>
					</tr>

				<?php endif; ?>

				<tr valign="top">
					<th scope="row">
						<label for="expire_date"><?php esc_html_e( 'Expires at', 'wc-serial-numbers' ); ?></label>
						<?php echo wc_help_tip( esc_html__( 'After this date, the key will not be assigned with any order. Leave blank for no expiry date.', 'wc-serial-numbers' ), true ); ?>
					</th>
					<td>
						<?php echo sprintf( '<input name="date_expire" id="date_expire" class="regular-text wc-serial-numbers-select-date" type="text" autocomplete="off" value="%s">', $item['date_expire'] ); ?>
					</td>
				</tr>

				<?php if ( $update ) : ?>
					<!-- status -->
					<tr valign="top">
						<th scope="row">
							<label for="status">
								<?php esc_html_e( 'Status', 'wc-serial-numbers' ); ?>
								<?php echo wc_help_tip( esc_html__( 'Status of the serial number.', 'wc-serial-numbers' ), true ); ?>
							</label>
						</th>
						<td>
							<select id="status" name="status" class="regular-text">
								<?php foreach ( Serial_Keys::get_statuses() as $key => $option ) : ?>
									<?php echo sprintf( '<option value="%s" %s>%s</option>', $key, selected( $item['status'], $key, false ), $option ); ?>
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
							<?php echo sprintf( '<input name="order_id" id="order_id" class="regular-text" type="number" value="%d" autocomplete="off">', $item['order_id'] ); ?>
						</td>
					</tr>
				<?php endif; ?>

				<tr>
					<td></td>
					<td>
						<p class="submit">
							<input type="hidden" name="action" value="wc_serial_numbers_edit_serial_number">
							<?php wp_nonce_field( 'edit_serial_number' ); ?>
							<?php if ( $update ) : ?>
								<?php echo sprintf( '<input type="hidden" name="id" value="%d">', $id ); ?>
								<?php submit_button( esc_html__( 'Update Serial Number', 'wc-serial-numbers' ), 'primary', 'submit', false ); ?>
							<?php else : ?>
								<?php submit_button( esc_html__( 'Add Serial Number', 'wc-serial-numbers' ) ); ?>
							<?php endif ?>
						</p>
					</td>
				</tr>

			</table>
		</form>
	</div>
<?php
