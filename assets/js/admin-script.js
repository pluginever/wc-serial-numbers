/*global jQuery */
jQuery( function ( $ ) {
	// Only display Tab if serial numbers' checkbox is checked
	$( 'input#_is_serial_numbers' )
		.on( 'change', function () {
			const $dependents = $( '.show_if_serial_numbers' );
			$dependents.hide();
			const is_checked = $( this ).is( ':checked' );
			if ( is_checked ) {
				$dependents.show();
			} else {
				$dependents.hide();
				if ( $( '.wc-serial-numbers-tab' ).is( '.active' ) ) {
					$( 'ul.product_data_tabs li:visible' )
						.eq( 0 )
						.find( 'a' )
						.click();
				}
			}
		} )
		.change();

	// Hide all source dependent components
	$( '#_serial_numbers_key_source' )
		.on( 'change', function () {
			$( "[class*='show_if_serial_numbers_key_source_is_']" ).hide();
			const source = $( '#_serial_numbers_key_source' ).val();
			if ( source ) {
				$( '.show_if_serial_numbers_key_source_is_' + source ).show();
			}
		} )
		.change();

	// Datepicker for API tab
	jQuery( '#_serial_numbers_software_last_updated' ).datepicker( {
		dateFormat: 'yy-mm-dd',
		numberOfMonths: 1,
		showButtonPanel: true,
	} );

	// Tooltips
	jQuery( '.tips, .help_tip' ).tipTip( {
		attribute: 'data-tip',
		fadeIn: 50,
		fadeOut: 50,
		delay: 200,
	} );
} );
