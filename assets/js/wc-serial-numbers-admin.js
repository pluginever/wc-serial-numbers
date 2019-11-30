(function ($, window, wp, document, undefined) {
	'use strict';
	var WC_Serial_Number_Admin = {
		$product_select:$('.product_id'),
		init:function () {
			this.$product_select.select2();
		}
	};

	$(document).ready(WC_Serial_Number_Admin.init);
});
