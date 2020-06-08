<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin {

	/**
	 * WC_Serial_Numbers_Admin constructor.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'buffer' ), 1 );
		add_action( 'init', array( __CLASS__, 'includes' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public static function buffer() {
		ob_start();
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {
		include_once dirname( __FILE__ ) . '/class-settings-framework.php';
		include_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-settings.php';
		include_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-menus.php';
		include_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-actions.php';
		include_once dirname( __FILE__ ) . '/class-wc-serial-numbers-admin-metaboxes.php';
	}



	/**
	 * Enqueue admin related assets
	 *
	 * @param $hook
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_scripts( $hook ) {
		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_style( 'select2' );
		wp_enqueue_style( 'wc-serial-numbers-admin', wc_serial_numbers()->plugin_url() . '/assets/css/serial-numbers-admin.css', array(
			'jquery-ui-style',
			'woocommerce_admin_styles'
		), wc_serial_numbers()->get_version() );

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'wc-serial-numbers', wc_serial_numbers()->plugin_url() . '/assets/js/serial-numbers-admin.js', [
			'jquery',
			'wp-util',
			'select2',
		], wc_serial_numbers()->get_version(), true );
		wp_localize_script( 'wc-serial-numbers', 'WCSerialNumbers', array(
			'i18n'         => array(
				'search_product_placeholder' => __( 'Search product by name', 'wc-serial-numbers' ),
				'search_order_placeholder'   => __( 'Search order', 'wc-serial-numbers' ),
				'show'                       => __( 'Show', 'wc-serial-numbers' ),
				'hide'                       => __( 'Hide', 'wc-serial-numbers' ),
			),
			'search_nonce' => wp_create_nonce( 'wcsn_search_nonce' ),
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
		) );
	}
}

WC_Serial_Numbers_Admin::init();
