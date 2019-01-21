<?php

namespace Pluginever\WCSerialNumbers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Email
{

	function __construct()
	{
		add_action("woocommerce_email_after_order_table", array($this, "add_serial_number_to_email"), 1, 1);
	}

	/**
	 * Add serial number details to the email and send it to customer.
	 *
	 * @param $order
	 *
	 * @return string|void
	 */
	function add_serial_number_to_email($order)
	{

		$order_id = $order->get_id();

		$serial_numbers = get_post_meta($order_id, 'serial_numbers', true);

		if (empty($serial_numbers) or ! wsn_check_status_to_send($order)) {

			return;

		}

		echo '<h2 style="color: #96588a;font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif;font-size: 18px;font-weight: bold;line-height: 130%;margin: 0 0 18px;text-align: left">'.__('Serial Numbers', 'wc-serial-numbers').'</h2>
			<table class="td" cellspacing="0" cellpadding="6"  style="width: 100%;margin-bottom: 20px;" border="1">
				<thead>
					<tr>
						<th class="td" scope="col" style="text-align:left;width: 20%;"><strong>' . __('Product', 'wc-serial-numbers') . '</strong></th>
						<th class="td" scope="col" style="text-align:left;width: 80%;"><strong>' . __('Serial Numbers', 'wc-serial-numbers') . '</strong></th>
					</tr>
				</thead>
				<tbody>';

		foreach ($serial_numbers as $product_id => $serial_number_id) { ?>

			<tr>
				<td class="td" scope="col" style="text-align:left;width: 20%;"><?php echo get_the_title($product_id) ?></td>
				<td class="td" scope="col" style="text-align:left;width: 80%;"><?php echo get_the_title($serial_number_id) ?></td>
			</tr>

		<?php }

		echo '</tbody></table>';

	}

}
