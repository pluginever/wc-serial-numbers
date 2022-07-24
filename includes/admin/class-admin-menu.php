<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin;

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
		add_filter( 'set-screen-option', array( __CLASS__, 'save_screen_options' ), 10, 3 );
		add_action( 'admin_menu', array( __CLASS__, 'register_pages' ) );
	}

	/**
	 * Save screen options
	 *
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 * @since 1.2.0
	 */
	public static function save_screen_options( $status, $option, $value ) {
		if ( 'serials_per_page' == $option ) {
			return $value;
		}
	}

	/**
	 * Register pages.
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

		if ( 'no' == get_option( 'wc_serial_numbers_disable_software_support' ) ) {
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
	}

	/**
	 * Render main serial_numbers page output.
	 *
	 * @since 1.3.1
	*/
	public static function render_main_page() {
		include_once __DIR__.'/views/html-main-page.php';
	}

	/**
	 * Render serial activation page output.
	 *
	 * @since 1.3.1
	 */
	public static function render_activations_page() {
		include_once __DIR__.'/views/html-activations-page.php';
	}

	/**
	 * Render serial generators page output.
	 *
	 * @since 1.3.1
	 */
	public static function render_generators_page() {
		include_once __DIR__.'/views/html-generators-page.php';
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
			'option'  => 'serials_per_page'
		);
		add_screen_option( 'per_page', $args );
		$status = "<ul>";
		$status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Available', 'wc-serial-numbers' ), __( 'Serial Numbers are valid and available for sell', 'wc-serial-numbers' ) );
		$status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Sold', 'wc-serial-numbers' ), __( 'Serial Numbers are sold and active', 'wc-serial-numbers' ) );
		$status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Refunded', 'wc-serial-numbers' ), __( 'Serial Numbers are sold then refunded', 'wc-serial-numbers' ) );
		$status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Cancelled', 'wc-serial-numbers' ), __( 'Serial Numbers are sold then cancelled', 'wc-serial-numbers' ) );
		$status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Expired', 'wc-serial-numbers' ), __( 'Serial Numbers are sold then expired', 'wc-serial-numbers' ) );
		$status .= sprintf( '<li><strong>%s</strong>: %s</li>', __( 'Inactive', 'wc-serial-numbers' ), __( 'Serial Numbers are are npt available for sell ', 'wc-serial-numbers' ) );
		$status .= "</ul>";

		get_current_screen()->add_help_tab(
			array(
				'id'      => 'status',
				'title'   => __( 'Statuses','wc-serial-numbers' ),
				'content' => $status,
			)
		);
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
