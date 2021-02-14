/**
 * External dependencies
 */
const path = require( 'path' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const plugins = [];

function resolve( ...paths ) {
	return path.resolve( __dirname, ...paths );
}

defaultConfig.plugins.forEach( ( item ) => {
	if ( item.constructor.name.toLowerCase() === 'minicssextractplugin' ) {
		item.options.filename = '../css/[name].css';
		item.options.chunkFilename = '../css/[name].css';
		item.options.esModule = true;
	}

	if ( item.constructor.name.toLowerCase() === 'livereloadplugin' ) {
		return;
	}

	plugins.push( item );
} );

module.exports = {
	...defaultConfig,

	plugins,

	entry: {
		upgrader: resolve( 'src/admin/upgrader/index.js' ),
		'wc-serial-numbers-admin': resolve(
			'src/admin/wc-serial-numbers-admin.js'
		),
		'meta-boxes-order': resolve( 'src/admin/meta-boxes-order.js' ),
	},

	output: {
		filename: '[name].js',
		path: resolve( 'assets', 'js' ),
	},
};
