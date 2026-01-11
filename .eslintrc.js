module.exports = {
	root: true,
	extends: ['plugin:@wordpress/eslint-plugin/recommended-with-formatting'],
	plugins: ['import'],
	env: {
		browser: true,
		es6: true,
	},
	globals: {
		wp: 'readonly',
		ajaxurl: 'readonly',
		FormData: 'readonly',
		window: true,
		document: true,
		jQuery: true,
	},
	rules: {
		'import/no-unresolved': 'off',
		'import/no-extraneous-dependencies': 'off',
		'no-console': 'off',
		'@wordpress/dependency-group': 'warn',
		'@wordpress/i18n-text-domain': [
			'error',
			{
				allowedTextDomain: 'wc-serial-numbers',
			},
		],
	},
};
