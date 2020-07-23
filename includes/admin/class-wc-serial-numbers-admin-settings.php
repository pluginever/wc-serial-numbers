<?php
defined( 'ABSPATH' ) || exit();

include_once( WC()->plugin_path() . '/includes/admin/class-wc-admin-settings.php' );

class WC_Serial_Numbers_Admin_Settings extends WC_Admin_Settings {
	private static $settings = array();
	private static $errors = array();
	private static $messages = array();

	/**
	 * Include the settings page classes
	 */
	public static function get_settings_pages() {
		if ( empty( self::$settings ) ) {
			$settings = array();

			include_once( WC()->plugin_path() . '/includes/admin/settings/class-wc-settings-page.php' );

			$settings[] = include( 'settings/wc-serial-numbers-settings-general.php' );
			$settings[] = include( 'settings/wc-serial-numbers-settings-help.php' );

			self::$settings = apply_filters( 'wc_serial_numbers_get_settings_pages', $settings );
		}

		return self::$settings;
	}


	/**
	 * Save the settings
	 */
	public static function save() {
		global $current_section, $current_tab;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wc-serial-numbers-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'wc-serial-numbers' ) );
		}

		// Trigger actions
		do_action( 'wc_serial_numbers_settings_save_' . $current_tab );
		do_action( 'wc_serial_numbers_update_options_' . $current_tab );
		do_action( 'wc_serial_numbers_update_options' );

		self::add_message( __( 'Your settings have been saved.', 'wc-serial-numbers' ) );

		// Re-add endpoints and flush rules
		WC()->query->init_query_vars();
		WC()->query->add_endpoints();
		flush_rewrite_rules();

		do_action( 'wc_serial_numbers_settings_saved' );
	}

	/**
	 * Add a message
	 *
	 * @param string $text
	 */
	public static function add_message( $text ) {
		self::$messages[] = $text;
	}

	/**
	 * Add an error
	 *
	 * @param string $text
	 */
	public static function add_error( $text ) {
		self::$errors[] = $text;
	}

	/**
	 * Output messages + errors
	 */
	public static function show_messages() {
		if ( sizeof( self::$errors ) > 0 ) {
			foreach ( self::$errors as $error ) {
				echo '<div id="message" class="error fade"><p><strong>' . esc_html( $error ) . '</strong></p></div>';
			}
		} elseif ( sizeof( self::$messages ) > 0 ) {
			foreach ( self::$messages as $message ) {
				echo '<div id="message" class="updated fade"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		}
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main settings page in admin.
	 *
	 * @access public
	 * @return void
	 */
	public static function output() {
		global $current_section, $current_tab;

		wp_enqueue_script( 'woocommerce_settings', WC()->plugin_url() . '/assets/js/admin/settings.min.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris' ), WC()->version, true );

		wp_localize_script( 'woocommerce_settings', 'woocommerce_settings_params', array(
			'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'wc-serial-numbers' )
		) );

		// Include settings pages
		self::get_settings_pages();

		// Get current tab/section
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		// Save settings if data has been posted
		if ( ! empty( $_POST ) ) {
			self::save();
		}

		// Add any posted messages
		if ( ! empty( $_GET['wc_error'] ) ) {
			self::add_error( stripslashes( $_GET['wc_error'] ) );
		}

		if ( ! empty( $_GET['wc_message'] ) ) {
			self::add_message( stripslashes( $_GET['wc_message'] ) );
		}

		self::show_messages();

		// Get tabs for the settings page
		$tabs = apply_filters( 'wc_serial_numbers_settings_tabs_array', array() );

		include 'views/html-admin-settings.php';
	}


}
