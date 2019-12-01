jQuery(document).ready(function () {
	'use strict';
	var productSearchField = $('.wcsn-product-select'),
		dateControlField = $('.wcsn-select-date');

	//init date field
	dateControlField.datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd',
		firstDay: 7,
		minDate: new Date()
	});

	var productSearchConfig = {
		ajax: {
			cache: true,
			delay: 500,
			url: ajaxurl,
			method: 'POST',
			dataType: 'json',
			data: function (params) {
				return {
					action: 'wcsn_product_search',
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
	};
	productSearchField.select2(productSearchConfig);

});
