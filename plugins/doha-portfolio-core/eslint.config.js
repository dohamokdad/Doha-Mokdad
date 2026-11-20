const wordpress = require( '@wordpress/eslint-plugin' );

module.exports = [
	{ ignores: [ 'build/**', 'node_modules/**', 'vendor/**' ] },
	...( Array.isArray( wordpress.configs.recommended )
		? wordpress.configs.recommended
		: [ wordpress.configs.recommended ] ),
	{
		// @wordpress/* packages are provided by WordPress at runtime (webpack externals).
		rules: {
			'import/no-unresolved': [ 'error', { ignore: [ '^@wordpress/' ] } ],
			'import/no-extraneous-dependencies': 'off',
		},
	},
];
