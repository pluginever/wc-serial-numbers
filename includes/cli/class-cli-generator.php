<?php

namespace PluginEver\WooCommerceSerialNumbers\CLI;

use PluginEver\WooCommerceSerialNumbers\Generators;
use PluginEver\WooCommerceSerialNumbers\Serial_Keys;
use \WP_CLI;

defined( 'ABSPATH' ) || exit;

class CLI_Generator {

	/**
	 * Registers a command for showing WooCommerce Tracker snapshot data.
	 */
	public static function register_commands() {
		WP_CLI::add_command( 'wc_serial_numbers make keys', array( __CLASS__, 'make_keys' ) );
		WP_CLI::add_command( 'wc_serial_numbers make generators', array( __CLASS__, 'make_generators' ) );
	}

	/**
	 * Generate generators.
	 *
	 * [--pattern=<pattern>]
	 * : Serial Number pattern e.g. SERIAL-####-####-####-####
	 * ---
	 * default: 'SERIAL-####-####-####-####'
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
	 * [--date_expire=<date_expire>]
	 * : When to expire this key.
	 * ---
	 * default: 0000-00-00 00:00:00
	 * ---
	 *
	 * [--sequential=<sequential>]
	 * : Is it sequential?
	 * ---
	 * default: false
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * wp wc_serial_numbers make generators --pattern=SERIAL-####-####-####-####
	 * wp wc_serial_numbers make generators --pattern=SERIAL-####-####-####-#### --activation_limit=10 --validity=365 --date_expire=2025-12-31
	 *
	 * @param array $args WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 */
	public static function make_generators(  $args, $assoc_args  ){
		$pattern          = $assoc_args['pattern'];
		$activation_limit = absint( $assoc_args['activation_limit'] );
		$validity         = absint( $assoc_args['validity'] );
		$date_expire      = absint( $assoc_args['date_expire'] );
		$sequential       = absint( $assoc_args['sequential'] );
		$start            = absint( $assoc_args['start'] );

		if ( empty( $pattern ) ) {
			WP_CLI::error( "Pattern could not be empty" );

			return;
		}

		if ( empty( $product_id ) ) {
			WP_CLI::error( "Product id is required" );

			return;
		}
	}

	/**
	 * Generate Serial Keys.
	 * <number>
	 * : Number of items to generate.
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
	 * [--date_expire=<date_expire>]
	 * : When to expire this key.
	 * ---
	 * default: 0000-00-00 00:00:00
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
	 * wp wc_serial_numbers make keys --pattern=SERIAL-####-####-####-####
	 * wp wc_serial_numbers make keys --pattern=SERIAL-####-####-####-#### --number=10 --product_id=10 --activation_limit=10 --validity
	 *
	 * @param array $args WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 */
	public static function make_keys( $args, $assoc_args ) {
		$number           = absint( $args[0] );
		$pattern          = $assoc_args['pattern'];
		$product_id       = absint( $assoc_args['product_id'] );
		$activation_limit = absint( $assoc_args['activation_limit'] );
		$validity         = absint( $assoc_args['validity'] );
		$date_expire      = absint( $assoc_args['date_expire'] );
		$sequential       = absint( $assoc_args['sequential'] );
		$start            = absint( $assoc_args['start'] );

		if ( empty( $pattern ) ) {
			WP_CLI::error( "Pattern could not be empty" );

			return;
		}

		if ( empty( $product_id ) ) {
			WP_CLI::error( "Product id is required" );

			return;
		}

		$keys     = Generators::generate_keys( $pattern, $number, $sequential, $start );
		$counter  = 0;
		$progress = WP_CLI\Utils\make_progress_bar( "Generating serial keys ($number)", $number );
		for ( $i = 0; $i < $number; $i ++ ) {
			$created = Serial_Keys::insert( array(
				'key'              => $keys[ $i ],
				'product_id'       => $product_id,
				'activation_limit' => $activation_limit,
				'validity'         => $validity,
				'date_expire'      => $date_expire,
			) );
			if ( $created ) {
				$counter ++;
			}
			$progress->tick();
		}

		$progress->finish();
		WP_CLI::success( "Successfully generated ($counter) serial keys." );
	}
}
