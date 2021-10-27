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
			plugin.init_select2('.wc-serial-numbers-select-product', 'wc_serial_numbers_search_products', wc_serial_numbers_admin_i10n.i18n.search_product);
			plugin.init_datepicker('.wc-serial-numbers-select-date');
			plugin.encrypt_decrypt();
		};

		plugin.init_select2 = function (el, action, placeholder) {
			placeholder = placeholder || 'Select..';
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
							page: params.page
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
		};
		plugin.init_datepicker = function (el) {
			$(el).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				firstDay: 7,
				minDate: new Date()
			});
		};


		plugin.encrypt_decrypt = function () {
			//show decrypted value
			$(document).on('click', '.wc-serial-numbers-decrypt-key', function (e) {
				e.preventDefault();
				var self = $(this);
				var id = self.data('serial-id');
				var nonce = self.data('nonce') || null;
				var td = self.closest('td');
				var code = td.find('.serial-key');
				var spinner = td.find('.serial-spinner');
				spinner.show();
				if (!code.hasClass('encrypted')) {
					code.addClass('encrypted');
					spinner.hide();
					code.text('');
					self.text(wc_serial_numbers_admin_i10n.i18n.show);
					return false;
				}
				wp.ajax.send('wc_serial_numbers_decrypt_key', {
					data: {
						serial_id: id,
						nonce: nonce
					},
					success: function (res) {
						code.text(res.key);
						spinner.hide();
						code.removeClass('encrypted');
						self.text(wc_serial_numbers_admin_i10n.i18n.hide);
					},
					error: function () {
						spinner.hide();
						code.text('');
						code.addClass('encrypted');
						self.text(wc_serial_numbers_admin_i10n.i18n.show);
						alert('Decrypting key failed');
					}
				});

				return false;
			});
		};
		plugin.init();
	};

	//$.fn
	$.fn.wc_serial_numbers_admin = function () {
		return new $.wc_serial_numbers_admin();
	};

	$.wc_serial_numbers_admin();
})(jQuery, window);
