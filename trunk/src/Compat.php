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
		add_action( 'wpo_wcpdf_after_order_details', array( __CLASS__, 'wpo_wcpdf_after_order_details' ), 10, 2 );
		add_action( 'pdf_template_table_headings', array( __CLASS__, 'woocommerce_pdf_invoice_support' ), 10, 2 );
		add_action( 'wf_module_generate_template_html', array( __CLASS__, 'wf_module_generate_template_html' ), 10, 4 );
	}

	/**
	 * WooCommerce PDF Invoices & Packing Slips plugin support.
	 *
	 * @param string    $type Document type.
	 * @param \WC_Order $order Order object.
	 *
	 * @since 1.2.0
	 */
	public static function wpo_wcpdf_after_order_details( $type, $order ) {
		wc_serial_numbers_get_order_table( $order );
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
