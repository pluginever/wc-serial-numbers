<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin;

use PluginEver\WooCommerceSerialNumbers\Plugin;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Admin Manager.
 *
 * @since   1.0.0
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Admin_Manager {


	/**
	 * Construct Admin_Manager.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {
		 add_action( 'init', [ __CLASS__, 'includes' ] );
		add_filter( 'woocommerce_screen_ids', array( __CLASS__, 'screen_ids' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_nav_items' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 20 );
//		add_filter( 'wc_serial_numbers_settings_general', [ __CLASS__, 'settings_fields' ] );

		add_filter( 'manage_edit-shop_order_columns', array( __CLASS__, 'add_order_serial_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( __CLASS__, 'add_order_serial_column_content' ), 20, 2 );
		add_action( 'admin_footer_text', array( __CLASS__, 'admin_footer_note' ) );
	}

	/**
	 * Includes the necessary files.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function includes() {
		include_once Plugin::instance()->get_plugin_path() . '/includes/admin/class-admin-menu.php';
		include_once Plugin::instance()->get_plugin_path() . '/includes/admin/class-admin-settings.php';
//		include_once Plugin::instance()->get_plugin_path() . '/includes/admin/class-admin-notices.php';
//		include_once Plugin::instance()->get_plugin_path() . '/includes/admin/class-meta-boxes.php';
	}


	/**
	 * Add the plugin screens to the WooCommerce screens
	 *
	 * @param  array $ids Screen ids.
	 * @return array
	 */
	public static function screen_ids( $ids ) {
		$ids[] = 'woocommerce_page_wc-serial-numbers-settings';
		return $ids;
	}

	/**
	 * Registers the navigation items in the WC Navigation Menu.
	 *
	 * @since 1.0.0
	 */
	public static function register_nav_items() {
		if ( function_exists( 'wc_admin_connect_page' ) ) {
			wc_admin_connect_page(
				array(
					'id'        => 'woocommerce_page_wc-serial-numbers-settings',
					'parent'    => 'woocommerce_page_wc',
					'screen_id' => 'woocommerce_page_wc-serial-numbers-settings',
					'title'     => __( 'Starter Plugin Settings', 'wc-serial-numbers' ),
				)
			);
		}
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook Page hook.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_scripts( $hook ) {

	}

	/**
	 * Add plugin settings fields.
	 *
	 * @param array $fields Settings fields.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public static function settings_fields( $fields ) {
		$settings = [
			array(
				'title' => __( 'Store Address', 'wc-serial-numbers' ),
				'type'  => 'title',
				'desc'  => __( 'This is where your business is located. Tax rates and shipping rates will use this address.', 'wc-serial-numbers' ),
				'id'    => 'store_address',
			),

			array(
				'title'    => __( 'Address line 1', 'wc-serial-numbers' ),
				'desc'     => __( 'The street address for your business location.', 'wc-serial-numbers' ),
				'id'       => 'woocommerce_store_address',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Address line 2', 'wc-serial-numbers' ),
				'desc'     => __( 'An additional, optional address line for your business location.', 'wc-serial-numbers' ),
				'id'       => 'woocommerce_store_address_2',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'City', 'wc-serial-numbers' ),
				'desc'     => __( 'The city in which your business is located.', 'wc-serial-numbers' ),
				'id'       => 'woocommerce_store_city',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Country / State', 'wc-serial-numbers' ),
				'desc'     => __( 'The country and state or province, if any, in which your business is located.', 'wc-serial-numbers' ),
				'id'       => 'woocommerce_default_country',
				'default'  => 'US:CA',
				'type'     => 'single_select_country',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Postcode / ZIP', 'wc-serial-numbers' ),
				'desc'     => __( 'The postal code, if any, in which your business is located.', 'wc-serial-numbers' ),
				'id'       => 'woocommerce_store_postcode',
				'css'      => 'min-width:50px;',
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Base color', 'wc-serial-numbers' ),
				/* translators: %s: default color */
				'desc'     => sprintf( __( 'The base color for WooCommerce email templates. Default %s.', 'wc-serial-numbers' ), '<code>#7f54b3</code>' ),
				'id'       => 'woocommerce_email_base_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#7f54b3',
				'autoload' => false,
				'desc_tip' => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'store_address',
			),
		];
		return array_merge( $fields, $settings );
	}

	/**
	 * @param $columns
	 *
	 * @return array|string[]
	 * @since 1.2.0
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
		if ( $column == 'order_serials' ) {
			echo '&mdash;';
		}
		// todo need to implement order serial numbers columns.
//			$total_ordered = wc_serial_numbers_order_has_serial_numbers( $order_id );
//			if ( empty( $total_ordered ) ) {
//				echo '&mdash;';
//			} else {
//				$total_connected = WC_Serial_Numbers_Query::init()->from( 'serial_numbers' )->where( 'order_id', intval( $order_id ) )->count();
//				if ( $total_ordered == $total_connected ) {
//					$style = "color:green";
//					$title = __( 'Order assigned all serial numbers.', 'wc-serial-numbers' );
//				} else if ( ! empty( $total_connected ) && $total_ordered !== $total_connected ) {
//					$style = "color:#f39c12";
//					$title = sprintf( __( 'Order partially missing serial numbers(%d)', 'wc-serial-numbers' ), $total_ordered );
//				} else {
//					$style = "color:red";
//					$title = sprintf( __( 'Order missing serial numbers(%d)', 'wc-serial-numbers' ), $total_ordered );
//				}
//				$url = add_query_arg( [ 'order_id' => $order_id ], admin_url( 'admin.php?page=wc-serial-numbers' ) );
//				echo sprintf( '<a href="%s" title="%s"><span class="dashicons dashicons-lock" style="%s"></span></a>', $url, $title, $style );
//
//			}
//		}
	}

	/**
	 * Add footer note
	 *
	 * @return string
	 */
	public static function admin_footer_note() {
		$screen = get_current_screen();
		if ( 'wc-serial-numbers' == $screen->parent_base ) {
			$star_url = 'https://wordpress.org/support/plugin/wc-serial-numbers/reviews/?filter=5#new-post';
			$text     = sprintf( __( 'If you like <strong>WooCommerce Serial Numbers</strong> please leave us a <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating. It takes a minute and helps a lot. Thanks in advance!', 'wc-serial-numbers' ), $star_url );
			return $text;
		}
	}

}

return new Admin_Manager();
