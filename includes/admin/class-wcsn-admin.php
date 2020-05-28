<?php
defined( 'ABSPATH' ) || exit();
if ( ! class_exists( 'Ever_Settings_Framework' ) ) {
	require_once dirname( __FILE__ ) . '/class-settings-framework.php';
}

class WCSN_Admin {
	/**
	 * @var Ever_Settings_Framework
	 */
	private $settings;

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		$this->settings = new Ever_Settings_Framework();
		add_action( 'plugins_loaded', array( $this, 'includes' ) );
		add_action( 'init', array( $this, 'init_hooks' ) );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
//		require_once( dirname( __FILE__ ) . '/class-admin-menu.php' );
//		require_once( dirname( __FILE__ ) . '/class-settings-api.php' );
//		require_once( dirname( __FILE__ ) . '/class-wcsn-settings.php' );
//		require_once( dirname( __FILE__ ) . '/class-metabox.php' );
//		require_once( dirname( __FILE__ ) . '/class-admin-notice.php' );
//		require_once( dirname( __FILE__ ) . '/actions-functions.php' );
	}

	/**
	 * Initialize all admin hooks
	 *
	 * @since 1.1.5
	 */
	public function init_hooks() {
		add_action( 'admin_init', array( __CLASS__, 'buffer' ), 1 );
		add_action( 'admin_init', array( __CLASS__, 'set_actions' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public static function buffer() {
		ob_start();
	}

	/**
	 * Setup actions
	 *
	 * since 1.0.0
	 */
	public static function set_actions() {

		$key = ! empty( $_GET['serial_numbers_action'] ) ? sanitize_key( $_GET['serial_numbers_action'] ) : false;

		if ( ! empty( $key ) ) {
			do_action( 'wcsn_admin_get_' . $key, $_GET );
		}

		$key = ! empty( $_POST['serial_numbers_action'] ) ? sanitize_key( $_POST['serial_numbers_action'] ) : false;

		if ( ! empty( $key ) ) {
			do_action( 'wcsn_admin_post_' . $key, $_POST );
		}
	}

	/**
	 * Enqueue admin related assets
	 *
	 * @param $hook
	 *
	 * @since 1.0.0
	 */
	public static function load_scripts( $hook ) {
//		$plugin_url = wcsn()->plugin_url();
//		wp_enqueue_style( 'jquery-ui-style' );
//		wp_enqueue_style( 'select2' );
//		wp_enqueue_style( 'wc-serial-numbers-admin', $plugin_url . '/assets/css/serial-numbers-admin.css', array(
//			'jquery-ui-style',
//			'woocommerce_admin_styles'
//		), wcsn()->version );
//
//		wp_enqueue_script( 'jquery-ui-datepicker' );
//		wp_enqueue_script( 'wc-serial-numbers', $plugin_url . '/assets/js/serial-numbers-admin.js', [
//			'jquery',
//			'wp-util',
//			'select2',
//		], time(), true );
//		wp_localize_script( 'wc-serial-numbers', 'WCSerialNumbers', array(
//			'dropDownNonce'             => wp_create_nonce( 'serial_numbers_search_dropdown' ),
//			'placeholderSearchProducts' => __( 'Search by product name', 'wc-serial-numbers' ),
//			'show'                      => __( 'Show', 'wc-serial-numbers' ),
//			'hide'                      => __( 'Hide', 'wc-serial-numbers' ),
//		) );
	}


	/**
	 * @since 1.2.0
	 */
	public function settings_init() {
		//set the settings
		$this->settings->set_sections( $this->get_settings_sections() );
		$this->settings->set_fields( $this->get_settings_fields() );
		//initialize settings
		$this->settings->admin_init();
	}

	public function get_settings_sections() {
		$sections = array(
			array(
				'id'    => 'wcsn_general_settings',
				'title' => __( 'General Settings', 'wc-serial-numbers' )
			),
			array(
				'id'    => 'wcsn_customer_dashboard',
				'title' => __( 'Customer Dashboard', 'wc-serial-numbers' )
			),
			array(
				'id'    => 'wcsn_stock_settings',
				'title' => __( 'Stock Settings', 'wc-serial-numbers' )
			),
			array(
				'id'    => 'wsn_delivery_settings',
				'title' => __( 'Delivery Settings', 'wc-serial-numbers' )
			),
		);

		return apply_filters( 'wc_serial_numbers_settings_sections', $sections );
	}


	public function get_settings_fields() {
		$settings_fields = array(
			'wcsn_general_settings'   => array(
				array(
					'name'    => 'automatic_delivery',
					'label'   => __( 'Automatic delivery', 'wc-serial-numbers' ),
					'desc'    => __( 'Automatically assign serial numbers with completed order', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'reuse_serial_numbers',
					'label'   => __( 'Reuse Serial Numbers', 'wc-serial-numbers' ),
					'desc'    => __( 'If an order is cancelled serials will be reused', 'wc-serial-numbers' ),
					'default' => 'on',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'allow_duplicate',
					'label'   => __( 'Allow Duplicate', 'wc-serial-numbers' ),
					'desc'    => __( 'will create duplicate serial numbers for each products', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'autocomplete_order',
					'label'   => __( 'Autocomplete Order', 'wc-serial-numbers' ),
					'desc'    => __( 'will automatically complete order upon successful payment', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'disable_software',
					'label'   => __( 'Disable Software', 'wc-serial-numbers' ),
					'desc'    => __( 'will disable all the features related to software API', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				)
			),
			'wcsn_customer_dashboard' => array(

			),
			'wsn_delivery_settings'   => array(
				array(
					'name'        => 'heading_text',
					'label'       => __( 'Heading Text', 'wc-serial-numbers-pro' ),
					'placeholder' => '',
					'desc'        => __( 'This text will show above the serial numbers.', 'wc-serial-numbers-pro' ),
					'class'       => 'ever-field-inline',
					'default'     => __( 'Serial Numbers', 'wc-serial-numbers-pro' ),
					'type'        => 'text',
				),
				array(
					'name'        => 'table_column_heading',
					'label'       => __( 'Table Column Heading', 'wc-serial-numbers-pro' ),
					'placeholder' => '',
					'desc'        => __( 'The heading will appear in the column of serial numbers', 'wc-serial-numbers-pro' ),
					'class'       => 'ever-field-inline',
					'default'     => __( 'Serial Number', 'wc-serial-numbers-pro' ),
					'type'        => 'text',
				),
				array(
					'name'        => 'serial_key_label',
					'label'       => __( 'Serial Key Label', 'wc-serial-numbers-pro' ),
					'placeholder' => '',
					'desc'        => __( 'Serial Key label .', 'wc-serial-numbers-pro' ),
					'class'       => 'ever-field-inline',
					'default'     => __( 'Serial Key', 'wc-serial-numbers-pro' ),
					'type'        => 'text',
				),
				array(
					'name'        => 'serial_email_label',
					'label'       => __( 'Serial Email Label', 'wc-serial-numbers-pro' ),
					'placeholder' => '',
					'desc'        => __( 'Serial Email label.', 'wc-serial-numbers-pro' ),
					'class'       => 'ever-field-inline',
					'default'     => __( 'Serial Email', 'wc-serial-numbers-pro' ),
					'type'        => 'text',
				),
				array(
					'name'    => 'show_validity',
					'label'   => __( 'Show Validity for Email', 'wc-serial-numbers-pro' ),
					'desc'    => __( 'If you don\'t like to show license key Validity, select \'No\'.', 'wc-serial-numbers-pro' ),
					'class'   => 'ever-field-inline',
					'type'    => 'select',
					'default' => 'yes',
					'options' => array(
						'yes' => __( 'Yes', 'wc-serial-numbers-pro' ),
						'no'  => __( 'No', 'wc-serial-numbers-pro' ),
					),
				),
				array(
					'name'    => 'show_activation_limit',
					'label'   => __( 'Show Activation Limit', 'wc-serial-numbers-pro' ),
					'desc'    => __( 'If you don\'t like to show license key Activation Limit, select \'No\'.', 'wc-serial-numbers-pro' ),
					'class'   => 'ever-field-inline',
					'type'    => 'select',
					'default' => 'yes',
					'options' => array(
						'yes' => __( 'Yes', 'wc-serial-numbers-pro' ),
						'no'  => __( 'No', 'wc-serial-numbers-pro' ),
					),
				)
			),
			'wcsn_stock_settings'     => array(
				array(
					'name'    => 'low_stock_alert',
					'label'   => __( 'Low Stock Alert', 'wc-serial-numbers' ),
					'desc'    => __( 'Enable low stock admin alert ', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'low_stock_notification',
					'label'   => __( 'Low Stock Notification', 'wc-serial-numbers' ),
					'desc'    => __( 'Enable low stock email notification ', 'wc-serial-numbers' ),
					'default' => '',
					'class'   => 'ever-field-inline',
					'type'    => 'checkbox',
					'checked' => '',
				),
				array(
					'name'    => 'low_stock_threshold',
					'label'   => __( 'Low Stock Threshold', 'wc-serial-numbers' ),
					'desc'    => __( 'Below the above number will trigger low stock email notification', 'wc-serial-numbers' ),
					'default' => '5',
					'class'   => 'ever-field-inline',
					'type'    => 'number',
				),
				array(
					'name'     => 'low_stock_notification_email',
					'label'    => __( 'Low Stock Email', 'wc-serial-numbers' ),
					'desc'     => __( 'The email address to be used for sending the low stock email notification', 'wc-serial-numbers' ),
					'default'  => get_option( 'admin_email' ),
					'class'    => 'ever-field-inline',
					'type'     => 'text',
					'sanitize' => 'sanitize_email',
				),
			),
		);

		return apply_filters( 'wc_serial_numbers_settings_fields', $settings_fields );
	}

	/**
	 * Adds page to admin menu
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			'manage_woocommerce', 'wc-serial-numbers',
			array( $this, 'serial_numbers_page' ),
			'dashicons-admin-network',
			'55.9'
		);
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			__( 'Serial Numbers', 'wc-serial-numbers' ),
			'manage_woocommerce', 'wc-serial-numbers',
			array( $this, 'serial_numbers_page' )
		);

		//todo if disabled hide
		add_submenu_page(
			'wc-serial-numbers',
			__( 'Activations', 'wc-serial-numbers' ),
			__( 'Activations', 'wc-serial-numbers' ),
			'manage_woocommerce',
			'wc-serial-numbers-activations',
			array( $this, 'activations_page' )
		);
		add_submenu_page(
			'wc-serial-numbers',
			'WC Serial Numbers Settings',
			'Settings',
			'manage_woocommerce',
			'wc-serial-numbers-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Renders serial numbers page.
	 *
	 * @since
	 */
	public function serial_numbers_page() {
		include dirname( __FILE__ ) . '/views/serials-page.php';
	}

	/**
	 * Renders activation page.
	 *
	 * @since
	 */
	public function activations_page() {

	}

	/**
	 * Render settings Page
	 * @since
	 */
	public function settings_page() {
		include dirname( __FILE__ ) . '/views/settings-page.php';
	}


}

new WCSN_Admin();
