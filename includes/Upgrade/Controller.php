<?php

namespace WCSerialNumbers\Upgrade;

class Controller {

	/**
	 * The db key refers we have an ongoing upgrading process
	 *
	 * @var string
	 */
	private $is_upgrading_db_key = 'wcsn_is_upgrading';

	/**
	 * Checks if update is required or not
	 *
	 * @since 1.2.8
	 *
	 * @return bool
	 */
	public function is_upgrade_required() {
		/**
		 * Filter to upgrade is required or not
		 *
		 * @since 1.2.8
		 *
		 * @param bool $is_required Is upgrade required
		 */
		return apply_filters( 'wcsn_upgrade_is_upgrade_required', false );
	}

	/**
	 * Checks for any ongoing process
	 *
	 * @since 1.2.8
	 *
	 * @return bool
	 */
	public function has_ongoing_process() {
		return !! get_option( $this->is_upgrading_db_key, false );
	}

	/**
	 * Get upgradable upgrades
	 *
	 * @since 1.2.8
	 *
	 * @return array
	 */
	public function get_upgrades() {
		$upgrades = get_option( $this->is_upgrading_db_key, null );

		if ( ! empty( $upgrades ) ) {
			return $upgrades;
		}

		/**
		 * Filter upgrades
		 *
		 * @since 1.2.8
		 *
		 * @var array
		 */
		$upgrades = apply_filters( 'wcsn_upgrade_upgrades', [] );

		uksort(
			$upgrades, function ( $a, $b ) {
				return version_compare( $b, $a, '<' );
			}
		);

		update_option( $this->is_upgrading_db_key, $upgrades, false );

		return $upgrades;
	}

	/**
	 * Run upgrades
	 *
	 * This will execute every method found in a
	 * upgrader class, execute `run` method defined
	 * in `AbstractUpgrader` class and then finally,
	 * `update_db_version` will update the db version
	 * reference in database.
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public function do_upgrade() {
		$upgrades = $this->get_upgrades();

		foreach ( $upgrades as $version => $upgraders ) {
			foreach ( $upgraders as $upgrader ) {
				$required_version = null;

				if ( is_array( $upgrader ) ) {
					$required_version = $upgrader['require'];
					$upgrader         = $upgrader['upgrader'];
				}

				call_user_func( [ $upgrader, 'run' ], $required_version );
				call_user_func( [ $upgrader, 'update_db_version' ] );
			}
		}

		delete_option( $this->is_upgrading_db_key );

		/**
		 * Fires after finish the upgrading
		 *
		 * At this point plugin should update the
		 * db version key to version constant like WC_SERIAL_NUMBER_PLUGIN_VERSION
		 *
		 * @since 1.2.8
		 */
		do_action( 'wcsn_upgrade_finished' );
	}

	/**
	 * Add upgrader to queue
	 *
	 * @since 1.2.8
	 *
	 * @param \WCSerialNumbers\Upgrade\AbstractUpgrader $handler
	 * @param array										$args
	 *
	 * @return void
	 */
	public function add_to_queue( $handler, $args ) {
		wc_serial_numbers()->queue->add( $handler, $args, 'wc_serial_numbers_queue_upgrader' );
	}
}
