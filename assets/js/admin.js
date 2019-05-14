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
		}
	};
	$(document).ready(WCSN_Admin.init);
})(jQuery, window, window.wp, document, undefined);
