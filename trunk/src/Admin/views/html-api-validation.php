<?php
/**
 * API Validation
 *
 * @package WooCommerceSerialNumbers/Admin/Views
 * @version 1.4.6
 */

defined( 'ABSPATH' ) || exit;

$api_url = add_query_arg(
	array(
		'wc-api' => 'serial-numbers-api',
	),
	home_url( '/' )
);
?>
<div class="pev-card">
	<div class="pev-card__header">
		<h2><?php esc_html_e( 'API Validation', 'wc-serial-numbers' ); ?></h2>
	</div>
	<div class="pev-card__body">
		<?php if ( ! WCSN()->is_premium_active() ) : ?>
			<div class="notice notice-warning">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: link to the pro version */
							__( 'You are using the free version of Serial Numbers for WooCommerce. <a href="%s" target="_blank">Upgrade to Pro</a> to get more features.', 'wc-serial-numbers' ),
							esc_url( WCSN()->get_premium_url() . '?utm_source=create_serial_page&utm_medium=button&utm_campaign=wc-serial-numbers&utm_content=View%20Details' )
						)
					);
					?>
				</p>
			</div>
		<?php endif; ?>
		<p>
			<?php esc_html_e( 'You can use the API to validate serial keys on your website or on another website.', 'wc-serial-numbers' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'To validate a serial key, you need to send a GET request to the following URL:', 'wc-serial-numbers' ); ?>
		</p>
		<pre><?php echo esc_html( $api_url ); ?></pre>
		<p>
			<?php esc_html_e( 'The request must contain the following parameters:', 'wc-serial-numbers' ); ?>
		</p>
		<ol>
			<li>
				<code>product_id</code> - <?php esc_html_e( 'The ID of the product for which the serial key is valid.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>serial_key</code> - <?php esc_html_e( 'The serial key to validate.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>request</code> - <?php esc_html_e( 'The request type. Must be set to "validate".', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>email</code> - <?php esc_html_e( 'Using email is completely voluntary. The API will verify that the serial number is associated with the given email address.', 'wc-serial-numbers' ); ?>
			</li>
		</ol>
		<p>
			<?php esc_html_e( 'Example:', 'wc-serial-numbers' ); ?>
			<code>
			<?php
			echo esc_html(
				add_query_arg(
					array(
						'product_id' => 1,
						'serial_key' => '123456789',
						'request'    => 'validate',
					),
					$api_url
				)
			);
			?>
			</code>
		</p>
		<p>
			<?php esc_html_e( 'The API will return a JSON response with the following parameters:', 'wc-serial-numbers' ); ?>
		</p>
		<ol>
			<li>
				<code>code</code> - <?php esc_html_e( 'The response code. If the serial key is valid, the code will be "key_valid".', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>message</code> - <?php esc_html_e( 'The response message. If the serial key is valid, the message will be "Serial key is valid".', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>activation_limit</code> - <?php esc_html_e( 'The activation limit for the serial key.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>activation_count</code> - <?php esc_html_e( 'The number of activations for the serial key.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>activations_left</code> - <?php esc_html_e( 'The number of activations left for the serial key.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>expire_date</code> - <?php esc_html_e( 'The expiration date for the serial key.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>status</code> - <?php esc_html_e( 'The status of the serial key.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>product_id</code> - <?php esc_html_e( 'The ID of the product for which the serial key is valid.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>product</code> - <?php esc_html_e( 'The name of the product for which the serial key is valid.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>activations</code> - <?php esc_html_e( 'The list of activations for the serial key.', 'wc-serial-numbers' ); ?>
			</li>
		</ol>

		<h4><?php esc_html_e( 'You can test the API using the form below.', 'wc-serial-numbers' ); ?></h4>

		<form class="wcsn-api-form" method="post">
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"><label for="product_id"><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></label></th>
					<td>
						<select name="product_id" id="product_id" class="wc-enhanced-select" required>
							<?php foreach ( $products as $product_id => $product_name ) : ?>
								<option value="<?php echo esc_attr( $product_id ); ?>"><?php echo esc_html( $product_name ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e( 'Select a product to validate serial key for.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="serial_key"><?php esc_html_e( 'Key', 'wc-serial-numbers' ); ?></label></th>
					<td>
						<input type="text" name="serial_key" id="serial_key" placeholder="<?php esc_attr_e( 'Please enter serial key to validate', 'wc-serial-numbers' ); ?>" required/>
						<p class="description">
							<?php esc_html_e( 'Required field. Enter serial key to validate.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="email"><?php esc_html_e( 'Email', 'wc-serial-numbers' ); ?></label></th>
					<td>
						<input type="email" name="email" id="email" placeholder="<?php esc_attr_e( 'Please enter a valid email address', 'wc-serial-numbers' ); ?>">
						<p class="description">
							<?php esc_html_e( 'Optional field. If email is provided, only serial key that are assigned to the email will be validated otherwise ignored.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'API response', 'wc-serial-numbers' ); ?></label></th>
					<td class="code">
						<pre><span class="wcsn-api-response">&mdash;</span></pre>
					</td>
				</tbody>

				<tfoot>
				<tr>
					<th scope="row"></th>
					<td>
						<input type="hidden" name="request" value="validate">
						<?php submit_button( __( 'Validate', 'wc-serial-numbers' ), 'primary', 'submit', false ); ?>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
