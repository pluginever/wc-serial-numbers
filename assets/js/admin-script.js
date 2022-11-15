/* global jQuery, wc_serial_numbers_vars */
( function( $, window ) {
	'use strict';

	if ( typeof wc_serial_numbers_vars === 'undefined' ) {
		return false;
	}

	var wc_serial_numbers_admin = {
		init: function() {
			this.select2( '.wcsn_search_order', 'wc_serial_numbers_search_order' );
			this.select2( '.wcsn_search_product', 'wc_serial_numbers_search_product' );
			this.select2( '.wcsn_search_customer', 'wc_serial_numbers_search_customer' );
			this.copy( '.wcsn_copy_text' );

			// Handle generate keys form.
			$( '#wcsn-generate-keys-form' ).on( 'submit', function( e ) {
				e.preventDefault();
				var $form = $( this ),
					data = wc_serial_numbers_admin.serialize( $form ),
					$button = $form.find( 'input[type = "submit"]' ),
					$inputs = $form.find( '#product_id', '#quantity' );

				$button.attr( 'disabled', 'disabled' );
				$inputs.attr( 'disabled', 'disabled' );

				wc_serial_numbers_admin.post( data, function( response ) {
					$button.removeAttr( 'disabled' );
					$inputs.removeAttr( 'disabled' );
					if ( response.data && response.data.message ) {
						window.alert( response.data.message );
					}
				} );
			} );

			// Handle key form.
			var $status_field = $( '#status', '#wcsn-key-form' );
			console.log($status_field);
			$status_field.on( 'change', function( e ) {
				e.preventDefault();
				var $field = $( this );
				var status = $field.val();
				var $form = $field.closest( 'form' );
				var $order = $form.find( '#order_id' );
				if ( status === 'sold' ) {
					$order.removeAttr( 'disabled' );
					$order.attr( 'required', 'required' );
				} else {
					$order.attr( 'disabled', 'disabled' );
					$order.removeAttr( 'required' );
				}
			} );
			if ( $status_field.length ) {
				$status_field.trigger( 'change' );
			}
		}, select2: function( el, action ) {
			var $el = $( el );
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
							nonce: window.wc_serial_numbers_vars.nonce,
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
		}, serialize: function( $form ) {
			var data = $form.serializeArray();

			if ( data.length <= 0 ) {
				return false;
			}

			var data_object = {};

			$.each( data, function( index, field ) {
				data_object[ field.name ] = field.value;
			} );

			return data_object;
		}, post: function( data, callback ) {
			$.post( wc_serial_numbers_vars.ajaxurl, data, function( response ) {
				if ( ! response.success ) {
					response.data.message = typeof response.data.message !== 'undefined' ? response.data.message : 'There was an issue with the AJAX request.';
					window.console.log( 'Error', response.data.message );
				}

				if ( typeof callback === 'function' ) {
					callback( response );
				}
			} );
		},
		copy: function( $el ) {
			$( $el ).on( 'click', function( e ) {
				e.preventDefault();
				var $this = $( this );
				var text = $( this ).text();
				if ( true === document.queryCommandSupported( 'copy' ) ) {
					var temp = document.createElement( 'textarea' );
					temp.value = text;
					document.body.appendChild( temp );
					temp.select();
					try {
						const successful = document.execCommand( 'copy' );
						const msg = successful ? 'Copied !!! ' : 'Could not copy!!!';
						$this.text( msg );
						$this.addClass( 'copied' );
					} catch ( err ) {
						console.log( 'Something went wrong' );
					}
					document.body.removeChild( temp );
				} else {
					window.prompt( 'Copy to clipboard: Ctrl+C or Command+C, Enter', text );
				}

				setTimeout( () => {
					$this.text( text );
					$this.removeClass( 'copied' );
				}, 1200 );

				// const selection = window.getSelection();
				// const range = document.createRange();
				// range.selectNodeContents( $el.get( 0 ) );
				// selection.removeAllRanges();
				// selection.addRange( range );
				// console.log(selection);
				// console.log(range);
				// //
				// try {
				// 	document.execCommand( 'copy' );
				// 	selection.removeAllRanges();
				//
				// 	const mailId = $el.textContent;
				// 	$el.textContent = 'Copied!';
				// 	$el.classList.add( 'success' );
				//
				// 	setTimeout( () => {
				// 		$el.textContent = mailId;
				// 		$el.classList.remove( 'success' );
				// 	}, 1000 );
				// } catch ( e ) {
				// 	$el.textContent = 'Couldn\'t copy, hit Ctrl+C!';
				// 	$el.classList.add( 'error' );
				//
				// 	setTimeout( () => {
				// 		errorMsg.classList.remove( 'show' );
				// 	}, 1200 );
				// }
			} );
		},

	};

	wc_serial_numbers_admin.init();
}( jQuery, window ) );
