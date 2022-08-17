<?php

namespace PluginEver\WooCommerceSerialNumbers\CLI;

use PluginEver\WooCommerceSerialNumbers\Generators;
use PluginEver\WooCommerceSerialNumbers\Keys;
use WP_CLI;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * CLI commands.
 *
 * @since 1.0.0
 */
class Commands {

	/**
	 * Construct Commands.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {
		WP_CLI::add_hook( 'after_wp_load', [ __CLASS__, 'register_commands' ] );
	}

	/**
	 * Register CLI commands.
	 *
	 * @since 1.3.0
	 */
	public static function register_commands() {
		WP_CLI::add_command( 'serial_numbers make keys', array( __CLASS__, 'make_keys' ) );
		WP_CLI::add_command( 'serial_numbers make generator', array( __CLASS__, 'make_generator' ) );
	}

	/**
	 * Generate Serial Keys.
	 *
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
	 * [--valid_for=<days_in_number>]
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
	 * [--random=<random>]
	 * : Random generated keys
	 *
	 * ## EXAMPLES
	 *
	 * wp serial_numbers make keys 10 --pattern=SERIAL-####-####-####-#### --random
	 * wp serial_numbers make keys 10 --pattern=SERIAL-####-####-####-#### --product_id=25 --activation_limit=10 --valid_for=365
	 *
	 * @param array $args WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 */
	public static function make_keys( $args, $assoc_args ) {
		$number           = absint( $args[0] );
		$pattern          = $assoc_args['pattern'];
		$is_random        = isset( $assoc_args['random'] ) ? true : false;
		$product_id       = absint( $assoc_args['product_id'] );
		$activation_limit = absint( $assoc_args['activation_limit'] );
		$valid_for        = absint( $assoc_args['valid_for'] );
		$date_expire      = absint( $assoc_args['date_expire'] );
		$sequential       = absint( $assoc_args['sequential'] );
		$start            = absint( $assoc_args['start'] );

		var_dump( $is_random );
		exit();

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
			$created = Keys::insert( array(
				'key'              => $keys[ $i ],
				'product_id'       => $product_id,
				'activation_limit' => $activation_limit,
				'valid_for'        => $valid_for,
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

	/**
	 * Make generator.
	 *
	 * [--name=<name>]
	 * : Name of the generator.
	 * ---
	 * default: null
	 * ---
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
	 * default: 0
	 * ---
	 *
	 * [--valid_for=<days_in_number>]
	 * : Valid till x days after purchase
	 * ---
	 * default: 0
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * wp serial_numbers make generator --pattern=SERIAL-####-####-####-####
	 * wp serial_numbers make generator --name="Test Generator" --pattern=SERIAL-####-####-####-#### --activation_limit=10 --valid_for=365
	 *
	 *
	 * @param array $args WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 */
	public static function make_generator( $args, $assoc_args ) {
		$name             = $assoc_args['name'];
		$pattern          = $assoc_args['pattern'];
		$activation_limit = absint( $assoc_args['activation_limit'] );
		$valid_for        = absint( $assoc_args['valid_for'] );
		if ( empty( $name ) ) {
			$name = $pattern;
		}

		if ( empty( $pattern ) ) {
			WP_CLI::error( "Pattern could not be empty" );

			return;
		}

		$created = Generators::insert( array(
			'name'             => $name,
			'pattern'          => $pattern,
			'activation_limit' => $activation_limit,
			'valid_for'        => $valid_for,
		) );

		if ( $created ) {
			WP_CLI::success( "Successfully created generator." );
		} else {
			WP_CLI::error( "Could not create generator." );
		}
	}
}
