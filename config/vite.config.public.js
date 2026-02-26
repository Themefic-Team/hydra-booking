const { ViteMinifyPlugin } = require('vite-plugin-minify');
const { defineConfig } = require('vite');

module.exports = defineConfig({
	build: {
		rollupOptions: {
			input: {
				app: "./sass/js/app.js",
				main: "./sass/js/main.js",
				widget: "./sass/js/widget.js",
				registration: "./sass/js/registration.js",
				login: "./sass/js/login.js",
			},
			output: {
                entryFileNames: '[name].js',
				format: 'es',
				dir: "./assets/app/js",
                inlineDynamicImports: false,
			}
		}

	},
	plugins: [
		ViteMinifyPlugin({}),
	],
});