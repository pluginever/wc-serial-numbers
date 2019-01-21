<?php

namespace Pluginever\WCSerialNumbers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Frontend {
	/**
	 * The single instance of the class.
	 *
	 * @var Frontend
	 * @since 1.0.0
	 */
	protected static $init = null;

	/**
	 * Frontend Instance.
	 *
	 * @since 1.0.0
	 * @static
	 * @return Frontend - Main instance.
	 */
	public static function init() {
		if ( is_null( self::$init ) ) {
			self::$init = new self();
			self::$init->setup();
		}

		return self::$init;
	}

	/**
	 * Initialize all frontend related stuff
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
	 * Includes all frontend related files
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function includes() {
		require_once dirname( __FILE__ ) . '/class-order-process.php';
		require_once dirname( __FILE__ ) . '/class-email.php';
	}

	/**
	 * Register all frontend related hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Fire off all the instances
	 *
	 * @since 1.0.0
	 */
	protected function instance() {
		new Order_Process();
		new Email();
	}

	/**
	 * Loads all frontend scripts/styles
	 *
	 * @param $hook
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts( ) {
		//styles
		wp_enqueue_style('wc-serial-numbers', WPWSN_ASSETS_URL."/css/frontend.css", [], WPWSN_VERSION);
		
		//scripts

		wp_localize_script('wc-serial-numbers', 'wpwsn', 
		[
			'ajaxurl' => admin_url( 'admin-ajax.php' ), 
			'nonce' => wp_create_nonce('wc-serial-numbers')
		]);		
	}

}

Frontend::init();
