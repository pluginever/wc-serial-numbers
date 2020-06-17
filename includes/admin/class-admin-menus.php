<?php

namespace PluginEver\SerialNumbers\Admin;

defined( 'ABSPATH' ) || exit();

class Admin_Menus {
	/**
	 * @var string
	 */
	protected $role;

	/**
	 * Admin_Menus constructor.
	 */
	public function __construct() {
		$this->role = apply_filters( 'wc_serial_numbers_menu_visibility_role', 'manage_woocommerce' );
		add_action( 'admin_menu', array( $this, 'register_pages' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
	}

	/**
	 * Adds page to admin menu
	 */
	public function register_pages() {
		if ( ! wc_serial_numbers()->is_wc_active() ) {
			return;
		}
		$serial_number_page = add_menu_page(
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			$this->role,
			'serial-numbers',
			array( $this, 'serial_numbers_page' ),
			'dashicons-lock',
			'55.9'
		);
		add_submenu_page(
			'serial-numbers',
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			$this->role,
			'serial-numbers',
			array( $this, 'serial_numbers_page' )
		);

		if ( ! wc_serial_numbers()->get_settings( 'disable_software_support', false, true ) ) {
			add_submenu_page(
				'serial-numbers',
				__( 'Activations', 'wc-serial-numbers' ),
				__( 'Activations', 'wc-serial-numbers' ),
				$this->role,
				'serial-numbers-activations',
				array( $this, 'activations_page' )
			);
		}
		add_submenu_page(
			'serial-numbers',
			__( 'Products', 'wc-serial-numbers' ),
			__( 'Products', 'wc-serial-numbers' ),
			$this->role,
			'serial-numbers-products',
			array( $this, 'serial_numbers_insight_page' )
		);

		add_submenu_page(
			'serial-numbers',
			__( 'Import', 'wc-serial-numbers' ),
			__( 'Import', 'wc-serial-numbers' ),
			$this->role,
			'serial-numbers-import',
			array( $this, 'serial_numbers_import_page' )
		);

		add_submenu_page(
			'serial-numbers',
			__( 'Export', 'wc-serial-numbers' ),
			__( 'Export', 'wc-serial-numbers' ),
			$this->role,
			'serial-numbers-export',
			array( $this, 'serial_numbers_export_page' )
		);
		add_action( 'load-' . $serial_number_page, array( $this, 'load_serial_numbers_page' ) );
	}

	public function load_serial_numbers_page() {
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
				'title'   => __( 'Statuses' ),
				'content' => $status,
			)
		);
	}

	/**
	 * Save screen options
	 *
	 * @param $status
	 * @param $option
	 * @param $value
	 *
	 * @return mixed
	 * @since 1.1.5
	 */
	public function save_screen_options( $status, $option, $value ) {
		if ( 'serials_per_page' == $option ) {
			return $value;
		}
	}


	public function serial_numbers_page() {
		Serials_Page::output();
	}

	public function activations_page() {
		Activations_Page::output();
	}

	public function serial_numbers_insight_page() {

	}

	public function serial_numbers_import_page() {

	}

	public function serial_numbers_export_page() {

	}
}

new Admin_Menus();
