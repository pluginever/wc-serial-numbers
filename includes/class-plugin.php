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
class Plugin extends Framework\Plugin {

	/**
	 * Setup plugin.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function setup() {
		if ( ! $this->is_requirements_meet() ) {
			return;
		}

		// Include required files.
		$this->includes();

		// Instantiate classes.
		$this->instantiate();

		// Register hooks.
		$this->init_hooks();

		do_action( 'wc_serial_numbers_plugin_loaded' );
	}

	/**
	 * Check that the WordPress and PHP setup meets the plugin requirements.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	protected function is_requirements_meet() {
		if ( ! self::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$this->activation_notices[] = sprintf(
			/* translators: %s Plugin Name, %s Missing Plugin Name, %s Download URL link. */
				__( '%1$s requires %2$s to be installed and active. You can download %3$s from here.', 'wc-serial-numbers' ),
				'<strong>' . $this->plugin_name() . '</strong>',
				'<strong>WooCommerce</strong>',
				'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
			);
		}

		return parent::is_requirements_meet();
	}

	/**
	 * Include required files.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function includes() {
		require_once __DIR__ . '/class-autoloader.php';
	}

	/**
	 * Instantiate classes.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function instantiate() {
		// Instantiate classes.
		$this->installer   = new Installer();
		$this->keys        = new Keys();
		$this->generators  = new Generators();
		$this->activations = new Activations();
		$this->encryption  = new Encryption();
		$this->products    = new Products();
		$this->orders      = new Orders();

		// Instantiate frontend classes.
		if ( self::is_request( 'frontend' ) ) {
//			$this->account = new My_Account();
//			$this->frontend = new Frontend();
//			$this->api      = new API();
		}

		// Instantiate admin classes.
		if ( self::is_request( 'admin' ) ) {
			$this->admin = new Admin\Admin();
		}

		// Instantiate CLI classes.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$this->cli_commands = new CLI\Commands();
		}
	}


	/**
	 * Register hooks.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function init_hooks() {
		// Register activation hook.
		register_activation_hook( $this->plugin_file(), array( Installer::class, 'install' ) );
		register_deactivation_hook( $this->plugin_file(), array( Installer::class, 'deactivate' ) );
		register_uninstall_hook( $this->plugin_file(), array( Installer::class, 'uninstall' ) );
	}
}
