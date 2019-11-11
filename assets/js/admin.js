var WCSN_Admin = {};
(function ($, window, wp, document, undefined) {
	'use strict';
	WCSN_Admin = {
		init: function () {
			$('.select-2').select2();

			$('.wcsn-select-date').datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				firstDay: 7,
				minDate: new Date()
			});
		},
		showWsnSerialKey: function (e) {
			e.preventDefault();
			var self = $(this);
			var id = self.data('serial-id');
			var nonce = self.data('nonce') || null;
			wp.ajax.send('wcsn_show_serial_key', {
				data: {
					serial_id: id,
					nonce: nonce
				},
				success: function (res) {
					console.log(res.message);
					$('#wsn-admin-serial-key-' + id).text(res.message);
				},
				error: function () {

				}
			});
		}
	};
	$(document).ready(WCSN_Admin.init);
	$(document).on('click', '.wsn-show-serial-key', WCSN_Admin.showWsnSerialKey);
})(jQuery, window, window.wp, document, undefined);
