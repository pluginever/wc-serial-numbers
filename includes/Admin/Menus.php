<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Activations;
use WooCommerceSerialNumbers\B8\Component;
use WooCommerceSerialNumbers\Keys;

defined( 'ABSPATH' ) || exit;

/**
 * Class Menus.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Menus extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		// Register the menus.
		add_action( 'admin_menu', array( $this, 'main_menu' ) );
		add_action( 'admin_menu', array( $this, 'activations_menu' ), 40 );
		add_action( 'admin_menu', array( $this, 'tools_menu' ), 50 );
		add_action( 'admin_menu', array( $this, 'reports_menu' ), 60 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 100 );
		add_action( 'admin_menu', array( $this, 'promo_menu' ), PHP_INT_MAX );

		// The pro plugin removes these tabs by the static callable names, so they must stay static.
		add_action( 'wc_serial_numbers_tools_tab_import', array( __CLASS__, 'import_tab' ) );
		add_action( 'wc_serial_numbers_tools_tab_export', array( __CLASS__, 'export_tab' ) );
		add_action( 'wc_serial_numbers_tools_tab_generators', array( __CLASS__, 'generators_tab' ) );
	}

	/**
	 * Add menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function main_menu() {
		add_menu_page(
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'wc-serial-numbers',
			null,
			'dashicons-lock',
			'55.9'
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Serial Keys', 'wc-serial-numbers' ),
			__( 'Serial Keys', 'wc-serial-numbers' ),
			'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'wc-serial-numbers',
			$this->app->callback( array( Keys\Admin::class, 'output_page' ) )
		);
	}

	/**
	 * Add activations menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function activations_menu() {
		if ( ! wcsn_is_software_support_enabled() ) {
			return;
		}
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Activations', 'wc-serial-numbers' ),
			__( 'Activations', 'wc-serial-numbers' ),
			'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'wc-serial-numbers-activations',
			$this->app->callback( array( Activations\Admin::class, 'output_page' ) )
		);
	}

	/**
	 * Add tools menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tools_menu() {
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Tools', 'wc-serial-numbers' ),
			__( 'Tools', 'wc-serial-numbers' ),
			'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'wc-serial-numbers-tools',
			$this->app->callback( array( Tools::class, 'output_page' ) )
		);
	}

	/**
	 * Add reports menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function reports_menu() {
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Reports', 'wc-serial-numbers' ),
			__( 'Reports', 'wc-serial-numbers' ),
			'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'wc-serial-numbers-reports',
			$this->app->callback( array( Reports::class, 'output_page' ) )
		);
	}

	/**
	 * Settings menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function settings_menu() {
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Settings', 'wc-serial-numbers' ),
			__( 'Settings', 'wc-serial-numbers' ),
			'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'wc-serial-numbers-settings',
			$this->app->callback( array( Settings::class, 'render' ) )
		);
	}

	/**
	 * Add promo Menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function promo_menu() {
		if ( ! $this->app->is_pro_active() ) {
			add_submenu_page(
				'wc-serial-numbers',
				'',
				'<span style="color:#05ef82;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Upgrade to Pro', 'wc-serial-numbers' ) . '</span>',
				'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
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
		wp_verify_nonce( '_nonce' );
		if ( isset( $_GET['page'] ) && 'go_wcsn_pro' === $_GET['page'] ) {
			wp_safe_redirect( 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=admin-menu&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers' );
			exit;
		}
	}

	/**
	 * Import tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function import_tab() {
		WCSN()->make( Tools::class )->import_tab();
	}

	/**
	 * Export tab content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function export_tab() {
		WCSN()->make( Tools::class )->export_tab();
	}

	/**
	 * Generators tab content.
	 *
	 * @since 1.4.6
	 * @return void
	 */
	public static function generators_tab() {
		WCSN()->make( Tools::class )->generators_tab();
	}
}
