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

		wc_serial_numbers()->enqueue_style( 'wc-serial-numbers-admin', 'css/admin-style.css' );
		wc_serial_numbers()->enqueue_script( 'wc-serial-numbers-admin', 'js/admin-script.js', array( 'jquery', 'jquery-ui-datepicker', 'select2', 'wp-util' ) );
		wp_localize_script(
			'wc-serial-numbers-admin',
			'wc_serial_numbers_vars',
			array(
				'i18n'         => array(
					'search_product' => __( 'Search product by name', 'wc-serial-numbers' ),
					'search_order'   => __( 'Search order', 'wc-serial-numbers' ),
					'show'           => __( 'Show', 'wc-serial-numbers' ),
					'hide'           => __( 'Hide', 'wc-serial-numbers' ),
				),
				'search_nonce' => wp_create_nonce( 'wc_serial_numbers_search_nonce' ),
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			)
		);
	}


}
