<?php

namespace WooCommerceSerialNumbers\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Notices.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Admin
 */
class Notices {
	/**
	 * Notices container.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $notices = array();

	/**
	 * Notices constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'add_notices' ) );
		add_action( 'admin_notices', array( $this, 'output_notices' ) );
		add_action( 'wp_ajax_wc_serial_numbers_dismiss_notice', array( $this, 'dismiss_notice' ) );
		add_action( 'admin_footer', array( $this, 'add_notice_script' ) );
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.0.0
	 */
	public function add_notices() {
		// Halloween's promotion notice.
		if ( ! $this->is_notice_dismissed( 'wcsn_halloween_promotion_2025' ) ) {
			if ( ! function_exists( 'wc_serial_numbers_pro' ) ) {
				$discount_percentage = esc_html__( '30%', 'wc-serial-numbers' );
				$this->notices[]     = array(
					'type'        => 'info',
					'classes'     => 'notice-alt notice-large wcsn-halloween',
					'dismissible' => true,
					'id'          => 'wcsn_halloween_promotion_2025',
					'message'     => sprintf(
					/* translators: %1$s: link to the plugin page, %2$s: Offer content, %3$s: link to the plugin page, %4$s: end link to the plugin page */
						__( '%1$s%2$s%3$s Upgrade Now and Save %4$s', 'wc-serial-numbers' ),
						'<div class="wcsn-halloween__header"><div class="wcsn-halloween__icon"><img src="' . WCSN()->get_dir_url( 'assets/dist/images/halloween-icon.svg' ) . '" alt="WC Serial Numbers Halloween offer"></div><div class="wcsn-halloween__content"><strong class="wcsn-halloween__title">',
						'👻 Halloween Sale: ' . $discount_percentage . ' OFF on WC Serial Numbers Pro</strong><p>Grab a ' . $discount_percentage . ' discount on WC Serial Numbers Pro and all our premium plugins this Halloween! Use code <strong>‘EVERSAVE30’</strong>. Don\'t miss out!</p>',
						'<a class="button button-primary" href="' . esc_url( WCSN()->get_premium_url() ) . '?utm_source=plugin&utm_medium=notice&utm_campaign=halloween-sale-2025&discount=EVERSAVE30" target="_blank">',
						$discount_percentage . '</a></div></div>',
					),
				);
			} else {
				$discount_percentage = esc_html__( '30%', 'wc-serial-numbers' );
				$this->notices[]     = array(
					'type'        => 'info',
					'classes'     => 'notice-alt notice-large wcsn-halloween',
					'dismissible' => true,
					'id'          => 'wcsn_halloween_promotion_2025',
					'message'     => sprintf(
					/* translators: %1$s: link to the plugin page, %2$s: Offer content, %3$s: link to the plugin page, %4$s: end link to the plugin page */
						__( '%1$s%2$s%3$s Claim your discount! %4$s', 'wc-serial-numbers' ),
						'<div class="wcsn-halloween__header"><div class="wcsn-halloween__icon"><img src="' . WCSN()->get_dir_url( 'assets/dist/images/halloween-icon.svg' ) . '" alt="WC Serial Numbers Halloween offer"></div><div class="wcsn-halloween__content"><strong class="wcsn-halloween__title">',
						'👻 Halloween Sale: ' . $discount_percentage . ' OFF on All Plugins</strong><p>Get ' . $discount_percentage . ' OFF on all premium plugins with code <strong>‘EVERSAVE30’</strong>. Hurry, this deal won’t last long!</p>',
						'<a class="button button-primary" href="' . esc_url( 'https://pluginever.com/plugins/?utm_source=plugin&utm_medium=notice&utm_campaign=halloween-sale-2025&discount=EVERSAVE30' ) . '" target="_blank">',
						'</a></div></div>',
					),
				);
			}
		}

		$is_outdated_pro = defined( 'WC_SERIAL_NUMBER_PRO_PLUGIN_VERSION' ) && version_compare( WCSN_PRO_VERSION, '1.4.0', '<' );
		if ( ! $is_outdated_pro ) {
			$is_outdated_pro = function_exists( 'wc_serial_numbers_pro' ) && is_callable( array( 'wc_serial_numbers_pro', 'get_version' ) ) && wc_serial_numbers_pro()->get_version() && version_compare( wc_serial_numbers_pro()->get_version(), '1.4.0', '<' );
		}
		if ( $is_outdated_pro ) {
			$this->notices[] = array(
				'type'    => 'error', // add notice-alt and notice-large class.
				'message' => sprintf(
				/* translators: %1$s: link to the plugin page, %2$s: link to the plugin page */
					__( '%s is not functional because you are using outdated version of the plugin, please update to the version 1.3.8 or higher.', 'wc-serial-numbers' ),
					'<a href="' . esc_url( WCSN()->get_data( 'premium_url' ) ) . '" target="_blank">WC Serial Numbers Pro</a>'
				),
			);
		}

		if ( ! $this->is_notice_dismissed( 'wc_serial_numbers_upgrade_to_pro_wcsnpro10' ) && ! function_exists( 'wc_serial_numbers_pro' ) ) {
			$this->notices[] = array(
				'type'        => 'info',
				'classes'     => 'notice-alt notice-large',
				'dismissible' => true,
				'id'          => 'wc_serial_numbers_upgrade_to_pro_wcsnpro10',
				'message'     => sprintf(
				/* translators: %1$s: link to the plugin page, %2$s: link to the plugin page */
					__( 'Upgrade to %6$s to unlock the full potential of %5$s and avail a %1$s discount by using the promo code %2$s. %3$s Upgrade Now%4$s.', 'wc-serial-numbers' ),
					'<strong>10%</strong>',
					'<strong>WCSNPRO10</strong>',
					'<a href="' . esc_url( WCSN()->get_premium_url() ) . '" target="_blank">',
					'</a>',
					'<strong>' . WCSN()->get_name() . '</strong>',
					'<strong>PRO</strong>'
				),
			);
		}
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.0.0
	 */
	public function output_notices() {
		foreach ( $this->notices as $notice ) {
			$notice = wp_parse_args(
				$notice,
				array(
					'id'          => wp_generate_password( 12, false ),
					'type'        => 'info',
					'classes'     => '',
					'message'     => '',
					'dismissible' => false,
				)
			);

			$notice_classes = array( 'notice', 'notice-' . $notice['type'] );
			if ( $notice['dismissible'] ) {
				$notice_classes[] = 'is-dismissible';
			}
			if ( $notice['classes'] ) {
				$notice_classes[] = $notice['classes'];
			}
			?>
			<div class="wcsn-notice <?php echo esc_attr( implode( ' ', $notice_classes ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wc_serial_numbers_dismiss_notice' ) ); ?>" data-notice-id="<?php echo esc_attr( $notice['id'] ); ?>">
				<p><?php echo wp_kses_post( $notice['message'] ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Dismiss notice.
	 *
	 * @since 1.0.0
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'wc_serial_numbers_dismiss_notice', 'nonce' );

		// Must have manage woocommerce user capability role to access this endpoint.
		if ( ! current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			wp_die();
		}

		$notice_id = isset( $_POST['notice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_id'] ) ) : '';
		if ( $notice_id ) {
			update_option( 'wc_serial_numbers_dismissed_notices', array_merge( get_option( 'wc_serial_numbers_dismissed_notices', array() ), array( $notice_id ) ) );
		}
		wp_die();
	}

	/**
	 * Check if notice is dismissed.
	 *
	 * @param string $notice_id Notice ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_notice_dismissed( $notice_id ) {
		return in_array( $notice_id, get_option( 'wc_serial_numbers_dismissed_notices', array() ), true );
	}

	/**
	 * Add notice script.
	 *
	 * @since 1.0.0
	 */
	public function add_notice_script() {
		?>
		<script type="text/javascript">
			jQuery(function ($) {
				$('.wcsn-notice').on('click', '.notice-dismiss', function () {
					var $notice = $(this).closest('.wcsn-notice');
					$.ajax({
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						method: 'POST',
						data: {
							action: 'wc_serial_numbers_dismiss_notice',
							nonce: $notice.data('nonce'),
							notice_id: $notice.data('notice-id'),
						},
					});
				});
			});
		</script>
		<?php
	}
}
