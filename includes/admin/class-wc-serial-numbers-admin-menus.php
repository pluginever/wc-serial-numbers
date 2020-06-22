<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin_Menus {

	/**
	 * WC_Serial_Numbers_Admin_Menus constructor.
	 */
	public function __construct() {
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'register_pages' ) );
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
	public function save_screen_options( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Register pages.
	 * @since 1.2.0
	 */
	public function register_pages() {
		$role               = wc_serial_numbers_get_user_role();
		$serial_number_page = add_menu_page(
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			$role,
			'wc-serial-numbers',
			array( 'WC_Serial_Numbers_Admin_Screen', 'output' ),
			'dashicons-lock',
			'55.9'
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			$role,
			'wc-serial-numbers',
			array( 'WC_Serial_Numbers_Admin_Screen', 'output' )
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Activations', 'wc-serial-numbers' ),
			__( 'Activations', 'wc-serial-numbers' ),
			$role,
			'wc-serial-numbers-activations',
			array( 'WC_Serial_Numbers_Admin_Activations_Screen', 'output' )
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Settings', 'wc-serial-numbers' ),
			__( 'Settings', 'wc-serial-numbers' ),
			$role,
			'wc-serial-numbers-settings',
			array( 'WC_Serial_Numbers_Admin_Settings', 'output' )
		);
	}

}

new WC_Serial_Numbers_Admin_Menus();
