<?php

namespace Pluginever\WCSerialNumbers\Admin;

use Pluginever\WCSerialNumbers\Ajax;
use Pluginever\WCSerialNumbers\FormHandler;

class Admin {
	/**
	 * The single instance of the class.
	 *
	 * @var Admin
	 * @since 1.0.0
	 */
	protected static $init = null;

	/**
	 * Frontend Instance.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Admin - Main instance.
	 */
	public static function init() {
		if ( is_null( self::$init ) ) {
			self::$init = new self();
			self::$init->setup();
		}

		return self::$init;
	}

	/**
	 * Initialize all Admin related stuff
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup() {
		$this->includes();
		$this->init_hooks();
		$this->instance();
	}

	/**
	 * Includes all files related to admin
	 */
	public function includes() {
		require_once dirname( __FILE__ ) . '/class-admin-menu.php';
		require_once dirname( __FILE__ ) . '/class-metabox.php';
		require_once dirname( __FILE__ ) . '/class-settings-api.php';
		require_once dirname( __FILE__ ) . '/class-settings.php';
		require_once WPWSN_INCLUDES . '/class-form-handler.php';
		require_once WPWSN_INCLUDES . '/class-ajax.php';
	}

	private function init_hooks() {
		add_action( 'admin_init', array( $this, 'buffer' ), 1 );
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}


	/**
	 * Fire off all the instances
	 *
	 * @since 1.0.0
	 */
	protected function instance() {
		new FormHandler();
		new Admin_Menu();
		new MetaBox();
		new Settings();
		new Ajax();
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 *
	 * @since 1.0.0
	 */
	public function buffer() {
		ob_start();
	}


	public function enqueue_scripts( $hook ) {
		$suffix = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '' : '.min';
		//styles
		wp_enqueue_style( 'wp-ever-css', WPWSN_ASSETS_URL . "/css/wp-ever{$suffix}.css", [], WPWSN_VERSION );
		wp_enqueue_style( 'wc-serial-numbers', WPWSN_ASSETS_URL . "/css/admin.css", [], WPWSN_VERSION );

		//scripts
		wp_enqueue_script( 'wc-serial-numbers', WPWSN_ASSETS_URL . "/js/admin{$suffix}.js", [
			'jquery',
			'wp-util'
		], WPWSN_VERSION, true );
		wp_localize_script( 'wc-serial-numbers', 'wpwsn', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => 'wc-serial-numbers'
		] );
	}


}

Admin::init();
