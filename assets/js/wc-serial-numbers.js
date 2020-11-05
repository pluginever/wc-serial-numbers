/**
 * WC Serial Numbers
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

(function ($) {
	'use strict';
	//$.fn
	$.wc_serial_numbers = function () {
		var plugin = this;
		plugin.init = function () {
			if (jQuery) {
				$('.wc-serial-numbers-order-items td.serial_key').append('<button class="copy_btn" style="float: right">Copy</button>');
			}
			plugin.copy_serial_numbers();
		};
		plugin.copy_serial_numbers = function () {
			$('body').on('click', '.copy_btn', function () {
				var $temp = $('<input>');
				$('body').append($temp);
				var serial = $(this).parents('.wc-serial-numbers-order-items tr').find('td.serial_key span').text();
				$temp.val(serial).select();
				document.execCommand('copy');
				alert('Serial Number Copied to Clipboard:  ' + serial);
				$temp.remove();
			});
		};
		plugin.init();
	};


	$.fn.wc_serial_numbers = function () {
		return new $.wc_serial_numbers();
	};

	$.wc_serial_numbers();
})(jQuery, window);
