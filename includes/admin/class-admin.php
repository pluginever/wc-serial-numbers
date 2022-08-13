<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin;

// don't call the file directly.
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

	}

}
