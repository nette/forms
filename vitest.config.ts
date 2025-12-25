import { defineConfig } from 'vitest/config';

export default defineConfig({
	test: {
		environment: 'jsdom',
		include: ['tests/netteForms/spec/**/*.spec.js'],
		setupFiles: ['./tests/netteForms/setup.js'],
		globals: true,
	},
});
