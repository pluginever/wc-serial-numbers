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
class CLI extends \WooCommerceSerialNumbers\Lib\Singleton {

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
//		WP_CLI::add_command( 'wcsn make keys', array( __CLASS__, 'make_keys' ) );
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
}
