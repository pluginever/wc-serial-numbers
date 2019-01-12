/**
 * WC Serial Numbers Admin
 * https://www.pluginever.com
 *
 * Copyright (c) 2018 pluginever
 * Licensed under the GPLv2+ license.
 */

/*jslint browser: true */
/*global jQuery:false */


window.Project = (function (window, document, $, undefined) {
	'use strict';
	var app = {
		init: function () {
			$(document).on('click', '#enable_serial_number', app.enable_serial_number);
			$(document).on('click', '.woocommerce_options_panel .add-serial-number-manually', app.add_tab_serial_number);
			$(document).on('click', '.woocommerce_options_panel .add-serial-title', app.tab_add_serial_number_toggle);

			$('.ever-serial_numbers_tab').on('click', app.load_tab_data);
			$(document).on('click', '#image_license_upload', app.upload_license_upload);
			$(document).on('click', '#image_license_remove', app.remove_license_upload);

			$(document).on('change', '#product', app.load_variations);
			$(document).on('change', '.ever-panel .validity_type', app.change_validity_type);
			$(document).on('click', '.wsn_generate_btn', app.generate_numbers);

			$('.ever-select').select2();
			$('.ever-date, .ever-panel #validity[type=text]').datepicker();

		},

		add_tab_serial_number: function (e) {
			e.preventDefault();

			wp.ajax.send('add_serial_number', {
				data: {
					product: $('#post_ID').val(),
					serial_number: $('.ever-panel #serial_number').val(),
					image_license: $('.ever-panel #image_license').val(),
					deliver_times: $('.ever-panel #deliver_times').val(),
					max_instance: $('.ever-panel #max_instance').val(),
					expires_on: $('.ever-panel #expires_on').val(),
					validity: $('.ever-panel #validity').val()
				},

				success: function (response) {
					if (response.html) {
						$('.ever-content-placeholder').html(response.html);
					} else if (response.empty_serial) {
						$('.wsn-message').html(response.empty_serial);
					}
				},
				error: function (error) {
					console.log(error);
				}

			});
		},

		enable_serial_number: function () {

			$('.wsn-serial-number-tab').addClass('ever-spinner');
			var enable_serial_number = '';
			var msg = '';
			if ($(this).is(':checked')) {
				enable_serial_number = 'enable';
				msg = wpwsn.i18n.serial_number_activated;
			} else {
				enable_serial_number = 'disable';
				msg = wpwsn.i18n.serial_number_deactivated;

			}


			$('.ever-content-placeholder').html('<div class="notice notice-success is-dismissible"><p><strong>' + msg + '</strong></p></div>');

			wp.ajax.send('enable_serial_number', {
				data: {
					enable_serial_number: enable_serial_number,
					post_id: $('#post_ID').val()
				},
				success: function (response) {
					$('.wsn-serial-number-tab').removeClass('ever-spinner');
					if (response.html) {
						$('.ever-content-placeholder').html(response.html);
						$('.wsn-body>input[type=hidden]').remove();
						$('.ever-panel>input[type=hidden]').remove();
					}
				}
			});


		},

		load_tab_data: function () {

			$('.wsn-serial-number-tab').addClass('ever-spinner');
			wp.ajax.send('load_tab_data', {
				data: {
					post_id: $('#post_ID').val()
				},
				success: function (response) {
					$('.wsn-serial-number-tab').removeClass('ever-spinner');

					if (response.html) {
						$('.ever-content-placeholder').html(response.html);
						$('.wsn-body>input[type=hidden]').remove();
						$('.ever-panel>input[type=hidden]').remove();
					}
				},
				error: function (error) {
					console.log(error);
				}
			});

		},

		tab_add_serial_number_toggle: function (e) {
			e.preventDefault();

			$('.ever-panel').toggle();
			$('.ever-select').select2();
			$('.ever-date').datepicker();
		},

		upload_license_upload: function (e) {
			e.preventDefault();
			var image = wp.media({
				title: 'Upload Image',
				multiple: false
			}).open()
				.on('select', function () {
					// This will return the selected image from the Media Uploader, the result is an object
					var uploaded_image = image.state().get('selection').first();

					// We convert uploaded_image to a JSON object to make accessing it easier
					// Output to the console uploaded_image
					var image_url = uploaded_image.toJSON().url;

					// Let's assign the url value to the input field
					$('.image_license_prev').attr('src', image_url);
					$('input[name="image_license"]').val(image_url);
					$('#image_license_remove').removeClass('hidden');
				});
		},

		remove_license_upload: function (e) {
			e.preventDefault();
			$('.image_license_prev').attr('src', '');
			$('input[name="image_license"]').val('');
			$(this).addClass('hidden');
		},

		load_variations: function (e) {
			e.preventDefault();

			$('.ever-spinner-product').css('visibility', 'visible');

			wp.ajax.send('load_variations', {
				data: {
					post_id: $(this).val()
				},
				success: function (response) {
					console.log(response);
					$('#variation').html(response.html);
					$('.ever-spinner-product').css('visibility', 'hidden');

				},
				error: function (error) {
					console.log(error);
				}
			});

		},

		change_validity_type: function () {

			var value = $(this).val();
			var validity = $('.ever-panel #validity');

			if (value === 'days') {

				validity.attr('type', 'number');
				validity.datepicker('destroy');

			} else if (value === 'date') {

				validity.attr('type', 'text');

				if ($('.ever-serial_numbers_tab').length > 0) {
					validity.attr('placeholder', 'dd-mm-yyyy');
					return;
				}

				validity.datepicker({
					dateFormat: 'dd-mm-yy'
				});

			}


		},

		generate_numbers: function (e) {

			e.preventDefault();

			var $limit = $(this).prev().val();
			var $rule_id = $(this).data('rule_id');

			var $confirm = window.confirm(wpwsn.i18n.generate_confirm + $limit + ' numbers?');

			if (!$confirm) {
				return;
			}

			wp.ajax.send('wsn_generate_numbers', {

				data: {
					limit: $limit,
					rule_id: $rule_id
				},

				success: function (response) {
					if (response.response) {

						window.alert($limit + wpwsn.i18n.generate_success);

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
