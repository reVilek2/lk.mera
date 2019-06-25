const mix = require('laravel-mix');
const ImageminPlugin = require('imagemin-webpack-plugin').default;
const imageminMozjpeg = require('imagemin-mozjpeg');
const imageminPngquant = require('imagemin-pngquant');
const CopyWebpackPlugin = require('copy-webpack-plugin');

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

if (mix.inProduction()) {
    mix.version();
}

mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.font\.js/,
                use: [
                    'css-loader',
                    'webfonts-loader'
                ]
            }
        ]
    },
    resolve: {
        modules: [
            'node_modules'
        ]
    }
}).options({
    processCssUrls: false,
    postCss: [
        require('autoprefixer')({
            browsers: [
                'last 4 versions',
                'ie >= 10',
                'Android >= 4',
                'Safari >= 7',
                'iOS >= 7'
            ],
            remove: false
        })
    ],
    cssNano: {
        discardComments: {removeAll: true},
        discardDuplicates: true,
        discardOverridden: true,
        mergeRules: true,
    }
});

// .extract(['bootstrap', 'mdb', 'popper', 'jquery'])
mix.js('resources/js/auth.js', 'public/js')
    .js('resources/js/app.js', 'public/js')
    .sass('resources/sass/auth.scss', 'public/css')
    .sass('resources/sass/app.scss', 'public/css')
    .minify([
        'public/css/auth.css',
        'public/css/app.css',
        'public/js/auth.js',
        'public/js/app.js'
    ]);

mix.copy('resources/images/', 'public/images/', false);
mix.copy('resources/svg/', 'public/svg/', false);
mix.copy('resources/fonts/', 'public/fonts/', false);

mix.disableNotifications();

// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.preact(src, output); <-- Identical to mix.js(), but registers Preact compilation.
// mix.coffee(src, output); <-- Identical to mix.js(), but registers CoffeeScript compilation.
// mix.ts(src, output); <-- TypeScript support. Requires tsconfig.json to exist in the same folder as webpack.mix.js
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.less(src, output);
// mix.stylus(src, output);
// mix.postCss(src, output, [require('postcss-some-plugin')()]);
// mix.browserSync('my-site.test');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.babelConfig({}); <-- Merge extra Babel configuration (plugins, etc.) with Mix's default.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.dump(); <-- Dump the generated webpack config object t the console.
// mix.extend(name, handler) <-- Extend Mix's API with your own components.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   globalVueStyles: file, // Variables file to be imported in every component.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   terser: {}, // Terser-specific options. https://github.com/webpack-contrib/terser-webpack-plugin#options
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
