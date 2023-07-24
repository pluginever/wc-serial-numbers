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
			.on('click', '.wcsn-key', function () {
				const $this = $(this);
				if ($this.hasClass('copying')) {
					return;
				}
				const $temp = $('<input>');
				$('body').append($temp);
				$temp.val($this.data('unmasked')).select();
				document.execCommand('copy');
				$temp.remove();
				$this.text(wc_serial_numbers_vars.i18n.copied).addClass('copying');
				setTimeout(function () {
					$this.text($this.data('masked')).removeClass('copying');
				}, 1000);
			})
			.on('mouseenter mouseleave', '.wcsn-key:not(.copying)', function (e) {
				const $this = $(this);
				// If enter show the unmasked key and leave show the masked key.
				if (e.type === 'mouseenter') {
					$this.text($this.data('unmasked'));
				} else {
					$this.text($this.data('masked'));
				}
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

		$('.wcsn_search_product, .wc-serial-numbers-select-product').select2({
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
