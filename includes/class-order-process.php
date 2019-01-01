<?php

namespace Pluginever\WCSerialNumbers\Admin;



class WSN_Process_Order {

	function __construct() {
		add_action( 'woocommerce_checkout_order_processed', [$this, 'wsn_order_process']);
		//add_action('woocommerce_order_details_after_order_table', [$this, 'wsn_order_serial_number_details']);
	}

	function wsn_order_process($order_id){
		$order = wc_get_order($order_id);
		$items = $order->get_items();
		foreach ($items as $item_id => $item_data){
			$product = $item_data->get_product();
			$product_id = $product->get_id();
			$enable_serial_number = get_post_meta($product_id, 'enable_serial_number', true);
			if($enable_serial_number){
				$serial_numbers = get_posts(['post_type'=>'serial_number', 'posts_per_page'=> -1 , 'meta_key'=> 'product', 'meta_value' => $product_id]);
			}
		}
	}
}
