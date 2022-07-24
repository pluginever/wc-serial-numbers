<?php

namespace PluginEver\WooCommerceSerialNumbers;
use PluginEver\WooCommerceSerialNumbers\CLI\CLI_Generator;
use \WP_CLI;

// don't call the file directly.
defined( 'ABSPATH' ) || exit;

/**
 * CLI class.
 *
 * @since 1.3.1
 * @package PluginEver\WooCommerceSerialNumbers
 */
class CLI {
	/**
	 * Load required files and hooks to make the CLI work.
	 */
	public function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Load command files.
	 */
	private function includes() {
		require_once dirname( __FILE__ ) . '/cli/class-cli-generator.php';
	}

	/**
	 * Sets up and hooks WP CLI to our CLI code.
	 */
	private function hooks() {
		WP_CLI::add_hook( 'after_wp_load', [ CLI_Generator::class, 'register_commands'] );
	}
}

new CLI();
