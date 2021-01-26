<?php

namespace WCSerialNumbers\Upgrade;

use ReflectionClass;
use ReflectionMethod;

abstract class AbstractUpgrader {

	protected static $reflection;

	/**
	 * Execute upgrader class methods
	 *
	 * This method will execute every method found in child
	 * upgrader class dynamically. Keep in mind that methods
	 * should be public static function.
	 *
	 * @since 1.2.8
	 *
	 * @param string $required_base_plugin_version Required in case of Pro upgraders
	 *
	 * @return void
	 */
	public static function run( $required_base_plugin_version = null ) {
		// This condition is useful for upgraders set in pro or extensions
		if ( $required_base_plugin_version ) {
			// Do not use `self::get_db_version_key()` or `static::get_db_version_key()` here,
			// `get_db_version_key` method will be overriden in pro upgrader abstract classe.
			$base_plugin_db_version = get_option( wc_serial_numbers()->get_db_version_key() );

			if ( version_compare( $base_plugin_db_version, $required_base_plugin_version, '<' ) ) {
				return;
			}
		}

		self::$reflection = new ReflectionClass( static::class );
		$methods          = self::$reflection->getMethods( ReflectionMethod::IS_PUBLIC );

		foreach ( $methods as $method ) {
			if ( $method->class === static::class ) {
				call_user_func( [ static::class, $method->name ] );
			}
		}
	}

	/**
	 * Update the DB version
	 *
	 * Upgrader files should follow naming convention
	 * as V_XX_XX_XX.php where Xs are number following
	 * semvar convention. For example if you have a upgrader
	 * for version 1.23.40, the the filename should be
	 * V_1_23_40.php.
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public static function update_db_version() {
		$base_class = self::$reflection->getShortName();
		$version = str_replace( [ 'V_', '_' ], [ '', '.' ], $base_class );

		update_option( static::get_db_version_key(), $version );
	}

	/**
	 * Get db versioning key
	 *
	 * This method should be overriden in pro or extension abstract class
	 *
	 * @since 1.2.8
	 *
	 * @return string
	 */
	protected static function get_db_version_key() {
		return wc_serial_numbers()->get_db_version_key();
	}
}
