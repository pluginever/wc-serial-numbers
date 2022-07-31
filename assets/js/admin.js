/*global jQuery, wc_serial_numbers_admin_i10n */

( function ( $, i10n ) {
	$( '.serial-numbers-product-search' ).select2( {
		ajax: {
			cache: true,
			delay: 500,
			url: i10n.ajaxurl,
			method: 'POST',
			dataType: 'json',
			data( params ) {
				return {
					action: 'wc_serial_numbers_search_products',
					nonce: i10n.nonce,
					search: params.term,
					page: params.page,
				};
			},
			processResults( data, params ) {
				params.page = params.page || 1;
				return {
					results: data.results,
					pagination: {
						more: data.pagination.more,
					},
				};
			},
		},
		placeholder: i10n.i18n.search_product,
		minimumInputLength: 1,
		allowClear: true,
	} );

	$( '.serial-numbers-order-search' ).select2( {
		ajax: {
			cache: true,
			delay: 500,
			url: i10n.ajaxurl,
			method: 'POST',
			dataType: 'json',
			data( params ) {
				return {
					action: 'wc_serial_numbers_search_orders',
					nonce: i10n.nonce,
					search: params.term,
					page: params.page,
				};
			},
			processResults( data, params ) {
				params.page = params.page || 1;
				return {
					results: data.results,
					pagination: {
						more: data.pagination.more,
					},
				};
			},
		},
		placeholder: i10n.i18n.search_order,
		minimumInputLength: 1,
		allowClear: true,
	} );
} )( jQuery, wc_serial_numbers_admin_i10n );
