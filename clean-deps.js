/**
 * Removes wp-polyfill from CSS assets extracted via @wordpress/dependency-extraction-webpack-plugin
 */

const { RawSource } = require('webpack-sources');
const path = require('path');

class CleanDeps {
	constructor(options) {
		this.options = options;
	}

	apply(compiler) {
		compiler.hooks.emit.tap('CleanExtractedDeps', (processAssets) => {

			for (const [entrypointName, entrypoint] of processAssets.entrypoints.entries()) {
				let compilationAssetMatch = false;
				let entryPointPath = false;
				Object.keys(processAssets.assets).forEach((processAssets) => {
					if (!compilationAssetMatch && processAssets.match(new RegExp(`/(style-)?${path.basename(entrypointName)}.asset.php$`))) {
						compilationAssetMatch = processAssets;
					}
					if (!entryPointPath && processAssets.match(new RegExp(`/(style-)?${path.basename(entrypointName)}.css$`))) {
						entryPointPath = processAssets;
					}
				});

				if (
					entrypoint.origins[0].request.match(/\.s?css$/) &&
					entryPointPath &&
					compilationAssetMatch
				) {
					const source = processAssets.assets[compilationAssetMatch].source();
					console.log('source', source);
					delete processAssets.assets[compilationAssetMatch];

					processAssets.assets[
						entryPointPath.replace('.css', '.asset.php')
					] = new RawSource(source.replace(/('|")wp-polyfill('|")[\s]*,?/, ''));
				}
			}
		});
	}
}

module.exports = CleanDeps;
