const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const MiniCSSExtractPlugin = require('mini-css-extract-plugin');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const CleanDeps = require('./clean-deps');
const path = require('path');
const isProduction = process.env.NODE_ENV === 'production';
const mode = isProduction ? 'production' : 'development';

module.exports = {
	...defaultConfig,
	entry: {
		'css/admin-style': './assets/css/admin-style.scss',
		'js/admin-script': './assets/js/admin-script.js',
	},
	output: {
		clean: true,
		path: path.resolve(__dirname, 'assets/dist'),
		// filename: 'js/[name].js',
		chunkFilename: 'chunks/[name].js',
		publicPath: path.resolve(__dirname, 'assets/dist'),
	},
	performance: {
		maxAssetSize: (isProduction ? 100 : 10000) * 1024,
		maxEntrypointSize: (isProduction ? 400 : 40000) * 1024,
		hints: 'warning',
	},
	module: {
		rules: [
			...defaultConfig.module.rules,
			{
				test: /\.svg$/,
				issuer: /\.(j|t)sx?$/,
				use: ['@svgr/webpack', 'url-loader'],
				type: 'javascript/auto',
			},
			{
				test: /\.svg$/,
				issuer: /\.(sc|sa|c)ss$/,
				type: 'asset/inline',
			},
			{
				test: /\.(bmp|png|jpe?g|gif)$/i,
				type: 'asset/resource',
				generator: {
					filename: 'images/[name].[hash:8][ext]',
				},
			}
		]
	},
	plugins: [
		...defaultConfig.plugins,
		// During rebuilds, all webpack assets that are not used anymore will be
		// removed automatically. There is an exception added in watch mode for
		// fonts and images. It is a known limitations:
		// https://github.com/johnagan/clean-webpack-plugin/issues/159
		new CleanWebpackPlugin({
			cleanAfterEveryBuildPatterns: ['!fonts/**', '!images/**'],
			// Prevent it from deleting webpack assets during builds that have
			// multiple configurations returned to the webpack config.
			cleanStaleWebpackAssets: false,
		}),
		// MiniCSSExtractPlugin to extract the CSS that's gets imported into JavaScript.
		new MiniCSSExtractPlugin({
			//esModule: false,
			filename: '[name].css',
			chunkFilename: '[id].css',
		}),
		// WP_NO_EXTERNALS global variable controls whether scripts' assets get
		// generated, and the default externals set.
		new RemoveEmptyScriptsPlugin(),
		//Removes wp-polyfill from CSS.
		new CleanDeps(),
	],
};
