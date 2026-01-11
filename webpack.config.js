/**
 * External dependencies
 */
const baseConfig = require( '@byteever/scripts/config/webpack.config' );

module.exports = {
	...baseConfig,
	entry: {
		...baseConfig.entry,
		'js/admin-script': './assets/src/js/admin-script.js',
		'js/frontend-script': './assets/src/js/frontend-script.js',
		'css/admin-style': './assets/src/css/admin-style.scss',
		'css/frontend-style': './assets/src/css/frontend-style.scss',
	},
};
