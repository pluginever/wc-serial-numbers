<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Scripts.
 *
 * Handle scripts and styles.
 *
 * @since 1.4.2
 * @package WooCommerceStarterPlugin
 */
class Scripts extends Lib\Singleton {

	/**
	 * Scripts constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @param string $hook Hook name.
	 *
	 * @since 1.0.0
	 */
	public function frontend_scripts( $hook ) {
		WCSN()->enqueue_style( 'wc-serial-numbers-frontend', 'css/frontend-style.css' );
		WCSN()->enqueue_script( 'wc-serial-numbers-frontend', 'js/frontend-script.js', array( 'jquery' ) );
		wp_localize_script(
			'wc-serial-numbers-frontend',
			'wc_serial_numbers_frontend_vars',
			array(
				'apiurl' => site_url( '?wc-api=serial-numbers-api' ),
				'i18n'   => array(
					'copied'  => __( 'Copied', 'wc-serial-numbers' ),
					'loading' => __( 'Loading', 'wc-serial-numbers' ),
				),
			)
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Hook name.
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts( $hook ) {
		$screen_ids = Admin\Admin::get_instance()->get_screen_ids();
		if ( ! in_array( $hook, $screen_ids, true ) ) {
			return;
		}
		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		WCSN()->enqueue_style( 'wc-serial-numbers-admin', 'css/admin-style.css' );
		WCSN()->enqueue_script( 'wc-serial-numbers-admin', 'js/admin-script.js', array( 'jquery', 'jquery-ui-datepicker', 'select2', 'wp-util' ) );
		wp_localize_script(
			'wc-serial-numbers-admin',
			'wc_serial_numbers_vars',
			array(
				'i18n'         => array(
					'search_product'  => __( 'Search by product', 'wc-serial-numbers' ),
					'search_order'    => __( 'Search by order', 'wc-serial-numbers' ),
					'search_customer' => __( 'Search by customer', 'wc-serial-numbers' ),
					'show'            => __( 'Show', 'wc-serial-numbers' ),
					'hide'            => __( 'Hide', 'wc-serial-numbers' ),
					'copied'          => __( 'Copied', 'wc-serial-numbers' ),
				),
				'search_nonce' => wp_create_nonce( 'wc_serial_numbers_search_nonce' ),
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'apiurl'       => site_url( '?wc-api=serial-numbers-api' ),
			)
		);
	}


}
