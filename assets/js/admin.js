/**
 * WC Serial Numbers Admin
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

/*jslint browser: true */
/*global jQuery:false */
/*global wc_serial_numbers:false */

window.Project = (function (window, document, $, undefined) {
	'use strict';
	var app = {
		init: function () {
			$('#add-serial-number').on('click', app.add_serial_number);
		},

		add_serial_number: function (e) {
			e.preventDefault();
			wp.ajax.send('add_serial_number', {
				data: {
					serial_number: $('#serial_number').val(),
					product: $('#product').val(),
					usage_limit: $('#usage_limit').val(),
					expires_on: $('#expires_on').val()
				},
				success: function (response) {
					console.log(response);
					$('#tab-table-serial-numbers').html(response.posts);
					alert('Yeahoo!, We are success!');
				},
				error: function (error) {
					console.log(error);
					alert('Ohshit... We are in error');
				}
			});
		}
	};

	$(document).ready(app.init);

	return app;

})(window, document, jQuery);
