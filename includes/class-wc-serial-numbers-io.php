<?php
defined( 'ABSPATH' ) || exit();

class WC_Serial_Numbers_IO {
	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var string
	 */
	protected $deliminator;

	/**
	 * Loads data and optionally a deliminator. Data is assumed to be an array
	 * of associative arrays.
	 *
	 * @param array $data
	 * @param string $deliminator
	 */
	function __construct( $data, $deliminator = "," ) {
		$this->data        = $data;
		$this->deliminator = $deliminator;
	}

	/**
	 * @param $data
	 *
	 * @return string
	 * @since 1.0.1
	 */
	private static function wrap_with_quotes( $data ) {
		$data = preg_replace( '/"(.+)"/', '""$1""', $data );

		return sprintf( '"%s"', $data );
	}

	/**
	 * Echos the escaped CSV file with chosen delimeter
	 *
	 * @return self
	 */
	public function output() {
		foreach ( $this->data as $row ) {
			$quoted_data = array_map( array( __CLASS__, 'wrap_with_quotes' ), $row );
			echo sprintf( "%s\n", implode( $this->deliminator, $quoted_data ) );
		}
	}

	/**
	 * Sets proper Content-Type header and attachment for the CSV output
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function headers( $name ) {
		header( 'Content-Type: application/csv' );
		header( "Content-disposition: attachment; filename={$name}.csv" );
	}


	/**
	 *
	 * @param array $args
	 * @param null $name
	 *
	 * @since 1.5.5
	 */
	public static function export_csv( $args = array() ) {
		$columns = apply_filters( 'wc_serial_numbers_csv_headers', array(
			'id',
			'serial_key',
			'product_id',
			'activation_limit',
			'order_id',
			'customer_id',
			'vendor_id',
			'activation_email',
			'status',
			'validity',
			'expire_date',
			'order_date',
			'created'
		) );
		$rows    = [];
		$serials = WC_Serial_Numbers_Manager::get_serial_numbers( $args );
		foreach ( $serials as $serial ) {
			$row    = [];
			$serial = get_object_vars( $serial );
			foreach ( $serial as $key => $value ) {
				if ( in_array( $key, $columns ) ) {
					$row[] = $key == 'serial_key' ? wc_serial_numbers()->decrypt( $value ) : $value;
				}
			}
			$rows[] = $row; //array_map( array( __CLASS__, 'wrap_with_quotes' ), $row );
		}


		$filename = date( 'YmdHis' ) . '_wc_serial_numbers.csv';

		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");

		ob_start();
		$df = fopen( "php://output", 'w' );
		fputcsv( $df, $columns );
		foreach ( $rows as $row ) {
			fputcsv( $df, $row );
		}

		fclose( $df );

	}

}
