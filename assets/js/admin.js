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
			$(document).on('click','.woocommerce_options_panel .add-serial-title', app.tab_add_serial_number_toggle);
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
			$('.ever-content-placeholder').html('');
			$('.wsn-serial-number-tab').addClass('ever-spinner');
			var enable_serial_number = '';
			var msg = '';
			if ($(this).is(':checked')) {
				enable_serial_number = 'enable';
				msg = 'Serial Number Activated.';
			} else {
				enable_serial_number = 'disable';
				msg = 'Serial Number Dectivated.';

			}


			$('.wsn_nottification').html('<div class="notice notice-success is-dismissible"> \n' +
				'\t<p><strong>'+msg+'</strong></p>\n' +
				'</div>');

			wp.ajax.send('enable_serial_number', {
				data: {
					enable_serial_number: enable_serial_number,
					post_id: $('#post_ID').val(),
				},
				success: function (response) {
					$('.wsn-serial-number-tab').removeClass('ever-spinner');
					if (response.html) {
						$('.ever-content-placeholder').html(response.html);
					}
				}
			});


		},

		load_tab_data: function () {
			$('.wsn-serial-number-tab').addClass('ever-spinner');
			wp.ajax.send('load_tab_data', {
				data: {
					post_id: $('#post_ID').val(),
				},
				success: function (response) {
					$('.wsn-serial-number-tab').removeClass('ever-spinner');
					console.log(response);
					if (response.html) {
						$('.ever-content-placeholder').html(response.html);
					}
				},
				error: function (error) {
					console.log(error);
				}
			});
		},

		tab_add_serial_number_toggle: function(e){
			e.preventDefault();
			$('.ever-panel').toggle();
			$('.ever-select').select2();
			$('.ever-date').datepicker();
		}
	};

	$(document).ready(app.init);

	return app;

})(window, document, jQuery);
