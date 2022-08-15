<?php

namespace PluginEver\WooCommerceSerialNumbers;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Useful helper functions for the plugin
 *
 * @since   1.0.0
 * @package PluginEver\WooCommerceStarterPlugin
 */
class Helper {

	/**
	 * Return true if pre argument version is older than the current version.
	 *
	 * @since #.#.#
	 *
	 * @param string $version WC version.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_pre( $version ) {
		return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, $version, '<' );
	}

	/**
	 * Check if product enabled for selling serial numbers.
	 *
	 * @param int $product_id Product ID
	 *
	 * since #.#.# function moved from function file.
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	public static function is_serial_product( $product_id ) {
		return 'yes' === get_post_meta( $product_id, '_selling_serial_numbers', true );
	}

	/**
	 * Update product metadata.
	 *
	 * @since #.#.#
	 *
	 * @param int|\WC_Product $product Product object.
	 * @param string $meta_key Meta key.
	 * @param mixed $meta_value Meta value.
	 */
	public static function update_product_meta( $product, $meta_key, $meta_value ) {
		$product = self::get_product_object( $product );

		if ( $product ) {
			if ( self::is_woocommerce_pre( '3.0' ) ) {
				update_post_meta( $product->get_id(), $meta_key, $meta_value );
			} else {
				$product->update_meta_data( $meta_key, $meta_value );
				$product->save_meta_data();
			}
		}
	}


	/**
	 * Return parent  product ID.
	 *
	 * @since #.#.#
	 *
	 * @param int|\WC_Product|\WC_Order_Item $product WooCommerce Product object.
	 *
	 * @return bool|int
	 */
	public static function get_parent_product_id( $product ) {
		$product = self::get_product_object( $product );

		if ( $product ) {
			if ( is_callable( array( $product, 'get_parent_id', 'is_type' ) ) ) {
				return $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
			}

			if ( is_callable( array( $product, 'get_product_id' ) ) ) {
				return ! empty( $product->get_product_id() ) ? $product->get_product_id() : $product->get_id();
			}

			return $product->get_id();
		}

		return false;
	}

	/**
	 * Return the product object.
	 *
	 * @since #.#.#
	 *
	 * @param int|mixed $product WC_Product or order ID.
	 *
	 * @return null|\WC_Product
	 */
	public static function get_product_object( $product ) {
		return is_object( $product ) ? $product : wc_get_product( $product );
	}

	/**
	 * Returns the product type, i.e. simple.
	 *
	 * @since #.#.#
	 *
	 * @param int|\WC_Product $product WC_Product.
	 *
	 * @return bool|string
	 */
	public static function get_product_type( $product ) {
		$product = self::get_product_object( $product );

		return $product ? $product->get_type() : false;
	}

	/**
	 * Return the customer/user ID.
	 *
	 * @since #.#.#
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return bool|int|mixed
	 */
	public static function get_customer_id( $order ) {
		$order = self::get_order_object( $order );

		if ( $order && ! ( $order instanceof \WC_Order_Refund ) ) {
			return self::is_woocommerce_pre( '3.0' ) ? $order->get_user_id() : $order->get_customer_id();
		}

		return false;
	}

	/**
	 * Return the order object.
	 *
	 * @since #.#.#
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return bool|\WC_Order
	 */
	public static function get_order_object( $order ) {
		return is_object( $order ) ? $order : wc_get_order( $order );
	}

	/**
	 * Returns true if the product exists and is not in the trash.
	 *
	 * @since #.#.#
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	public static function is_product_purchasable( $product_id ) {
		$product = self::get_product_object( $product_id );

		return $product && $product->is_purchasable();
	}

	/**
	 * Returns a formatted array of order line item data from an order.
	 *
	 * @since #.#.#
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array order line item data.
	 */
	public static function get_order_line_items( $order_id ) {
		$line_items = array();
		$order      = self::get_order_object( $order_id );
		$items      = $order->get_items();
		if ( is_object( $order ) && count( $items ) > 0 ) {
			foreach ( $items as $item_id => $item ) {
				$product_id = $item->get_product_id();
				if ( Helper::is_serial_product( $product_id ) ) {
					$line_items[] = $item;
				}


//				$parent_product_id = self::get_parent_product_id( $item );
//				$is_serial_product = self::is_serial_product( ! empty( $parent_product_id ) ? $parent_product_id : $item->get_product_id() );
//				// Only store API resource data for API products that have an order status of completed.
//				if ( $is_serial_product ) {
//					$values         = array();
//					$variation_id   = ! empty( $item->get_variation_id() ) && self::is_product_purchasable( $parent_product_id ) ? $item->get_variation_id() : 0;
//					$product_id     = ! empty( $variation_id ) ? $variation_id : $item->get_product_id(); // purchasing product id.
//					$is_purchasable = self::is_product_purchasable( $product_id );
//
//					// Check if purchasable and not subscription then proceed.
//					if ( $is_purchasable && self::is_valid_product_type( $product_id ) ) {
//						$item_qty                = ! empty( $item->get_quantity() ) ? $item->get_quantity() : 0;
//						$refund_qty              = $order->get_qty_refunded_for_item( $item_id );
//						$values['item_qty']      = $item_qty;
//						$values['refund_qty']    = absint( $refund_qty );
//						$values['product_id']    = $product_id;
//						$values['parent_id']     = $parent_product_id;
//						$values['order_item_id'] = ! empty( $item_id ) ? (int) $item_id : 0;
//						$values['key_source']    = get_post_meta( $product_id, '_serial_numbers_key_source', true );
//						$line_items[]            = $values;
//					}
//				}
			}
		}

		return apply_filters( 'wc_serial_numbers_order_line_items', $line_items, $order_id );
	}

	/**
	 * Adds a new order, or updates an existing order.
	 *
	 * @since #.#.#
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return boolean if changes then true otherwise false.
	 */
	public static function update_order( $order_id ) {
		$order           = self::get_order_object( $order_id );
		$customer_id     = self::get_customer_id( $order );
		$line_items      = self::get_order_line_items( $order );
		$revoke_statuses = self::get_revoke_statues();
		$is_reuse        = 'yes' === get_option( 'wc_serial_numbers_reuse', 'yes' );
		$order_status    = $order->get_status( 'edit' );
		if ( empty( $line_items ) ) {
			delete_post_meta( $order->get_id(), '_is_serial_number_processed' );

			return false;
		}
		add_post_meta( $order->get_id(), '_is_serial_number_processed', 'yes' );

		// Connect any missing keys.
		foreach ( $line_items as $k => $item ) {
			$delivered_keys = Keys::query(
				[
					'product_id__in'    => absint( $item['product_id'] ),
					'order_id__in'      => $order->get_id(),
					'order_item_id__in' => absint( $item['order_item_id'] ),
					'per_page'          => - 1,
				],
			);

			if ( in_array( $order_status, [ 'processing', 'completed' ], true ) ) {
				if ( $item['refund_qty'] >= $item['item_qty'] ) {
					continue;
				}
				$delivered_qty = count( $delivered_keys );
				$needed_count  = $delivered_qty - absint( $item['item_qty'] );

				if ( $delivered_qty >= $item['item_qty'] || empty( $needed_count ) ) {
					continue;
				}

				$keys = apply_filters(
					'wc_serial_numbers_order_item_keys',
					Keys::query(
						[
							'product_id__in' => absint( $item['product_id'] ),
							'status__in'     => 'available',
							'per_page'       => absint( $needed_count ),
						]
					),
					$item,
					$order_id
				);

				if ( count( $keys ) < $needed_count ) {
					$order->add_order_note(
						sprintf(
						/* translators: 1: product title 2: source and 3: Quantity */
							esc_html__( 'There is not enough serial numbers for the product %1$s from selected source %2$s, needed total %3$d.', 'wc-serial-numbers' ),
							self::get_product_title( $item['product_id'] ),
							$item['key_source'],
							$item['item_qty']
						),
						false
					);

					continue;
				}

				foreach ( $keys as $key ) {
					$key->set_props(
						[
							'order_id'      => $order->get_id(),
							'order_item_id' => $item['order_item_id'],
							'status'        => 'processing' === $order_status ? 'sold' : 'delivered',
							'customer_id'   => $customer_id,
							'date_ordered'  => current_time( 'mysql' ),
						]
					);

					$key->save();
				}

				return true;
			}
		}

		// update status.
		$delivered_keys = Keys::query(
			[
				'order_id__in' => $order->get_id(),
				'per_page'     => - 1,
			],
		);

		foreach ( $delivered_keys as $delivered_key ) {
			$validity_expire_date = strtotime( "+{$delivered_key->valid_for}} day", strtotime( $delivered_key->date_order ) );
			$is_expired           = ! empty( $delivered_key->valid_for ) && strtotime( $validity_expire_date ) > strtotime( $delivered_key->date_order );
			$key_source           = get_post_meta( $delivered_key->product_id, '_serial_numbers_key_source', true );

			if ( $is_expired && 'expired' !== $delivered_key->status ) {
				$delivered_key->set_status( 'expired' );
				$delivered_key->save();
			} elseif ( 'completed' === $order_status && 'delivered' !== $delivered_key->status && ! $is_expired ) {
				$delivered_key->set_status( 'delivered' );
				$delivered_key->save();
			} elseif ( 'processing' === $order_status && 'sold' !== $delivered_key->status && ! $is_expired ) {
				$delivered_key->set_status( 'sold' );
				$delivered_key->save();
			} elseif ( in_array( $order_status, $revoke_statuses, true ) ) {
				if ( $is_reuse && 'pre_generated' === $key_source ) {
					$delivered_key->set_status( 'available' );
					$delivered_key->save();
				} else {
					$delivered_key->delete();
				}
			}
		}

		return false;
	}

	/**
	 * Returns supported product types.
	 *
	 * 'simple',
	 * 'variable',
	 * 'subscription',
	 * 'simple-subscription',
	 * 'variable-subscription',
	 * 'subscription_variation'
	 *
	 * @since #.#.#
	 * @return mixed|void
	 */
	public static function get_valid_product_types() {
		return apply_filters( 'wc_serial_numbers_valid_product_types', array( 'simple' ) );
	}

	/**x
	 * Check if the product supports serial numbers.
	 *
	 * @param int|\WC_Product $product_id WC_Product.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public static function is_valid_product_type( $product_id ) {
		$product_type = self::get_product_type( $product_id );

		return ! empty( $product_type ) && in_array( $product_type, self::get_valid_product_types(), true );
	}

	/**
	 * Get key sources.
	 *
	 * @since 1.2.0
	 * @return mixed|void
	 */
	public static function get_key_sources() {
		$sources = array(
			'pre_generated' => __( 'Pre generated', 'wc-serial-numbers' ),
			'generator'     => __( 'Generator rule', 'wc-serial-numbers' ),
		);

		return apply_filters( 'wc_serial_numbers_key_sources', $sources );
	}


	/**
	 * Output queued JavaScript code in the footer inline.
	 *
	 * @since #.#.#
	 *
	 * @param string $queued_js JavaScript.
	 */
	public static function print_js(
		$queued_js
	) {
		if ( ! empty( $queued_js ) ) {
			// Sanitize.
			$queued_js = wp_check_invalid_utf8( $queued_js );
			$queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $queued_js );
			$queued_js = str_replace( "\r", '', $queued_js );

			echo "<!-- WooCommerce Serial Numbers JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) ";
			echo '{';
			echo $queued_js . "});\n</script>\n";

			unset( $queued_js );
		}
	}

	/**
	 * Check if software support is enabled or not.
	 *
	 * @since #.#.#
	 * @return bool
	 */
	public static function is_software_support_enabled() {
		return 'yes' !== get_option( 'wc_serial_numbers_disable_software_support', 'no' );
	}

	/**
	 * Get product title.
	 *
	 * @param \WC_Product| int $product Product title.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public static function get_product_title( $product ) {
		$product = self::get_product_object( $product );
		if ( $product && ! empty( $product->get_id() ) ) {
			return sprintf(
				'(#%1$s) %2$s',
				$product->get_id(),
				html_entity_decode( $product->get_formatted_name() )
			);
		}

		return '';
	}

	/**
	 * Get order revoke statuses.
	 *
	 * @since #.#.#
	 * @return array
	 */
	public static function get_revoke_statues() {
		$statues       = [];
		$check_statues = [
			'wc_serial_numbers_revoke_status_cancelled' => 'cancelled',
			'wc_serial_numbers_revoke_status_refunded'  => 'refunded',
			'wc_serial_numbers_revoke_status_failed'    => 'failed',
		];

		foreach ( $check_statues as $option => $status ) {
			if ( 'yes' === get_option( $option, 'yes' ) ) {
				$statues[] = $status;
			}
		}

		return $statues;
	}

	/**
	 * Display key properties.
	 *
	 * @param array $props Properties.
	 * @param array $args Arguments.
	 *
	 * @since #.#.#
	 * @return mixed|void
	 */
	public static function display_key_props(
		$props, $args = []
	) {
		$strings     = array();
		$html        = '';
		$text_align  = is_rtl() ? 'right' : 'left';
		$margin_side = is_rtl() ? 'left' : 'right';
		$args        = (array) wp_parse_args(
			$args,
			array(
				'before'       => '<ul class="wc-item-meta"><li>',
				'after'        => '</li></ul>',
				'separator'    => '</li><li>',
				'echo'         => true,
				'autop'        => false,
				'label_before' => '<strong class="wc-item-meta-label" style="float: ' . esc_attr( $text_align ) . '; margin-' . esc_attr( $margin_side ) . ': .25em; clear: both">',
				'label_after'  => ':</strong> ',
			)
		);

		foreach ( $props as $prop ) {
			$prop      = wp_parse_args(
				$prop,
				[
					'display_value' => '',
					'display_key'   => '',
				]
			);
			$value     = $args['autop'] ? wp_kses_post( $prop['display_value'] ) : wp_kses_post( trim( $prop['display_value'] ) );
			$strings[] = $args['label_before'] . wp_kses_post( $prop['display_key'] ) . $args['label_after'] . '<span class="' . sanitize_html_class( sanitize_key( $prop['display_key'] ) ) . '">' . $value . '</span>';
		}

		if ( $strings ) {
			$html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
		}

		$html = apply_filters( 'wc_serial_numbers_display_item_props', $html, $props, $args );

		if ( $args['echo'] ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $html;
		} else {
			return $html;
		}
	}
}
