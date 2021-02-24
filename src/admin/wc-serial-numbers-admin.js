/**
 * External dependencies
 */
import $ from 'jquery';

/**
 * Internal dependencies
 */
import './wc-serial-numbers-admin.scss';

$.wc_serial_numbers_admin = function () {
	const plugin = this;
	plugin.init = function () {
		plugin.init_select2(
			'.wc-serial-numbers-select-product',
			'wc_serial_numbers_search_products',
			wc_serial_numbers_admin_i10n.i18n.search_product
		);
		plugin.init_datepicker( '.wc-serial-numbers-select-date' );
		plugin.encrypt_decrypt();
	};

	plugin.init_select2 = function ( el, action, placeholder ) {
		placeholder = placeholder || 'Select..';
		$( el ).select2( {
			ajax: {
				cache: true,
				delay: 500,
				url: window.wc_serial_numbers_admin_i10n.ajaxurl,
				method: 'POST',
				dataType: 'json',
				data( params ) {
					return {
						action,
						nonce: window.wc_serial_numbers_admin_i10n.nonce,
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
			placeholder,
			minimumInputLength: 1,
			allowClear: true,
		} );
	};
	plugin.init_datepicker = function ( el ) {
		$( el ).datepicker( {
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd',
			firstDay: 7,
			minDate: new Date(),
		} );
	};

	plugin.encrypt_decrypt = function () {
		//show decrypted value
		$( document ).on(
			'click',
			'.wc-serial-numbers-decrypt-key',
			function ( e ) {
				e.preventDefault();
				const self = $( this );
				const nonce = self.data( 'nonce' ) || null;
				const td = self.closest( 'td' );
				const code = td.find( '.serial-key' );
				const spinner = td.find( '.serial-spinner' );
				spinner.show();
				if ( ! code.hasClass( 'encrypted' ) ) {
					code.addClass( 'encrypted' );
					spinner.hide();
					code.text( '' );
					self.text( wc_serial_numbers_admin_i10n.i18n.show );
					return false;
				}

				const id = self.data( 'serial-id' );
				wp.ajax.send( 'wc_serial_numbers_decrypt_key', {
					data: {
						serial_id: id,
						nonce,
					},
					success( res ) {
						code.text( res.key );
						spinner.hide();
						code.removeClass( 'encrypted' );
						self.text( wc_serial_numbers_admin_i10n.i18n.hide );
					},
					error() {
						spinner.hide();
						code.text( '' );
						code.addClass( 'encrypted' );
						self.text( wc_serial_numbers_admin_i10n.i18n.show );
						alert( 'Decrypting key failed' );
					},
				} );

				return false;
			}
		);
	};
	plugin.init();
};

//$.fn
$.fn.wc_serial_numbers_admin = function () {
	return new $.wc_serial_numbers_admin();
};

$.wc_serial_numbers_admin();
