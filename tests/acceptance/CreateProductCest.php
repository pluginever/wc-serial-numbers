<?php

class CreateProductCest {
	public function _before( AcceptanceTester $I ) {
		$I->cli('option get admin_email');
	}

	// tests
	public function tryToTest( AcceptanceTester $I ) {
	}
}
