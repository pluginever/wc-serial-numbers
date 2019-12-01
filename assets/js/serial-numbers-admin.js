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

});
