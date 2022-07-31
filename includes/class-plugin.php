<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Main plugin class.
 *
 * @since 1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Plugin extends Framework\AbstractPlugin {

	/**
	 * Setup plugin.
	 *
	 * @return void
	 * @since 1.3.1
	 */
	public function setup() {
		// initialize the plugin.
		if ( ! self::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'admin_notices', array( $this, 'dependency_notice' ) );

			return;
		}
		add_action( 'woocommerce_loaded', array( $this, 'init_plugin' ) );
	}
	/**
	 * Missing dependency notice.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function dependency_notice() {
		$notice = sprintf(
		/* translators: %s Plugin Name, %s Missing Plugin Name, %s Download URL link. */
			__( '%1$s requires %2$s to be installed and active. You can download %3$s from here.', 'wc-serial-numbers' ),
			'<strong>' . $this->get_plugin_name() . '</strong>',
			'<strong>WooCommerce</strong>',
			'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
		);
		echo wp_kses_post( '<div class="notice notice-error"><p>' . $notice . '</p></div>' );
	}
	/**
	 * Initializes the plugin.
	 *
	 * Plugins can override this to set up any handlers after WordPress is ready.
	 *
	 * @return void
	 * @since 1.3.1
	 */
	public function init_plugin() {
		include_once __DIR__ . '/class-helper.php';
		include_once __DIR__ . '/class-install.php';
		include_once __DIR__ . '/class-serial-keys.php';
		include_once __DIR__ . '/class-generators.php';
		include_once __DIR__ . '/class-activations.php';
		include_once __DIR__ . '/entity/class-data.php';
		include_once __DIR__ . '/entity/class-serial-key.php';
		include_once __DIR__ . '/entity/class-generator.php';
		include_once __DIR__ . '/entity/class-activation.php';
		include_once __DIR__ . '/class-order.php';
		include_once __DIR__ . '/class-product.php';

		if ( self::is_request( 'ajax' ) ) {
			include_once __DIR__ . '/class-ajax.php';
		}

		if ( self::is_request( 'admin' ) || self::is_request( 'ajax' ) ) {
			include_once __DIR__ . '/admin/class-admin-manager.php';
			include_once __DIR__ . '/admin/class-admin-settings.php';
			include_once __DIR__ . '/admin/class-admin-menu.php';
			include_once __DIR__ . '/admin/class-admin-order.php';
			include_once __DIR__ . '/admin/class-admin-product.php';
			// include_once __DIR__ . '/admin/class-meta-boxes.php';
		}

		if ( defined( '\WP_CLI' ) && WP_CLI ) {
			include_once __DIR__ . '/class-cli.php';
		}

		do_action( 'wc_serial_numbers_loaded' );
	}
}
