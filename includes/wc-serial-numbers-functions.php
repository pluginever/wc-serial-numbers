<?php
defined( 'ABSPATH' ) || exit();


add_action( 'wc_serial_numbers_update_activation', 'wc_serial_numbers_update_activation_count' );
add_action( 'wc_serial_numbers_delete_activation', 'wc_serial_numbers_update_activation_count' );
add_action( 'wc_serial_numbers_insert_activation', 'wc_serial_numbers_update_activation_count' );
add_filter( 'woocommerce_product_get_stock_quantity', 'wc_serial_numbers_find_stock_quantity', 10, 2 );
add_filter( 'wc_serial_numbers_order_table_columns', 'wc_serial_numbers_control_order_table_columns', 99 );
