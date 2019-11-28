<?php
// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php _e( 'Serial Numbers API Doc', 'wc-serial-numbers' ); ?>
	</h1>

	<p>Visit the official <a href="https://www.pluginever.com/docs/woocommerce-serial-numbers/woocommerce-serial-numbers-api-docs/" target="_blank">documentation</a> or read the quick implementation guide below.</p>
	<h3>Endpoints</h3>
	<hr>
	<h4>STATUS ENDPOINT</h4>
	<p>Endpoint used to check status of a serial number.</p>
	<pre style="text-align: left;color: #F44336"><?php echo esc_url( add_query_arg( array( 'wc-api' => 'serial-numbers-api' ), home_url() ) ); ?></pre>
	<table style="width: 100%">
		<thead style="text-align: left">
		<tr>
			<th>Parameter</th>
			<th>Type</th>
			<th>Description</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><code>request</code></td>
			<td>string</td>
			<td><code>check</code></td>
		</tr>
		<tr>
			<td><code>email</code></td>
			<td>string</td>
			<td>Activation email</td>
		</tr>
		<tr>
			<td><code>serial_key</code></td>
			<td>string</td>
			<td>The license key provided to the customer.</td>
		</tr>
		<tr>
			<td><code>product_id</code></td>
			<td>integer</td>
			<td>Product ID</td>
		</tr>
		</tbody>
	</table>
	<hr>
	<h4>ACTIVATE ENDPOINT</h4>
	<p>Endpoint used to activate a serial number.</p>
	<pre style="text-align: left;color: #F44336"><?php echo esc_url( add_query_arg( array( 'wc-api' => 'serial-numbers-api' ), home_url() ) ); ?></pre>
	<table style="width: 100%">
		<thead style="text-align: left">
		<tr>
			<th>Parameter</th>
			<th>Type</th>
			<th>Description</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><code>request</code></td>
			<td>string</td>
			<td><code>activate</code></td>
		</tr>
		<tr>
			<td><code>email</code></td>
			<td>string</td>
			<td>Activation email</td>
		</tr>
		<tr>
			<td><code>serial_key</code></td>
			<td>string</td>
			<td>The license key provided to the customer.</td>
		</tr>
		<tr>
			<td><code>product_id</code></td>
			<td>integer</td>
			<td>Product ID</td>
		</tr>
		<tr>
			<td><code>instance</code></td>
			<td>string</td>
			<td>Pass to activate existing uses (previously deactivated). If empty, new activation record is created. When empty, its value is timestamped when the request made.</td>
		</tr>
		<tr>
			<td><code>platform</code></td>
			<td>string</td>
			<td>Decided by user, can be anything.(Optional)</td>
		</tr>
		</tbody>
	</table>

	<hr>
	<h4>DEACTIVATE ENDPOINT</h4>
	<p>Endpoint used to deactivate a serial number.</p>
	<pre style="text-align: left;color: #F44336"><?php echo esc_url( add_query_arg( array( 'wc-api' => 'serial-numbers-api' ), home_url() ) ); ?></pre>
	<table style="width: 100%">
		<thead style="text-align: left">
		<tr>
			<th>Parameter</th>
			<th>Type</th>
			<th>Description</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><code>request</code></td>
			<td>string</td>
			<td><code>deactivate</code></td>
		</tr>
		<tr>
			<td><code>email</code></td>
			<td>string</td>
			<td>Activation email</td>
		</tr>
		<tr>
			<td><code>serial_key</code></td>
			<td>string</td>
			<td>The license key provided to the customer.</td>
		</tr>
		<tr>
			<td><code>product_id</code></td>
			<td>integer</td>
			<td>Product ID</td>
		</tr>
		<tr>
			<td><code>instance</code></td>
			<td>string</td>
			<td>Instance set at the time of activation</td>
		</tr>
		</tbody>
	</table>


</div>
