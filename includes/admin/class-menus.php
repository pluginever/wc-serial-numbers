<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin;

// don't call the file directly.
use PluginEver\WooCommerceSerialNumbers\Admin\List_Tables\Activations_List_Table;
use PluginEver\WooCommerceSerialNumbers\Admin\List_Tables\Generators_List_Table;
use PluginEver\WooCommerceSerialNumbers\Admin\List_Tables\Keys_List_Table;
use PluginEver\WooCommerceSerialNumbers\Helper;
use PluginEver\WooCommerceSerialNumbers\Plugin;

defined( 'ABSPATH' ) || exit();

/**
 * Admin Menu class.
 *
 * Responsible for creating the admin menus.
 *
 * @since 1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Menus {

	/**
	 * Admin menu constructor.
	 *
	 * @since 1.3.1
	 */
	public function __construct() {
		// Load correct list table classes for current screen.
		add_action( 'current_screen', array( $this, 'setup_screen' ) );
		add_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );

		// Save screen options.
		add_filter( 'set-screen-option', array( __CLASS__, 'save_screen_options' ), 10, 3 );

		// Add menus.
		add_action( 'admin_menu', array( $this, 'connect_pages' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Setup screen.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function setup_screen() {
		$screen_id = false;
		if ( function_exists( 'get_current_screen' ) ) {
			$screen    = get_current_screen();
			$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
		}

		if ( strpos( $screen_id, 'serial-numbers' ) === false ) {
			return;
		}
		$screen_id = str_replace( array( 'toplevel_page_wc-', 'serial-numbers_page_wsn-' ), '', $screen_id );
		global $wp_list_table;

		if ( 'serial-numbers' === $screen_id && ( ! isset( $_GET['create'], $_GET['edit'] ) ) ) {
			$wp_list_table = new Keys_List_Table();
			$action        = $wp_list_table->current_action();
			$wp_list_table->process_bulk_actions( $action );
			add_screen_option(
				'per_page',
				array(
					'default' => 20,
					'option'  => 'wcsn_keys_per_page',
				)
			);
		} elseif ( 'generators' === $screen_id && ( ! isset( $_GET['generate'], $_GET['edit'], $_GET['create'] ) ) ) {
			$wp_list_table = new Generators_List_Table();
			$action        = $wp_list_table->current_action();
			$wp_list_table->process_bulk_actions( $action );
			add_screen_option(
				'per_page',
				array(
					'default' => 20,
					'option'  => 'wcsn_generators_per_page',
				)
			);
		} elseif ( 'activations' === $screen_id ) {
			$wp_list_table = new Activations_List_Table();
			add_screen_option(
				'per_page',
				array(
					'default' => 20,
					'option'  => 'wcsn_activations_per_page',
				)
			);
		}

		// Ensure the table handler is only loaded once. Prevents multiple loads if a plugin calls check_ajax_referer many times.
		remove_action( 'current_screen', array( $this, 'setup_screen' ) );
		remove_action( 'check_ajax_referer', array( $this, 'setup_screen' ) );
	}

	/**
	 * Save screen options.
	 *
	 * @param mixed $status Status.
	 * @param string $option Option.
	 * @param mixed $value Value.
	 *
	 * @since 1.3.1
	 * @return mixed
	 */
	public static function save_screen_options( $status, $option, $value ) {
		if ( in_array( $option, array( 'wcsn_keys_per_page', 'wcsn_generators_per_page', 'wcsn_activations_per_page' ), true ) ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Connect pages.
	 *
	 * @since #.#.#
	 * @return void
	 */
	public function connect_pages() {
		if ( ! function_exists( 'wc_admin_connect_page' ) ) {
			return;
		}
		wc_admin_connect_page(
			array(
				'id'        => 'serial-numbers',
				'title'     => __( 'WooCommerce Serial Numbers', 'wc-serial-numbers' ),
				'screen_id' => 'toplevel_page_wc-serial-numbers',
				'parent'    => 'toplevel_page_wc-serial-numbers',
			)
		);

		wc_admin_connect_page(
			array(
				'id'        => 'wsn-generators',
				'title'     => __( 'WooCommerce Serial Numbers', 'wc-serial-numbers' ),
				'screen_id' => 'serial-numbers_page_wsn-generators',
				'parent'    => 'toplevel_page_wc-serial-numbers',
			)
		);

		wc_admin_connect_page(
			array(
				'id'        => 'wsn-activations',
				'title'     => __( 'WooCommerce Serial Numbers', 'wc-serial-numbers' ),
				'screen_id' => 'serial-numbers_page_wsn-activations',
				'parent'    => 'toplevel_page_wc-serial-numbers',
			)
		);

		wc_admin_connect_page(
			array(
				'id'        => 'wsn-tools',
				'title'     => __( 'WooCommerce Serial Numbers', 'wc-serial-numbers' ),
				'screen_id' => 'serial-numbers_page_wsn-tools',
				'parent'    => 'toplevel_page_wc-serial-numbers',
			)
		);

		wc_admin_connect_page(
			array(
				'id'        => 'wsn-settings',
				'title'     => __( 'WooCommerce Serial Numbers', 'wc-serial-numbers' ),
				'screen_id' => 'serial-numbers_page_wsn-settings',
				'parent'    => 'toplevel_page_wc-serial-numbers',
			)
		);

	}

	/**
	 * Register pages.
	 *
	 * @since 1.2.0
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			'manage_options',
			'wc-serial-numbers',
			array( $this, 'render_keys_page' ),
			'dashicons-admin-network',
			'55.9'
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Serial Keys', 'wc-serial-numbers' ),
			__( 'Serial Keys', 'wc-serial-numbers' ),
			'manage_options',
			'wc-serial-numbers',
			array( $this, 'render_keys_page' )
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Generators', 'wc-serial-numbers' ),
			__( 'Generators', 'wc-serial-numbers' ),
			'manage_options',
			'wsn-generators',
			array( $this, 'render_generators_page' )
		);

		if ( Helper::is_software_support_enabled() ) {
			add_submenu_page(
				'wc-serial-numbers',
				__( 'Activations', 'wc-serial-numbers' ),
				__( 'Activations', 'wc-serial-numbers' ),
				'manage_options',
				'wsn-activations',
				array( $this, 'render_activations_page' )
			);
		}

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Tools', 'wc-serial-numbers' ),
			__( 'Tools', 'wc-serial-numbers' ),
			'manage_options',
			'wsn-tools',
			array( $this, 'render_tools_page' )
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Settings', 'wc-serial-numbers' ),
			__( 'Settings', 'wc-serial-numbers' ),
			'manage_options',
			'wsn-settings',
			array( Settings_Page::class, 'output' )
		);

		if ( ! Plugin::is_plugin_active( 'wc-serial-numbers-pro/wc-serial-numbers-pro.php' ) ) {
			add_submenu_page(
				'wc-serial-numbers',
				'',
				'<span style="color:#ff7a03;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Go Pro', 'wc-serial-numbers' ) . '</span>',
				'manage_options',
				'wsn-go-pro',
				array( $this, 'go_pro_redirect' )
			);
		}
	}


	/**
	 * Render keys page.
	 *
	 * @since 1.2.0
	 */
	public function render_keys_page() {
		include __DIR__ . '/views/html-list-keys.php';
	}

	/**
	 * Render generators page.
	 *
	 * @since 1.2.0
	 */
	public function render_generators_page() {
		include __DIR__ . '/views/html-list-generators.php';
	}

	/**
	 * Render activations page.
	 *
	 * @since 1.2.0
	 */
	public function render_activations_page() {
		include __DIR__ . '/views/html-list-activations.php';
	}

	/**
	 * Render tools page.
	 *
	 *
	 * @since #.#.#
	 */
	public function render_tools_page() {
		$tabs    = array(
			'general' => __( 'General', 'wc-serial-numbers' ),
			'import'  => __( 'Import', 'wc-serial-numbers' ),
			'export'  => __( 'Export', 'wc-serial-numbers' ),
		);
		$tab_ids = array_keys( $tabs );
		$tab     = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : reset( $tab_ids );
		if ( ! array_key_exists( $tab, $tabs ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wsn-tools&tab=' . reset( $tab_ids ) ) );
		}
		$current_tab = array_key_exists( $tab, $tabs ) ? $tab : '';
		include_once __DIR__ . '/views/html-tools.php';
	}

	/**
	 * Pro version redirect.
	 *
	 * @since 1.2.0
	 */
	public function go_pro_redirect() {
		if ( isset( $_GET['page'] ) && 'go_wcsn_pro' === $_GET['page'] ) {
			wp_redirect( 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=wp-menu&utm_campaign=gopro&utm_medium=wp-dash' );
			die;
		}
	}
}
