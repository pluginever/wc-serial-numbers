<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_Compat {
	/**
	 * WC_Serial_Numbers_Compat constructor.
	 */
	public function __construct() {
		add_action( 'wpo_wcpdf_after_order_details', array( __CLASS__, 'wpo_wcpdf_after_order_details' ), 10, 2 );
		add_action( 'pdf_template_table_headings', array( $this, 'woocommerce_pdf_invoice_support' ), 10, 2 );
		add_action( 'wf_module_generate_template_html', array( $this, 'wf_module_generate_template_html' ), 10, 4 );
	}

	/**
	 * WooCommerce PDF Invoices & Packing Slips plugin support.
	 *
	 * @param $type
	 * @param $order
	 *
	 * @since 1.2.0
	 */
	public static function wpo_wcpdf_after_order_details( $type, $order ) {
		wc_serial_numbers_get_order_table( $order );
	}

	/**
	 * WooCommerce PDF Invoices
	 *
	 * @param $headers
	 * @param $order_id
	 *
	 * @return string
	 * @since 1.1.1
	 */
	function woocommerce_pdf_invoice_support( $headers, $order_id ) {
		$order   = wc_get_order( $order_id );
		$content = wc_serial_numbers_get_order_table( $order, true );;

		return $content . $headers;
	}


	/**
	 * Support WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels plugin
	 *
	 * @param $find_replace
	 * @param $html
	 * @param $template_type
	 * @param $order
	 *
	 * @return array
	 * @since 1.1.1
	 *
	 */

	function wf_module_generate_template_html( $find_replace, $html, $template_type, $order ) {
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

new WC_Serial_Numbers_Compat();
