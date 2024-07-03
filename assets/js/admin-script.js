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
					if (false === data.success){
						alert(data.data.message);
					}
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
					if (false === data.success){
						alert(data.data.message);
					}
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
					if (false === data.success){
						alert(data.data.message);
					}
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
		if (typeof typeof $.fn.select2 !== 'undefined') {
			$(':input.wcsn-select2').filter(':not(.enhanced)').each(function () {
				var $element = $(this);
				var select2_args = {
					allowClear: $element.data('allow_clear') && !$element.prop('multiple') || true,
					placeholder: $element.data('placeholder') || $element.attr('placeholder') || '',
					minimumInputLength: $element.data('minimum_input_length') ? $element.data('minimum_input_length') : 0,
					ajax: {
						url: wc_serial_numbers_vars.ajaxurl,
						dataType: 'json',
						delay: 250,
						method: 'POST',
						data: function (params) {
							return {
								term: params.term,
								action: $element.data('action'),
								type: $element.data('type'),
								_wpnonce: $element.data('nonce')||wc_serial_numbers_vars.ajax_nonce,
								exclude: $element.data('exclude'),
								include: $element.data('include'),
								limit: $element.data('limit'),
								page: params.page || 1,
							};
						},
						processResults: function (data) {
							data.page = data.page || 1;
							return data;
						},
						cache: true
					}
				}

				// if data action is set then use ajax.
				if (!$element.data('action')) {
					delete select2_args.ajax;
				}

				$element.select2(select2_args).addClass('enhanced');
			});
		}

		// Add key form.
		$('#wcsn-add-key-form :input[name="status"]').on('change', function () {
			var $this = $(this);
			var $form = $this.closest('form');
			var $customer = $form.find(':input[name="customer_id"]');
			var $order = $form.find(':input[name="order_id"]');
			var value = $(this).is(':checked') ? $(this).val() : '';
			if (!value) {
				return false;
			}

			if ($this.val() === 'create_order') {
				$customer.prop('required', true).closest('tr').show();
				$order.prop('required', false).closest('tr').hide();
			} else if ($this.val() === 'existing_order') {
				$customer.prop('required', false).closest('tr').hide();
				$order.prop('required', true).closest('tr').show();
			}else {
				$customer.prop('required', false).closest('tr').hide();
				$order.prop('required', false).closest('tr').hide();
			}
		}).trigger('change');
	});

}(jQuery, window));
