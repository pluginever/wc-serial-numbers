<?php
/**
 * API actions
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
		<h2><?php esc_html_e( 'API Actions', 'wc-serial-numbers' ); ?></h2>
	</div>

	<div class="pev-card__body">
		<p>
			<?php esc_html_e( 'You can use the API to perform actions on your website or on another website.', 'wc-serial-numbers' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'To perform an action, you need to send a POST request to the following URL:', 'wc-serial-numbers' ); ?>
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
				<code>request</code> - <?php esc_html_e( 'The request type. Must be set to "activate" or "deactivate".', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>instance</code> - <?php esc_html_e( 'Instance is the base of activation and deactivation. It is a unique identifier for the installation. For example, you can use the domain name of the website.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>platform</code> - <?php esc_html_e( 'Optional. The platform on which the serial key is used. For example, "Windows" or "Mac".', 'wc-serial-numbers' ); ?>
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
						'product_id' => 123,
						'serial_key' => '123456789',
						'request'    => 'activate',
						'instance'   => 'example.com',
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
				<code>code</code> - <?php esc_html_e( 'The response code. "key_activated" or "key_deactivated" if the request was successful. "invalid_key" or "invalid_request" if the request was not successful.', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>message</code> - <?php esc_html_e( 'The response message. If the serial key is valid, the message will be "Serial key is valid".', 'wc-serial-numbers' ); ?>
			</li>
			<li>
				<code>activated/deactivated</code> - <?php esc_html_e( 'Activated or deactivated when the request is successful.', 'wc-serial-numbers' ); ?>
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
				<code>expires_at</code> - <?php esc_html_e( 'The expiration date for the serial key in Unix timestamp format.', 'wc-serial-numbers' ); ?>
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
		<h4><?php esc_html_e( 'You can use the form below to test the API activation/deactivation.', 'wc-serial-numbers' ); ?></h4>
		<form class="wcsn-api-form" method="post">
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row"><label for="product_id"><?php esc_html_e( 'Product', 'wc-serial-numbers' ); ?></label></th>
					<td>
						<select name="product_id" id="product_id" class="wc-enhanced-select">
							<?php foreach ( $products as $product_id => $product_name ) : ?>
								<option value="<?php echo esc_attr( $product_id ); ?>"><?php echo esc_html( $product_name ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php esc_html_e( 'Select a product to activate/deactivate serial key for.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="serial_key"><?php esc_html_e( 'Key', 'wc-serial-numbers' ); ?></label></th>
					<td>
						<input type="text" name="serial_key" id="serial_key" placeholder="<?php esc_attr_e( 'Please enter serial key to activate/deactivate', 'wc-serial-numbers' ); ?>" required/>
						<p class="description">
							<?php esc_html_e( 'Required field. Enter serial key to activate/deactivate.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="instance"><?php esc_html_e( 'Instance', 'wc-serial-numbers' ); ?></label></th>
					<td>
						<input type="text" name="instance" id="instance" placeholder="<?php esc_attr_e( 'Please enter a unique instance', 'wc-serial-numbers' ); ?>" value="<?php echo esc_attr( time() ); ?>" required>
						<p class="description">
							<?php esc_html_e( 'Required field. Instance is the unique identifier of the activation record. It is used to identify the activation when activating/deactivating serial key.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>

				<!--platform-->
				<tr>
					<th scope="row"><label for="platform"><?php esc_html_e( 'Platform', 'wc-serial-numbers' ); ?></label></th>
					<td>
						<input type="text" name="platform" id="platform" placeholder="<?php esc_attr_e( 'Please enter a platform. e.g. Windows', 'wc-serial-numbers' ); ?>"/>
						<p class="description">
							<?php esc_html_e( 'Optional field. Platform is the extra information of the activation record. You can use it to identify the platform of the activation.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="email"><?php esc_html_e( 'Email', 'wc-serial-numbers' ); ?></label></th>
					<td>
						<input type="email" name="email" id="email" placeholder="<?php esc_attr_e( 'Please enter a valid email address', 'wc-serial-numbers' ); ?>">
						<p class="description">
							<?php esc_html_e( 'Optional field when duplicate key is off. If email is provided, only serial key that are assigned to the email will be activated/deactivated otherwise ignored.', 'wc-serial-numbers' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="request"><?php esc_html_e( 'Action', 'wc-serial-numbers' ); ?></label></th>
					<td>
						<select name="request" id="request">
							<option value="activate"><?php esc_html_e( 'Activate', 'wc-serial-numbers' ); ?></option>
							<option value="deactivate"><?php esc_html_e( 'Deactivate', 'wc-serial-numbers' ); ?></option>
						</select>
						<p class="description">
							<?php esc_html_e( 'Select an action to perform on the serial key.', 'wc-serial-numbers' ); ?>
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
						<?php submit_button( __( 'Submit', 'wc-serial-numbers' ), 'primary', 'submit', false ); ?>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
