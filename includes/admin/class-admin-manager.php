<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
use PluginEver\WooCommerceSerialNumbers\Plugin;

defined( 'ABSPATH' ) || exit();

/**
 * Class Admin Manager.
 *
 * @since   1.0.0
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Admin_Manager {

	/**
	 * Construct Admin_Manager.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 20 );
		add_action( 'wp_ajax_serial_numbers_get_admin_template', array( __CLASS__, 'get_admin_template' ) );
		add_action( 'admin_footer_text', array( __CLASS__, 'admin_footer_note' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook Page hook.
	 *
	 * @since 1.0.0
	 */
	public static function enqueue_scripts( $hook ) {
		wp_register_style( 'serial-numbers-admin', Plugin::instance()->get_assets_url( 'css/admin.css' ), [], Plugin::instance()->get_plugin_version() );
		wp_register_script( 'serial-numbers-admin', Plugin::instance()->get_assets_url( 'js/admin.js' ), [ 'jquery' ], Plugin::instance()->get_plugin_version(), true );

		if ( str_contains( $hook, 'serial-numbers' ) ) {
			wp_enqueue_style( 'serial-numbers-admin' );
			wp_enqueue_script( 'serial-numbers-admin' );

			wp_localize_script(
				'serial-numbers-admin',
				'wc_serial_numbers_admin_i10n',
				array(
					'i18n'    => array(
						'search_product' => __( 'Search product by name', 'wc-serial-numbers' ),
						'search_order'   => __( 'Search order', 'wc-serial-numbers' ),
						'show'           => __( 'Show', 'wc-serial-numbers' ),
						'hide'           => __( 'Hide', 'wc-serial-numbers' ),
					),
					'nonce'   => wp_create_nonce( 'wc_serial_numbers_admin_js_nonce' ),
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			);
		}
	}


	public static function get_admin_template() {
		ob_start();
		include __DIR__ . '/views/html-insert-serial-number.php';
		wp_die();
	}

	/**
	 * Add footer note
	 *
	 * @return string
	 */
	public static function admin_footer_note() {
		$screen = get_current_screen();
		if ( 'wc-serial-numbers' === $screen->parent_base ) {
			$star_url = 'https://wordpress.org/support/plugin/wc-serial-numbers/reviews/?filter=5#new-post';
			$text     = sprintf( __( 'If you like <strong>WooCommerce Serial Numbers</strong> please leave us a <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating. It takes a minute and helps a lot. Thanks in advance!', 'wc-serial-numbers' ), $star_url );

			return $text;
		}
	}

}

return new Admin_Manager();
