<?php

namespace PluginEver\SerialNumbers;
defined( 'ABSPATH' ) || exit();

class Admin {

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( __CLASS__, 'buffer' ), 1 );
		add_action( 'init', array( __CLASS__, 'includes' ) );
		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'add_order_serial_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'add_order_serial_column_content' ), 20, 2 );
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public static function buffer() {
		ob_start();
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {
		require_once dirname( __FILE__ ) . '/admin/class-admin-notice.php';
		require_once dirname( __FILE__ ) . '/admin/class-admin-menus.php';
		require_once dirname( __FILE__ ) . '/admin/class-admin-metaboxes.php';
		require_once dirname( __FILE__ ) . '/admin/class-admin-actions.php';
		require_once dirname( __FILE__ ) . '/admin/class-serials-page.php';
		require_once dirname( __FILE__ ) . '/admin/class-activations-page.php';
//		require_once dirname( __FILE__ ) . '/admin/class-admin-settings.php';
		require_once dirname( __FILE__ ) . '/admin/class-wc-serial-numbers-admin-settings.php';
		require_once dirname( __FILE__ ) . '/admin/class-admin-ajax.php';
	}

	/**
	 * @since 1.2.0
	 * @param $columns
	 *
	 * @return array|string[]
	 */
	public static function add_order_serial_column( $columns ) {
		$postition = 3;
		$new       = array_slice( $columns, 0, $postition, true ) + array( 'order_serials' => '<span class="dashicons dashicons-lock"></span>' ) + array_slice( $columns, $postition, count( $columns ) - $postition, true );;
		return $new;
	}

	/**
	 * @since 1.2.0
	 * @param $column
	 * @param $order_id
	 */
	public static function add_order_serial_column_content( $column, $order_id ) {
		if ( $column == 'order_serials' ) {
			$total = Order::order_contains_serials( $order_id );
			if ( empty( $total ) ) {
				echo '&mdash;';
			} else {
				$total_connected = Query_Serials::init()->where( 'order_id', intval( $order_id ) )->count();
				$style           = '';
				$title           = '';
				if ( $total > $total_connected ) {
					$style = "color:red";
					$title = sprintf( __( 'Order missing serial numbers(%d)', 'wc-serial-numbers' ), $total );
				} else {
					$style = "color:green";
					$title = __( 'Order assigned all serial numbers.', 'wc-serial-numbers' );
				}
				$url = add_query_arg( [ 'order_id' => $order_id ], admin_url( 'admin.php?page=serial-numbers' ) );
				echo sprintf( '<a href="%s" title="%s"><span class="dashicons dashicons-lock" style="%s"></span></a>', $url, $title, $style );

			}
		}
	}

}

new Admin();
