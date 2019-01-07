<?php
namespace Pluginever\WCSerialNumbers;

class Email {

	function __construct()
	{
		add_action("woocommerce_email_after_order_table", array($this, "add_serial_number_to_email"), 1, 1);
	}

	/**
	 * Add serial number details to the email and send it to customer.
	 * @param $order
	 */
	function add_serial_number_to_email($order){
		
	}

}
