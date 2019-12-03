<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.0
	 */
	private static $instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @return self Main instance.
	 * @since  1.0.0
	 * @static
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		$this->define_constants();
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
		add_action( 'admin_init', array( $this, 'set_actions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}


	/**
	 * define all required constants
	 *
	 * since 1.0.0
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'WC_SERIAL_NUMBERS_ADMIN_ABSPATH', dirname( __FILE__ ) );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		require_once( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/class-admin-menu.php' );
		require_once( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/class-settings-api.php' );
		require_once( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/class-settings.php' );
		require_once( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/class-metabox.php' );
		require_once( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/class-admin-notice.php' );
		require_once( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/actions-functions.php' );
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public function buffer() {
		ob_start();
	}

	/**
	 * Setup actions
	 *
	 * since 1.0.0
	 */
	public function set_actions() {

		$key = ! empty( $_GET['serial_numbers_action'] ) ? sanitize_key( $_GET['serial_numbers_action'] ) : false;

		if ( ! empty( $key ) ) {
			do_action( 'wc_serial_numbers_admin_get_' . $key, $_GET );
		}

		$key = ! empty( $_POST['serial_numbers_action'] ) ? sanitize_key( $_POST['serial_numbers_action'] ) : false;

		if ( ! empty( $key ) ) {
			do_action( 'wc_serial_numbers_admin_post_' . $key, $_POST );
		}
	}


	/**
	 * Enqueue admin related assets
	 *
	 * @param $hook
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		$plugin_url = wc_serial_numbers()->plugin_url();
		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_style( 'select2' );
		wp_enqueue_style( 'wc-serial-numbers-admin', $plugin_url . '/assets/css/serial-numbers-admin.css', array(
			'jquery-ui-style',
			'woocommerce_admin_styles'
		), wc_serial_numbers()->version );

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'wc-serial-numbers', $plugin_url . '/assets/js/serial-numbers-admin.js', [
			'jquery',
			'wp-util',
			'select2',
		], time(), true );
		wp_localize_script( 'wc-serial-numbers', 'WCSerialNumbers', array(
			'dropDownNonce'             => wp_create_nonce( 'serial_numbers_search_dropdown' ),
			'placeholderSearchProducts' => __( 'Search by product name', 'wc-serial-numbers' ),
			'show'                      => __( 'Show', 'wc-serial-numbers' ),
			'hide'                      => __( 'Hide', 'wc-serial-numbers' ),
		) );
	}

}

WC_Serial_Numbers_Admin::instance();
