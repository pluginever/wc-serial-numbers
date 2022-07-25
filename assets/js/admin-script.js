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
		const plugin = this;
		plugin.init = function () {
			plugin.init_select2(
				'.wc-serial-numbers-select-product',
				'wc_serial_numbers_search_products',
				// eslint-disable-next-line camelcase,no-undef
				wc_serial_numbers_admin_i10n.i18n.search_product
			);
			plugin.init_datepicker('.wc-serial-numbers-select-date');
			plugin.encrypt_decrypt();
			plugin.control_keysource_view();
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
					data(params) {
						return {
							action,
							nonce: window.wc_serial_numbers_admin_i10n.nonce,
							search: params.term,
							page: params.page,
						};
					},
					processResults(data, params) {
						params.page = params.page || 1;
						return {
							results: data.results,
							pagination: {
								more: data.pagination.more,
							},
						};
					},
				},
				placeholder,
				minimumInputLength: 1,
				allowClear: true,
			});
		};
		plugin.init_datepicker = function (el) {
			$(el).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				firstDay: 7,
				minDate: new Date(),
			});
		};

		plugin.encrypt_decrypt = function () {
			//show decrypted value
			$(document).on(
				'click',
				'.wc-serial-numbers-decrypt-key',
				function (e) {
					e.preventDefault();
					const self = $(this);
					// eslint-disable-next-line @wordpress/no-unused-vars-before-return
					const id = self.data('serial-id');
					const nonce = self.data('nonce') || null;
					const td = self.closest('td');
					const code = td.find('.serial-key');
					const spinner = td.find('.serial-spinner');
					spinner.show();
					if (!code.hasClass('encrypted')) {
						code.addClass('encrypted');
						spinner.hide();
						code.text('');
						// eslint-disable-next-line no-undef,camelcase
						self.text(wc_serial_numbers_admin_i10n.i18n.show);
						return false;
					}
					wp.ajax.send('wc_serial_numbers_decrypt_key', {
						data: {
							serial_id: id,
							nonce,
						},
						success(res) {
							code.text(res.key);
							spinner.hide();
							code.removeClass('encrypted');
							// eslint-disable-next-line no-undef,camelcase
							self.text(wc_serial_numbers_admin_i10n.i18n.hide);
						},
						error() {
							spinner.hide();
							code.text('');
							code.addClass('encrypted');
							// eslint-disable-next-line no-undef,camelcase
							self.text(wc_serial_numbers_admin_i10n.i18n.show);
							// eslint-disable-next-line no-alert,no-undef
							alert('Decrypting key failed');
						},
					});

					return false;
				}
			);
		};

		plugin.control_keysource_view = function () {
			$(document).on('change', '.serial_key_source', function () {
				$('.serial_key_source').each(function () {
					const source = $(this).val();

					$(this)
						.closest('div')
						.find('.wc-serial-numbers-key-source-settings')
						.each(function () {
							const dataSource = $(this).data('source');
							if (dataSource === source) {
								$(this).show();
							} else {
								$(this).hide();
							}
						});
				});
			});
		};
		plugin.init();
	};

	//$.fn
	$.fn.wc_serial_numbers_admin = function () {
		return new $.wc_serial_numbers_admin();
	};

	$.wc_serial_numbers_admin();
	// eslint-disable-next-line no-undef
})(jQuery, window);
