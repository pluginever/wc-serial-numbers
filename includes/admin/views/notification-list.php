<?php
defined( 'ABSPATH' ) || exit();
echo '<span class="ever-notification"><span class="alert">' . sprintf( '%02d', count( $low_stock_products ) ) . '</span></span><ul class="ever-notification-list alert">';


foreach ($low_stock_products as $product_id => $stock){
	$product_id = absint($product_id);
	if(!$product_id){
		continue;
	}
	$product = wc_get_product($product_id);
	$name = sprintf( '<a href="%s">%s</a>', get_edit_post_link( $product->get_id() ), $product->get_formatted_name() );
	$msg  = sprintf( __( '%s stock - %d', 'wc-serial-numbers' ), $name, $stock );
	echo '<li>' . $msg . '</li>';
}
echo '</ul>'; //End the list
