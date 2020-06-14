<?php

namespace pluginever\SerialNumbers;
defined( 'ABSPATH' ) || exit();

class Admin {

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( __CLASS__, 'buffer' ), 1 );
		add_action( 'init', array( __CLASS__, 'includes' ) );
	}

	/**
	 * Output buffering allows admin screens to make redirects later on.
	 */
	public static function buffer() {
		ob_start();
	}

	/**
	 * Include any classes we need within admin.
	 */
	public static function includes() {
		require_once dirname( __FILE__ ) . '/admin/class-admin-notice.php';
		require_once dirname( __FILE__ ) . '/admin/class-admin-menus.php';
		require_once dirname( __FILE__ ) . '/admin/class-admin-metaboxes.php';
		require_once dirname( __FILE__ ) . '/admin/class-admin-actions.php';
		require_once dirname( __FILE__ ) . '/admin/class-serials-page.php';
		require_once dirname( __FILE__ ) . '/admin/class-activations-page.php';
		require_once dirname( __FILE__ ) . '/admin/class-admin-settings.php';
		require_once dirname( __FILE__ ) . '/admin/class-admin-ajax.php';
	}
}

new Admin();
