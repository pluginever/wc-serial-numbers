<?php

namespace PluginEver\WooCommerceSerialNumbers\Admin;


// don't call the file directly.
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
//		wp_die(var_dump($hook));
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
