/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/.eslintrc' );

module.exports = {
	...defaultConfig,
	rules: {
		...defaultConfig.rules,
		'@wordpress/dependency-group': 'error',
		'no-alert': 'off',
		'no-unused-expressions': [
			'error',
			{
				allowShortCircuit: true,
			},
		],
	},
	globals: {
		Event: true,
		alert: true,
		woocommerce_admin_meta_boxes: true,
		wc_serial_numbers_meta_boxes_order_i10n: true,
		wc_serial_numbers_admin_i10n: true,
	},
};
