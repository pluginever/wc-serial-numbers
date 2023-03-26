<?php

namespace WooCommerceSerialNumbers\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Orders.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Orders extends \WooCommerceSerialNumbers\Lib\Singleton {

	/**
	 * Orders constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'add_order_serial_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'add_order_serial_column_content' ), 20, 2 );
	}

	/**
	 * @param $columns
	 *
	 * @since 1.2.0
	 * @return array|string[]
	 */
	public static function add_order_serial_column( $columns ) {
		$postition = 3;
		$new       = array_slice( $columns, 0, $postition, true ) + array( 'order_serials' => '<span class="dashicons dashicons-lock"></span>' ) + array_slice( $columns, $postition, count( $columns ) - $postition, true );;

		return $new;
	}

	/**
	 * @param $column
	 * @param $order_id
	 *
	 * @since 1.2.0
	 */
	public static function add_order_serial_column_content( $column, $order_id ) {
		$order_status = wc_get_order( $order_id )->get_status();
		if ( $column == 'order_serials' ) {
			if ( ! wcsn_order_has_products( $order_id ) || ! in_array( $order_status, [ 'completed', 'processing' ] ) ) {
				echo '&mdash;';
			} else {
				if ( wcsn_order_is_fullfilled( $order_id ) ) {
					$style = "color:green";
					$title = __( 'Order fullfilled.', 'wc-serial-numbers' );
				} else {
					$style = "color:red";
					$title = __( 'Order not fullfilled.', 'wc-serial-numbers' );
				}
				$url = add_query_arg( [ 'order_id' => $order_id ], admin_url( 'admin.php?page=wc-serial-numbers' ) );
				echo sprintf( '<a href="%s" title="%s"><span class="dashicons dashicons-lock" style="%s"></span></a>', $url, $title, $style );
			}
		}
	}

}
