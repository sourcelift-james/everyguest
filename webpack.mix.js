const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
	.sass('resources/sass/app.scss', 'public/css')
	.copy('semantic/dist/semantic.min.js', 'public/js/semantic.min.js')
	.copy('semantic/dist/semantic.min.css', 'public/css/semantic.min.css')
	.copy('semantic/dist/themes/default/assets/fonts/icons.woff','public/css/themes/default/assets/fonts/icons.woff')
	.copy('semantic/dist/themes/default/assets/fonts/icons.woff2','public/css/themes/default/assets/fonts/icons.woff2')
	.copy('semantic/dist/themes/default/assets/fonts/icons.ttf','public/css/themes/default/assets/fonts/icons.ttf');
