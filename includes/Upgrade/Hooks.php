<?php

namespace WCSerialNumbers\Upgrade;

class Hooks {

	/**
	 * Class constructor
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public function __construct() {
		add_filter( 'wcsn_upgrade_is_upgrade_required', [ Upgrades::class, 'is_upgrade_required' ], 1 );
		add_filter( 'wcsn_upgrade_upgrades', [ Upgrades::class, 'get_upgrades' ], 1 );
		add_action( 'admin_notices', [ AdminNotice::class, 'show_notice' ] );
		add_action( 'wp_ajax_wcsn_do_upgrade', [ AdminNotice::class, 'do_upgrade' ] );
		add_action( 'wcsn_upgrade_is_not_required', [ Upgrades::class, 'update_db_version' ] );
		add_action( 'wcsn_upgrade_finished', [ Upgrades::class, 'update_db_version' ] );
	}
}
