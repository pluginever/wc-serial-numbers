/**
 * WC Serial Numbers
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */
/* global jQuery, wc_serial_numbers_vars */
(function ($, window) {
	'use strict';

	if (typeof wc_serial_numbers_vars === 'undefined') {
		return false;
	}

	$(document).ready(function () {
		$(document)
			.on('click', '.wcsn_unmask_key', function (e) {
				e.preventDefault();
				const self = $(this);
				const key = self.data('key');
				const $key = self.closest('td').find('.wcsn-key');
				console.log($key)
				if ($key.hasClass('masked')) {
					$key.text(key).removeClass('masked');
					self.text(wc_serial_numbers_vars.i18n.hide);
				} else {
					$key.text('').addClass('masked');
					self.text(wc_serial_numbers_vars.i18n.show);
				}
			})
			.on('click', '.wcsn-key:not(.masked), .wcsn-key-copy', function () {
				const $this = $(this);
				if ($this.hasClass('copying')) {
					return;
				}
				const $temp = $('<input>');
				$('body').append($temp);
				$temp.val($this.text()).select();
				document.execCommand('copy');
				$temp.remove();
				$this.attr('data-key', $this.text());
				$this.text(wc_serial_numbers_vars.i18n.copied).addClass('copying');
				setTimeout(function () {
					$this.text($this.data('key')).removeClass('copying');
				}, 1000);
			})
			.on('submit', '.wcsn-api-form', function (e) {
				e.preventDefault();
				const $form = $(this);
				$.ajax({
					url: wc_serial_numbers_vars.apiurl,
					method: 'POST',
					data: $form.serialize(),
					dataType: 'json',
					beforeSend() {
						$form.addClass('loading');
						$form.find('.wcsn-api-response').text('Loading...');
					},
					success(response) {
						$form.find('.wcsn-api-response').text(JSON.stringify(response, null, 2));
					},
					error: function (response) {
						$form.find('.wcsn-api-response').text(JSON.stringify(response, null, 2));
					},
					always() {
						$form.removeClass('loading');
					}
				});
			});

		$('.wcsn_search_product').select2({
			ajax: {
				cache: true,
				delay: 500,
				url: wc_serial_numbers_vars.ajaxurl,
				method: 'POST',
				dataType: 'json',
				data(params) {
					return {
						action: 'wc_serial_numbers_search_product',
						nonce: wc_serial_numbers_vars.search_nonce,
						search: params.term,
						page: params.page || 1,
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
			placeholder: wc_serial_numbers_vars.i18n.search_product,
			minimumInputLength: 1,
			allowClear: true,
		});
		$('.wcsn_search_order').select2({
			ajax: {
				cache: true,
				delay: 500,
				url: wc_serial_numbers_vars.ajaxurl,
				method: 'POST',
				dataType: 'json',
				data(params) {
					return {
						action: 'wc_serial_numbers_search_orders',
						nonce: wc_serial_numbers_vars.search_nonce,
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
			placeholder: wc_serial_numbers_vars.i18n.search_order,
			minimumInputLength: 1,
			allowClear: true,
		});
		$('.wcsn_search_customer').select2({
			ajax: {
				cache: true,
				delay: 500,
				url: wc_serial_numbers_vars.ajaxurl,
				method: 'POST',
				dataType: 'json',
				data(params) {
					return {
						action: 'wc_serial_numbers_search_customers',
						nonce: wc_serial_numbers_vars.search_nonce,
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
			placeholder: wc_serial_numbers_vars.i18n.search_customer,
			minimumInputLength: 1,
			allowClear: true,
		});
		if (typeof $.fn.datepicker !== 'undefined') {
			$('.wc-serial-numbers-select-date').datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				firstDay: 7,
				minDate: new Date()
			});
		}
	});
}(jQuery, window));
