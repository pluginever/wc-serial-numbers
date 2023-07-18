<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\Lib\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Admin extends Singleton {

	/**
	 * Admin constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'init' ), 1 );
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), PHP_INT_MAX );
		add_filter( 'update_footer', array( $this, 'update_footer' ), PHP_INT_MAX );
	}

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		Settings::instantiate();
		Menus::instantiate();
		Notices::instantiate();
		Actions::instantiate();
		Metaboxes::instantiate();
		Orders::instantiate();
		Products::instantiate();
	}

	/**
	 * Add the plugin screens to the WooCommerce screens.
	 * This will load the WooCommerce admin styles and scripts.
	 *
	 * @param array $ids Screen ids.
	 *
	 * @return array
	 */
	public function screen_ids( $ids ) {
		return array_merge( $ids, self::get_screen_ids() );
	}

	/**
	 * Admin footer text.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( wc_serial_numbers()->get_review_url() && in_array( get_current_screen()->id, self::get_screen_ids(), true ) ) {
			$footer_text = sprintf(
			/* translators: 1: Plugin name 2: WordPress */
				__( 'Thank you for using %1$s! Share your appreciation with a five-star review %2$s.', 'wc-serial-numbers' ),
				'<strong>' . esc_html( wc_serial_numbers()->get_name() ) . '</strong>',
				'<a href="' . esc_url( wc_serial_numbers()->get_review_url() ) . '" target="_blank" class="wc-serial-numbers-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'wc-serial-numbers' ) . '">here</a>'
			);
		}

		return $footer_text;
	}

	/**
	 * Update footer.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function update_footer( $footer_text ) {
		if ( in_array( get_current_screen()->id, self::get_screen_ids(), true ) ) {
			/* translators: 1: Plugin version */
			$footer_text = sprintf( esc_html__( 'Version %s', 'wc-serial-numbers' ), wc_serial_numbers()->get_version() );
		}

		return $footer_text;
	}

	/**
	 * Get screen ids.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_screen_ids() {
		$screen_id  = sanitize_title( __( 'Serial Numbers', 'wc-serial-numbers' ) );
		$screen_ids = [
			'toplevel_page_' . $screen_id,
			'toplevel_page_wc-serial-numbers',
			$screen_id . '_page_wc-serial-numbers-activations',
			$screen_id . '_page_wc-serial-numbers-products',
			$screen_id . '_page_wc-serial-numbers-tools',
			$screen_id . '_page_wc-serial-numbers-reports',
			$screen_id . '_page_wc-serial-numbers-settings',
		];

		return apply_filters( 'wc_serial_numbers_screen_ids', $screen_ids );
	}

	/**
	 * Render a view.
	 *
	 * @param string $view The name of the view to render.
	 * @param array  $args The arguments to pass to the view.
	 * @param string $path The path to the view file.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function view( $view, $args = [], $path = '' ) {
		if ( empty( $path ) ) {
			$path = __DIR__ . '/views/';
		}
		// replace .php extension if it was added.
		$view = str_replace( '.php', '', $view );
		$view = ltrim( $view, '/' );
		$path = rtrim( $path, '/' );

		$file = $path . '/' . $view . '.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		if ( $args && is_array( $args ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		include $file;
	}

	/**
	 * Get list table.
	 *
	 * @param string $name List table name.
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function get_list_table( $name ) {
		static $list_tables = [];

		if ( ! isset( $list_tables[ $name ] ) ) {
			switch ( $name ) {
				case 'keys':
					$list_tables[ $name ] = new Keys_List_Table();
					break;
				case 'activations':
					$list_tables[ $name ] = new Activations_List_Table();
					break;
				case 'products':
					$list_tables[ $name ] = new Products_List_Table();
					break;
				case 'tools':
					$list_tables[ $name ] = new Tools_List_Table();
					break;
				case 'reports':
					$list_tables[ $name ] = new Reports_List_Table();
					break;
			}
		}

		return $list_tables[ $name ];
	}
}
