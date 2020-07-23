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
		if ( 'serials_per_page' == $option ) {
			return $value;
		}
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
				'title'   => __( 'Statuses','wc-serial-numbers' ),
				'content' => $status,
			)
		);
	}

}

new WC_Serial_Numbers_Admin_Menus();
