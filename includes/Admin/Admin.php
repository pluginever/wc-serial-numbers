<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Admin extends Component {

	/**
	 * Child components.
	 *
	 * @since 1.0.0
	 * @var array<int|string, class-string>
	 */
	public array $components = array(
		Settings::class,
		Menus::class,
		Requests::class,
		Orders::class,
		Products::class,
	);

	/**
	 * Whether to load.
	 *
	 * @since 2.4.0
	 * @return bool
	 */
	public function autoload(): bool {
		return is_admin();
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), PHP_INT_MAX );
		add_filter( 'update_footer', array( $this, 'update_footer' ), PHP_INT_MAX );
		add_filter( 'allowed_redirect_hosts', array( $this, 'allowed_redirect_hosts' ) );
		add_filter( 'plugin_action_links_' . $this->app->basename(), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Register the admin notices.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_notices(): void {
		$is_outdated_pro = defined( 'WC_SERIAL_NUMBER_PRO_PLUGIN_VERSION' ) && version_compare( WC_SERIAL_NUMBER_PRO_PLUGIN_VERSION, '1.4.0', '<' );
		if ( ! $is_outdated_pro ) {
			$is_outdated_pro = function_exists( 'wc_serial_numbers_pro' ) && is_callable( array( wc_serial_numbers_pro(), 'get_version' ) ) && wc_serial_numbers_pro()->get_version() && version_compare( wc_serial_numbers_pro()->get_version(), '1.4.0', '<' );
		}

		if ( $is_outdated_pro ) {
			$this->app->notices->add(
				array(
					'notice_id'   => 'wc_serial_numbers_outdated_pro',
					'type'        => 'error',
					'dismissible' => false,
					'message'     => sprintf(
					/* translators: %s: link to the plugin page */
						__( '%s is not functional because you are using outdated version of the plugin, please update to the version 1.3.8 or higher.', 'wc-serial-numbers' ),
						'<a href="' . esc_url( $this->app->get( 'upgrade_url' ) ) . '" target="_blank">Serial Numbers Pro</a>'
					),
				)
			);
		}

		if ( ! $this->app->is_pro_active() ) {
			$this->app->notices->add(
				array(
					'notice_id'   => 'wc_serial_numbers_upgrade_to_pro_wcsnpro10',
					'type'        => 'info',
					'class'       => 'notice-alt notice-large',
					'dismissible' => true,
					'message'     => sprintf(
					/* translators: 1: discount amount 2: promo code 3: link start 4: link end 5: plugin name 6: PRO label */
						__( 'Upgrade to %6$s to unlock the full potential of %5$s and avail a %1$s discount by using the promo code %2$s. %3$s Upgrade Now%4$s.', 'wc-serial-numbers' ),
						'<strong>10%</strong>',
						'<strong>WCSNPRO10</strong>',
						'<a href="' . esc_url( $this->app->get( 'upgrade_url' ) ) . '" target="_blank">',
						'</a>',
						'<strong>' . esc_html( $this->app->get( 'name' ) ) . '</strong>',
						'<strong>PRO</strong>'
					),
				)
			);
		}
	}

	/**
	 * Add plugin action links.
	 *
	 * @since 1.0.0
	 * @param array<string, string> $links Plugin action links.
	 * @return array<string, string>
	 */
	public function plugin_action_links( array $links ): array {
		$actions = array(
			'settings' => sprintf(
				'<a href="%s">%s</a>',
				esc_url( (string) $this->app->get( 'settings_url' ) ),
				esc_html__( 'Settings', 'wc-serial-numbers' )
			),
		);

		if ( ! $this->app->is_pro_active() ) {
			$pro_link          = add_query_arg(
				array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-action-link',
					'utm_campaign' => 'plugins-page',
					'utm_term'     => 'go-pro',
					'utm_id'       => $this->app->slug,
				),
				(string) $this->app->get( 'upgrade_url' )
			);
			$actions['go_pro'] = sprintf(
				'<a href="%1$s" target="_blank" style="color: #39b54a; font-weight: bold;">%2$s</a>',
				esc_url( $pro_link ),
				esc_html__( 'Go Pro', 'wc-serial-numbers' )
			);
		}

		return array_merge( $actions, $links );
	}

	/**
	 * Add the plugin row meta links.
	 *
	 * @since 1.0.0
	 * @param array<int, string> $links Plugin row meta links.
	 * @param string             $file  Plugin file path relative to the plugins directory.
	 * @return array<int, string>
	 */
	public function plugin_row_meta( array $links, string $file ): array {
		if ( $file !== $this->app->basename() ) {
			return $links;
		}

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->app->get( 'docs_url' ) ),
			esc_html__( 'Documentation', 'wc-serial-numbers' )
		);

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->app->get( 'support_url' ) ),
			esc_html__( 'Support', 'wc-serial-numbers' )
		);

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->app->get( 'review_url' ) ),
			esc_html__( 'Review', 'wc-serial-numbers' )
		);

		$links[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( (string) $this->app->get( 'store_url' ) ),
			esc_html__( 'More Plugins', 'wc-serial-numbers' )
		);

		return $links;
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Hook name.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		if ( ! in_array( $hook, self::get_screen_ids(), true ) ) {
			return;
		}

		// Determine which select2 to use. WooCommerce 3.6+ uses its own select2 but the latest WooCommerce 10.3+ deprecated it and added wc-select2 style/js handle.
		$which_select2_style  = wp_style_is( 'wc-select2', 'registered' ) ? 'wc-select2' : 'select2';
		$which_select2_script = wp_script_is( 'wc-select2', 'registered' ) ? 'wc-select2' : 'select2';

		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_style( $which_select2_style );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		$this->app->scripts->enqueue_style( 'wc-serial-numbers-admin', 'admin.css' );
		$this->app->scripts->enqueue_script( 'wc-serial-numbers-admin', 'admin.js', array( 'jquery', 'jquery-ui-datepicker', $which_select2_script, 'wp-util' ) );
		wp_localize_script(
			'wc-serial-numbers-admin',
			'wc_serial_numbers_vars',
			array(
				'i18n'         => array(
					'search_product'  => __( 'Search by product', 'wc-serial-numbers' ),
					'search_order'    => __( 'Search by order', 'wc-serial-numbers' ),
					'search_customer' => __( 'Search by customer', 'wc-serial-numbers' ),
					'show'            => __( 'Show', 'wc-serial-numbers' ),
					'hide'            => __( 'Hide', 'wc-serial-numbers' ),
					'copied'          => __( 'Copied', 'wc-serial-numbers' ),
				),
				'search_nonce' => wp_create_nonce( 'wc_serial_numbers_search_nonce' ),
				'ajax_nonce'   => wp_create_nonce( 'wcsn_ajax_search' ),
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'apiurl'       => site_url( '?wc-api=serial-numbers-api' ),
			)
		);

		// add inline style for select2 --wp-admin-theme-color.
		wp_add_inline_style( 'common', ':root{--wp-admin-theme-color:#0073aa;}' );
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
		if ( $this->app->get( 'review_url' ) && in_array( get_current_screen()->id, self::get_screen_ids(), true ) ) {
			$footer_text = sprintf(
			/* translators: 1: Plugin name 2: WordPress */
				__( 'Thank you for using %1$s! Share your appreciation with a five-star review %2$s.', 'wc-serial-numbers' ),
				'<strong>' . esc_html( $this->app->get( 'name' ) ) . '</strong>',
				'<a href="' . esc_url( $this->app->get( 'review_url' ) ) . '" target="_blank" class="wc-serial-numbers-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'wc-serial-numbers' ) . '">here</a>'
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
			$footer_text = sprintf( esc_html__( 'Version %s', 'wc-serial-numbers' ), $this->app->version );
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
		$screen_ids = array(
			'toplevel_page_' . $screen_id,
			'toplevel_page_wc-serial-numbers',
			$screen_id . '_page_wc-serial-numbers-activations',
			$screen_id . '_page_wc-serial-numbers-products',
			$screen_id . '_page_wc-serial-numbers-tools',
			$screen_id . '_page_wc-serial-numbers-reports',
			$screen_id . '_page_wc-serial-numbers-settings',
		);

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
	public static function view( $view, $args = array(), $path = '' ) {
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
	 * Allowed redirect hosts.
	 *
	 * @param array $hosts Allowed hosts.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function allowed_redirect_hosts( $hosts ) {
		$hosts[] = 'pluginever.com';
		$hosts[] = 'www.pluginever.com';

		return $hosts;
	}
}
