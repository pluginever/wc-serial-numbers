<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin;

// don't call the file directly.
use PluginEver\WooCommerceSerialNumbers\Plugin;

defined( 'ABSPATH' ) || exit();

/**
 * Admin class.
 *
 * @since 1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class Admin {

	/**
	 * Admin constructor.
	 *
	 * @since 1.3.1
	 */
	public function __construct() {
		$this->instantiate();
		$this->init_hooks();
	}

	/**
	 * Instantiate classes.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	protected function instantiate() {
		$this->menus    = new Menus();
		$this->products = new Products();
		$this->orders   = new Orders();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	protected function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.3.1
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {
		wp_register_style( 'wc-serial-numbers-admin', Plugin::get()->assets_url( 'css/admin-style.css' ), array(), Plugin::get()->plugin_version() );
		wp_register_script( 'wc-serial-numbers-admin', Plugin::get()->assets_url( 'js/admin-script.js' ), array( 'jquery' ), Plugin::get()->plugin_version(), true );

		wp_enqueue_style( 'wc-serial-numbers-admin' );
		wp_enqueue_script( 'wc-serial-numbers-admin' );
	}

}
