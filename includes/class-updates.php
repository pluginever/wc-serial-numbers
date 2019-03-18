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
	);

	/**
	 * Get the plugin version
	 *
	 * @return string
	 */
	public function get_version() {
		return get_option( 'wpcp_version' );
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
		foreach ( self::$upgrades as $version => $file ) {
			if ( version_compare( $installed_version, $version, '<' ) ) {
				include $path . $file;
				update_option( 'wc_serial_numbers_version', $version );
			}
		}

		delete_option( 'wpcp_version' );
		update_option( 'wc_serial_numbers_version', WC_SERIAL_NUMBERS_VERSION );
	}
}

