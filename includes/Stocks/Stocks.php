<?php

namespace WooCommerceSerialNumbers\Stocks;

use WooCommerceSerialNumbers\B8\Component;
use WooCommerceSerialNumbers\Models\Key;

defined( 'ABSPATH' ) || exit;

/**
 * Class Stocks.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers\Stocks
 */
class Stocks extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		add_filter( 'woocommerce_product_get_stock_quantity', array( $this, 'get_stock_quantity' ), 20, 2 );

		// Manage Stocks.
		add_action( 'wc_serial_numbers_key_inserted', array( $this, 'update_stocks' ) );
		add_action( 'wc_serial_numbers_key_updated', array( $this, 'update_stocks' ) );
		add_action( 'wc_serial_numbers_key_deleted', array( $this, 'update_stocks' ) );
		add_action( 'wc_serial_numbers_daily_event', array( $this, 'send_stock_alert_email' ) );
	}

	/**
	 * Get stock quantity.
	 *
	 * @param int         $quantity Stock quantity.
	 * @param \WC_Product $product Product object.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_stock_quantity( $quantity, $product ) {
		if ( wcsn_is_product_enabled( $product->get_id() ) ) {
			$stocks = wcsn_get_stocks_count();
			if ( isset( $stocks[ $product->get_id() ] ) ) {
				$quantity = $stocks[ $product->get_id() ];
			}
		}

		return $quantity;
	}

	/**
	 * Update stocks.
	 *
	 * @param Key $key Key object.
	 *
	 * @since 2.1.6
	 * @return void
	 */
	public function update_stocks( $key ) {
		if ( 'no' === get_option( 'wcsn_manage_stocks', 'no' ) ) {
			return; // Return if stock management is disabled.
		}

		$product = $key->get_product();

		// Check if product exists and stock management is enabled.
		if ( ! $product || ! $product->get_manage_stock() ) {
			return;
		}

		// Check if product is enabled for WCSN.
		if ( ! wcsn_is_product_enabled( $product->get_id() ) ) {
			return;
		}

		// Get the total stock quantity. This will be the sum of all available keys.
		$quantity = $this->get_stock_quantity( $product->get_stock_quantity(), $product );

		// Update the product stock meta directly.
		$product->set_stock_quantity( $quantity );
		$product->save();
	}

	/**
	 * Send low stock email notification.
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	public function send_stock_alert_email() {
		if ( 'yes' !== get_option( 'wc_serial_numbers_enable_stock_notification' ) ) {
			return false;
		}

		$stock_threshold = get_option( 'wc_serial_numbers_stock_threshold', 5 );
		$to              = get_option( 'wc_serial_numbers_notification_recipient', get_option( 'admin_email' ) );
		if ( empty( $to ) ) {
			return false;
		}

		$low_stock_products = wcsn_get_stocks_count( $stock_threshold );
		if ( empty( $low_stock_products ) ) {
			return false;
		}

		$subject = __( 'Serial Numbers stock running low', 'wc-serial-numbers' );
		/** $woocommerce WooCommerce */
		global $woocommerce;
		$mailer = $woocommerce->mailer();

		ob_start();
		wcsn_get_template( 'email-stock-notification.php', array( 'low_stock_products' => $low_stock_products ) );
		$message = ob_get_contents();
		ob_get_clean();

		$message = $mailer->wrap_message( $subject, $message );
		$headers = apply_filters( 'woocommerce_email_headers', '', 'wc_serial_numbers_low_stock_notification', $mailer );
		$mailer->send( $to, $subject, $message, $headers, array() );

		exit();
	}
}
