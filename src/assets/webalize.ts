// @ts-nocheck
let webalizeTable = { \u00e1: 'a', \u00e4: 'a', \u010d: 'c', \u010f: 'd', \u00e9: 'e', \u011b: 'e', \u00ed: 'i', \u013e: 'l', \u0148: 'n', \u00f3: 'o', \u00f4: 'o', \u0159: 'r', \u0161: 's', \u0165: 't', \u00fa: 'u', \u016f: 'u', \u00fd: 'y', \u017e: 'z' };

/**
 * Converts string to web safe characters [a-z0-9-] text.
 * @param {string} s
 * @return {string}
 */
export function webalize(s) {
	s = s.toLowerCase();
	let res = '', ch;
	for (let i = 0; i < s.length; i++) {
		ch = webalizeTable[s.charAt(i)];
		res += ch ? ch : s.charAt(i);
	}
	return res.replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}
