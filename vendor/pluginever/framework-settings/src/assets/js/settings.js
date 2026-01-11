/**
 * PluginEver Settings JS
 * https://www.pluginever.com
 *
 * Copyright (c) 2024 pluginever
 * Licensed under the GPLv2+ license.
 */

(function( $ ) {
	// trigger change event on load.
	jQuery(document).ready(function ($) {
		document.querySelectorAll('[data-cond-id]').forEach(function (element) {
			var $this = element;
			var conditional_id = $this.getAttribute('data-cond-id');
			var conditional_value = $this.getAttribute('data-cond-value') || '';
			var conditional_operator = $this.getAttribute('data-cond-operator') || '==';
			var $conditional_field = document.getElementById(conditional_id);
			$conditional_field.addEventListener('change', function () {
				var value = this.value.trim();
				if (this.type === 'checkbox' || this.type === 'radio') {
					conditional_operator = 'checked';
				}

				var show = false;
				if (conditional_operator === '==') {
					show = value == conditional_value ? true : false; // eslint-disable-line eqeqeq
				} else if (conditional_operator === '!=') {
					show = value != conditional_value; // eslint-disable-line eqeqeq
				} else if (conditional_operator === 'contains') {
					show = value.indexOf(conditional_value) > -1;
				} else if (conditional_operator === 'checked') {
					show = this.checked;
				} else {
					show = false;
				}

				if (show) {
					$this.closest('tr').style.display = 'table-row';
				} else {
					$this.closest('tr').style.display = 'none';
				}
			});

			$conditional_field.dispatchEvent(new Event('change'));
		});
		// check if iris is loaded.
		if (typeof $.fn.iris !== 'undefined') {
			// Color picker.
			$('.colorpick')
				.iris({
					change: function (event, ui) {
						$(this)
							.parent()
							.find('.colorpickpreview')
							.css({backgroundColor: ui.color.toString()});
					},
					hide: true,
					border: true,
				})
				.on('click focus', function (event) {
					event.stopPropagation();
					$('.iris-picker').hide();
					$(this).closest('td').find('.iris-picker').show();
					$(this).data('originalValue', $(this).val());
				})
				.on('change', function () {
					if ($(this).is('.iris-error')) {
						var original_value = $(this).data('originalValue');
						if (
							original_value.match(
								/^\#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/
							)
						) {
							$(this)
								.val($(this).data('originalValue'))
								.trigger('change');
						} else {
							$(this).val('').trigger('change');
						}
					}
				});

			$('body').on('click', function () {
				$('.iris-picker').hide();
			});
		}
	});
}( jQuery ));
