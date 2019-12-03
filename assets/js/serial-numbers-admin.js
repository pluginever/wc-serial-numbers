jQuery(document).ready(function ($) {
	'use strict';
	var productSelectField = $('.serial-number-product-select'),
		dateControlField = $('.p-ever-select-date');

	//init date field
	dateControlField.datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd',
		firstDay: 7,
		minDate: new Date()
	});

	productSelectField.select2({
		ajax: {
			cache: true,
			delay: 500,
			url: ajaxurl,
			method: 'POST',
			dataType: 'json',
			data: function (params) {
				return {
					action: 'serial_numbers_product_search',
					nonce: WCSerialNumbers.dropDownNonce,
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
		placeholder: WCSerialNumbers.placeholderSearchProducts,
		minimumInputLength: 1,
		allowClear: true
	});

	//show decrypted value
	$(document).on('click', '.wsn-show-serial-key', function (e) {
		console.log('wsn-show-serial-key');
		e.preventDefault();
		var self = $(this);
		var id = self.data('serial-id');
		var nonce = self.data('nonce') || null;
		var td = self.closest('td');
		var code = td.find('.serial-number-key');
		var spinner = td.find('.wcsn-spinner');
		spinner.show();
		if(!code.hasClass('encrypted')){
			code.addClass('encrypted');
			spinner.hide();
			code.text('');
			self.text(WCSerialNumbers.show);
			return false;
		}

		wp.ajax.send('serial_numbers_get_decrypted_key', {
			data: {
				serial_id: id,
				nonce: nonce
			},
			success: function (res) {
				code.text(res.key);
				spinner.hide();
				code.removeClass('encrypted');
				self.text(WCSerialNumbers.hide);
			},
			error: function () {
				spinner.hide();
				code.text('');
				code.addClass('encrypted');
				self.text(WCSerialNumbers.show);
				alert('Decrypting key failed');
			}
		});

		return false;
	});


});

