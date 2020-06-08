<?php
defined( 'ABSPATH' ) || exit();

add_action( 'woocommerce_email_after_order_table', 'wc_serial_numbers_order_print_items' );
add_action( 'woocommerce_order_details_after_order_table', 'wc_serial_numbers_order_print_items' );
