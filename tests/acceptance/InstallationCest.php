<?php

class InstallationCest {

	public function _before( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$I->seePluginDeactivated( 'woocommerce' );
		$I->seePluginDeactivated( 'wc-serial-numbers' );
	}


	public function woocommere_plugin_dependency_test( AcceptanceTester $I ) {
		$I->wantToTest('Plugin will not activate if woocommerce plugin is not activated');
		$I->activatePlugin( 'wc-serial-numbers' );
		$I->seePluginDeactivated( 'wc-serial-numbers' );
		$I->see( 'WooCommerce Serial Numbers requires WooCommerce to be installed and active. You can download WooCommerce from here.' );

		$I->activatePlugin('woocommerce');
		$I->activatePlugin( 'wc-serial-numbers' );
		$I->seePluginActivated( 'woocommerce' );
		$I->seePluginActivated( 'wc-serial-numbers' );
	}


}
