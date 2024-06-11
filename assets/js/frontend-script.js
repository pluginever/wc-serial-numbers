(function ($) {
	'use strict';
	$(document).ready(function () {
		$(document)
			.on('submit', '.wcsn-api-form', function (e) {
				e.preventDefault();
				const $form = $(this);
				const $submit = $form.find('input[type="submit"]');
				// bail if the form is already loading.
				if ($form.hasClass('loading')) {
					return false;
				}
				$.ajax({
					url: wc_serial_numbers_frontend_vars.ajax_url,
					method: 'POST',
					data: $form.serialize(),
					dataType: 'json',
					beforeSend() {
						$form.addClass('loading');
						$submit.attr('data-label', $submit.val()).attr('disabled', 'disabled').val(wc_serial_numbers_frontend_vars.i18n.loading);
					},
					complete(response) {
						// get response data.
						const json = response.responseJSON;
						//If there is a message, display it.
						if (json && json.message) {
							window.alert(json.message);
						}
						$submit.removeAttr('disabled').val($submit.attr('data-label'));
						$form.removeClass('loading');
					}
				});
			});
	});
})(jQuery);
