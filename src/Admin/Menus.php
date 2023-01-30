<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Lib\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Class Menus.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Menus extends Singleton {

	/**
	 * Menus constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'admin_menu', array( $this, 'setting_menu' ), 55 );
		add_action( 'admin_menu', array( $this, 'promo_menu' ), PHP_INT_MAX );
	}

	/**
	 * Add menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setting_menu() {
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Settings', 'wc-serial-numbers' ),
			__( 'Settings', 'wc-serial-numbers' ),
			wc_serial_numbers_get_manager_role(),
			'wc-serial-numbers-settings',
			array( Settings::class, 'output' )
		);
	}

	/**
	 * Add promo Menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function promo_menu() {
		$role = wc_serial_numbers_get_manager_role();
		if ( ! wc_serial_numbers()->is_premium_active() ) {
			add_submenu_page(
				'wc-serial-numbers',
				'',
				'<span style="color:#ff7a03;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Go Pro', 'wc-serial-numbers' ) . '</span>',
				$role,
				'go_wcsn_pro',
				array( $this, 'go_pro_redirect' )
			);
		}
	}

	/**
	 * Redirect to pro page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function go_pro_redirect() {
		if ( isset( $_GET['page'] ) && 'go_wcsn_pro' === $_GET['page'] ) {
			wp_redirect( 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=admin-menu&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers' );
			die;
		}
	}
}
