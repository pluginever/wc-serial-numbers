<?php

namespace WooCommerceSerialNumbers\Admin;

use WooCommerceSerialNumbers\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the premium version prompts.
 *
 * Remove this file when the plugin ships without a premium version.
 *
 * @since   2.4.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Premium extends Component {

	/**
	 * Whether to load.
	 *
	 * @since 2.4.0
	 * @return bool
	 */
	public function autoload(): bool {
		return ! $this->app->is_pro_active();
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_notices' ) );
		add_action( 'admin_menu', array( $this, 'promo_menu' ), PHP_INT_MAX );
		add_filter( 'plugin_action_links_' . $this->app->basename(), array( $this, 'go_pro_link' ) );
		$this->app->on_action( 'settings_sidebar', array( $this, 'render_sidebar' ) );
	}

	/**
	 * Register the premium notices.
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function register_notices(): void {
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

	/**
	 * Register the promotional menu.
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function promo_menu(): void {
		add_submenu_page(
			'wc-serial-numbers',
			'',
			'<span style="color:#05ef82;"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> ' . __( 'Upgrade to Pro', 'wc-serial-numbers' ) . '</span>',
			'manage_woocommerce', // phpcs:ignore WordPress.WP.Capabilities.Unknown
			'go_wcsn_pro',
			array( $this, 'go_pro_redirect' )
		);
	}

	/**
	 * Redirect to the pro version page.
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function go_pro_redirect(): void {
		wp_verify_nonce( '_nonce' );
		if ( isset( $_GET['page'] ) && 'go_wcsn_pro' === $_GET['page'] ) {
			wp_safe_redirect( 'https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=admin-menu&utm_medium=link&utm_campaign=upgrade&utm_id=wc-serial-numbers' );
			exit;
		}
	}

	/**
	 * Add the Go Pro action link.
	 *
	 * @since 2.4.0
	 * @param array<string, string> $links Plugin action links.
	 * @return array<string, string>
	 */
	public function go_pro_link( array $links ): array {
		$pro_link = add_query_arg(
			array(
				'utm_source'   => 'plugins-page',
				'utm_medium'   => 'plugin-action-link',
				'utm_campaign' => 'plugins-page',
				'utm_term'     => 'go-pro',
				'utm_id'       => $this->app->slug,
			),
			(string) $this->app->get( 'upgrade_url' )
		);

		$go_pro = sprintf(
			'<a href="%1$s" target="_blank" style="color: #39b54a; font-weight: bold;">%2$s</a>',
			esc_url( $pro_link ),
			esc_html__( 'Go Pro', 'wc-serial-numbers' )
		);

		return array_merge( array( 'go_pro' => $go_pro ), $links );
	}

	/**
	 * Render the settings sidebar panel.
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function render_sidebar(): void {
		$features = array(
			__( 'Create and assign keys for WooCommerce variable products.', 'wc-serial-numbers' ),
			__( 'Generate bulk keys with your custom key generator rule.', 'wc-serial-numbers' ),
			__( 'Random & sequential key order for the generator rules.', 'wc-serial-numbers' ),
			__( 'Automatic key generator to auto-create & assign keys with orders.', 'wc-serial-numbers' ),
			__( 'License key management option from the order page with required actions.', 'wc-serial-numbers' ),
			__( 'Support for bulk import/export of keys from/to CSV.', 'wc-serial-numbers' ),
			__( 'Send keys via SMS with Twilio.', 'wc-serial-numbers' ),
			__( 'Option to sell keys even if there are no available keys in the stock.', 'wc-serial-numbers' ),
			__( 'Custom deliverable quantity to deliver multiple keys with a single product.', 'wc-serial-numbers' ),
			__( 'Manual delivery option to manually deliver license keys instead of automatic.', 'wc-serial-numbers' ),
			__( 'Email template to easily and quickly customize the order confirmation & low stock alert email.', 'wc-serial-numbers' ),
			__( 'Many more ...', 'wc-serial-numbers' ),
		);
		?>
		<div class="pev-panel promo-panel">
			<h3><?php esc_html_e( 'Want More?', 'wc-serial-numbers' ); ?></h3>
			<p><?php esc_attr_e( 'This plugin offers a premium version which comes with the following features:', 'wc-serial-numbers' ); ?></p>
			<ul>
				<?php foreach ( $features as $feature ) : ?>
					<li>- <?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
			<a href="https://pluginever.com/plugins/woocommerce-serial-numbers-pro/?utm_source=plugin-settings&utm_medium=banner&utm_campaign=upgrade&utm_id=wc-serial-numbers" class="button" target="_blank"><?php esc_html_e( 'Upgrade to PRO', 'wc-serial-numbers' ); ?></a>
		</div>
		<?php
	}
}