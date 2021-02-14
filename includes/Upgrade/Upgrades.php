<?php

namespace WCSerialNumbers\Upgrade;

class Upgrades {

	/**
	 * Files or classes containing updates
	 *
	 * @since 1.2.8
	 *
	 * @var array
	 */
	private static $upgrades = [
	];

	/**
	 * Get DB installed version number
	 *
	 * @since 1.2.8
	 *
	 * @return string
	 */
	public static function get_db_installed_version() {
		return get_option( wc_serial_numbers()->get_db_version_key(), null );
	}

	/**
	 * Checks if upgrade is required or not
	 *
	 * @since 1.2.8
	 *
	 * @param bool $is_required
	 *
	 * @return bool
	 */
	public static function is_upgrade_required( $is_required = false ) {
		$installed_version = self::get_db_installed_version();
		$upgrade_versions  = array_keys( self::$upgrades );

		if ( $installed_version && version_compare( $installed_version, end( $upgrade_versions ), '<' ) ) {
			return true;
		}

		return $is_required;
	}

	/**
	 * Update DB version
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public static function update_db_version() {
		$installed_version = self::get_db_installed_version();

		if ( version_compare( $installed_version, WC_SERIAL_NUMBER_PLUGIN_VERSION, '<' ) ) {
			update_option( wc_serial_numbers()->get_db_version_key(), WC_SERIAL_NUMBER_PLUGIN_VERSION );
		}
	}

	/**
	 * Get upgrades
	 *
	 * @since 1.2.8
	 *
	 * @param array $upgrades
	 *
	 * @return array
	 */
	public static function get_upgrades( $upgrades = [] ) {
		if ( ! self::is_upgrade_required() ) {
			return $upgrades;
		}

		$installed_version = self::get_db_installed_version();

		foreach ( self::$upgrades as $version => $class_name ) {
			if ( version_compare( $installed_version, $version, '<' ) ) {
				$upgrades[ $version ][] = $class_name;
			}
		}

		return $upgrades;
	}
}
