<p>Customize the content how serial numbers will be displayed on Thank you page and Order email.</p>
<table class="wcsn-view-template-settings">
	<thead>
	<tr>
		<td colspan="2">
			<label for="wcsn_tmpl_heading">Heading</label>
			<input
				type="text"
				id="wcsn_tmpl_heading"
				name="wcsn_tmpl_heading"
				class="large-text"
				disabled="disabled"
				placeholder="Heading" value="Serial Numbers">
			<p class="description">This will appear above ordered serial numbers table.</p>
		</td>
	</tr>
	<tr>
		<td width="50%">
			<label for="wcsn_tmpl_product_col_heading">Product Column Heading</label>
			<input type="text" id="wcsn_tmpl_product_col_heading" name="wcsn_tmpl_product_col_heading" class="large-text" placeholder="Product" value="Product" disabled="disabled">
			<p class="description">Ordered serial numbers table product column heading text.</p>
		</td>
		<td width="50%">
			<label for="wcsn_tmpl_serial_col_heading">Serial Number Column Heading</label>
			<input type="text" id="wcsn_tmpl_serial_col_heading" name="wcsn_tmpl_serial_col_heading" class="large-text" placeholder="Serial Number" value="Serial Number" disabled="disabled">
			<p class="description">Ordered serial numbers table serial numbers column heading text.</p>
		</td>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td>
			<label for="wcsn_tmpl_product_col_content">Product column content</label>
			<textarea class="large-text code" name="wcsn_tmpl_product_col_content" id="wcsn_tmpl_product_col_content" rows="10" disabled="disabled">{product_title}</textarea>
			<p class="description">This is the place normally will show product Name. Default {product_title}</p>
		</td>
		<td>
			<label for="wcsn_tmpl_serial_col_content">Serial Number Column Content</label>
			<textarea class="large-text code" name="wcsn_tmpl_serial_col_content" id="wcsn_tmpl_serial_col_content" rows="10" disabled="disabled"><strong>Serial Numbers:</strong>{serial_number}<br/><strong>Activation Email:</strong>{activation_email}<br/><strong>Expire At:</strong>{expired_at}<br/><strong>Activation Limit:</strong>{activation_limit}</textarea>
			<p class="description">The content will show for each serial number ordered.</p>
		</td>
	</tr>
	</tbody>
</table>
<style>
	.wcsn-view-template-settings label {
		font-weight: 700;
		display: inline-block;
		margin-bottom: 10px;
	}
</style>
