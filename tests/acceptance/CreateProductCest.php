<?php

class CreateProductCest {
	public function _before( AcceptanceTester $I ) {
		$I->cli('option get admin_email');
		$I->loginAsAdmin();
	}

	// tests
	public function tryToTest( AcceptanceTester $I ) {
	}
}
