<p><?php printf( esc_html__( 'Hi There,', 'wc-serial-numbers' ) ); ?></p>
<p><?php printf( esc_html__( 'There are few  products stock running low, please add serial numbers for these products', 'wc-serial-numbers' ) ); ?></p>
<ul>
	<?php
	foreach ($low_stock_products as $product_id => $stock){
		$product_id = absint($product_id);
		if(!$product_id){
			continue;
		}
		$product = wc_get_product($product_id);

		echo sprintf("<li><a href='%s' target='_blank'>%s</a> - Stock %s</li>",  get_edit_post_link( $product->get_id() ), $product->get_formatted_name(), $stock);
	}
	?>
</ul>

<br>
<br>
<p> <?php echo sprintf(__('The email is sent by <a href="%s" target="_blank">WC Serial Numbers</a>', 'wc-serial-numbers'), 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=serialnumberemail&utm_medium=email&utm_campaign=lowstocknotification'); ?></p>
