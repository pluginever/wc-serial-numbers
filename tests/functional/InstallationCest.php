<?php


class InstallationCest {

	public function _before( FunctionalTester $I ) {
		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$I->seePluginDeactivated( 'woocommerce' );
		$I->seePluginDeactivated( 'wc-serial-numbers' );
		$I->activatePlugin( 'woocommerce' );
		$I->activatePlugin( 'wc-serial-numbers' );
		$I->seePluginActivated( 'woocommerce' );
		$I->seePluginActivated( 'wc-serial-numbers' );
	}

	public function version_test( FunctionalTester $I ) {
		$I->wantTo( 'check that the plugin version is stored in the database after activation' );
		$version = $I->grabOptionFromDatabase( 'wc_serial_numbers_version' );
		$I->seeOptionInDatabase( 'wc_serial_numbers_version', $version );
	}

	public function install_date_test( FunctionalTester $I ) {
		$I->wantTo( 'Check that the plugin install time is stored in the database after activation' );
		$time = $I->grabOptionFromDatabase( 'wc_serial_numbers_install_date' );
		$I->seeOptionInDatabase( 'wc_serial_numbers_install_date', $time );
	}

	public function database_tables_test( FunctionalTester $I ) {
		$I->wantTo( 'Check that the plugin database tables are created after activation' );
		$tables = [ 'wcsn_keys', 'wcsn_activations', 'wcsn_generators' ];
		foreach ( $tables as $table ) {
			$table_name = $I->grabPrefixedTableNameFor( $table );
			$I->seeTableInDatabase( $table_name );
		}
	}

}
