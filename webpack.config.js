/**
 * External dependencies
 */
const path = require( 'path' );

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const LiveReloadPlugin = require( 'webpack-livereload-plugin' );
const plugins = [];

function resolve( ...paths ) {
	return path.resolve( __dirname, ...paths );
}

defaultConfig.plugins.forEach( ( item ) => {
	if ( item instanceof MiniCssExtractPlugin ) {
		item.options.filename = '../css/[name].css';
		item.options.chunkFilename = '../css/[name].css';
		item.options.esModule = true;
	}

	if ( item instanceof LiveReloadPlugin ) {
		return;
	}

	plugins.push( item );
} );

module.exports = {
	...defaultConfig,

	plugins,

	entry: {
		'wc-serial-numbers-admin': resolve(
			'src/admin/wc-serial-numbers-admin.js'
		),
		'meta-boxes-order': resolve( 'src/admin/meta-boxes-order.js' ),
		'upgrader': resolve( 'src/admin/upgrader/index.js' ),
	},

	output: {
		filename: '[name].js',
		path: resolve( 'assets', 'js' ),
	},
};
