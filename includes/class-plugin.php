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
		add_action( 'init', [ $this, 'load_textdomain' ] );
		add_filter( 'network_admin_plugin_action_links_' . $this->get_plugin_basename(), [ $this, 'register_network_plugin_actions' ], 10, 4 );
		add_filter( 'plugin_action_links_' . $this->get_plugin_basename(), [ $this, 'register_plugin_actions' ], 10, 4 );
		add_filter( 'plugin_row_meta', [ $this, 'register_plugin_row_meta' ], 10, 4 );
		add_action( 'plugins_loaded', array( $this, 'need_plugin' ) );
		add_action( 'woocommerce_loaded', array( $this, 'init_plugin' ) );
	}

	/**
	 * Loads the plugin text domain.
	 *
	 * @return void
	 * @since  1.3.1
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wc-serial-numbers', false, dirname( $this->get_plugin_basename() ) . '/languages' );
	}

	/**
	 * Registers plugin actions on network pages.
	 *
	 * @param array $actions An array of plugin action links.
	 * @param string $plugin_file Path to the plugin file relative to the plugins' directory.
	 * @param array $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @param string $context The plugin context. By default, this can include 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 *
	 * @return array
	 * @since 1.3.1
	 *
	 */
	public function register_network_plugin_actions( $actions, $plugin_file, $plugin_data, $context ) {
		return $actions;
	}

	/**
	 * Registers plugin actions on blog pages.
	 *
	 * @param string[] $actions An array of plugin action links.
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @param string $context The plugin context. By default, this can include 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 *
	 * @return string[]
	 * @since  1.3.1
	 */
	public function register_plugin_actions( $actions, $plugin_file, $plugin_data, $context ) {
		if ( ! empty( $this->get_plugin_settings_url() ) ) {
			$settings_link = sprintf(
				'<a href="%1$s" aria-label="%2$s">%3$s</a>',
				esc_url( $this->get_plugin_settings_url() ),
				_x( 'settings', 'aria-label: settings link', 'wc-serial-numbers' ),
				_x( 'Settings', 'plugin action link', 'wc-serial-numbers' )
			);
			array_unshift( $actions, $settings_link );
		}

		return $actions;
	}

	/**
	 * Register plugin meta information and/or links.
	 *
	 * @param array $plugin_meta An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @param string $status Status filter currently applied to the plugin list. Possible values are: 'all', 'active', 'inactive', 'recently_activated',
	 *                            'upgrade', 'mustuse', 'dropins', 'search', 'paused', 'auto-update-enabled', 'auto-update-disabled'.
	 *
	 * @since  1.3.1
	 * @return array
	 */
	public function register_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
		if ( $this->get_plugin_basename() !== $plugin_file ) {
			return $plugin_meta;
		}

		$links = [
			'plugins' => sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( admin_url( 'plugin-install.php?s=pluginever&tab=search&type=author' ) ),
				esc_html_x( 'More Plugins', 'noun', 'wc-serial-numbers' )
			),
		];

		if ( ! empty( $this->get_plugin_docs_url() ) ) {
			$links['docs'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $this->get_plugin_docs_url() ),
				esc_html_x( 'Docs', 'noun', 'wc-serial-numbers' )
			);
		}

		if ( ! empty( $this->get_plugin_support_url() ) ) {
			$links['support'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $this->get_plugin_support_url() ),
				esc_html_x( 'Support', 'noun', 'wc-serial-numbers' )
			);
		}

		if ( ! empty( $this->get_plugin_support_url() ) ) {
			$links['review'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $this->get_plugin_review_url() ),
				esc_html_x( 'Review', 'noun', 'wc-serial-numbers' )
			);
		}

		return array_merge( $plugin_meta, $links );
	}

	/**
	 * Check if WooCommerce is loaded.
	 *
	 * @return void
	 * @since 1.3.1
	 */
	public function need_plugin() {
		if ( ! class_exists( '\WooCommerce' ) ) {
			add_action( 'admin_notices', [ $this, 'missing_woocommerce_notice' ] );
		}
	}

	/**
	 * Missing WooCommerce notice.
	 *
	 * @return void
	 * @since 1.3.1
	 */
	public function missing_woocommerce_notice() {
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
		$this->includes();

		do_action( 'wc_serial_numbers_loaded' );
	}

	/**
	 * Includes the necessary files.
	 *
	 * @since 1.3.1
	 */
	public function includes() {
		include_once __DIR__ . '/class-install.php';
		if ( is_admin() ) {
			include_once __DIR__ . '/admin/class-admin-manager.php';

		}
	}
}
