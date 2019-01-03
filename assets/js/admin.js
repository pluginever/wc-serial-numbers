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
			$(document).on('click','#enable_serial_number', app.enable_serial_number);
			$('.ever-select').select2();
			$('.ever-date').datepicker();
			$('.ever-serial_numbers_tab').on('click', app.load_tab_data);
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
					if (response.posts) {
						$('#tab-table-serial-numbers tbody').html(response.posts);
					} else if (response.empty_serial === true) {
						$('.wsn-add-serial-number-notification').html('<p class="error-message">Please enter a valid serial number</p>');
						//$('.wsn-serial-number-form-group').addClass('form-invalid');
					}
				},
				error: function (error) {
					console.log(error);
				}
			});
		},

		enable_serial_number: function () {
			var enable_serial_number = '';
			var msg = '';
			if ($(this).is(':checked')) {
				enable_serial_number = true;
				msg = 'Serial Number Activated.';
			} else {
				enable_serial_number = false;
				msg = 'Serial Number Dectivated.';

			}
			wp.ajax.send('enable_serial_number', {
				data: {
					product: $('#product').val(),
					enable_serial_number: enable_serial_number,
				},
				success: function (response) {
					console.log(response)
				}
			});

			$('.wsn_nottification').html('<div class="notice notice-success is-dismissible"> \n' +
				'\t<p><strong>'+msg+'</strong></p>\n' +
				'</div>');
		},

		load_tab_data: function () {
			wp.ajax.send('load_tab_data', {
				data: {
					post_id: $('#post_ID').val(),
				},
				success: function (response) {
					console.log(response);
					if (response.html) {
						$('.ever-content-placeholder').html(response.html);
					}
				},
				error: function (error) {
					console.log(error);
				}
			});
		}
	};

	$(document).ready(app.init);

	return app;

})(window, document, jQuery);
