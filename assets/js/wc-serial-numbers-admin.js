/**
 * WC Serial Numbers
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

(function ($, window) {
	'use strict';
	$.wc_serial_numbers_admin = function () {
		var plugin = this;
		plugin.init = function () {
			plugin.init_select2('.wc-serial-numbers-select-product', 'wc_serial_numbers_search_products', wc_serial_numbers_admin_i10n.i18n.search_product)
			plugin.init_datepicker('.wc-serial-numbers-select-date')
		}

		plugin.init_select2 = function (el, action, placeholder) {
			placeholder = placeholder || 'Select..'
			$(el).select2({
				ajax: {
					cache: true,
					delay: 500,
					url: window.wc_serial_numbers_admin_i10n.ajaxurl,
					method: 'POST',
					dataType: 'json',
					data: function (params) {
						return {
							action: action,
							nonce: window.wc_serial_numbers_admin_i10n.nonce,
							search: params.term,
							page: params.page,
						};
					},
					processResults: function (data, params) {
						params.page = params.page || 1;
						return {
							results: data.results,
							pagination: {
								more: data.pagination.more
							}
						};
					}
				},
				placeholder: placeholder,
				minimumInputLength: 1,
				allowClear: true
			});
		}
		plugin.init_datepicker = function (el) {
			$(el).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				firstDay: 7,
				minDate: new Date()
			});
		}
		plugin.init();
	}

	//$.fn
	$.fn.wc_serial_numbers_admin = function () {
		return new $.wc_serial_numbers_admin();
	};

	$.wc_serial_numbers_admin();
})(jQuery, window);
