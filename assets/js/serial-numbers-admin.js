/**
 * WC Serial Numbers
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

(function ($, window) {
	'use strict';
	$.wc_serial_numbers = function () {
		var plugin = this;
		plugin.vars = {};
		plugin.vars.$product_field = $('.wcsn-select-product');
		plugin.vars.$order_field = $('.wcsn-select-order');
		plugin.vars.$customer_field = $('.wcsn-select-customer');
		plugin.vars.$date_field = $('.wcsn-date-picker');
		plugin.vars.$numeric_field = $('.wcsn-numeric-field');
		plugin.vars.$serial_key_field = $('.wcsn-serial-key');

		plugin.init = function () {
			console.log(plugin.vars.$product_field)
			plugin.init_select2(
				plugin.vars.$product_field,
				'wc_serial_numbers_search_products',
				WCSerialNumbers.search_product_placeholder
			);

			plugin.init_datepicker(
				plugin.vars.$date_field
			);

			plugin.encrypt_decrypt();
		}

		plugin.init_select2 = function ($el, action, placeholder) {
			$el.select2({
				ajax: {
					cache: true,
					delay: 500,
					url: window.WCSerialNumbers.ajaxurl,
					method: 'POST',
					dataType: 'json',
					data: function (params) {
						return {
							action: action,
							nonce: window.WCSerialNumbers.search_nonce,
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

		plugin.init_datepicker = function ($el) {
			$el.datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				firstDay: 7,
				minDate: new Date()
			});
		}

		plugin.encrypt_decrypt = function(){
			//show decrypted value
			$(document).on('click', '.wcsn-show-serial-key', function (e) {
				e.preventDefault();
				var self = $(this);
				var id = self.data('serial-id');
				var nonce = self.data('nonce') || null;
				var td = self.closest('td');
				var code = td.find('.wcsn-serial-key');
				var spinner = td.find('.wcsn-spinner');
				spinner.show();
				if(!code.hasClass('encrypted')){
					code.addClass('encrypted');
					spinner.hide();
					code.text('');
					self.text(WCSerialNumbers.i18n.show);
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
						self.text(WCSerialNumbers.i18n.hide);
					},
					error: function () {
						spinner.hide();
						code.text('');
						code.addClass('encrypted');
						self.text(WCSerialNumbers.i18n.show);
						alert('Decrypting key failed');
					}
				});

				return false;
			});
		}

		plugin.init();
	}

	//$.fn
	$.fn.wc_serial_numbers = function () {
		return new $.wc_serial_numbers();
	};

	$.wc_serial_numbers();
})(jQuery, window);
