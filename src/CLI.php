<?php

namespace WooCommerceSerialNumbers;

use WooCommerceSerialNumbers\Models\Key;
use WP_CLI;

defined( 'ABSPATH' ) || exit;

/**
 * Class CLI.
 *
 * @since   1.0.0
 * @package WooCommerceSerialNumbers
 */
class CLI extends Lib\Singleton {

	/**
	 * CLI constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		WP_CLI::add_hook( 'after_wp_load', [ __CLASS__, 'register_commands' ] );
	}

	/**
	 * Register commands.
	 *
	 * @throws \Exception
	 * @return void
	 */
	public static function register_commands() {
		WP_CLI::add_command( 'wcsn import', array( __CLASS__, 'import' ) );
		WP_CLI::add_command( 'wcsn export', array( __CLASS__, 'export' ) );
		WP_CLI::add_command( 'wcsn make keys', array( __CLASS__, 'make_keys' ) );
//		WP_CLI::add_command( 'wcsn make generators', array( __CLASS__, 'make_generators' ) );
	}

	/**
	 * Import keys from CSV file.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to CSV file.
	 *
	 * [--delimiter=<delimiter>]
	 * : Delimiter.
	 * ---
	 * default: ,
	 * ---
	 *
	 * [--enclosure=<enclosure>]
	 * : Enclosure.
	 * ---
	 * default: "
	 * ---
	 *
	 * [--escape=<escape>]
	 * : Escape.
	 * ---
	 * default: \
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * wp wcsn import /path/to/file.csv
	 *
	 * @param array $args WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 *
	 * @throws \Exception
	 */
	public static function import( $args, $assoc_args ) {
		$file      = $args[0];
		$delimiter = $assoc_args['delimiter'];
		$enclosure = $assoc_args['enclosure'];
		$escape    = $assoc_args['escape'];

		// Read CSV file.
		$handle = fopen( $file, 'r' );
		if ( false === $handle ) {
			throw new \Exception( 'Unable to open file.' );
		}

		// Get header.
		$header = fgetcsv( $handle, 0, $delimiter, $enclosure, $escape );
		if ( false === $header ) {
			throw new \Exception( 'Unable to read header.' );
		}

		// Get data.
		$data = [];
		while ( ( $row = fgetcsv( $handle, 0, $delimiter, $enclosure, $escape ) ) !== false ) {
			$data[] = array_combine( $header, $row );
		}

		$progress = WP_CLI\Utils\make_progress_bar( 'Importing keys', count( $data ) );

		// Import data.
		$imported = 0;
		$failed   = 0;
		foreach ( $data as $row ) {
			$key = Key::insert( $row );
			if ( is_wp_error( $key ) ) {
				WP_CLI::warning( $key->get_error_message() );
				$failed ++;
			} else {
				$imported ++;
			}
			$progress->tick();
		}

		$progress->finish();
		// Show a nice message to the user with the number of imported keys and failed keys.
		WP_CLI::success( sprintf( 'Imported %d keys, failed %d keys.', $imported, $failed ) );
	}

	/**
	 * Export keys to CSV file.
	 *
	 * ## OPTIONS
	 *
	 * [--delimiter=<delimiter>]
	 * : Delimiter.
	 * ---
	 * default: ,
	 * ---
	 *
	 * [--enclosure=<enclosure>]
	 * : Enclosure.
	 * ---
	 * default: "
	 * ---
	 *
	 * [--escape=<escape>]
	 * : Escape.
	 * ---
	 * default: \
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * wp wcsn export /path/to/file.csv
	 *
	 * @param array $args WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 *
	 * @throws \Exception
	 */
	public static function export( $args, $assoc_args ) {
		$delimiter = $assoc_args['delimiter'];
		$enclosure = $assoc_args['enclosure'];
		$escape    = $assoc_args['escape'];

		// Make a file name.
		$file_name = 'exported-serial-numbers-' . wp_date( 'Y-m-d_H-i-s' ) . '.csv';
		// Create the in uploads directory.
		$file = wp_upload_dir()['basedir'] . '/' . $file_name;

		// Get keys.
		$keys = Key::query();

		// Open file.
		$handle = fopen( $file, 'w' );
		if ( false === $handle ) {
			throw new \Exception( 'Unable to open file.' );
		}

		// Write header.
		$header = array_keys( $keys[0]->get_data() );
		fputcsv( $handle, $header, $delimiter, $enclosure, $escape );

		$progress = WP_CLI\Utils\make_progress_bar( 'Exporting keys', count( $keys ) );

		// Write data.
		foreach ( $keys as $key ) {
			fputcsv( $handle, array_values( $key->get_data() ), $delimiter, $enclosure, $escape );
			$progress->tick();
		}

		$progress->finish();
		// Show a nice message to the user with the number of exported keys.
		WP_CLI::success( sprintf( 'Exported %d keys. File: %s', count( $keys ), $file ) );
	}

	/**
	 * Make keys.
	 *
	 * ## OPTIONS
	 * <count>
	 * : Number of keys to make.
	 * ---
	 * default: 10
	 * ---
	 *
	 * [--pattern=<pattern>]
	 * : Serial Number pattern e.g. SERIAL-####-####-####-####
	 * ---
	 * default: 'SERIAL-####-####-####-####'
	 * ---
	 *
	 * [--generator_id=<generator_id>]
	 * : Generator id if generator id is passed it will use e.g --generator_id=10
	 * ---
	 * default: null
	 * ---
	 *
	 * [--product_id=<product_id>]
	 * : ID of the product to generate serial keys for.
	 * ---
	 * default: null
	 * ---
	 *
	 * [--activation_limit=<activation_limit>]
	 * : API activation limit
	 * ---
	 * default: 10
	 * ---
	 *
	 * [--validity=<days_in_number>]
	 * : Valid till x days after purchase
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--sequential=<sequential>]
	 * : Is it sequential?
	 * ---
	 * default: false
	 * ---
	 *
	 * [--start=<start>]
	 * : Sequential start number
	 * ---
	 * default: 0
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * wp wcsn make keys --pattern=SERIAL-####-####-####-####
	 * wp wcsn make keys --pattern=SERIAL-####-####-####-#### --number=10 --product_id=10 --activation_limit=10 --validity=20 --sequential=true --start=0
	 *
	 * @param array $args WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 *
	 * @throws \Exception
	 */
	public static function make_keys( $args, $assoc_args ) {
		$pattern = $assoc_args['pattern'];
//		$generator_id     = $assoc_args['generator_id'];
//		$product_id       = $assoc_args['product_id'];
		$activation_limit = $assoc_args['activation_limit'];
		$validity         = $assoc_args['validity'];
//		$sequential       = $assoc_args['sequential'];
//		$start            = $assoc_args['start'];
		$count = $args[0];
//
//		if ( empty( $product_id ) ) {
//			WP_CLI::error( "Product id is required" );
//			return;
//		}
//
//		// If pattern and generator id is not passed.
//		if ( ! $pattern && ! $generator_id ) {
//			WP_CLI::error(( 'Please pass pattern or generator id.' ));
//		}
//
//		// If generator id is passed then get pattern from generator.
//		if ( $generator_id ) {
//			$generator = Generator::get( $generator_id );
//			$pattern   = $generator->get_pattern();
//		}

		$random_product = wc_get_products( [
			'limit'   => 1,
			'orderby' => 'rand',
		] );
		$product_id     = $random_product[0]->get_id();
		$progress       = WP_CLI\Utils\make_progress_bar( 'Making keys', $count );

		// Make keys.
		$keys = [];
		for ( $i = 0; $i < $count; $i ++ ) {
			$key = wc_serial_numbers_pro_generate_serial_keys( $pattern );
			Key::insert( [
				'serial_key'       => $key,
				'product_id'       => $product_id,
				'activation_limit' => $activation_limit,
				'validity'         => $validity,
				'status'           => 'instock',
			] );
			$progress->tick();
		}

		$progress->finish();

		// Show a nice message to the user with the number of exported keys.
		WP_CLI::success( sprintf( 'Made %d keys.', count( $keys ) ) );
	}
}
