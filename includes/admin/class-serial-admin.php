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
	 * EAccounting_Admin constructor.
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
		require_once( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/class-serial-admin-menus.php' );
		require_once( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/class-serial-admin-notices.php' );
		require_once( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/class-metabox.php' );
		require_once( WC_SERIAL_NUMBERS_ADMIN_ABSPATH . '/admin-functions.php' );
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

		$key = ! empty( $_GET['wcsn-action'] ) ? sanitize_key( $_GET['wcsn-action'] ) : false;

		if ( ! empty( $key ) ) {
			do_action( 'wcsn_admin_get_' . $key, $_GET );
		}

		$key = ! empty( $_POST['wcsn-action'] ) ? sanitize_key( $_POST['wcsn-action'] ) : false;

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
	public function enqueue_scripts( $hook ) {
//		if ( ! preg_match( '/accounting/', $hook ) ) {
//			return;
//		}


	}


}

WC_Serial_Numbers_Admin::instance();
