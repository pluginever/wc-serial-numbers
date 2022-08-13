<?php

class OrderCest {

	public function _before( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->amOnPluginsPage();
	}
}
