<?php

/**
 * Plugin Upgrade Routine
 *
 * @since 1.0.0
 */
class WCSN_Updates {
	/**
	 * The upgrades
	 *
	 * @var array
	 */
	private static $upgrades = array(
		'1.0.1' => 'updates/update-1.0.1.php',
		'1.0.6' => 'updates/update-1.0.6.php',
		'1.0.8' => 'updates/update-1.0.8.php',
		'1.1.2' => 'updates/update-1.1.2.php',
	);

	public function get_key() {
		$key = sanitize_key( wc_serial_numbers()->plugin_name );

		return $key . '_version';
	}

	/**
	 * Get the plugin version
	 *
	 * @return string
	 */
	public function get_version() {
		$key = $this->get_key();

		return get_option( $key );
	}

	/**
	 * Check if the plugin needs any update
	 *
	 * @return boolean
	 */
	public function needs_update() {
		// may be it's the first install
		if ( ! $this->get_version() ) {
			return false;
		}
		if ( version_compare( $this->get_version(), WC_SERIAL_NUMBERS_VERSION, '<' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Perform all the necessary upgrade routines
	 *
	 * @return void
	 */
	function perform_updates() {
		$installed_version = $this->get_version();
		$path              = trailingslashit( WC_SERIAL_NUMBERS_INCLUDES );
		$key               = $this->get_key();
		foreach ( self::$upgrades as $version => $file ) {
			if ( version_compare( $installed_version, $version, '<' ) ) {
				include $path . $file;
				update_option( $key, $version );
			}
		}

		delete_option( $key );
		update_option( $key, WC_SERIAL_NUMBERS_VERSION );
	}
}

