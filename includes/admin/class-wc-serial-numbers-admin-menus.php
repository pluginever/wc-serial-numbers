<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin_Menus {

	protected $settings;

	/**
	 * WC_Serial_Numbers_Admin_Menus constructor.
	 */
	public function __construct() {
		$this->settings = new Ever_Settings_Framework();
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'wcsn_settings_tab_content_settings', array( $this, 'render_settings' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
	}

	/**
	 * Adds page to admin menu
	 */
	public function admin_menu() {
		$role               = apply_filters( 'wc_serial_numbers_menu_visibility_role', 'manage_woocommerce' );
		$serial_number_page = add_menu_page(
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			$role,
			'wc-serial-numbers',
			array( $this, 'serial_numbers_page' ),
			'dashicons-admin-network',
			'55.9'
		);
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			$role,
			'wc-serial-numbers',
			array( $this, 'serial_numbers_page' )
		);

		add_submenu_page(
			'wc-serial-numbers',
			__( 'Products', 'wc-serial-numbers' ),
			__( 'Products', 'wc-serial-numbers' ),
			$role,
			'wc-serial-numbers-products',
			array( $this, 'products_page' )
		);

		if ( wc_serial_numbers()->api_enabled() ) {
			add_submenu_page(
				'wc-serial-numbers',
				__( 'Activations', 'wc-serial-numbers' ),
				__( 'Activations', 'wc-serial-numbers' ),
				$role,
				'wc-serial-numbers-activations',
				array( $this, 'activations_page' )
			);
		}

		add_submenu_page(
			'wc-serial-numbers',
			__( 'WC Serial Numbers Settings', 'wc-serial-numbers' ),
			__( 'Settings', 'wc-serial-numbers' ),
			$role,
			'wc-serial-numbers-settings',
			array( $this, 'settings_page' )
		);

		add_action( 'load-' . $serial_number_page, array( $this, 'load_serial_numbers_page' ) );
	}

	/**
	 * @since 1.1.5
	 */
	public function load_serial_numbers_page() {
		//wp_enqueue_style('wc-serial-numbers-admin');

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

	/**
	 * Setup settings fields
	 *
	 * @since 1.1.5
	 */
	public function settings_init() {
		//set the settings
		$this->settings->set_sections( apply_filters( 'wcsn_setting_sections', [] ) );
		$this->settings->set_fields( apply_filters( 'wcsn_setting_fields', [] ) );

		//initialize settings
		$this->settings->admin_init();
	}

	/**
	 * Render serial numbers page
	 * @since 1.1.5
	 */
	public function serial_numbers_page() {
		$action = isset( $_GET['action'] ) && ! empty( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : 'list';
		switch ( $action ) {
			case 'add':
				include dirname( __FILE__ ) . '/views/add-serial-number.php';
				break;
			case 'edit':
				include dirname( __FILE__ ) . '/views/edit-serial-number.php';
				break;
			case 'list':
			default:
				include dirname( __FILE__ ) . '/views/list-serial-numbers.php';
				do_action( 'wpcp_serial_number_page', $action );
		}
	}

	/**
	 * Render activation page
	 *
	 * @since 1.1.5
	 */
	public function products_page() {
		include dirname( __FILE__ ) . '/views/list-products.php';
	}

	/**
	 * Render activation page
	 *
	 * @since 1.1.5
	 */
	public function activations_page() {

	}

	/**
	 * @since 1.1.5
	 */
	public function settings_page() {
		include dirname( __FILE__ ) . '/views/settings-page.php';
	}

	/**
	 * @since 1.1.5
	 */
	public function render_settings() {
		$this->settings->show_settings();
	}

}

new WC_Serial_Numbers_Admin_Menus();
