<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Admin_Notice {
	/**
	 * WC_Serial_Numbers_Admin_Notices presisting on the next request.
	 * @var array
	 * @since 1.2.0
	 */
	public static $saved_notices = array();

	/**
	 * WC_Serial_Numbers_Admin_Notices displayed on the current request.
	 *
	 * @var array
	 * @since 1.2.0
	 */
	protected static $notices = [];

	/**
	 * Dismissible notices displayed on the current request.
	 * @var array
	 * @since 1.2.0
	 */
	protected static $dismissed_notices = array();

	/**
	 * Constructor.
	 */
	public static function init() {
		self::$dismissed_notices = get_user_meta( get_current_user_id(), 'wc_serial_numbers_dismissed_notices', true );
		self::$dismissed_notices = empty( self::$dismissed_notices ) ? array() : self::$dismissed_notices;
		// Show meta box notices.
		add_action( 'admin_notices', array( __CLASS__, 'output_notices' ) );
		// Save meta box notices.
		add_action( 'shutdown', array( __CLASS__, 'save_notices' ), 100 );
	}

	/**
	 * Show any stored error messages.
	 */
	public static function output_notices() {
		$saved_notices = get_option( 'wc_serial_numbers_notices', array() );
		$notices       = $saved_notices + self::$notices;

		if ( ! empty( $notices ) ) {

			foreach ( $notices as $notice ) {

				$notice_classes = array( 'wc_serial_numbers_notice', 'notice', 'notice-' . $notice['type'] );
				$dismiss_attr   = $notice['dismiss_class'] ? 'data-dismiss_class="' . $notice['dismiss_class'] . '"' : '';

				if ( $notice['dismiss_class'] ) {
					$notice_classes[] = $notice['dismiss_class'];
					$notice_classes[] = 'is-dismissible';
				}

				echo '<div class="' . implode( ' ', $notice_classes ) . '"' . $dismiss_attr . '>';
				echo wpautop( wp_kses_post( $notice['content'] ) );
				echo '</div>';
			}

			if ( function_exists( 'wc_enqueue_js' ) ) {
				wc_enqueue_js( "
					jQuery( function( $ ) {
						jQuery( '.wc_serial_numbers_notice .notice-dismiss' ).on( 'click', function() {

							var data = {
								action: 'wc_serial_numbers_notice',
								notice: jQuery( this ).parent().data( 'dismiss_class' ),
								security: '" . wp_create_nonce( 'wc_serial_numbers_notice_nonce' ) . "'
							};

							jQuery.post( '" . WC()->ajax_url() . "', data );
						} );
					} );
				" );
			}

			// Clear.
			delete_option( 'wc_serial_numbers_notices' );
		}
	}

	/**
	 * Save errors to an option.
	 */
	public static function save_notices() {
		update_option( 'wc_serial_numbers_notices', self::$saved_notices );
	}

	/**
	 * Add a notice/error.
	 *
	 * @param string $text
	 * @param mixed $args
	 * @param boolean $save_notice
	 *
	 * @since 1.2.0
	 */
	public static function add_notice( $text, $args, $save_notice = true ) {
		if ( is_array( $args ) ) {
			$type          = $args['type'];
			$dismiss_class = isset( $args['dismiss_class'] ) ? $args['dismiss_class'] : false;
		} else {
			$type          = $args;
			$dismiss_class = false;
		}

		$notice = array(
			'type'          => $type,
			'content'       => $text,
			'dismiss_class' => $dismiss_class
		);

		if ( $save_notice ) {
			self::$saved_notices[] = $notice;
		} else {
			self::$notices[] = $notice;
		}
	}

	/**
	 * Add a dismissible notice/error.
	 *
	 * @param string $text
	 * @param mixed $args
	 *
	 * @since  1.2.0
	 *
	 */
	public static function add_dismissible_notice( $text, $args ) {
//		if ( ! isset( $args['dismiss_class'] ) || ! self::is_dismissible_notice_dismissed( $args['dismiss_class'] ) ) {
		self::add_notice( $text, $args );
//		}
	}

	/**
	 * Checks if a dismissible notice has been dismissed in the past.
	 *
	 * @param string $notice_name
	 *
	 * @return boolean
	 * @since  1.2.0
	 *
	 */
	public static function is_dismissible_notice_dismissed( $notice_name ) {
		return in_array( $notice_name, self::$dismissed_notices );
	}

	/**
	 * Remove a dismissible notice.
	 *
	 * @param string $notice_name
	 *
	 * @return bool
	 * @since  1.2.0
	 *
	 */
	public static function remove_dismissible_notice( $notice_name ) {
		// Remove if not already removed.
		if ( ! self::is_dismissible_notice_dismissed( $notice_name ) ) {
			self::$dismissed_notices = array_merge( self::$dismissed_notices, array( $notice_name ) );
			update_user_meta( get_current_user_id(), 'wc_serial_numbers_dismissed_notices', self::$dismissed_notices );

			return true;
		}

		return false;
	}

	/**
	 * Add 'welcome' notice.
	 *
	 * @since  1.2.0
	 */
	public static function welcome_notice() {
//		global $current_screen;
//		$screen_id       = $current_screen ? $current_screen->id : '';
//		$show_on_screens = array(
//			'dashboard',
//			'plugins',
//		);

		// Onboarding notices should only show on the main dashboard, and on the plugins screen.
//		if ( ! in_array( $screen_id, $show_on_screens, true ) ) {
//			return;
//		}

		ob_start();

		?>
		<div class="notice-left">
			<span class="dashicons dashicons-lock plugin-icon"></span>
		</div>

		<div class="notice-right">
			<h2 class="serial-welcome-title"><?php esc_attr_e( 'Ready to sell serial numbers?', 'wc-serial-numbers' ); ?></h2>
			<p class="serial-welcome-text"><?php esc_attr_e( 'Thank you for installing WooCommerce Serial Numbers.', 'wc-serial-numbers' ); ?>
				<br/><?php esc_attr_e( 'Let\'s get started by inserting your first serial number!', 'wc-serial-numbers' ); ?>
			</p>
			<a href="<?php echo admin_url( 'admin.php?page=wc-serial-numbers?action=add' ); ?>"
			   class="sw-welcome-button button-primary"><?php esc_attr_e( 'Let\'s go!', 'wc-serial-numbers' ); ?></a>
		</div>
		<?php

		$notice = ob_get_clean();

		self::add_dismissible_notice( $notice, array( 'type' => 'native', 'dismiss_class' => 'welcome' ) );
	}
}

WC_Serial_Numbers_Admin_Notice::init();
