<?php

namespace pluginever\SerialNumbers;
defined( 'ABSPATH' ) || exit();

class Assets {
	public static function init() {
//		add_action( 'enqueue_scripts', array( __CLASS__, 'public_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );
	}

	/**
	 * Enqueue admin related assets
	 *
	 * @param $hook
	 *
	 * @since 1.2.0
	 */
	public static function public_scripts() {

	}

	/**
	 * Enqueue admin related assets
	 *
	 * @param $hook
	 *
	 * @since 1.2.0
	 */
	public static function admin_scripts( $hook ) {
		$css_url = wc_serial_numbers()->plugin_url() . '/assets/css';
		$js_url  = wc_serial_numbers()->plugin_url() . '/assets/js';
		$version = wc_serial_numbers()->get_version();
		wp_register_style( 'serial-list-tables', $css_url .'/list-tables.css', array(), $version );
		wp_enqueue_style( 'admin-general', $css_url .'/admin-general.css', array('woocommerce_admin_styles', 'jquery-ui-style'), $version );




		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'wc-serial-numbers', $js_url . '/serial-numbers-admin.js', [ 'jquery', 'wp-util', 'select2', ], $version, true );

		wp_localize_script( 'wc-serial-numbers', 'SerialNumberVars', array(
			'i18n'         => array(
				'search_product_placeholder' => __( 'Search product by name', 'wc-serial-numbers' ),
				'search_order_placeholder'   => __( 'Search order', 'wc-serial-numbers' ),
				'show'                       => __( 'Show', 'wc-serial-numbers' ),
				'hide'                       => __( 'Hide', 'wc-serial-numbers' ),
			),
			'search_nonce' => wp_create_nonce( 'wc_serial_numbers_search_nonce' ),
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
		) );
	}

}

Assets::init();
