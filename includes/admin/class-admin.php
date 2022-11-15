<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Admin\List_Tables\Activations_List_Table;
use WooCommerceSerialNumbers\Admin\List_Tables\Keys_List_Table;
use WooCommerceSerialNumbers\Admin\List_Tables\List_Table;
use WooCommerceSerialNumbers\Controller;
use WooCommerceSerialNumbers\Key;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Admin class
 *
 * @package PluginEver\WooCommerceSerialNumbers\Admin
 */
class Admin extends Controller {
	/**
	 * List table.
	 *
	 * @since #.#.#
	 * @var List_Table
	 */
	protected $list_table;

	/**
	 * Set up the controller.
	 *
	 * Load files or register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function init() {
		add_action( 'init', array( $this, 'add_controllers' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'register_nav_items' ), 20 );
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	/**
	 * Register admin controllers.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_controllers() {
		$controllers = array(
			'admin_settings' => Settings::class,
			'meta_boxes'     => Meta_Boxes::class,
		);
		$this->add_controller( $controllers );
	}

	/**
	 * Add the plugin screens to the WooCommerce screens
	 *
	 * @param  array $ids Screen ids.
	 * @return array
	 */
	public function screen_ids( $ids ) {
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
					'id'        => 'woocommerce_page_wc-serial-numbers',
					'parent'    => 'woocommerce_page_wc',
					'screen_id' => 'woocommerce_page_wc-serial-numbers',
					'title'     => __( 'WooCommerce Serial Numbers Settings', 'wc-serial-numbers' ),
				)
			);
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		$style_deps  = array( 'woocommerce_admin_styles' );
		$script_deps = array( 'jquery', 'wc-enhanced-select' );
		$this->get_plugin()->register_style( 'wc-serial-numbers-admin', 'css/admin-style.css', $style_deps );
		$this->get_plugin()->register_script( 'wc-serial-numbers-admin', 'js/admin-script.js', $script_deps );

		wp_enqueue_style( 'wc-serial-numbers-admin' );
		wp_enqueue_script( 'wc-serial-numbers-admin' );

		wp_localize_script(
			'wc-serial-numbers-admin',
			'wc_serial_numbers_vars',
			array(
				'i18n'    => array(
					'search_product'  => __( 'Search product by name', 'wc-serial-numbers' ),
					'search_order'    => __( 'Search order', 'wc-serial-numbers' ),
					'search_customer' => __( 'Search customer', 'wc-serial-numbers' ),
					'show'            => __( 'Show', 'wc-serial-numbers' ),
					'hide'            => __( 'Hide', 'wc-serial-numbers' ),
				),
				'nonce'   => wp_create_nonce( 'wc_serial_numbers_search_nonce' ),
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);

		// Adjust label style.
		wp_add_inline_style(
			'forms',
			'._serial_key_source_field label { margin: 0 !important;width: 100% !important; }'
		);
	}


	/**
	 * Register menu.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function register_menu() {
		$manager_role = self::get_manager_role();
		add_menu_page(
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			$manager_role,
			'wc-serial-numbers',
			array( $this, 'render_main_page' ),
			'dashicons-lock',
			'55.9'
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			$manager_role,
			'wc-serial-numbers',
			array( $this, 'render_main_page' )
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Activations', 'wc-serial-numbers' ),
			__( 'Activations', 'wc-serial-numbers' ),
			$manager_role,
			'wc-serial-numbers-activations',
			array( $this, 'render_activations_page' )
		);

		if ( ! defined( 'WC_SERIAL_NUMBER_PRO_PLUGIN_VERSION' ) ) {
			// add_submenu_page(
			// 'wc-serial-numbers',
			// '',
			// '<span style="color:#ff7a03;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Go Pro', 'wc-serial-numbers' ) . '</span>',
			// 'edit_others_posts',
			// 'go_wcsn_pro',
			// array( $this, 'go_pro_redirect' )
			// );
		}
	}

	/**
	 * Render main page.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public function render_main_page() {
		if ( isset( $_GET['add'] ) || isset( $_GET['edit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$id  = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$key = new Key( $id );
			$this->render(
				'html-edit-key',
				[
					'key' => $key,
				]
			);
			return;
		}
		$this->list_table = new Keys_List_Table();
		$this->render(
			'html-list-keys',
			[
				'list_table' => $this->list_table,
			]
		);
	}

	/**
	 * Render activations page.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public function render_activations_page() {
		$this->list_table = new Activations_List_Table();
		$this->render(
			'html-list-activations',
			[
				'list_table' => $this->list_table,
			]
		);
	}

	/**
	 * Redirect to pro page.
	 *
	 * @since 1.4.0
	 * @return void
	 */
	public function go_pro_redirect() {
		wp_redirect( 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	/**
	 * Get serial number user role.
	 * Capability to manage serial numbers.
	 *
	 * @since 1.4.0
	 * @return string
	 */
	public static function get_manager_role() {
		return apply_filters( 'wc_serial_numbers_manager_role', 'manage_woocommerce' );
	}
}
