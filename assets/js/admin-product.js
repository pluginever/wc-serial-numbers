/**
 * WC Serial Numbers
 * https://pluginever.com
 *
 * Copyright (c) 2014 PluginEver
 * Licensed under the GPLv2+ license.
 */
jQuery( function ( $ ) {
	/********** ADMIN:PRODUCT EDIT **********/
	$(document.body)
		.on('change', ':input[name="_serial_key_source"]', function(){
			var source = $(this).val();
			$('*[class*="wcsn_show_if_key_source__"]').hide();
			$('.wcsn_show_if_key_source__' + source).show();
		});

	// Trigger change to show/hide fields on load.
	$(':input[name="_serial_key_source"]').trigger('change');
});
