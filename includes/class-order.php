<?php

namespace PluginEver\SerialNumbers;
defined( 'ABSPATH' ) || exit();

class Order {
	public static function init() {
		add_action( 'woocommerce_check_cart_items', array( __CLASS__, 'validate_checkout' ) );
		//autocomplete
		add_action( 'template_redirect', array( __CLASS__, 'maybe_autocomplete_order' ) );

		//add serial numbers
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'maybe_assign_serial_numbers' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'maybe_assign_serial_numbers' ) );
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'maybe_assign_serial_numbers' ) );
		add_action( 'woocommerce_order_status_on-hold', array( __CLASS__, 'maybe_assign_serial_numbers' ) );

		// revoke ordered serial numbers
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'revoke_serial_numbers' ) );
		add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'revoke_serial_numbers' ) );
		add_action( 'woocommerce_order_status_failed', array( __CLASS__, 'revoke_serial_numbers' ) );
		add_action( 'woocommerce_order_partially_refunded', array( __CLASS__, 'revoke_serial_numbers' ), 10, 2 );

		//
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'order_print_items' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'order_print_items' ) );
	}

	/**
	 * If selling from stock then check if there is enough
	 * serial numbers available otherwise disable checkout
	 *
	 * since 1.2.0
	 * @return void
	 */
	public static function validate_checkout() {
		$car_products = WC()->cart->get_cart_contents();
		foreach ( $car_products as $id => $cart_product ) {
			/** @var \WC_Product $product */
			$product         = $cart_product['data'];
			$product_id      = Helper::get_product_id( $product );
			$quantity        = $cart_product['quantity'];
			$allow_backorder = apply_filters( 'wc_serial_numbers_allow_backorder', false, $product_id );

			if ( Helper::product_is_selling_serial( $product_id ) && ! $allow_backorder ) {
				$per_item_quantity = absint( apply_filters( 'wc_serial_numbers_per_product_delivery_qty', 1, $product_id ) );
				$needed_quantity   = $quantity * ( empty( $per_item_quantity ) ? 1 : absint( $per_item_quantity ) );
				$source            = apply_filters( 'wc_serial_numbers_product_serial_source', 'custom_source', $product_id, $needed_quantity );

				if ( 'custom_source' == $source ) {
					$total_number = Query_Serials::init()
					                             ->where( 'product_id', $product_id )
					                             ->where( 'status', 'available' )
					                             ->where( 'source', $source )
					                             ->limit( $needed_quantity )
					                             ->count();

					if ( $total_number < $needed_quantity ) {
						$stock   = ceil( $total_number / $per_item_quantity );
						$message = sprintf( __( 'Sorry, There is not enough serial numbers available for %s, Please remove this item or lower the quantity, For now we have %s Serial Number for this product.', 'wc-serial-numbers' ), '{product_title}', '{stock_quantity}' );
						$notice  = apply_filters( 'wc_serial_numbers_low_stock_message', $message );
						$notice  = str_replace( '{product_title}', $product->get_title(), $notice );
						$notice  = str_replace( '{stock_quantity}', $stock, $notice );

						wc_add_notice( $notice, 'error' );

						return false;
					}
				}
			}

			do_action( 'wc_serial_number_product_cart_validation_complete', $product_id, $cart_product );
		}
	}

	/**
	 * Autocomplete order.
	 *
	 *
	 * @return bool
	 * @since 1.2.0
	 */
	public static function maybe_autocomplete_order() {
		if ( is_checkout() && ! empty( is_wc_endpoint_url( 'order-received' ) ) && ! empty( get_query_var( 'order-received' ) ) ) {
			$order_id = get_query_var( 'order-received' );
			$order    = wc_get_order( $order_id );

			//only autocomplete if contains serials
			if ( empty( $order ) || ! self::order_contains_serials( $order_id ) ) {
				return $order;
			}

			if ( 'completed' === $order->get_status() ) {
				return false;
			}

			$order->update_status( 'completed' );
			//$order->add_order_note( __( 'Order marked as complete by WC Serial Numbers', 'wc-serial-numbers' ) );

			return true;
		}

		return false;
	}

	/**
	 * Conditionally add serial numbers.
	 *
	 * @param int $order_id
	 *
	 * @version 1.2.0
	 */
	public static function maybe_assign_serial_numbers( $order_id ) {
		$manual_delivery = apply_filters( 'wc_serial_numbers_maybe_manual_delivery', false );
		$order           = wc_get_order( $order_id );
		$order->add_order_note( $order->get_status() );
		if ( ! $manual_delivery ) {
			self::add_serials( $order_id );
		}
	}

	/**
	 * Handle an order that is cancelled or refunded by:
	 *
	 * 1) Removing any serial numbers assigned for the order
	 *
	 * 2) If serial number is reusing then return back as available
	 *
	 * @param int $order_id the WC_Order ID
	 *
	 * @since 1.0
	 */
	public static function revoke_serial_numbers( $order_id ) {
		$order = wc_get_order( $order_id );

		$order_id = $order->get_id();

		// bail for no order
		if ( ! $order_id ) {
			return;
		}

		$remove_statuses = (array) wc_serial_numbers()->get_settings( 'revoke_statuses', [
			'cancelled' => true,
			'refunded'  => true,
			'failed'    => true,
		] );

		if ( array_key_exists( $order->get_status( 'edit' ), $remove_statuses ) ) {

			self::remove_serials( $order_id );
		}
	}


	/**
	 * Order contains serials?
	 *
	 * @param $order
	 *
	 * @return bool|int
	 * @since 1.2.0
	 */
	public static function order_contains_serials( $order ) {
		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}
		$order_id = $order->get_id();

		// bail for no order
		if ( ! $order_id ) {
			return false;
		}

		$quantity = 0;
		$items    = $order->get_items();
		foreach ( $items as $item ) {
			$product_id = empty( $item->get_variation_id() ) ? $item->get_product_id() : $item->get_variation_id();
			if ( ! Helper::product_is_selling_serial( $product_id ) ) {
				continue;
			}
			$quantity += 1;
		}

		return $quantity;
	}

	/**
	 * Assign serials with order.
	 *
	 * @param $order_id
	 *
	 * @return bool|int
	 * @since 1.2.0
	 */
	public static function add_serials( $order_id ) {
		global $wpdb;
		$order    = wc_get_order( $order_id );
		$order_id = $order->get_id();

		// bail for no order
		if ( ! $order_id ) {
			return false;
		}
		$items = $order->get_items();

		$total_added = 0;

		foreach ( $items as $item ) {
			$product_id = empty( $item->get_variation_id() ) ? $item->get_product_id() : $item->get_variation_id();

			$quantity = $item->get_quantity();
			if ( ! Helper::product_is_selling_serial( $product_id ) ) {
				continue;
			}

			$per_product_delivery_qty       = absint( apply_filters( 'wc_serial_numbers_per_product_delivery_qty', 1, $product_id ) );
			$per_product_total_delivery_qty = $quantity * $per_product_delivery_qty;
			$delivered_qty                  = Query_Serials::init()->where( 'order_id', $order_id )->where( 'product_id', $product_id )->count();
			if ( $delivered_qty >= $per_product_total_delivery_qty ) {
				continue;
			}
			$total_delivery_qty = $per_product_total_delivery_qty - $delivered_qty;
			$source             = apply_filters( 'wc_serial_numbers_product_serial_source', 'custom_source', $product_id, $total_delivery_qty );
			do_action( 'wc_serial_numbers_pre_order_item_add_serials', $product_id, $total_delivery_qty, $source, $order_id );

			$serials = Query_Serials::init()
			                        ->where( 'product_id', $product_id )
			                        ->where( 'status', 'available' )
			                        ->where( 'source', $source )
			                        ->limit( $total_delivery_qty )
			                        ->column( 0 );

			foreach ( $serials as $serial_id ) {
				$updated     = $wpdb->update(
					$wpdb->prefix . 'wc_serial_numbers',
					array(
						'order_id'   => $order_id,
						'status'     => 'sold',
						'order_date' => current_time( 'mysql' ),
					),
					array(
						'id' => $serial_id
					) );
				$total_added += $updated ? 1 : 0;
			}
		}

		return $total_added;
	}

	/**
	 * Remove serial numbers from order.
	 *
	 * @param $order_id
	 *
	 * @return bool|int
	 * @since 1.2.0
	 */
	public static function remove_serials( $order_id, $force = false ) {
		$order    = wc_get_order( $order_id );
		$order_id = $order->get_id();

		// bail for no order
		if ( ! $order_id ) {
			return false;
		}

		if ( ! self::order_contains_serials( $order ) ) {
			return false;
		}

		$reuse_serial = wc_serial_numbers()->get_settings( 'reuse_serial', true );
		$data         = array(
			'status' => $order->get_status( 'edit' ),
		);

		if ( $reuse_serial ) {
			$data['status']     = 'available';
			$data['order_id']   = '';
			$data['order_date'] = '';
		}
		if ( $reuse_serial ) {
			global $wpdb;
			Query_Activations::init()->whereRaw( $wpdb->prepare( "serial_id IN (SELECT id from {$wpdb->prefix}wc_serial_numbers WHERE order_id=%d)", $order_id ) )->delete();
		}

		return Query_Serials::init()->where( 'order_id', $order_id )->update( $data );
	}

	/**
	 * Print ordered serials
	 * @since 1.2.0
	 * @param $order
	 *
	 * @throws \Exception
	 */
	public static function order_print_items( $order ) {
		$order_id = $order->get_id();

		$order = wc_get_order( $order_id );

		if ( 'completed' !== $order->get_status( 'edit' ) ) {
			return;
		}
		global $serial_numbers;
		$serial_numbers = \PluginEver\SerialNumbers\Query_Serials::init()->where('order_id', intval($order_id))->get();

		if ( empty( $serial_numbers ) ) {
			return;
		}

		$heading                = apply_filters( 'wc_serial_numbers_headline', __( 'Serial Numbers', 'wc-serial-numbers' ) );
		$product_column         = apply_filters( 'wc_serial_numbers_product_cell_heading', __( 'Product', 'wc-serial-numbers' ) );
		$content_column         = apply_filters( 'wc_serial_numbers_serial_cell_heading', __( 'Serial Number', 'wc-serial-numbers' ) );
		$product_column_content = apply_filters( 'wc_serial_numbers_product_cell_content', '<a href="{product_url}">{product_title}</a>' );
		$serial_column_content  = apply_filters( 'wc_serial_numbers_serial_cell_content', '<ul><li><strong>Serial Numbers:</strong>{serial_number}</li><li><strong>Activation Email:</strong>{activation_email}</li><li><strong>Expire At:</strong>{expired_at}</li><li><strong>Activation Limit:</strong>{activation_limit}</li></ul>' );

		include dirname( __FILE__ ) . '/admin/views/order-table.php';
	}
}

Order::init();
