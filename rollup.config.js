import json from '@rollup/plugin-json';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import typescript from '@rollup/plugin-typescript';
import terser from '@rollup/plugin-terser';
import dts from 'rollup-plugin-dts';


// adds a header and calls initOnLoad() in the browser
function fix() {
	return {
		renderChunk(code) {
			code = `/*!
 * NetteForms - simple form validation.
 *
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
`
				+ code;
			code = code.replace('global.Nette = factory()', 'global.Nette?.noInit ? (global.Nette = factory()) : (global.Nette = factory()).initOnLoad()');
			code = code.replace(/\/\*\*.*\* \*\//s, '');
			return code;
		},
	};
}

function spaces2tabs() {
	return {
		renderChunk(code) {
			return code.replaceAll('    ', '\t');
		},
	};
}


export default [
	{
		input: 'src/assets/index.umd.ts',
		output: [
			{
				format: 'umd',
				name: 'Nette',
				dir: 'src/assets',
				entryFileNames: 'netteForms.js',
				generatedCode: 'es2015',
			},
			{
				format: 'umd',
				name: 'Nette',
				dir: 'src/assets',
				entryFileNames: 'netteForms.min.js',
				generatedCode: 'es2015',
				plugins: [
					terser(),
				],
			},
		],
		plugins: [
			json(),
			nodeResolve(),
			typescript(),
			fix(),
			spaces2tabs(),
		],
	},

	{
		input: 'src/assets/index.umd.ts',
		output: [{
			file: 'src/assets/netteForms.d.ts',
			format: 'es',
		}],
		plugins: [
			dts(),
			spaces2tabs(),
		],
	},
];
