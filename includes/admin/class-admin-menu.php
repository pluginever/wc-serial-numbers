<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Class Admin Manager.
 *
 * @since   1.0.0
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Admin_Menu {

	/**
	 * Construct Admin_Menu.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {
		// add_filter( 'woocommerce_screen_ids', array( __CLASS__, 'screen_ids' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_nav_items' ), 20 );
		add_filter( 'set-screen-option', array( __CLASS__, 'save_screen_options' ), 10, 3 );
		add_action( 'admin_menu', array( __CLASS__, 'register_pages' ) );
	}


	/**
	 * Add the plugin screens to the WooCommerce screens
	 *
	 * @param array $ids Screen ids.
	 *
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
		if ( ! class_exists( '\Automattic\WooCommerce\Admin\Features\Navigation\Menu' ) ) {
			return;
		}
		// if ( function_exists( 'wc_admin_connect_page' ) ) {
		// wc_admin_connect_page(
		// array(
		// 'id'        => 'toplevel_page_wc-serial-numbers',
		// 'parent'    => 'toplevel_page_wc-serial-numbers',
		// 'screen_id' => 'toplevel_page_wc-serial-numbers',
		// 'title'     => __( 'Starter Plugin Settings', 'wc-serial-numbers' ),
		// )
		// );
		// }
	}

	/**
	 * Save screen options
	 *
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @since 1.2.0
	 * @return mixed
	 */
	public static function save_screen_options( $status, $option, $value ) {
		if ( 'serials_per_page' === $option ) {
			return $value;
		}
	}

	/**
	 * Register pages.
	 *
	 * @since 1.2.0
	 */
	public static function register_pages() {
		$serial_number_page = add_menu_page(
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			'manage_options',
			'wc-serial-numbers',
			array( __CLASS__, 'render_main_page' ),
			'dashicons-lock',
			'55.9'
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			'manage_options',
			'wc-serial-numbers',
			array( __CLASS__, 'render_main_page' )
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Generators', 'wc-serial-numbers' ),
			__( 'Generators', 'wc-serial-numbers' ),
			'manage_options',
			'wc-serial-numbers-generators',
			array( __CLASS__, 'render_generators_page' )
		);

		if ( Helper::is_software_support_enabled() ) {
			add_submenu_page(
				'wc-serial-numbers',
				__( 'Activations', 'wc-serial-numbers' ),
				__( 'Activations', 'wc-serial-numbers' ),
				'manage_options',
				'wc-serial-numbers-activations',
				array( __CLASS__, 'render_activations_page' )
			);
		}

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Settings', 'wc-serial-numbers' ),
			__( 'Settings', 'wc-serial-numbers' ),
			'manage_options',
			'wc-serial-numbers-settings',
			array( Admin_Settings::class, 'output' )
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Tools', 'wc-serial-numbers' ),
			__( 'Tools', 'wc-serial-numbers' ),
			'manage_options',
			'wc-serial-numbers-tools',
			array( __CLASS__, 'render_tools_page' )
		);

		if ( ! defined( 'WC_SERIAL_NUMBER_PRO_PLUGIN_VERSION' ) ) {
			add_submenu_page(
				'wc-serial-numbers',
				'',
				'<span style="color:#ff7a03;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Go Pro', 'wc-serial-numbers' ) . '</span>',
				'edit_others_posts',
				'go_wcsn_pro',
				array( __CLASS__, 'go_pro_redirect' )
			);
		}

		add_action( 'load-' . $serial_number_page, array( __CLASS__, 'load_serial_numbers_page' ) );

		if ( function_exists( 'wc_admin_connect_page' ) ) {
			wc_admin_connect_page(
				array(
					'id'        => 'toplevel_page_wc-serial-numbers',
					'screen_id' => 'toplevel_page_wc-serial-numbers',
					'title'     => '',
					'path'      => add_query_arg( 'page', 'wc-serial-numbers', 'admin.php' ),
				)
			);
			wc_admin_connect_page(
				array(
					'id'        => 'serial-numbers-activations',
					'parent'    => 'toplevel_page_wc-serial-numbers',
					'screen_id' => 'serial-numbers_page_wc-serial-numbers-activations',
					'title'     => '',
					'path'      => add_query_arg( 'page', 'wc-serial-numbers', 'admin.php' ),
				)
			);
			wc_admin_connect_page(
				array(
					'id'        => 'wc-serial-numbers-generators',
					'parent'    => 'toplevel_page_wc-serial-numbers',
					'screen_id' => 'serial-numbers_page_wc-serial-numbers-generators',
					'title'     => '',
					'path'      => add_query_arg( 'page', 'wc-serial-numbers', 'admin.php' ),
				)
			);
		}
	}

	/**
	 * Render main serial_numbers page output.
	 *
	 * @since 1.3.1
	 */
	public static function render_main_page() {
		$errors = [];
		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$id     = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( ! empty( $id ) ) {
			$serial_key = Serial_Keys::get( $id );
			if ( ! $serial_key->exists() ) {
				wp_safe_redirect( remove_query_arg( 'id' ) );
				exit();
			}
		}

		try {
			if ( ! empty( $_POST ) && ! check_admin_referer( 'serial_numbers_edit_key' ) ) {
				throw new \Exception( __( 'Error - please try again', 'wc-serial-numbers' ) );
			}

			if ( ! empty( $_POST['serial_numbers_edit'] ) ) {
				$id               = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
				$product_id       = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
				$order_id         = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
				$activation_limit = isset( $_POST['activation_limit'] ) ? absint( $_POST['activation_limit'] ) : 0;
				$valid_for        = isset( $_POST['valid_for'] ) ? absint( $_POST['valid_for'] ) : 0;
				$status           = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'available';
				$serial_key       = isset( $_POST['serial_key'] ) ? sanitize_textarea_field( wp_unslash( $_POST['serial_key'] ) ) : '';
				$expire_date      = isset( $_POST['expire_date'] ) ? sanitize_textarea_field( wp_unslash( $_POST['expire_date'] ) ) : '';

				if ( ! $product_id ) {
					throw new \Exception( __( 'Please select a product', 'wc-serial-numbers' ) );
				}
				if ( empty( $serial_key ) ) {
					throw new \Exception( __( 'Please insert serial key.', 'wc-serial-numbers' ) );
				}
				$serial = new Serial_Key( $id );
				$serial->set_props(
					[
						'product_id'       => $product_id,
						'order_id'         => $order_id,
						'key'              => $serial_key,
						'status'           => $status,
						'activation_limit' => $activation_limit,
						'valid_for'        => $valid_for,
					]
				);
				$save = $serial->save();
				if ( is_wp_error( $save ) ) {
					throw new \Exception( $save->get_error_message() );
				}

				wp_safe_redirect(
					[
						'page' => 'wc-serial-numbers',
						$id    => $id,
					]
				);
			}
		} catch ( \Exception $e ) {
			$errors[] = $e->getMessage();
		}

		if ( 'add' === $action || ! empty( $id ) ) {
			$serial_key = new Serial_key( $id );
			include_once __DIR__ . '/views/html-edit-serial-key.php';
		} else {
			include_once __DIR__ . '/views/html-list-serial-keys.php';
		}
	}

	/**
	 * Render serial activation page output.
	 *
	 * @since 1.3.1
	 */
	public static function render_activations_page() {
		include_once __DIR__ . '/views/html-activations-page.php';
	}

	/**
	 * Render serial generators page output.
	 *
	 * @since 1.3.1
	 */
	public static function render_generators_page() {
		$errors = [];
		$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );
		$id     = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		if ( ! empty( $id ) ) {
			$generator = Generators::get( $id );
			if ( ! $generator->exists() ) {
				wp_safe_redirect( remove_query_arg( 'id' ) );
				exit();
			}
		}

		try {
			if ( ! empty( $_POST ) && ! check_admin_referer( 'serial_numbers_edit_generator' ) ) {
				throw new \Exception( __( 'Error - please try again', 'wc-serial-numbers' ) );
			}

			if ( ! empty( $_POST['serial_generators_edit'] ) ) {
				$id               = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
				$name             = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : 0;
				$pattern          = isset( $_POST['pattern'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pattern'] ) ) : '';
				$type             = isset( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : '0';
				$activation_limit = isset( $_POST['activation_limit'] ) ? absint( $_POST['activation_limit'] ) : 0;
				$is_sequential    = isset( $_POST['type'] ) && 'sequential' === $_POST['type'] ? 1 : 0;
				$validity         = isset( $_POST['validity'] ) ? absint( $_POST['validity'] ) : 0;
				$expire_date      = isset( $_POST['date_expire'] ) ? sanitize_textarea_field( wp_unslash( $_POST['date_expire'] ) ) : '';

				if ( ! $name ) {
					throw new \Exception( __( 'Please insert name', 'wc-serial-numbers' ) );
				}
				if ( empty( $pattern ) ) {
					throw new \Exception( __( 'Please insert pattern.', 'wc-serial-numbers' ) );
				}
				$generator = new Generator( $id );
				$generator->set_props(
					[
						'name'             => $name,
						'pattern'          => $pattern,
						'is_sequential'    => $is_sequential,
						'activation_limit' => $activation_limit,
						'validity'         => $validity,
						'date_expire'      => $expire_date,
					]
				);
				$save = $generator->save();

				if ( is_wp_error( $save ) ) {
					throw new \Exception( $save->get_error_message() );
				}

				wp_safe_redirect(
					[
						'page' => 'wc-serial-numbers-generators',
						$id    => $id,
					]
				);
			}
		} catch ( \Exception $e ) {
			$errors[] = $e->getMessage();
		}

		if ( 'add' === $action || ! empty( $id ) ) {
			$generator = new Generator( $id );
			include_once __DIR__ . '/views/html-edit-serial-generator.php';
		} else {
			include_once __DIR__ . '/views/html-generators-page.php';
		}
	}

	/**
	 * Render tools page output.
	 *
	 * @since 1.3.1
	 */
	public static function render_tools_page() {
		include_once __DIR__ . '/views/html-tools-page.php';
	}

	/**
	 * Load serial numbers pages.
	 *
	 * @since 1.2.0
	 */
	public static function load_serial_numbers_page() {
		$args = array(
			'label'   => __( 'Serials per page', 'wc-serial-numbers' ),
			'default' => 20,
			'option'  => 'serials_per_page',
		);
		add_screen_option( 'per_page', $args );

		// $status = "<ul>";
		// $status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Available', 'wc-serial-numbers' ), __( 'Serial Numbers are valid and available for sell', 'wc-serial-numbers' ) );
		// $status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Sold', 'wc-serial-numbers' ), __( 'Serial Numbers are sold and active', 'wc-serial-numbers' ) );
		// $status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Refunded', 'wc-serial-numbers' ), __( 'Serial Numbers are sold then refunded', 'wc-serial-numbers' ) );
		// $status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Cancelled', 'wc-serial-numbers' ), __( 'Serial Numbers are sold then cancelled', 'wc-serial-numbers' ) );
		// $status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Expired', 'wc-serial-numbers' ), __( 'Serial Numbers are sold then expired', 'wc-serial-numbers' ) );
		// $status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Inactive', 'wc-serial-numbers' ), __( 'Serial Numbers are are npt available for sell ', 'wc-serial-numbers' ) );
		// $status .= "</ul>";
		//
		// get_current_screen()->add_help_tab(
		// array(
		// 'id'      => 'status',
		// 'title'   => __( 'Statuses','wc-serial-numbers' ),
		// 'content' => $status,
		// )
		// );
	}

	/**
	 * Pro version redirect.
	 *
	 * @since 1.2.0
	 */
	public static function go_pro_redirect() {
		if ( isset( $_GET['page'] ) && 'go_wcsn_pro' === $_GET['page'] ) {
			wp_redirect( 'https://www.pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=wp-menu&utm_campaign=gopro&utm_medium=wp-dash' );
			die;
		}
	}
}

return new Admin_Menu();
