/**
 * WC Serial Numbers
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */
/* global jQuery, wc_serial_numbers_vars */
( function( $, window ) {
	'use strict';

	if ( typeof wc_serial_numbers_vars === 'undefined' ) {
		return false;
	}

	var wc_serial_numbers_admin = {
		init: function() {
			this.select2( '.wc_serial_numbers_search_product', 'wc_serial_numbers_search_product' );
			$(document).on('click', '.wc-serial-numbers-decrypt-key', this.encrypt_decrypt_key);
			this.init_datepicker('.wc-serial-numbers-select-date');
		},
		select2: function( el, action ) {
			var $el = $( el );
			console.log($el);
			if ( ! $el.length ) {
				return;
			}
			$el.select2( {
				ajax: {
					cache: true,
					delay: 500,
					url: window.wc_serial_numbers_vars.ajaxurl,
					method: 'POST',
					dataType: 'json',
					data( params ) {
						return {
							action,
							nonce: window.wc_serial_numbers_vars.search_nonce,
							search: params.term,
							page: params.page,
						};
					},
				},
				placeholder: $( el ).attr( 'placeholder' ),
				minimumInputLength: 1,
				allowClear: true,
			} );
			$( document.body ).trigger( 'wc_serial_numbers_init_select2_' + action, { $el: $el } );
		},
		encrypt_decrypt_key: function( e ) {
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
				self.text(wc_serial_numbers_vars.i18n.show);
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
					self.text(wc_serial_numbers_vars.i18n.hide);
				},
				error: function () {
					spinner.hide();
					code.text('');
					code.addClass('encrypted');
					self.text(wc_serial_numbers_vars.i18n.show);
					alert('Decrypting key failed');
				}
			});
		},
		init_datepicker: function(el) {
			// If datepicker is not defined, exit.
			if ( typeof $.fn.datepicker === 'undefined' ) {
				return;
			}
			$(el).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				firstDay: 7,
				minDate: new Date()
			});
		}
	}

	// Initialize the script on document ready.
	$( document ).ready( function() {
		wc_serial_numbers_admin.init();
	} );

}( jQuery, window ) );
