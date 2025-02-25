<?php

namespace WooCommerceSerialNumbers;

defined( 'ABSPATH' ) || exit;

/**
 * Class Compat.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class Compat {

	/**
	 * Compat constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// WooCommerce PDF Invoices & Packing Slips plugin support.
		add_action( 'wpo_wcpdf_after_item_meta', array( __CLASS__, 'wpo_wcpdf_after_item_meta' ), 10, 3 );

		// WooCommerce PDF Invoices plugin support.
		add_action( 'pdf_template_table_headings', array( __CLASS__, 'woocommerce_pdf_invoice_support' ), 10, 2 );

		// WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels plugin support.
		add_action( 'wf_module_generate_template_html', array( __CLASS__, 'wf_module_generate_template_html' ), 10, 4 );
	}

	/**
	 * WooCommerce PDF Invoices & Packing Slips plugin support.
	 * This will display serial numbers in the invoice.
	 *
	 * @param string    $type Document type.
	 * @param array     $item Item data.
	 * @param \WC_Order $order Order object.
	 *
	 * @since 2.1.4
	 */
	public static function wpo_wcpdf_after_item_meta( $type, $item, $order ) {
		$item_id      = isset( $item['item_id'] ) ? $item['item_id'] : 0;
		$variation_id = isset( $item['variation_id'] ) ? $item['variation_id'] : 0;
		$order_id     = $order->get_id();
		$product_id   = isset( $item['product_id'] ) ? $item['product_id'] : 0;

		if ( empty( $item_id ) || empty( $order_id ) || empty( $product_id ) ) {
			return;
		}

		$product = wc_get_product( $product_id );

		if ( ! $product || ! wcsn_is_product_enabled( $product->get_id() ) ) {
			return;
		}

		$keys = wcsn_get_keys(
			apply_filters(
				'wcsn_order_item_keys_query_args',
				array(
					'order_id'   => $order_id,
					'product_id' => $product->get_id(),
					'limit'      => - 1,
				),
				$item_id,
				$order_id
			)
		);

		if ( empty( $keys ) ) {
			return;
		}

		echo '<p style="color: #888;">' . esc_html__( 'Serial keys sold with this product:', 'wc-serial-numbers' ) . '</p>';

		foreach ( $keys as $index => $key ) {
			$data = array(
				'key'              => array(
					'label' => __( 'Key', 'wc-serial-numbers' ),
					'value' => '<code>' . $key->get_key() . '</code>',
				),
				'expire_date'      => array(
					'label' => __( 'Expire date', 'wc-serial-numbers' ),
					'value' => $key->get_expire_date() ? $key->get_expire_date() : __( 'Lifetime', 'wc-serial-numbers' ),
				),
				'activation_limit' => array(
					'label' => __( 'Activation limit', 'wc-serial-numbers' ),
					'value' => $key->get_activation_limit() ? $key->get_activation_limit() : __( 'Unlimited', 'wc-serial-numbers' ),
				),
				'status'           => array(
					'label' => __( 'Status', 'wc-serial-numbers' ),
					'value' => $key->get_status_label(),
				),
			);

			$data = apply_filters( 'wc_serial_numbers_admin_order_item_data', $data, $key, $item, $product, $order_id );
			if ( empty( $data ) ) {
				continue;
			}

			?>
			<table cellspacing="0" class="display_meta wcsn-admin-order-item-meta" style="margin-bottom: 10px;">
				<tbody>
				<tr>
					<th colspan="2">
						<?php // translators: %s is the item number. ?>
						<?php printf( '#%s:', esc_html( $index + 1 ) ); ?>
					</th>
				</tr>
				<?php foreach ( $data as $prop => $field ) : ?>
					<tr class="<?php echo sanitize_html_class( $prop ); ?>">
						<th><?php echo esc_html( $field['label'] ); ?>:</th>
						<td><?php echo wp_kses_post( $field['value'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php
		}
	}

	/**
	 * WooCommerce PDF Invoices
	 *
	 * @param string $headers Header content.
	 * @param int    $order_id Order ID.
	 *
	 * @return string
	 * @since 1.1.1
	 */
	public static function woocommerce_pdf_invoice_support( $headers, $order_id ) {
		$order   = wc_get_order( $order_id );
		$content = wc_serial_numbers_get_order_table( $order, true );

		return $content . $headers;
	}


	/**
	 * Support WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels plugin
	 *
	 * @param array     $find_replace Find and replace array.
	 * @param string    $html HTML content.
	 * @param string    $template_type Template type.
	 * @param \WC_Order $order Order object.
	 *
	 * @return array
	 * @since 1.1.1
	 */
	public static function wf_module_generate_template_html( $find_replace, $html, $template_type, $order ) {
		if ( isset( $find_replace['[wfte_product_table_start]'] ) ) {
			ob_start();
			wc_serial_numbers_get_order_table( $order );
			?>
			<style type="text/css">
				.wfte_product_table.wcsn-pdf-table {
					margin-bottom: 30px;
				}
			</style>
			<?php
			$find_replace['[wfte_product_table_start]'] = ob_get_clean();
		}

		return $find_replace;
	}
}
