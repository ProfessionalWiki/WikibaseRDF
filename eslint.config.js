'use strict';

const { defineConfig, globalIgnores } = require( 'eslint/config' );
const wikimedia = require( 'eslint-config-wikimedia/flat' );

module.exports = defineConfig( [
	globalIgnores( [
		'coverage/',
		'vendor/'
	] ),

	// These run in Node and have their own config below
	{
		ignores: [ 'Gruntfile.js', 'eslint.config.js' ],
		extends: [ wikimedia( 'client/es6', 'mediawiki', 'jquery' ) ],
		rules: {
			'no-jquery/no-parse-html-literal': 'warn',
			'no-jquery/no-global-selector': 'warn',
			'no-jquery/no-class-state': 'warn'
		}
	},
	{
		files: [ 'Gruntfile.js', 'eslint.config.js' ],
		extends: [ wikimedia( 'server' ) ]
	}
] );
