<?php
namespace WooCommerceSerialNumbers\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Class Frontend.
 *
 * This class is responsible for all frontend functionality.
 *
 * @since   1.5.6
 * @package WooCommerceSerialNumbers\Frontend
 */
class Frontend {

	/**
	 * Frontend constructor.
	 *
	 * @since 1.5.6
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wc_serial_numbers_before_display_order_keys', 'wcsn_display_order_keys_title', 10, 2 );
		add_action( 'wc_serial_numbers_display_order_keys', 'wcsn_display_order_keys_table', 10, 2 );
	}

	/**
	 * Init classes.
	 *
	 * Example:
	 * WCSP()->services['frontend/my-account'] = new MyAccount();
	 *
	 * @since 1.5.6
	 * @return void
	 */
	public function init() {
		WCSN()->services['frontend/shortcodes'] = new Shortcodes();
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @since 1.5.6
	 * @return void
	 */
	public function enqueue_scripts() {
		WCSN()->enqueue_style( 'wc-serial-numbers-frontend', 'css/frontend-style.css' );
		WCSN()->enqueue_script( 'wc-serial-numbers-frontend', 'js/frontend-script.js', array( 'jquery' ) );
		wp_localize_script(
			'wc-serial-numbers-frontend',
			'wc_serial_numbers_frontend_vars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'i18n'     => array(
					'copied'  => __( 'Copied', 'wc-serial-numbers' ),
					'loading' => __( 'Loading', 'wc-serial-numbers' ),
				),
			)
		);
	}
}
